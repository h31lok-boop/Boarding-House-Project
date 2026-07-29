<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\ReservationLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationLifecycleService $reservationLifecycleService,
    ) {}

    public function store(Request $request, BoardingHouse $boardingHouse)
    {
        $this->reservationLifecycleService->expireStaleReservations();

        // Daily limit: one reservation request per user per day.
        $alreadySentToday = Reservation::where('user_id', $request->user()->id)
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadySentToday) {
            return back()->with(
                'reservation_limit',
                'You already sent a reservation request today. Please try again tomorrow.'
            );
        }

        $data = $request->validate([
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'check_in_date' => ['nullable', 'date'],
            'check_out_date' => ['nullable', 'date', 'after_or_equal:check_in_date'],
            'occupants' => ['nullable', 'integer', 'min:1', 'max:20'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'terms_accepted' => ['required', 'accepted'],
        ]);

        $selectedRoom = null;
        if (! empty($data['room_id'])) {
            $belongsToHouse = DB::table('rooms')
                ->leftJoin('room_categories', 'room_categories.id', '=', 'rooms.room_category_id')
                ->where('rooms.id', $data['room_id'])
                ->where(function ($query) use ($boardingHouse) {
                    $query->where('rooms.boarding_house_id', $boardingHouse->id)
                        ->orWhere('room_categories.boarding_house_id', $boardingHouse->id);
                })
                ->exists();

            if (! $belongsToHouse) {
                return back()
                    ->withErrors(['room_id' => 'Selected room does not belong to this boarding house.'])
                    ->withInput();
            }

            $selectedRoom = Room::query()->find($data['room_id']);

            if (! $selectedRoom) {
                return back()
                    ->withErrors(['room_id' => 'Selected room is no longer available.'])
                    ->withInput();
            }

            $roomStatus = strtolower((string) ($selectedRoom->status ?? 'available'));
            $availableSlots = (int) ($selectedRoom->available_slots ?? ($selectedRoom->capacity ?? 1));

            if ($availableSlots < 1 || in_array($roomStatus, ['occupied', 'maintenance'], true)) {
                return back()
                    ->withErrors(['room_id' => 'Selected room is no longer available.'])
                    ->withInput();
            }
        }

        $notes = isset($data['notes']) ? strip_tags(trim((string) $data['notes'])) : null;
        $tenant = $request->user()->loadMissing('tenantProfile');
        $price = $selectedRoom?->price
            ?? $boardingHouse->effective_price
            ?? (is_numeric($boardingHouse->price ?? null) ? (float) $boardingHouse->price : null);

        $reservation = DB::transaction(function () use ($tenant, $boardingHouse, $data, $notes, $selectedRoom, $price) {
            if ($selectedRoom) {
                $selectedRoom->refresh();
                $this->reservationLifecycleService->holdSelectedRoom($selectedRoom);
            }

            $reservation = Reservation::create([
                'user_id' => $tenant->id,
                'boarding_house_id' => $boardingHouse->id,
                'room_id' => $data['room_id'] ?? null,
                'check_in_date' => $data['check_in_date'] ?? null,
                'check_out_date' => $data['check_out_date'] ?? null,
                'occupants' => $data['occupants'] ?? 1,
                'emergency_contact_name' => $data['emergency_contact_name']
                    ?? $tenant->tenantProfile?->emergency_contact_name,
                'emergency_contact_number' => $data['emergency_contact_number']
                    ?? $tenant->tenantProfile?->emergency_contact_number
                    ?? $tenant->emergency_contact,
                'total_amount' => $price ?? 0,
                'payment_status' => Schema::hasColumn('reservations', 'payment_status') ? 'unpaid' : null,
                'terms_accepted_at' => Schema::hasColumn('reservations', 'terms_accepted_at') ? now() : null,
                'expires_at' => Schema::hasColumn('reservations', 'expires_at') ? now()->addHours(48) : null,
                'notes' => $notes,
                'status' => 'pending',
            ]);

            $this->reservationLifecycleService->notifyReservationSubmitted($reservation->loadMissing('boardingHouse'));

            return $reservation;
        });

        return redirect()
            ->route('user.reservations.index')
            ->with('success', 'Reservation request submitted.');
    }
}
