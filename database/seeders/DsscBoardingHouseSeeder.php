<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\BoardingHouse;
use App\Models\OwnerProfile;
use App\Models\RoomCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DsscBoardingHouseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LocationReferenceSeeder::class,
            AmenitySeeder::class,
        ]);

        foreach (['Study Table', 'Kitchen', 'Secure Gate', 'CCTV'] as $name) {
            Amenity::firstOrCreate(['name' => $name]);
        }

        $cityId = (int) DB::table('cities_municipalities')
            ->where('city_name', 'Digos City')
            ->value('id');
        $provinceId = (int) DB::table('provinces')
            ->where('province_name', 'Davao del Sur')
            ->value('id');
        $regionId = (int) DB::table('regions')
            ->where('region_code', '11')
            ->value('id');

        $amenityIds = Amenity::query()->pluck('id', 'name');
        $ownerProfiles = OwnerProfile::query()
            ->with('user:id,name,phone,contact_number')
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->get();

        foreach ($this->samples() as $index => $sample) {
            $ownerProfile = $ownerProfiles->isNotEmpty()
                ? $ownerProfiles[$index % $ownerProfiles->count()]
                : null;
            $owner = $ownerProfile?->user;
            $barangayId = (int) DB::table('barangays')
                ->where('city_id', $cityId)
                ->where('barangay_name', $sample['barangay'])
                ->value('id');

            $house = BoardingHouse::query()->updateOrCreate(
                ['name' => $sample['name']],
                [
                    'owner_profile_id' => $ownerProfile?->id,
                    'owner_id' => $owner?->id,
                    'user_id' => $owner?->id,
                    'slug' => Str::slug($sample['name']),
                    'address' => $sample['address'],
                    'full_address' => $sample['address'].', Davao del Sur',
                    'region_id' => $regionId ?: null,
                    'province_id' => $provinceId ?: null,
                    'city_id' => $cityId ?: null,
                    'barangay_id' => $barangayId ?: null,
                    'barangay' => $sample['barangay'],
                    'nearby_landmark' => config('dssc.landmark'),
                    'latitude' => $sample['latitude'],
                    'longitude' => $sample['longitude'],
                    'distance_from_dssc' => $sample['distance'],
                    'is_near_dssc' => true,
                    'location_status' => 'approximate',
                    'description' => 'Sample student listing near DSSC Main Campus. Coordinates are approximate and intended for testing only.',
                    'house_rules' => 'Observe quiet study hours. Keep shared spaces clean. No smoking indoors.',
                    'landlord_info' => $owner?->name,
                    'contact_name' => $owner?->name,
                    'contact_person' => $owner?->name,
                    'contact_number' => $owner?->contact_number ?: $owner?->phone,
                    'contact_phone' => $owner?->contact_number ?: $owner?->phone,
                    'monthly_payment' => (string) $sample['min_rent'],
                    'price' => $sample['min_rent'],
                    'capacity' => $sample['available_slots'] * 2,
                    'available_rooms' => $sample['available_slots'],
                    'is_active' => true,
                    'status' => 'approved',
                    'approval_status' => 'approved',
                    'approval_date' => now()->toDateString(),
                ]
            );

            $house->amenities()->sync(
                collect($sample['amenities'])
                    ->map(fn (string $name) => $amenityIds[$name] ?? null)
                    ->filter()
                    ->all()
            );

            foreach ($sample['room_types'] as $index => $roomType) {
                RoomCategory::query()->updateOrCreate(
                    [
                        'boarding_house_id' => $house->id,
                        'name' => $roomType,
                    ],
                    [
                        'description' => 'Sample room category for DSSC-area testing.',
                        'capacity' => str_contains(strtolower($roomType), 'solo') ? 1 : 2,
                        'monthly_rate' => $index === 0 ? $sample['min_rent'] : $sample['max_rent'],
                        'total_rooms' => $sample['available_slots'],
                        'available_rooms' => max(1, $sample['available_slots'] - $index),
                        'occupied_rooms' => 0,
                        'reserved_rooms' => 0,
                        'maintenance_rooms' => 0,
                        'is_available' => true,
                    ]
                );
            }
        }
    }

    private function samples(): array
    {
        return [
            [
                'name' => 'Matti Student Boarding House',
                'barangay' => 'Matti',
                'address' => 'Matti, Digos City',
                'distance' => 0.50,
                'latitude' => 6.76260000,
                'longitude' => 125.31140000,
                'min_rent' => 1500,
                'max_rent' => 2500,
                'room_types' => ['Shared Room'],
                'amenities' => ['Wi-Fi', 'Study Table', 'Kitchen'],
                'available_slots' => 4,
            ],
            [
                'name' => 'Purok 3 Boarding House',
                'barangay' => 'Purok 3, Matti',
                'address' => 'Purok 3, Matti, Digos City',
                'distance' => 0.80,
                'latitude' => 6.76450000,
                'longitude' => 125.31350000,
                'min_rent' => 1200,
                'max_rent' => 2000,
                'room_types' => ['Bed Space'],
                'amenities' => ['Wi-Fi', 'Laundry Area', 'Kitchen'],
                'available_slots' => 5,
            ],
            [
                'name' => 'DSSC Ladies Boarding House',
                'barangay' => 'Matti',
                'address' => 'Near DSSC Main Campus, Matti, Digos City',
                'distance' => 0.60,
                'latitude' => 6.75600000,
                'longitude' => 125.31370000,
                'min_rent' => 1500,
                'max_rent' => 3000,
                'room_types' => ['Shared Room'],
                'amenities' => ['Wi-Fi', 'Study Area', 'Kitchen', 'Secure Gate'],
                'available_slots' => 4,
            ],
            [
                'name' => 'Mahayahay Student Home',
                'barangay' => 'Mahayahay',
                'address' => 'Mahayahay, Digos City',
                'distance' => 2.50,
                'latitude' => 6.77400000,
                'longitude' => 125.32550000,
                'min_rent' => 2000,
                'max_rent' => 3500,
                'room_types' => ['Shared Room', 'Solo Room'],
                'amenities' => ['Wi-Fi', 'Kitchen', 'Study Table'],
                'available_slots' => 3,
            ],
            [
                'name' => 'Tres de Mayo Boarding House',
                'barangay' => 'Tres de Mayo',
                'address' => 'Tres de Mayo, Digos City',
                'distance' => 3.50,
                'latitude' => 6.78100000,
                'longitude' => 125.33300000,
                'min_rent' => 1800,
                'max_rent' => 3000,
                'room_types' => ['Bed Space', 'Shared Room'],
                'amenities' => ['Wi-Fi', 'Kitchen', 'Laundry Area'],
                'available_slots' => 4,
            ],
        ];
    }
}
