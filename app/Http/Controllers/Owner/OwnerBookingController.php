<?php

namespace App\Http\Controllers\Owner;

use App\Models\BoardingHouseApplication;
use App\Models\Booking;
use App\Models\Reservation;
use App\Support\TenantOccupancyManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OwnerBookingController extends OwnerBaseController
{
    public function index(Request $request): View
    {
        $houseIds = $this->ownerBoardingHouseIds($request);
        $roomIds = $request->user()->ownedBoardingHouses()->with('rooms:id,boarding_house_id')->get()
            ->flatMap(fn ($house) => $house->rooms->pluck('id'))
            ->unique()
            ->values();

        $reservations = Reservation::query()
            ->with(['user:id,name,email', 'boardingHouse:id,name', 'room:id,boarding_house_id,room_no', 'booking'])
            ->whereIn('boarding_house_id', $houseIds)
            ->latest()
            ->paginate(10, ['*'], 'reservations_page');

        $bookings = Booking::query()
            ->with(['user:id,name,email', 'room.boardingHouse:id,name'])
            ->whereIn('room_id', $roomIds)
            ->latest()
            ->paginate(10, ['*'], 'bookings_page');

        return view('owner.bookings.index', [
            'reservations' => $reservations,
            'bookings' => $bookings,
        ]);
    }

    public function updateReservation(Request $request, Reservation $reservation, TenantOccupancyManager $occupancyManager): RedirectResponse
    {
        $reservation = $this->ensureOwnsReservation($request, $reservation);
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'confirmed', 'rejected', 'cancelled'])],
            'owner_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $previousStatus = strtolower((string) $reservation->status);
        $nextStatus = strtolower((string) $validated['status']);

        if ($nextStatus === 'confirmed' && $reservation->room && (int) $reservation->room->available_slots <= 0 && $previousStatus !== 'confirmed') {
            return back()->withErrors(['status' => 'This room has no available slots left.']);
        }

        DB::transaction(function () use ($request, $reservation, $validated, $nextStatus, $previousStatus, $occupancyManager) {
            $reservation->update([
                'status' => $nextStatus,
                'owner_notes' => trim(strip_tags((string) ($validated['owner_notes'] ?? ''))),
                'processed_at' => now(),
                'processed_by' => $request->user()->id,
            ]);

            $booking = $reservation->booking ?: Booking::query()->create([
                'reservation_id' => $reservation->id,
                'room_id' => $reservation->room_id,
                'user_id' => $reservation->user_id,
                'status' => 'Pending',
                'start_date' => $reservation->check_in_date,
                'end_date' => $reservation->check_out_date,
                'notes' => $reservation->notes,
            ]);

            $booking->update([
                'status' => $this->bookingStatusFromReservationStatus($nextStatus),
                'notes' => $this->mergedNotes($reservation->notes, $reservation->owner_notes),
            ]);

            if ($nextStatus === 'confirmed') {
                $occupancyManager->assign(
                    $reservation->user,
                    $reservation->boardingHouse,
                    $reservation->room,
                    $reservation->check_in_date ?? now()
                );

                BoardingHouseApplication::query()
                    ->where('user_id', $reservation->user_id)
                    ->where('boarding_house_id', $reservation->boarding_house_id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->update(['status' => 'approved']);
            }

            $this->syncRoomInventoryFromStatusChange($reservation->room, $previousStatus, $nextStatus);
        });

<<<<<<< Updated upstream
        return redirect()
            ->route($request->routeIs('admin.*') ? 'admin.bookings.index' : 'owner.bookings.index')
            ->with('success', 'Reservation updated.');
=======
        return redirect()->route('admin.bookings.index')->with('success', 'Reservation updated.');
>>>>>>> Stashed changes
    }

    public function updateBooking(Request $request, Booking $booking, TenantOccupancyManager $occupancyManager): RedirectResponse
    {
        $booking = $this->ensureOwnsBooking($request, $booking);
        $validated = $request->validate([
            'status' => ['required', Rule::in(['Pending', 'Processing', 'Confirmed', 'Cancelled'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $previousStatus = strtolower((string) $booking->status);
        $nextStatus = strtolower((string) $validated['status']);

        if ($nextStatus === 'confirmed' && $booking->room && (int) $booking->room->available_slots <= 0 && $previousStatus !== 'confirmed') {
            return back()->withErrors(['status' => 'This room has no available slots left.']);
        }

        DB::transaction(function () use ($request, $booking, $validated, $nextStatus, $previousStatus, $occupancyManager) {
            $booking->update([
                'status' => $validated['status'],
                'notes' => trim(strip_tags((string) ($validated['notes'] ?? ''))),
            ]);

            if ($booking->reservation) {
                $booking->reservation->update([
                    'status' => $this->reservationStatusFromBookingStatus($nextStatus),
                    'owner_notes' => $booking->notes,
                    'processed_at' => now(),
                    'processed_by' => $request->user()->id,
                ]);
            }

            if ($nextStatus === 'confirmed' && $booking->room?->boardingHouse && $booking->user) {
                $occupancyManager->assign(
                    $booking->user,
                    $booking->room->boardingHouse,
                    $booking->room,
                    $booking->start_date ?? now()
                );

                BoardingHouseApplication::query()
                    ->where('user_id', $booking->user_id)
                    ->where('boarding_house_id', $booking->room->boardingHouse->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->update(['status' => 'approved']);
            }

            $this->syncRoomInventoryFromStatusChange($booking->room, $previousStatus, $nextStatus);
        });

<<<<<<< Updated upstream
        return redirect()
            ->route($request->routeIs('admin.*') ? 'admin.bookings.index' : 'owner.bookings.index')
            ->with('success', 'Booking updated.');
=======
        return redirect()->route('admin.bookings.index')->with('success', 'Booking updated.');
>>>>>>> Stashed changes
    }

    private function bookingStatusFromReservationStatus(string $status): string
    {
        return match ($status) {
            'confirmed' => 'Confirmed',
            'pending' => 'Pending',
            default => 'Cancelled',
        };
    }

    private function reservationStatusFromBookingStatus(string $status): string
    {
        return match ($status) {
            'confirmed' => 'confirmed',
            'processing' => 'pending',
            'pending' => 'pending',
            default => 'cancelled',
        };
    }

    private function mergedNotes(?string $tenantNotes, ?string $ownerNotes): ?string
    {
        $parts = collect([
            filled($tenantNotes) ? 'Tenant: '.trim($tenantNotes) : null,
            filled($ownerNotes) ? 'Owner: '.trim($ownerNotes) : null,
        ])->filter()->values();

        return $parts->isEmpty() ? null : $parts->implode(' | ');
    }

    private function syncRoomInventoryFromStatusChange($room, string $previousStatus, string $nextStatus): void
    {
        if (! $room) {
            return;
        }

        $previousConfirmed = $previousStatus === 'confirmed';
        $nextConfirmed = $nextStatus === 'confirmed';

        if ($previousConfirmed === $nextConfirmed) {
            return;
        }

        if ($nextConfirmed) {
            $room->available_slots = max(0, (int) $room->available_slots - 1);
            if ($room->status !== 'Unavailable') {
                $room->status = (int) $room->available_slots > 0 ? 'Available' : 'Reserved';
            }
        } else {
            $room->available_slots = min((int) $room->capacity, (int) $room->available_slots + 1);
            if ($room->status !== 'Unavailable') {
                $room->status = (int) $room->available_slots > 0 ? 'Available' : 'Occupied';
            }
        }

        $room->save();

        if ($room->boardingHouse) {
            $this->refreshBoardingHouseAvailability($room->boardingHouse);
        }
    }
}
