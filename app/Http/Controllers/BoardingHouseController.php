<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use App\Support\SystemActionLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BoardingHouseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $filterStatus = request('status'); // available | occupied | all/blank

        $houses = BoardingHouse::withCount('tenants')
            ->when($filterStatus === 'available', function ($q) {
                $q->having('tenants_count', '<', \DB::raw('capacity'));
            })
            ->when($filterStatus === 'occupied', function ($q) {
                // treat any occupancy (>0) as occupied
                $q->having('tenants_count', '>', 0);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends(['status' => $filterStatus]);

        return view('admin.boarding-houses.index', compact('houses'));
    }

    public function create()
    {
        return view('admin.boarding-houses.create');
    }

    public function store(Request $request)
    {
        $requiresDetails = $request->wantsJson();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => [Rule::requiredIf($requiresDetails), 'string'],
            'house_rules' => ['nullable', 'string'],
            'landlord_info' => [Rule::requiredIf($requiresDetails), 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'monthly_payment' => [Rule::requiredIf($requiresDetails), 'numeric', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'approval_status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'exterior_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'room_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'cr_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'kitchen_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

        $data['capacity'] = $data['capacity'] ?? 1;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['approval_status'] = $data['approval_status'] ?? 'approved';
        $data = $this->sanitizeBoardingHouseInput($data);

        if ($request->user()?->isOwner() && empty($data['owner_id'])) {
            $data['owner_id'] = $request->user()->id;
        }

        foreach (['exterior_image', 'room_image', 'cr_image', 'kitchen_image'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('boarding-houses', 'public');
            }
        }

        $house = BoardingHouse::create($data);
        SystemActionLogger::log($request->user()?->id, 'create', 'boarding_house', (int) $house->id, [
            'source' => 'admin',
            'name' => $house->name,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Boarding house added.',
                'house' => [
                    'id' => $house->id,
                    'name' => $house->name,
                    'landlord_info' => $house->landlord_info,
                    'address' => $house->address,
                    'latitude' => $house->latitude,
                    'longitude' => $house->longitude,
                    'contact_name' => $house->contact_name,
                    'contact_phone' => $house->contact_phone,
                    'monthly_payment' => $house->monthly_payment,
                    'description' => $house->description,
                    'house_rules' => $house->house_rules,
                    'is_active' => $house->is_active,
                    'approval_status' => $house->approval_status,
                    'status_label' => $house->is_active ? 'Active' : 'Inactive',
                    'exterior_url' => $house->exterior_image ? Storage::url($house->exterior_image) : '',
                    'room_url' => $house->room_image ? Storage::url($house->room_image) : '',
                    'cr_url' => $house->cr_image ? Storage::url($house->cr_image) : '',
                    'kitchen_url' => $house->kitchen_image ? Storage::url($house->kitchen_image) : '',
                    'update_url' => route('admin.boarding-houses.update', $house),
                ],
            ]);
        }

        return redirect()->route('admin.boarding-houses.index')->with('success', 'Boarding house created.');
    }

    public function edit(BoardingHouse $boarding_house)
    {
        return view('admin.boarding-houses.edit', ['house' => $boarding_house]);
    }

    public function show(BoardingHouse $boarding_house)
    {
        $boarding_house->loadCount('tenants');
        return view('admin.boarding-houses.show', ['house' => $boarding_house]);
    }

    public function update(Request $request, BoardingHouse $boarding_house)
    {
        $requiresDetails = $request->wantsJson();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string'],
            'house_rules' => ['nullable', 'string'],
            'landlord_info' => [Rule::requiredIf($requiresDetails), 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'monthly_payment' => [Rule::requiredIf($requiresDetails), 'numeric', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'approval_status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'exterior_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'room_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'cr_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'kitchen_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

        $data['capacity'] = $data['capacity'] ?? $boarding_house->capacity ?? 1;
        $data['is_active'] = $request->boolean('is_active', $boarding_house->is_active);
        $data = $this->sanitizeBoardingHouseInput($data);

        $removeMap = [
            'remove_exterior_image' => 'exterior_image',
            'remove_room_image' => 'room_image',
            'remove_cr_image' => 'cr_image',
            'remove_kitchen_image' => 'kitchen_image',
        ];

        foreach ($removeMap as $removeField => $imageField) {
            if ($request->boolean($removeField)) {
                if ($boarding_house->{$imageField}) {
                    Storage::disk('public')->delete($boarding_house->{$imageField});
                }
                $data[$imageField] = null;
            }
        }

        foreach (['exterior_image', 'room_image', 'cr_image', 'kitchen_image'] as $field) {
            if ($request->hasFile($field)) {
                if ($boarding_house->{$field}) {
                    Storage::disk('public')->delete($boarding_house->{$field});
                }
                $data[$field] = $request->file($field)->store('boarding-houses', 'public');
            }
        }

        $boarding_house->update($data);
        SystemActionLogger::log($request->user()?->id, 'update', 'boarding_house', (int) $boarding_house->id, [
            'source' => 'admin',
            'name' => $boarding_house->name,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Boarding house updated.',
                'house' => [
                    'id' => $boarding_house->id,
                    'name' => $boarding_house->name,
                    'landlord_info' => $boarding_house->landlord_info,
                    'address' => $boarding_house->address,
                    'latitude' => $boarding_house->latitude,
                    'longitude' => $boarding_house->longitude,
                    'contact_name' => $boarding_house->contact_name,
                    'contact_phone' => $boarding_house->contact_phone,
                    'monthly_payment' => $boarding_house->monthly_payment,
                    'description' => $boarding_house->description,
                    'house_rules' => $boarding_house->house_rules,
                    'is_active' => $boarding_house->is_active,
                    'approval_status' => $boarding_house->approval_status,
                    'status_label' => $boarding_house->is_active ? 'Active' : 'Inactive',
                    'exterior_url' => $boarding_house->exterior_image ? Storage::url($boarding_house->exterior_image) : '',
                    'room_url' => $boarding_house->room_image ? Storage::url($boarding_house->room_image) : '',
                    'cr_url' => $boarding_house->cr_image ? Storage::url($boarding_house->cr_image) : '',
                    'kitchen_url' => $boarding_house->kitchen_image ? Storage::url($boarding_house->kitchen_image) : '',
                    'update_url' => route('admin.boarding-houses.update', $boarding_house),
                ],
            ]);
        }

        return redirect()->route('admin.boarding-houses.index')->with('success', 'Boarding house updated.');
    }

    public function destroy(BoardingHouse $boarding_house)
    {
        $id = (int) $boarding_house->id;
        $name = $boarding_house->name;
        $boarding_house->delete();
        SystemActionLogger::log(request()->user()?->id, 'delete', 'boarding_house', $id, [
            'source' => 'admin',
            'name' => $name,
        ]);
        return redirect()->route('admin.boarding-houses.index')->with('success', 'Boarding house deleted.');
    }

    private function sanitizeBoardingHouseInput(array $data): array
    {
        foreach (['name', 'address', 'description', 'house_rules', 'landlord_info', 'contact_name', 'contact_phone'] as $field) {
            if (! array_key_exists($field, $data) || $data[$field] === null) {
                continue;
            }
            $data[$field] = trim(strip_tags((string) $data[$field]));
        }

        return $data;
    }
}
