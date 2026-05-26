<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class Phase1AuthSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(GeoBoardAccessSeeder::class);
    }
}
