<?php

namespace App\Http\Controllers\Owner;

use App\Models\Booking;
use App\Models\Inquiry;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OwnerInquiryController extends OwnerBaseController
{
    public function index(Request $request): View
    {
        $houseIds = $this->ownerBoardingHouseIds($request);
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        $queries = Inquiry::query()
            ->with(['user:id,name,email,phone,contact_number', 'boardingHouse:id,name', 'boardingHouse.rooms:id,boarding_house_id,room_no,name,available_slots,status'])
            ->whereIn('boarding_house_id', $houseIds)
            ->when($status !== '', fn ($query) => $query->whereRaw('LOWER(status) = ?', [strtolower($status)]))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.strtolower($search).'%';
                $query->where(function ($nested) use ($like) {
                    $nested->whereRaw('LOWER(message) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(response_message) LIKE ?', [$like])
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->whereRaw('LOWER(name) LIKE ?', [$like])->orWhereRaw('LOWER(email) LIKE ?', [$like]))
                        ->orWhereHas('boardingHouse', fn ($houseQuery) => $houseQuery->whereRaw('LOWER(name) LIKE ?', [$like]));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $statsBase = Inquiry::query()->whereIn('boarding_house_id', $houseIds);
        $stats = [
            'new' => (clone $statsBase)->whereIn('status', ['new', 'pending'])->count(),
            'pending' => (clone $statsBase)->whereIn('status', ['pending', 'in_progress'])->count(),
            'confirmed' => (clone $statsBase)->whereIn('status', ['confirmed', 'replied'])->count(),
            'declined' => (clone $statsBase)->whereIn('status', ['declined', 'closed'])->count(),
            'total' => (clone $statsBase)->count(),
        ];

        return view('owner.inquiries.index', [
            'inquiries' => $queries,
            'stats' => $stats,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function update(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $inquiry = $this->ensureOwnsInquiry($request, $inquiry);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['new', 'pending', 'in_progress', 'accepted', 'declined', 'confirmed', 'replied', 'closed'])],
            'response_message' => ['nullable', 'string', 'max:2000'],
        ]);

        $responseMessage = trim(strip_tags((string) ($validated['response_message'] ?? '')));
        if (in_array($validated['status'], ['replied', 'closed', 'accepted', 'declined', 'confirmed'], true) && $responseMessage === '' && ! filled($inquiry->response_message)) {
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

        return redirect()->route($this->indexRouteName($request))->with('success', 'Inquiry updated.');
    }

    public function storeReservation(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $inquiry = $this->ensureOwnsInquiry($request, $inquiry);

        $validated = $request->validate([
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'check_in_date' => ['nullable', 'date'],
            'check_out_date' => ['nullable', 'date', 'after_or_equal:check_in_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $room = null;
        if (! empty($validated['room_id'])) {
            $room = $inquiry->boardingHouse->rooms()->findOrFail($validated['room_id']);
        }

        DB::transaction(function () use ($inquiry, $validated, $room) {
            $reservation = Reservation::create([
                'user_id' => $inquiry->user_id,
                'boarding_house_id' => $inquiry->boarding_house_id,
                'room_id' => $room?->id,
                'check_in_date' => $validated['check_in_date'] ?? null,
                'check_out_date' => $validated['check_out_date'] ?? null,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? $inquiry->message,
            ]);

            if ($room) {
                Booking::create([
                    'reservation_id' => $reservation->id,
                    'room_id' => $room->id,
                    'user_id' => $inquiry->user_id,
                    'status' => 'Pending',
                    'start_date' => $reservation->check_in_date,
                    'end_date' => $reservation->check_out_date,
                    'notes' => $reservation->notes,
                ]);
            }

            $inquiry->update([
                'status' => 'confirmed',
                'response_message' => $inquiry->response_message ?: 'Reservation request created for owner review.',
                'replied_at' => now(),
            ]);
        });

        return redirect()
            ->route($request->routeIs('admin.*') ? 'admin.bookings.index' : 'owner.bookings.index')
            ->with('success', 'Reservation created from inquiry.');
    }

    public function destroy(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $inquiry = $this->ensureOwnsInquiry($request, $inquiry);
        $inquiry->delete();

        return redirect()->route($this->indexRouteName($request))->with('success', 'Inquiry deleted.');
    }

    private function indexRouteName(Request $request): string
    {
        return $request->routeIs('admin.*') ? 'admin.inquiries.index' : 'owner.inquiries.index';
    }
}
