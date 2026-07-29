<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReservationLifecycleService
{
    /**
     * @return Collection<int, Reservation>
     */
    public function expireStaleReservations(): Collection
    {
        if (! Schema::hasTable('reservations') || ! Schema::hasColumn('reservations', 'expires_at')) {
            return collect();
        }

        $expirableStatuses = ['pending', 'reserved'];

        $reservations = Reservation::with(['boardingHouse', 'room'])
            ->whereIn(DB::raw('LOWER(status)'), $expirableStatuses)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->when(Schema::hasColumn('reservations', 'payment_status'), function ($query) {
                $query->where(function ($paymentQuery) {
                    $paymentQuery->whereNull('payment_status')
                        ->orWhereNotIn(DB::raw('LOWER(payment_status)'), ['paid', 'approved', 'settled']);
                });
            })
            ->get();

        return $reservations->filter(fn (Reservation $reservation) => $this->expireReservation($reservation));
    }

    public function expireReservation(Reservation $reservation, ?Carbon $expiredAt = null): bool
    {
        $expiredAt ??= now();
        $status = strtolower((string) ($reservation->status ?? ''));
        $paymentStatus = strtolower((string) ($reservation->payment_status ?? ''));

        if (! in_array($status, ['pending', 'reserved'], true)) {
            return false;
        }

        if (in_array($paymentStatus, ['paid', 'approved', 'settled'], true)) {
            return false;
        }

        return DB::transaction(function () use ($reservation, $expiredAt) {
            $reservation->refresh();
            $currentStatus = strtolower((string) ($reservation->status ?? ''));
            $currentPaymentStatus = strtolower((string) ($reservation->payment_status ?? ''));

            if (! in_array($currentStatus, ['pending', 'reserved'], true)) {
                return false;
            }

            if (in_array($currentPaymentStatus, ['paid', 'approved', 'settled'], true)) {
                return false;
            }

            $this->releaseHeldRoom($reservation, $expiredAt);

            $reservation->forceFill([
                'status' => 'expired',
                'expired_at' => $expiredAt,
                'payment_status' => Schema::hasColumn('reservations', 'payment_status')
                    ? 'expired'
                    : $reservation->payment_status,
                'notes' => $this->appendNote($reservation->notes, 'Reservation expired on '.$expiredAt->format('M d, Y h:i A').'.'),
            ])->save();

            $this->notifyReservationExpired($reservation);

            return true;
        });
    }

    public function releaseHeldRoom(Reservation $reservation, ?Carbon $releasedAt = null): void
    {
        if (! $reservation->room_id || ! Schema::hasTable('rooms')) {
            return;
        }

        if (Schema::hasColumn('reservations', 'room_released_at') && $reservation->room_released_at) {
            return;
        }

        $room = Room::query()->lockForUpdate()->find($reservation->room_id);
        if (! $room) {
            return;
        }

        $availableSlots = max((int) ($room->available_slots ?? 0), 0) + 1;
        $room->available_slots = $availableSlots;

        $currentStatus = strtolower((string) ($room->status ?? 'available'));
        if (! in_array($currentStatus, ['occupied', 'maintenance'], true)) {
            $room->status = 'Available';
        }

        $room->save();

        if (Schema::hasColumn('reservations', 'room_released_at')) {
            $reservation->room_released_at = $releasedAt ?? now();
            $reservation->save();
        }
    }

    public function holdSelectedRoom(Room $room): void
    {
        $blockedStatuses = ['occupied', 'maintenance'];
        $currentStatus = strtolower((string) ($room->status ?? 'available'));
        $availableSlots = (int) ($room->available_slots ?? ($room->capacity ?? 1));

        if ($availableSlots < 1 || in_array($currentStatus, $blockedStatuses, true)) {
            abort(422, 'Selected room is no longer available.');
        }

        $room->available_slots = max(0, $availableSlots - 1);
        $room->status = $room->available_slots > 0 ? 'Available' : 'Reserved';
        $room->save();
    }

    public function relevantReservationForUser(int $userId): ?Reservation
    {
        return Reservation::with(['boardingHouse', 'room'])
            ->where('user_id', $userId)
            ->whereNotIn(DB::raw('LOWER(status)'), ['cancelled', 'canceled', 'rejected'])
            ->latest('id')
            ->first();
    }

    public function canProcessPayment(?Reservation $reservation): bool
    {
        if (! $reservation) {
            return true;
        }

        return strtolower((string) ($reservation->status ?? '')) !== 'expired';
    }

    public function notifyReservationSubmitted(Reservation $reservation): void
    {
        $title = 'Reservation submitted';
        $message = 'A new room reservation request was submitted for '.($reservation->boardingHouse?->name ?? 'your boarding house').'.';

        $this->notify($reservation->user_id, 'reservation', 'reservation:'.$reservation->id.':submitted', $title, 'Your reservation request was submitted successfully.');
        $this->notify($reservation->boardingHouse?->owner_id, 'reservation', 'reservation:'.$reservation->id.':owner-submitted', $title, $message);
    }

    public function notifyReservationExpired(Reservation $reservation): void
    {
        $houseName = $reservation->boardingHouse?->name ?? 'the boarding house';

        $this->notify(
            $reservation->user_id,
            'reservation',
            'reservation:'.$reservation->id.':expired:user',
            'Reservation expired',
            'Your reservation for '.$houseName.' expired after 48 hours without approval or payment.'
        );

        $this->notify(
            $reservation->boardingHouse?->owner_id,
            'reservation',
            'reservation:'.$reservation->id.':expired:owner',
            'Reservation expired',
            'A reservation for '.$houseName.' expired and the room was released back to available status.'
        );
    }

    private function notify(?int $userId, string $type, string $referenceId, string $title, string $message): void
    {
        if (! $userId || ! Schema::hasTable('notifications')) {
            return;
        }

        $now = now();
        $match = [
            'user_id' => $userId,
            'type' => $type,
        ];

        if (Schema::hasColumn('notifications', 'reference_id')) {
            $match['reference_id'] = $referenceId;
        } else {
            $match['title'] = $title;
            $match['message'] = $message;
        }

        $values = [
            'title' => $title,
            'message' => $message,
            'data' => json_encode(['reference_id' => $referenceId]),
            'updated_at' => $now,
        ];

        $existing = DB::table('notifications')->where($match)->first();
        if ($existing) {
            DB::table('notifications')->where('id', $existing->id)->update($values);

            return;
        }

        DB::table('notifications')->insert($match + $values + [
            'is_read' => false,
            'read_at' => null,
            'created_at' => $now,
        ]);
    }

    private function appendNote(?string $existing, string $note): string
    {
        $existing = trim((string) $existing);

        return $existing !== '' ? $existing."\n".$note : $note;
    }
}
