<?php

namespace App\Http\Controllers\Owner;

use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OwnerInquiryController extends OwnerBaseController
{
    public function index(Request $request): View
    {
        $queries = Inquiry::query()
            ->with(['user:id,name,email', 'boardingHouse:id,name'])
            ->whereIn('boarding_house_id', $this->ownerBoardingHouseIds($request))
            ->latest()
            ->paginate(12);

        return view('owner.inquiries.index', [
            'inquiries' => $queries,
        ]);
    }

    public function update(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $inquiry = $this->ensureOwnsInquiry($request, $inquiry);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'in_progress', 'replied', 'closed'])],
            'response_message' => ['nullable', 'string', 'max:2000'],
        ]);

        $responseMessage = trim(strip_tags((string) ($validated['response_message'] ?? '')));
        if (in_array($validated['status'], ['replied', 'closed'], true) && $responseMessage === '') {
            return back()->withErrors(['response_message' => 'A response message is required for replied or closed inquiries.']);
        }

        $payload = [
            'status' => $validated['status'],
        ];

        if ($responseMessage !== '') {
            $payload['response_message'] = $responseMessage;
            $payload['responded_by'] = $request->user()->id;
            $payload['replied_at'] = now();
        }

        $inquiry->update($payload);

        return redirect()->route('admin.inquiries.index')->with('success', 'Inquiry updated.');
    }
}
