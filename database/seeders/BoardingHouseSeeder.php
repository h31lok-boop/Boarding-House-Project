<?php

namespace Database\Seeders;

use App\Models\BoardingHouse;
use Illuminate\Database\Seeder;

class BoardingHouseSeeder extends Seeder
{
    public function run(): void
    {
        BoardingHouse::factory()->count(10)->create();
    }
}
