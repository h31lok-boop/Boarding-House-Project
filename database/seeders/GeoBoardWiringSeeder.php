<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GeoBoardWiringSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LocationReferenceSeeder::class,
            GeoBoardAccessSeeder::class,
            GeoBoardManagementSeeder::class,
        ]);
    }
}
