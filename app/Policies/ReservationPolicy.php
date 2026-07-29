<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /**
     * Super-admins may act on any reservation; owners only on reservations
     * belonging to one of their boarding houses.
     */
    private function owns(User $user, Reservation $reservation): bool
    {
        return $user->isSuperAdmin()
            || (int) ($reservation->boardingHouse?->owner_id) === (int) $user->id;
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return $this->owns($user, $reservation);
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $this->owns($user, $reservation);
    }
}
