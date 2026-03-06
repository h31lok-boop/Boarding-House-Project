<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            'Wi-Fi',
            'Air Conditioning',
            'Laundry Area',
            'Kitchen Access',
            'Study Area',
            'CCTV',
            'Parking',
            'Water Included',
            'Electricity Included',
            'Security Guard',
        ];

        foreach ($amenities as $amenity) {
            Amenity::firstOrCreate(['name' => $amenity]);
        }
    }
}
