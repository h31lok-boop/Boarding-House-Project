<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $boardingHouses = \App\Models\BoardingHouse::all();

        if ($boardingHouses->isEmpty()) {
            $boardingHouses = \App\Models\BoardingHouse::factory()->count(3)->create();
        }

        // Pin all seeded tenants to the first boarding house so occupancy is visible.
        $targetHouseId = $boardingHouses->first()->id;

        $roomNumbers = collect(range(101, 199))->map(fn ($n) => 'R' . $n);

        User::factory()
            ->count(8)
            ->create([
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'is_active' => true,
                'move_in_date' => now()->subDays(rand(1, 60)),
                'boarding_house_id' => $targetHouseId,
            ])
            ->each(function ($user) use ($roomNumbers) {
                $user->room_number = $roomNumbers->shift() ?? null;
                $user->save();

                if (method_exists($user, 'syncRoles')) {
                    $user->syncRoles(['tenant']);
                }
            });
    }
}
