<?php

namespace App\Policies;

use App\Models\BoardingHouse;
use App\Models\User;

class BoardingHousePolicy
{
    /**
     * Owners may manage only their own boarding houses. Administrators have
     * read-only oversight access and are intentionally excluded here.
     */
    private function owns(User $user, BoardingHouse $house): bool
    {
        return $user->isStrictOwner() && (int) $house->owner_id === (int) $user->id;
    }

    public function view(User $user, BoardingHouse $house): bool
    {
        return $user->isSuperAdmin() || $this->owns($user, $house);
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
