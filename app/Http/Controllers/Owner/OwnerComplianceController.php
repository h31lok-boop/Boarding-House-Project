<?php

namespace App\Http\Controllers\Owner;

use App\Models\BoardingHouse;
use App\Models\ComplianceRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class OwnerComplianceController extends OwnerBaseController
{
    public function index(Request $request): View
    {
        $houseIds = $this->ownerBoardingHouseIds($request);

        $houses = $this->ownerBoardingHousesQuery($request)
            ->with([
                'approvals:id,boarding_house_id,remarks,reviewed_at',
                'accreditation:id,boarding_house_id,status,decision_log',
                'complianceRequirements:id,boarding_house_id,requirement_name,uploaded_file,submission_date,validation_status,validator_remarks,reviewed_at,created_at,updated_at',
            ])
            ->latest()
            ->get()
            ->map(function ($house) {
                return [
                    'house' => $house,
                    'compliance' => $this->complianceSummary($house),
                ];
            });

        $status = trim((string) $request->query('status', ''));
        $search = trim((string) $request->query('q', ''));

        $requirements = ComplianceRequirement::query()
            ->with('boardingHouse:id,name,owner_id')
            ->whereIn('boarding_house_id', $houseIds)
            ->when($status !== '', function ($query) use ($status) {
                $query->whereRaw('LOWER(validation_status) = ?', [strtolower($status)]);
            })
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.strtolower($search).'%';
                $query->where(function ($nested) use ($like) {
                    $nested->whereRaw('LOWER(requirement_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(uploaded_file) LIKE ?', [$like])
                        ->orWhereHas('boardingHouse', fn ($houseQuery) => $houseQuery->whereRaw('LOWER(name) LIKE ?', [$like]));
                });
            })
            ->latest('submission_date')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'approved' => ComplianceRequirement::query()->whereIn('boarding_house_id', $houseIds)->whereRaw('LOWER(validation_status) = ?', ['approved'])->count(),
            'pending' => ComplianceRequirement::query()->whereIn('boarding_house_id', $houseIds)->whereIn('validation_status', ['pending', 'under_review'])->count(),
            'rejected' => ComplianceRequirement::query()->whereIn('boarding_house_id', $houseIds)->whereRaw('LOWER(validation_status) = ?', ['rejected'])->count(),
            'total' => ComplianceRequirement::query()->whereIn('boarding_house_id', $houseIds)->count(),
        ];

        return view('owner.compliance.index', [
            'houses' => $houses,
            'houseOptions' => BoardingHouse::query()->whereIn('id', $houseIds)->orderBy('name')->get(['id', 'name']),
            'requirements' => $requirements,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'q' => $search,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedDocument($request);
        $house = BoardingHouse::query()->findOrFail($validated['boarding_house_id']);
        $this->ensureOwnsBoardingHouse($request, $house);

        ComplianceRequirement::create([
            'boarding_house_id' => $house->id,
            'submitted_by' => $request->user()->id,
            'requirement_name' => $validated['requirement_name'],
            'uploaded_file' => $request->file('uploaded_file')->store('compliance-documents', 'public'),
            'submission_date' => now()->toDateString(),
            'validation_status' => 'pending',
        ]);

        return redirect()->route($this->indexRouteName($request))->with('success', 'Compliance document uploaded.');
    }

    public function update(Request $request, ComplianceRequirement $requirement): RedirectResponse
    {
        $requirement = $this->ensureOwnsComplianceRequirement($request, $requirement);
        $validated = $this->validatedDocument($request, requireFile: false);

        $payload = [
            'requirement_name' => $validated['requirement_name'],
            'validation_status' => 'pending',
            'validator_remarks' => null,
            'reviewed_at' => null,
            'submission_date' => now()->toDateString(),
        ];

        if ($request->hasFile('uploaded_file')) {
            Storage::disk('public')->delete($requirement->uploaded_file);
            $payload['uploaded_file'] = $request->file('uploaded_file')->store('compliance-documents', 'public');
        }

        $requirement->update($payload);

        return redirect()->route($this->indexRouteName($request))->with('success', 'Compliance document updated and resubmitted.');
    }

    public function destroy(Request $request, ComplianceRequirement $requirement): RedirectResponse
    {
        $requirement = $this->ensureOwnsComplianceRequirement($request, $requirement);
        Storage::disk('public')->delete($requirement->uploaded_file);
        $requirement->delete();

        return redirect()->route($this->indexRouteName($request))->with('success', 'Compliance document deleted.');
    }

    public function download(Request $request, ComplianceRequirement $requirement): BinaryFileResponse
    {
        $requirement = $this->ensureOwnsComplianceRequirement($request, $requirement);
        abort_unless(Storage::disk('public')->exists($requirement->uploaded_file), 404);

        return response()->download(
            Storage::disk('public')->path($requirement->uploaded_file),
            basename($requirement->uploaded_file)
        );
    }

    public function submitAll(Request $request): RedirectResponse
    {
        $houseIds = $this->ownerBoardingHouseIds($request);

        ComplianceRequirement::query()
            ->whereIn('boarding_house_id', $houseIds)
            ->whereIn('validation_status', ['draft', 'rejected'])
            ->update([
                'validation_status' => 'pending',
                'validator_remarks' => null,
                'reviewed_at' => null,
                'submission_date' => now()->toDateString(),
                'updated_at' => now(),
            ]);

        $this->ownerBoardingHousesQuery($request)
            ->whereIn('approval_status', ['draft', 'rejected'])
            ->update([
                'approval_status' => 'pending',
                'status' => 'pending',
                'updated_at' => now(),
            ]);

        return redirect()->route($this->indexRouteName($request))->with('success', 'Compliance package submitted for review.');
    }

    private function validatedDocument(Request $request, bool $requireFile = true): array
    {
        return $request->validate([
            'boarding_house_id' => ['required', 'integer', 'exists:boarding_houses,id'],
            'requirement_name' => ['required', 'string', 'max:255'],
            'uploaded_file' => [$requireFile ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:10240'],
            'validation_status' => ['nullable', Rule::in(['draft', 'pending', 'under_review', 'approved', 'rejected'])],
        ]);
    }

    private function indexRouteName(Request $request): string
    {
        return $request->routeIs('admin.*') ? 'admin.compliance.index' : 'owner.compliance.index';
    }
}
