<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            GeoBoardWiringSeeder::class,
            DsscBoardingHouseSeeder::class,
            AdminSeeder::class,
        ]);
    }

    // To generate admin invite codes, run:
    //   php artisan db:seed --class=AdminInviteCodeSeeder
}
