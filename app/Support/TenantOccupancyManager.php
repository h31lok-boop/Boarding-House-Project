<?php

namespace App\Support;

use App\Models\BoardingHouse;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;

class TenantOccupancyManager
{
    public function assign(User $tenantUser, BoardingHouse $boardingHouse, ?Room $room = null, mixed $moveInDate = null): Tenant
    {
        $effectiveMoveInDate = $this->resolveMoveInDate($moveInDate, $tenantUser);

        $tenantRecord = Tenant::query()->firstOrNew([
            'user_id' => $tenantUser->id,
            'boarding_house_id' => $boardingHouse->id,
        ]);

        $tenantRecord->fill([
            'room_id' => $room?->id ?? $tenantRecord->room_id,
            'move_in_date' => $effectiveMoveInDate,
            'move_out_date' => null,
            'status' => 'active',
        ]);
        $tenantRecord->save();

        Tenant::query()
            ->where('user_id', $tenantUser->id)
            ->whereKeyNot($tenantRecord->id)
            ->where('status', 'active')
            ->update([
                'status' => 'inactive',
                'move_out_date' => $effectiveMoveInDate,
            ]);

        $roomNumber = $room?->effective_room_number;
        if (! $roomNumber && (int) $tenantUser->boarding_house_id === (int) $boardingHouse->id) {
            $roomNumber = $tenantUser->room_number;
        }

        $tenantUser->forceFill([
            'boarding_house_id' => $boardingHouse->id,
            'room_number' => $roomNumber,
            'move_in_date' => $effectiveMoveInDate,
            'is_active' => true,
        ])->save();

        return $tenantRecord;
    }

    private function resolveMoveInDate(mixed $moveInDate, User $tenantUser): string
    {
        if ($moveInDate instanceof Carbon) {
            return $moveInDate->toDateString();
        }

        if (is_string($moveInDate) && trim($moveInDate) !== '') {
            return Carbon::parse($moveInDate)->toDateString();
        }

        if ($tenantUser->move_in_date instanceof Carbon) {
            return $tenantUser->move_in_date->toDateString();
        }

        if ($tenantUser->move_in_date) {
            return Carbon::parse($tenantUser->move_in_date)->toDateString();
        }

        return now()->toDateString();
    }
}
