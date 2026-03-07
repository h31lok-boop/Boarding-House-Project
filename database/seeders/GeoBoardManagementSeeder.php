<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\BoardingHouse;
use App\Models\BoardingHouseImage;
use App\Models\OwnerProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GeoBoardManagementSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AmenitySeeder::class);
        $imagePaths = $this->ensureSampleImages();

        $ownerProfiles = OwnerProfile::query()->with('user:id,name,phone,contact_number')->get();
        if ($ownerProfiles->isEmpty()) {
            return;
        }

        $barangays = DB::table('barangays as b')
            ->join('cities_municipalities as c', 'c.id', '=', 'b.city_id')
            ->join('provinces as p', 'p.id', '=', 'c.province_id')
            ->join('regions as r', 'r.id', '=', 'p.region_id')
            ->select([
                'b.id as barangay_id',
                'b.barangay_name',
                'b.latitude',
                'b.longitude',
                'c.id as city_id',
                'c.city_name',
                'p.id as province_id',
                'p.province_name',
                'r.id as region_id',
                'r.region_name',
            ])
            ->orderBy('b.id')
            ->get();

        if ($barangays->isEmpty()) {
            return;
        }

        $approverId = (int) DB::table('users')->where('role', 'superduperadmin')->value('id')
            ?: (int) DB::table('users')->where('role', 'admin')->value('id');

        $houseTemplates = [
            ['name' => 'Sunrise Student Boarding House', 'price' => 2800, 'available_rooms' => 6, 'description' => 'Budget-friendly, near schools, with quiet study zones and stable internet.'],
            ['name' => 'Greenleaf Bedspace and Dorm', 'price' => 3200, 'available_rooms' => 5, 'description' => 'Affordable dorm setup with shared kitchen, laundry access, and CCTV security.'],
            ['name' => 'CityWalk Boarding Suites', 'price' => 4500, 'available_rooms' => 4, 'description' => 'Mid-range rooms close to transport routes and commercial establishments.'],
            ['name' => 'Haven Point Boarding Residence', 'price' => 5200, 'available_rooms' => 3, 'description' => 'Modern boarding space with curated amenities and strong safety policies.'],
            ['name' => 'MetroNest Boarding Hub', 'price' => 6000, 'available_rooms' => 4, 'description' => 'Premium room options with semi-furnished setup and co-working corner.'],
            ['name' => 'Casa Digos Boarding Stay', 'price' => 3500, 'available_rooms' => 7, 'description' => 'Accessible location with practical facilities for students and workers.'],
        ];

        $amenityIds = Amenity::query()->pluck('id');
        $now = now();

        foreach ($houseTemplates as $index => $template) {
            $ownerProfile = $ownerProfiles[$index % $ownerProfiles->count()];
            $barangay = $barangays[$index % $barangays->count()];
            $slug = Str::slug($template['name']).'-'.($index + 1);

            $address = sprintf(
                'Purok %d, %s, %s, %s',
                $index + 1,
                $barangay->barangay_name,
                $barangay->city_name,
                $barangay->province_name
            );

            $boardingHouse = BoardingHouse::updateOrCreate(
                ['slug' => $slug],
                [
                    'owner_profile_id' => $ownerProfile->id,
                    'owner_id' => $ownerProfile->user_id,
                    'name' => $template['name'],
                    'slug' => $slug,
                    'address' => $address,
                    'full_address' => $address,
                    'description' => $template['description'],
                    'latitude' => $barangay->latitude ?? 6.74400000,
                    'longitude' => $barangay->longitude ?? 125.35500000,
                    'region_id' => $barangay->region_id,
                    'province_id' => $barangay->province_id,
                    'city_id' => $barangay->city_id,
                    'barangay_id' => $barangay->barangay_id,
                    'price' => $template['price'],
                    'monthly_payment' => (string) $template['price'],
                    'available_rooms' => $template['available_rooms'],
                    'capacity' => 24,
                    'is_active' => true,
                    'status' => 'approved',
                    'approval_status' => 'approved',
                    'approval_date' => $now->toDateString(),
                    'approved_by' => $approverId ?: null,
                    'contact_person' => $ownerProfile->user?->name,
                    'contact_name' => $ownerProfile->user?->name,
                    'contact_number' => $ownerProfile->user?->contact_number ?: $ownerProfile->user?->phone,
                    'contact_phone' => $ownerProfile->user?->contact_number ?: $ownerProfile->user?->phone,
                    'house_rules' => 'Observe quiet hours from 10:00 PM to 6:00 AM. Keep shared spaces clean.',
                    'featured_image' => $imagePaths[$index % count($imagePaths)],
                ]
            );

            if ($amenityIds->isNotEmpty()) {
                $selectedAmenityIds = $amenityIds->shuffle()->take(min(5, $amenityIds->count()))->all();
                $boardingHouse->amenities()->sync($selectedAmenityIds);
            }

            $this->syncImages($boardingHouse, $imagePaths, $index);
            $this->syncRoomCategoriesAndRooms($boardingHouse, (float) $template['price'], (int) $template['available_rooms']);
            $this->syncApprovalLog($boardingHouse, $approverId, $now);
        }
    }

    /**
     * @return array<int, string>
     */
    private function ensureSampleImages(): array
    {
        $disk = Storage::disk('public');
        $images = [
            'boarding-houses/sample-house-1.svg' => '#1d4ed8',
            'boarding-houses/sample-house-2.svg' => '#0f766e',
            'boarding-houses/sample-house-3.svg' => '#be123c',
            'boarding-houses/sample-house-4.svg' => '#7c3aed',
        ];

        foreach ($images as $path => $color) {
            if ($disk->exists($path)) {
                continue;
            }

            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720">
  <rect width="1280" height="720" fill="{$color}"/>
  <rect x="90" y="90" width="1100" height="540" rx="24" fill="#ffffff" opacity="0.12"/>
  <text x="640" y="360" font-family="Arial, sans-serif" font-size="54" fill="#ffffff" text-anchor="middle">GeoBoard Sample Boarding House</text>
  <text x="640" y="420" font-family="Arial, sans-serif" font-size="28" fill="#ffffff" text-anchor="middle">Geotagged Listing Image</text>
</svg>
SVG;

            $disk->put($path, $svg);
        }

        return array_keys($images);
    }

    private function syncImages(BoardingHouse $boardingHouse, array $imagePaths, int $seedIndex): void
    {
        BoardingHouseImage::where('boarding_house_id', $boardingHouse->id)->update(['is_primary' => 0]);

        foreach ($imagePaths as $index => $path) {
            BoardingHouseImage::updateOrCreate(
                [
                    'boarding_house_id' => $boardingHouse->id,
                    'image_path' => $path,
                ],
                [
                    'image_label' => $index === 0 ? 'Primary' : 'Gallery',
                    'is_primary' => $index === ($seedIndex % count($imagePaths)),
                    'sort_order' => $index,
                ]
            );
        }
    }

    private function syncRoomCategoriesAndRooms(BoardingHouse $boardingHouse, float $basePrice, int $targetAvailableRooms): void
    {
        $categorySeeds = [
            [
                'name' => 'Standard',
                'monthly_rate' => max(1800, $basePrice),
                'total_rooms' => 6,
                'available_rooms' => max(1, min(4, $targetAvailableRooms)),
            ],
            [
                'name' => 'Deluxe',
                'monthly_rate' => max(2300, $basePrice + 1200),
                'total_rooms' => 4,
                'available_rooms' => max(1, min(3, $targetAvailableRooms)),
            ],
        ];

        foreach ($categorySeeds as $categorySeed) {
            DB::table('room_categories')->updateOrInsert(
                [
                    'boarding_house_id' => $boardingHouse->id,
                    'name' => $categorySeed['name'],
                ],
                [
                    'description' => $categorySeed['name'].' room package',
                    'capacity' => 2,
                    'monthly_rate' => $categorySeed['monthly_rate'],
                    'total_rooms' => $categorySeed['total_rooms'],
                    'available_rooms' => $categorySeed['available_rooms'],
                    'occupied_rooms' => max(0, $categorySeed['total_rooms'] - $categorySeed['available_rooms']),
                    'reserved_rooms' => 0,
                    'maintenance_rooms' => 0,
                    'is_available' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $roomCategoryId = (int) DB::table('room_categories')
                ->where('boarding_house_id', $boardingHouse->id)
                ->where('name', $categorySeed['name'])
                ->value('id');

            if (! $roomCategoryId) {
                continue;
            }

            for ($slot = 1; $slot <= $categorySeed['total_rooms']; $slot++) {
                $roomNumber = strtoupper(substr($categorySeed['name'], 0, 1)).'-'.str_pad((string) $slot, 2, '0', STR_PAD_LEFT);
                $available = $slot <= $categorySeed['available_rooms'];

                DB::table('rooms')->updateOrInsert(
                    [
                        'room_category_id' => $roomCategoryId,
                        'room_number' => $roomNumber,
                    ],
                    [
                        'room_name' => $categorySeed['name'].' Room '.$slot,
                        'boarding_house_id' => $boardingHouse->id,
                        'room_no' => $roomNumber,
                        'name' => $categorySeed['name'].' Room '.$slot,
                        'price' => $categorySeed['monthly_rate'],
                        'capacity' => 2,
                        'status' => $available ? 'Available' : 'Occupied',
                        'available_slots' => $available ? 1 : 0,
                        'description' => 'Seeded room for mapping and comparison demo.',
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $availableFromCategories = (int) DB::table('room_categories')
            ->where('boarding_house_id', $boardingHouse->id)
            ->sum('available_rooms');

        $boardingHouse->forceFill([
            'available_rooms' => $availableFromCategories,
        ])->save();
    }

    private function syncApprovalLog(BoardingHouse $boardingHouse, int $reviewerId, $reviewedAt): void
    {
        if (! $reviewerId) {
            return;
        }

        $alreadyLogged = DB::table('approvals')
            ->where('boarding_house_id', $boardingHouse->id)
            ->where('decision', 'approved')
            ->exists();

        if ($alreadyLogged) {
            return;
        }

        DB::table('approvals')->insert([
            'boarding_house_id' => $boardingHouse->id,
            'reviewer_id' => $reviewerId,
            'decision' => 'approved',
            'remarks' => 'Seeded approval for capstone management module.',
            'reviewed_at' => $reviewedAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
