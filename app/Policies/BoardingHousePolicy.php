<?php

namespace App\Policies;

use App\Models\BoardingHouse;
use App\Models\User;

class BoardingHousePolicy
{
    /**
     * Super-admins may act on any boarding house; owners only on their own.
     */
    private function owns(User $user, BoardingHouse $house): bool
    {
        return $user->isSuperAdmin() || (int) $house->owner_id === (int) $user->id;
    }

    public function view(User $user, BoardingHouse $house): bool
    {
        return $this->owns($user, $house);
    }

    public function update(User $user, BoardingHouse $house): bool
    {
        return $this->owns($user, $house);
    }

    public function delete(User $user, BoardingHouse $house): bool
    {
        return $this->owns($user, $house);
    }
}
