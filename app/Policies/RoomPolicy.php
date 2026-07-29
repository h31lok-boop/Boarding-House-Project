<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    /**
     * Super-admins may act on any room; owners only on rooms in their houses.
     */
    private function owns(User $user, Room $room): bool
    {
        return $user->isSuperAdmin()
            || (int) ($room->boardingHouse?->owner_id) === (int) $user->id;
    }

    public function update(User $user, Room $room): bool
    {
        return $this->owns($user, $room);
    }

    public function delete(User $user, Room $room): bool
    {
        return $this->owns($user, $room);
    }
}
