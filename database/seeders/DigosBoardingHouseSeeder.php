<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\BoardingHouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DigosBoardingHouseSeeder extends Seeder
{
    // Digos City DB ids (confirmed from cities_municipalities)
    private const CITY_ID = 1;

    private const PROVINCE_ID = 1;

    private const REGION_ID = 1;

    private const BARANGAY_ID = 1;

    public function run(): void
    {
        // Ensure all required amenities exist
        $extra = [
            'Swimming Pool', 'Garden / Patio', 'Balcony', 'Hot Shower',
            'Private Bathroom', 'Dining Area', 'Living Room', 'Mountain View',
            'Creekside', 'Outdoor Area', 'Farm View', 'Beach Access',
        ];
        foreach ($extra as $name) {
            Amenity::firstOrCreate(['name' => $name]);
        }

        $am = Amenity::pluck('id', 'name');
        $ids = fn (array $names) => collect($names)
            ->map(fn ($n) => $am[$n] ?? null)
            ->filter()
            ->values()
            ->all();

        $listings = $this->listings();

        foreach ($listings as $data) {
            $amenityNames = $data['_amenities'] ?? [];
            $roomCats = $data['_room_cats'] ?? [];
            unset($data['_amenities'], $data['_room_cats']);

            // Skip if already seeded by name
            if (BoardingHouse::where('name', $data['name'])->exists()) {
                continue;
            }

            $bh = BoardingHouse::create($data);

            if ($amenityNames) {
                $bh->amenities()->syncWithoutDetaching($ids($amenityNames));
            }

            foreach ($roomCats as $cat) {
                DB::table('room_categories')->insert(array_merge($cat, [
                    'boarding_house_id' => $bh->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    private function base(array $overrides = []): array
    {
        return array_merge([
            'owner_profile_id' => 11,
            'owner_id' => null,
            'city_id' => self::CITY_ID,
            'province_id' => self::PROVINCE_ID,
            'region_id' => self::REGION_ID,
            'barangay_id' => self::BARANGAY_ID,
            'status' => 'approved',
            'approval_status' => 'approved',
            'approval_date' => now()->toDateString(),
            'is_active' => true,
            'is_featured' => false,
            'allows_visitors' => true,
            'allows_pets' => false,
            'smoking_policy' => 'not_allowed',
            'has_cctv' => false,
            'has_security_guard' => false,
            'security_deposit_required' => true,
            'payment_due_day' => 5,
            'min_stay_months' => 1,
            'property_type' => 'boarding_house',
            'views_count' => rand(10, 120),
            'inquiry_count' => rand(1, 20),
        ], $overrides);
    }

    private function listings(): array
    {
        return [

            // ── 1. K/C Boarding House ──────────────────────────────────────
            $this->base([
                'name' => 'K/C Boarding House',
                'full_address' => 'Zone 1, Digos City, Davao del Sur',
                'address' => 'Zone 1, Digos City, Davao del Sur',
                'description' => 'Affordable and well-maintained boarding house in Digos City. Ideal for students and working professionals. Secure environment with basic amenities included in the monthly rate.',
                'house_rules' => 'No overnight visitors. Quiet hours 10PM–6AM. Keep common areas clean.',
                'monthly_payment' => '2500',
                'price' => 2500,
                'contact_person' => 'KC Owner',
                'contact_name' => 'KC Owner',
                'contact_number' => '09270000101',
                'contact_phone' => '09270000101',
                'latitude' => 6.7450,
                'longitude' => 125.3560,
                'capacity' => 20,
                'available_rooms' => 4,
                '_amenities' => ['Wi-Fi', 'Water Included', 'Laundry Area', 'CCTV'],
                '_room_cats' => [
                    ['name' => 'Single Room',  'monthly_rate' => 2500, 'total_rooms' => 8,  'available_rooms' => 2, 'is_available' => true, 'capacity' => 1],
                    ['name' => 'Shared Room',  'monthly_rate' => 1800, 'total_rooms' => 6,  'available_rooms' => 2, 'is_available' => true, 'capacity' => 2],
                ],
            ]),

            // ── 2. Ernest Boarding House ───────────────────────────────────
            $this->base([
                'name' => 'Ernest Boarding House',
                'full_address' => 'Zone 3, Digos City, Davao del Sur',
                'address' => 'Zone 3, Digos City, Davao del Sur',
                'description' => 'Ernest Boarding House offers comfortable and clean accommodations in the heart of Digos City. Close to public transport, markets, and schools.',
                'house_rules' => 'No loud music after 9PM. Visitors allowed until 8PM only.',
                'monthly_payment' => '2800',
                'price' => 2800,
                'contact_person' => 'Ernest Reyes',
                'contact_name' => 'Ernest Reyes',
                'contact_number' => '09280000102',
                'contact_phone' => '09280000102',
                'latitude' => 6.7430,
                'longitude' => 125.3545,
                'capacity' => 18,
                'available_rooms' => 3,
                '_amenities' => ['Wi-Fi', 'Water Included', 'Electricity Included', 'Kitchen Access'],
                '_room_cats' => [
                    ['name' => 'Single Room',  'monthly_rate' => 2800, 'total_rooms' => 10, 'available_rooms' => 3, 'is_available' => true, 'capacity' => 1],
                    ['name' => 'Bedspace',     'monthly_rate' => 1500, 'total_rooms' => 4,  'available_rooms' => 0, 'is_available' => false, 'capacity' => 4],
                ],
            ]),

            // ── 3. Berchby Loft ────────────────────────────────────────────
            $this->base([
                'name' => 'Berchby Loft',
                'full_address' => 'Poblacion, Digos City, Davao del Sur',
                'address' => 'Poblacion, Digos City, Davao del Sur',
                'description' => 'A modern loft-style accommodation in Digos City. Features a stylish interior with high ceilings, comfortable sleeping loft, and a relaxing living area. Perfect for professionals and couples.',
                'house_rules' => 'No smoking indoors. Respect neighboring units. Check-in after 2PM, check-out by 12NN.',
                'monthly_payment' => '5500',
                'price' => 5500,
                'contact_person' => 'Berch Lim',
                'contact_name' => 'Berch Lim',
                'contact_number' => '09290000103',
                'contact_phone' => '09290000103',
                'latitude' => 6.7480,
                'longitude' => 125.3590,
                'property_type' => 'apartment',
                'capacity' => 4,
                'available_rooms' => 1,
                '_amenities' => ['Wi-Fi', 'Air Conditioning', 'Private Bathroom', 'Balcony', 'Kitchen Access'],
                '_room_cats' => [
                    ['name' => 'Loft Unit', 'monthly_rate' => 5500, 'total_rooms' => 2, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 2],
                ],
            ]),

            // ── 4. Summers Cozy Studio ─────────────────────────────────────
            $this->base([
                'name' => 'Summers Cozy Studio',
                'full_address' => 'Zone 2, Digos City, Davao del Sur',
                'address' => 'Zone 2, Digos City, Davao del Sur',
                'description' => 'A cozy studio unit perfect for solo travelers, students, or young professionals. Fully furnished with a warm and homey atmosphere. Walking distance from key establishments in Digos City.',
                'house_rules' => 'No pets allowed. Keep the unit clean at all times. No sub-leasing.',
                'monthly_payment' => '4500',
                'price' => 4500,
                'contact_person' => 'Summer Go',
                'contact_name' => 'Summer Go',
                'contact_number' => '09300000104',
                'contact_phone' => '09300000104',
                'latitude' => 6.7465,
                'longitude' => 125.3600,
                'property_type' => 'apartment',
                'capacity' => 2,
                'available_rooms' => 1,
                '_amenities' => ['Wi-Fi', 'Air Conditioning', 'Water Included', 'Electricity Included', 'Private Bathroom'],
                '_room_cats' => [
                    ['name' => 'Studio Unit', 'monthly_rate' => 4500, 'total_rooms' => 3, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 2],
                ],
            ]),

            // ── 5. Suburban Sanctuary ──────────────────────────────────────
            $this->base([
                'name' => 'Suburban Sanctuary',
                'full_address' => 'Mahayahay, Digos City, Davao del Sur',
                'address' => 'Mahayahay, Digos City, Davao del Sur',
                'description' => 'Find peace and comfort at Suburban Sanctuary — a quiet residential boarding house surrounded by lush greenery. Ideal for those seeking a calm environment away from the city noise while still being accessible.',
                'house_rules' => 'Quiet hours strictly enforced 9PM–7AM. No loud gatherings.',
                'monthly_payment' => '3200',
                'price' => 3200,
                'contact_person' => 'Property Manager',
                'contact_name' => 'Property Manager',
                'contact_number' => '09310000105',
                'contact_phone' => '09310000105',
                'latitude' => 6.7500,
                'longitude' => 125.3620,
                'capacity' => 16,
                'available_rooms' => 5,
                '_amenities' => ['Wi-Fi', 'Garden / Patio', 'Water Included', 'Parking', 'Study Area'],
                '_room_cats' => [
                    ['name' => 'Single Room', 'monthly_rate' => 3200, 'total_rooms' => 8,  'available_rooms' => 3, 'is_available' => true, 'capacity' => 1],
                    ['name' => 'Shared Room', 'monthly_rate' => 2200, 'total_rooms' => 4,  'available_rooms' => 2, 'is_available' => true, 'capacity' => 2],
                ],
            ]),

            // ── 6. Serenity Resthouse 2 ────────────────────────────────────
            $this->base([
                'name' => 'Serenity Resthouse 2',
                'full_address' => 'Zone 5, Digos City, Davao del Sur',
                'address' => 'Zone 5, Digos City, Davao del Sur',
                'description' => 'Serenity Resthouse 2 is the second branch of the popular Serenity Resthouse, offering clean rooms and a serene atmosphere. Ideal for transient guests and monthly boarders looking for a peaceful retreat.',
                'house_rules' => 'No cooking in rooms. Curfew at 10PM for overnight guests.',
                'monthly_payment' => '3000',
                'price' => 3000,
                'contact_person' => 'Serenity Management',
                'contact_name' => 'Serenity Management',
                'contact_number' => '09320000106',
                'contact_phone' => '09320000106',
                'latitude' => 6.7420,
                'longitude' => 125.3530,
                'capacity' => 14,
                'available_rooms' => 2,
                '_amenities' => ['Wi-Fi', 'Water Included', 'Air Conditioning', 'Hot Shower', 'Kitchen Access'],
                '_room_cats' => [
                    ['name' => 'Standard Room',  'monthly_rate' => 3000, 'total_rooms' => 6,  'available_rooms' => 1, 'is_available' => true,  'capacity' => 1],
                    ['name' => 'Transient Room', 'monthly_rate' => 800,  'total_rooms' => 4,  'available_rooms' => 1, 'is_available' => true,  'capacity' => 2, 'description' => 'Per night rate'],
                ],
            ]),

            // ── 7. Summer Hub ──────────────────────────────────────────────
            $this->base([
                'name' => 'Summer Hub',
                'full_address' => 'Zone 4, Digos City, Davao del Sur',
                'address' => 'Zone 4, Digos City, Davao del Sur',
                'description' => 'Summer Hub is a vibrant shared accommodation space perfect for students and young workers. Features common areas for socializing, fast Wi-Fi, and affordable monthly rates.',
                'house_rules' => 'Respect all housemates. Keep common areas tidy. No overnight visitors without prior approval.',
                'monthly_payment' => '2000',
                'price' => 2000,
                'contact_person' => 'Summer Hub Admin',
                'contact_name' => 'Summer Hub Admin',
                'contact_number' => '09330000107',
                'contact_phone' => '09330000107',
                'latitude' => 6.7470,
                'longitude' => 125.3570,
                'capacity' => 24,
                'available_rooms' => 6,
                '_amenities' => ['Wi-Fi', 'Study Area', 'Laundry Area', 'Kitchen Access', 'Water Included'],
                '_room_cats' => [
                    ['name' => 'Bedspace',    'monthly_rate' => 2000, 'total_rooms' => 6,  'available_rooms' => 3, 'is_available' => true, 'capacity' => 4],
                    ['name' => 'Single Room', 'monthly_rate' => 3000, 'total_rooms' => 4,  'available_rooms' => 2, 'is_available' => true, 'capacity' => 1],
                    ['name' => 'Shared Room', 'monthly_rate' => 2500, 'total_rooms' => 4,  'available_rooms' => 1, 'is_available' => true, 'capacity' => 2],
                ],
            ]),

            // ── 8. Kecai's Place ───────────────────────────────────────────
            $this->base([
                'name' => "Kecai's Place",
                'full_address' => 'Aplaya, Digos City, Davao del Sur',
                'address' => 'Aplaya, Digos City, Davao del Sur',
                'description' => "Kecai's Place is a cozy home-style boarding house near Digos City bayside. Clean rooms, friendly landlady, and a warm community environment make it ideal for long-term boarders.",
                'house_rules' => 'Be respectful to all tenants. No loud noise after 10PM.',
                'monthly_payment' => '2700',
                'price' => 2700,
                'contact_person' => 'Kecai',
                'contact_name' => 'Kecai',
                'contact_number' => '09340000108',
                'contact_phone' => '09340000108',
                'latitude' => 6.7440,
                'longitude' => 125.3555,
                'capacity' => 12,
                'available_rooms' => 3,
                '_amenities' => ['Wi-Fi', 'Water Included', 'Electricity Included', 'Laundry Area', 'Kitchen Access'],
                '_room_cats' => [
                    ['name' => 'Single Room', 'monthly_rate' => 2700, 'total_rooms' => 6,  'available_rooms' => 2, 'is_available' => true, 'capacity' => 1],
                    ['name' => 'Shared Room', 'monthly_rate' => 2000, 'total_rooms' => 3,  'available_rooms' => 1, 'is_available' => true, 'capacity' => 2],
                ],
            ]),

            // ── 9. Digos City 3BR Transient Near Bus Terminal ──────────────
            $this->base([
                'name' => 'Digos City 3BR Transient Near Bus Terminal',
                'full_address' => 'Near Digos Bus Terminal, Digos City, Davao del Sur',
                'address' => 'Near Bus Terminal, Digos City, Davao del Sur',
                'description' => 'A spacious 3-bedroom transient house conveniently located near the Digos City Bus Terminal. Perfect for families or groups visiting Digos City. Fully equipped kitchen, comfortable living area, and reliable Wi-Fi.',
                'house_rules' => 'No parties. Maximum 8 guests. Check-in 2PM, check-out 12NN.',
                'monthly_payment' => '3500',
                'price' => 3500,
                'contact_person' => 'Property Manager',
                'contact_name' => 'Property Manager',
                'contact_number' => '09350000109',
                'contact_phone' => '09350000109',
                'latitude' => 6.7490,
                'longitude' => 125.3580,
                'property_type' => 'other',
                'capacity' => 8,
                'available_rooms' => 3,
                'nearby_landmarks' => 'Digos Bus Terminal',
                '_amenities' => ['Wi-Fi', 'Air Conditioning', 'Kitchen Access', 'Dining Area', 'Living Room', 'Parking', 'Hot Shower'],
                '_room_cats' => [
                    ['name' => 'Master Bedroom', 'monthly_rate' => 0, 'daily_rate' => 600,  'total_rooms' => 1, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 2, 'description' => 'Per night'],
                    ['name' => 'Bedroom 2',      'monthly_rate' => 0, 'daily_rate' => 500,  'total_rooms' => 1, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 2, 'description' => 'Per night'],
                    ['name' => 'Bedroom 3',      'monthly_rate' => 0, 'daily_rate' => 500,  'total_rooms' => 1, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 2, 'description' => 'Per night'],
                    ['name' => 'Whole Unit',     'monthly_rate' => 0, 'daily_rate' => 3500, 'total_rooms' => 1, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 8, 'description' => 'Entire house per night'],
                ],
            ]),

            // ── 10. Aesthetic Minimalist House with Mini Pool and Golf ──────
            $this->base([
                'name' => 'Aesthetic Minimalist House with Mini Pool and Golf',
                'full_address' => 'Digos City, Davao del Sur',
                'address' => 'Digos City, Davao del Sur',
                'description' => 'An Instagram-worthy minimalist house featuring a private mini swimming pool and mini golf area. Perfect for a relaxing staycation or weekend getaway. Clean lines, modern decor, and a serene ambiance await you.',
                'house_rules' => 'No outside guests allowed in pool area. Pool hours 7AM–9PM. Respect the property.',
                'monthly_payment' => '8000',
                'price' => 8000,
                'contact_person' => 'Property Owner',
                'contact_name' => 'Property Owner',
                'contact_number' => '09360000110',
                'contact_phone' => '09360000110',
                'latitude' => 6.7510,
                'longitude' => 125.3640,
                'property_type' => 'other',
                'capacity' => 6,
                'available_rooms' => 1,
                'allows_pets' => false,
                'is_featured' => true,
                '_amenities' => ['Swimming Pool', 'Wi-Fi', 'Air Conditioning', 'Kitchen Access', 'Dining Area', 'Living Room', 'Garden / Patio', 'Parking', 'Private Bathroom'],
                '_room_cats' => [
                    ['name' => 'Whole House', 'monthly_rate' => 0, 'daily_rate' => 8000, 'total_rooms' => 1, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 6, 'description' => 'Entire house per night — includes pool & mini golf access'],
                    ['name' => 'Day Use',     'monthly_rate' => 0, 'daily_rate' => 3000, 'total_rooms' => 1, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 8, 'description' => 'Day use rate (8AM–6PM)'],
                ],
            ]),

            // ── 11. Mt. Apo View Creekside Whole House with Pool ───────────
            $this->base([
                'name' => 'Mt. Apo View Creekside Whole House with Pool',
                'full_address' => 'Digos City, Davao del Sur (Mt. Apo vicinity)',
                'address' => 'Digos City, Davao del Sur',
                'description' => 'Wake up to a breathtaking view of Mt. Apo from this stunning creekside whole house rental with a private pool. A nature lover\'s paradise with the sound of flowing water, mountain breeze, and lush surroundings. Perfect for families and barkada getaways.',
                'house_rules' => 'No loud music after 10PM. Take care of the natural surroundings. Pool opens at 7AM.',
                'monthly_payment' => '12000',
                'price' => 12000,
                'contact_person' => 'Property Owner',
                'contact_name' => 'Property Owner',
                'contact_number' => '09370000111',
                'contact_phone' => '09370000111',
                'latitude' => 6.7550,
                'longitude' => 125.3700,
                'property_type' => 'other',
                'capacity' => 10,
                'available_rooms' => 1,
                'allows_pets' => true,
                'is_featured' => true,
                '_amenities' => ['Swimming Pool', 'Wi-Fi', 'Air Conditioning', 'Kitchen Access', 'Dining Area', 'Living Room', 'Mountain View', 'Creekside', 'Garden / Patio', 'Parking', 'Outdoor Area'],
                '_room_cats' => [
                    ['name' => 'Whole House',   'monthly_rate' => 0, 'daily_rate' => 12000, 'total_rooms' => 1, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 10, 'description' => 'Entire house per night — includes pool & creek access'],
                    ['name' => 'Weekend Rate',  'monthly_rate' => 0, 'daily_rate' => 14000, 'total_rooms' => 1, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 10, 'description' => 'Friday–Sunday premium rate'],
                ],
            ]),

            // ── 12. Moss & Grove Peaceful Stay ─────────────────────────────
            $this->base([
                'name' => 'Moss & Grove Peaceful Stay',
                'full_address' => 'Digos City, Davao del Sur',
                'address' => 'Digos City, Davao del Sur',
                'description' => 'Nestled amidst greenery and tranquility, Moss & Grove Peaceful Stay offers a nature-immersed lodging experience. Surrounded by moss-covered stones and a shaded grove, this is the perfect digital detox retreat in Digos City.',
                'house_rules' => 'Respect nature — no littering. Quiet and peaceful environment. No loud music.',
                'monthly_payment' => '5000',
                'price' => 5000,
                'contact_person' => 'Moss & Grove Host',
                'contact_name' => 'Moss & Grove Host',
                'contact_number' => '09380000112',
                'contact_phone' => '09380000112',
                'latitude' => 6.7530,
                'longitude' => 125.3680,
                'property_type' => 'other',
                'capacity' => 6,
                'available_rooms' => 2,
                'allows_pets' => true,
                '_amenities' => ['Wi-Fi', 'Garden / Patio', 'Outdoor Area', 'Hot Shower', 'Kitchen Access', 'Dining Area'],
                '_room_cats' => [
                    ['name' => 'Garden Room',   'monthly_rate' => 0, 'daily_rate' => 2500, 'total_rooms' => 2, 'available_rooms' => 1, 'is_available' => true,  'capacity' => 2],
                    ['name' => 'Grove Cottage', 'monthly_rate' => 0, 'daily_rate' => 5000, 'total_rooms' => 1, 'available_rooms' => 1, 'is_available' => true,  'capacity' => 4],
                ],
            ]),

            // ── 13. Digos Staycation House ─────────────────────────────────
            $this->base([
                'name' => 'Digos Staycation House',
                'full_address' => 'Digos City, Davao del Sur',
                'address' => 'Digos City, Davao del Sur',
                'description' => 'Digos Staycation House is a well-maintained family home available for short-term stays. Clean, comfortable, and fully equipped with everything you need for a relaxing staycation experience in Digos City.',
                'house_rules' => 'No smoking inside. Check-in 2PM, check-out 11AM. Maximum occupancy must be observed.',
                'monthly_payment' => '4000',
                'price' => 4000,
                'contact_person' => 'Staycation Host',
                'contact_name' => 'Staycation Host',
                'contact_number' => '09390000113',
                'contact_phone' => '09390000113',
                'latitude' => 6.7460,
                'longitude' => 125.3565,
                'property_type' => 'other',
                'capacity' => 8,
                'available_rooms' => 1,
                '_amenities' => ['Wi-Fi', 'Air Conditioning', 'Kitchen Access', 'Living Room', 'Dining Area', 'Parking', 'Water Included'],
                '_room_cats' => [
                    ['name' => 'Whole House', 'monthly_rate' => 0, 'daily_rate' => 4000, 'total_rooms' => 1, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 8, 'description' => 'Per night rate for entire house'],
                ],
            ]),

            // ── 14. Amazing Grace Digos Staycation ─────────────────────────
            $this->base([
                'name' => 'Amazing Grace Digos Staycation',
                'full_address' => 'Poblacion, Digos City, Davao del Sur',
                'address' => 'Poblacion, Digos City, Davao del Sur',
                'description' => 'Amazing Grace Digos Staycation is a charming and well-furnished property that offers a homey and gracious stay. Located near the Digos City plaza and market, it\'s perfect for visitors exploring the city.',
                'house_rules' => 'Guests must be registered at check-in. No unregistered guests after 9PM.',
                'monthly_payment' => '3500',
                'price' => 3500,
                'contact_person' => 'Grace Villanueva',
                'contact_name' => 'Grace Villanueva',
                'contact_number' => '09400000114',
                'contact_phone' => '09400000114',
                'latitude' => 6.7455,
                'longitude' => 125.3575,
                'property_type' => 'other',
                'capacity' => 6,
                'available_rooms' => 1,
                'nearby_landmarks' => 'Digos City Plaza, Public Market',
                '_amenities' => ['Wi-Fi', 'Air Conditioning', 'Kitchen Access', 'Living Room', 'Dining Area', 'Hot Shower', 'CCTV'],
                '_room_cats' => [
                    ['name' => 'Whole House',   'monthly_rate' => 0, 'daily_rate' => 3500, 'total_rooms' => 1, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 6, 'description' => 'Per night'],
                    ['name' => 'Standard Room', 'monthly_rate' => 0, 'daily_rate' => 800,  'total_rooms' => 2, 'available_rooms' => 2, 'is_available' => true, 'capacity' => 2, 'description' => 'Room only per night'],
                ],
            ]),

            // ── 15. Leikendee Airbnb Digos City ───────────────────────────
            $this->base([
                'name' => 'Leikendee Airbnb Digos City',
                'full_address' => 'Zone 6, Digos City, Davao del Sur',
                'address' => 'Zone 6, Digos City, Davao del Sur',
                'description' => 'Leikendee Airbnb offers a clean, cozy, and Instagram-worthy space in Digos City. Thoughtfully designed interiors with all the essentials for a comfortable short or long-term stay. Your home away from home.',
                'house_rules' => 'No parties or events. Pets considered upon request. Quiet hours 10PM–7AM.',
                'monthly_payment' => '6500',
                'price' => 6500,
                'contact_person' => 'Leikendee Host',
                'contact_name' => 'Leikendee Host',
                'contact_number' => '09410000115',
                'contact_phone' => '09410000115',
                'latitude' => 6.7475,
                'longitude' => 125.3595,
                'property_type' => 'apartment',
                'capacity' => 4,
                'available_rooms' => 1,
                'allows_pets' => true,
                '_amenities' => ['Wi-Fi', 'Air Conditioning', 'Private Bathroom', 'Kitchen Access', 'Balcony', 'Hot Shower', 'Dining Area'],
                '_room_cats' => [
                    ['name' => 'Studio Unit', 'monthly_rate' => 6500, 'daily_rate' => 900, 'total_rooms' => 2, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 2],
                ],
            ]),

            // ── 16. Quinto's Farm Glasshouse ──────────────────────────────
            $this->base([
                'name' => "Quinto's Farm Glasshouse",
                'full_address' => 'Outskirts of Digos City, Davao del Sur',
                'address' => 'Digos City, Davao del Sur',
                'description' => "A one-of-a-kind glamping experience — Quinto's Farm Glasshouse lets you sleep under the stars through a transparent glass roof while surrounded by a working farm. Experience nature and modern comfort at the same time.",
                'house_rules' => 'Respect the farm animals and crops. No open fire. Leave no trace.',
                'monthly_payment' => '7500',
                'price' => 7500,
                'contact_person' => 'Quinto Family',
                'contact_name' => 'Quinto Family',
                'contact_number' => '09420000116',
                'contact_phone' => '09420000116',
                'latitude' => 6.7580,
                'longitude' => 125.3720,
                'property_type' => 'other',
                'capacity' => 4,
                'available_rooms' => 1,
                'allows_pets' => true,
                'is_featured' => true,
                '_amenities' => ['Farm View', 'Outdoor Area', 'Garden / Patio', 'Wi-Fi', 'Hot Shower', 'Dining Area', 'Parking'],
                '_room_cats' => [
                    ['name' => 'Glasshouse Unit', 'monthly_rate' => 0, 'daily_rate' => 7500, 'total_rooms' => 1, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 2, 'description' => 'Per night — stargazing glass ceiling'],
                    ['name' => 'Farm Cottage',    'monthly_rate' => 0, 'daily_rate' => 5000, 'total_rooms' => 1, 'available_rooms' => 0, 'is_available' => false, 'capacity' => 4],
                ],
            ]),

            // ── 17. Lumbayan Beach Resort ──────────────────────────────────
            $this->base([
                'name' => 'Lumbayan Beach Resort',
                'full_address' => 'Coastal Area, Digos City, Davao del Sur',
                'address' => 'Coastal Area, Digos City, Davao del Sur',
                'description' => 'Lumbayan Beach Resort is a seaside resort offering affordable accommodations right on the coast of Digos City. Enjoy the beach, fresh sea breeze, and beautiful sunsets. Open for daily, overnight, and weekend stays.',
                'house_rules' => 'Swimming is at your own risk. Life vests available. No bringing of outside food to function areas.',
                'monthly_payment' => '1500',
                'price' => 1500,
                'contact_person' => 'Lumbayan Resort Management',
                'contact_name' => 'Lumbayan Resort Management',
                'contact_number' => '09430000117',
                'contact_phone' => '09430000117',
                'latitude' => 6.7350,
                'longitude' => 125.3450,
                'property_type' => 'other',
                'capacity' => 30,
                'available_rooms' => 8,
                'allows_pets' => false,
                'nearby_landmarks' => 'Digos City Coastline',
                '_amenities' => ['Beach Access', 'Outdoor Area', 'Dining Area', 'Parking', 'Swimming Pool', 'Water Included'],
                '_room_cats' => [
                    ['name' => 'Standard Cottage', 'monthly_rate' => 0, 'daily_rate' => 1500, 'total_rooms' => 6, 'available_rooms' => 4, 'is_available' => true,  'capacity' => 4],
                    ['name' => 'Deluxe Cottage',   'monthly_rate' => 0, 'daily_rate' => 2500, 'total_rooms' => 4, 'available_rooms' => 2, 'is_available' => true,  'capacity' => 6],
                    ['name' => 'Function Hall',    'monthly_rate' => 0, 'daily_rate' => 5000, 'total_rooms' => 1, 'available_rooms' => 1, 'is_available' => true,  'capacity' => 50],
                    ['name' => 'Glamping Tent',    'monthly_rate' => 0, 'daily_rate' => 1200, 'total_rooms' => 4, 'available_rooms' => 2, 'is_available' => true,  'capacity' => 2],
                ],
            ]),

            // ── 18. Summer Loft ────────────────────────────────────────────
            $this->base([
                'name' => 'Summer Loft',
                'full_address' => 'Zone 2, Digos City, Davao del Sur',
                'address' => 'Zone 2, Digos City, Davao del Sur',
                'description' => 'Summer Loft is a breezy and bright loft-style unit in Digos City. High ceilings, natural light, and a fun summer vibe make this the perfect place for creative professionals, students, or anyone looking for a fresh living space.',
                'house_rules' => 'No smoking. Keep loft area tidy. Noise curfew 10PM.',
                'monthly_payment' => '5000',
                'price' => 5000,
                'contact_person' => 'Summer Loft Host',
                'contact_name' => 'Summer Loft Host',
                'contact_number' => '09440000118',
                'contact_phone' => '09440000118',
                'latitude' => 6.7485,
                'longitude' => 125.3605,
                'property_type' => 'apartment',
                'capacity' => 4,
                'available_rooms' => 2,
                '_amenities' => ['Wi-Fi', 'Air Conditioning', 'Balcony', 'Kitchen Access', 'Private Bathroom', 'Water Included'],
                '_room_cats' => [
                    ['name' => 'Loft Studio', 'monthly_rate' => 5000, 'daily_rate' => 800, 'total_rooms' => 3, 'available_rooms' => 2, 'is_available' => true, 'capacity' => 2],
                ],
            ]),

            // ── 19. Cozy 2BR House Digos ───────────────────────────────────
            $this->base([
                'name' => 'Cozy 2BR House Digos',
                'full_address' => 'Residential Area, Digos City, Davao del Sur',
                'address' => 'Digos City, Davao del Sur',
                'description' => 'A cozy 2-bedroom house perfect for small families, couples, or friends traveling together. Fully furnished with a homey atmosphere, spacious living area, and a well-equipped kitchen. Located in a quiet residential area of Digos City.',
                'house_rules' => 'No sub-leasing. Maintain cleanliness. Report damages immediately.',
                'monthly_payment' => '7000',
                'price' => 7000,
                'contact_person' => 'Property Owner',
                'contact_name' => 'Property Owner',
                'contact_number' => '09450000119',
                'contact_phone' => '09450000119',
                'latitude' => 6.7445,
                'longitude' => 125.3560,
                'property_type' => 'other',
                'capacity' => 6,
                'available_rooms' => 2,
                'allows_pets' => true,
                '_amenities' => ['Wi-Fi', 'Air Conditioning', 'Kitchen Access', 'Living Room', 'Dining Area', 'Parking', 'Water Included', 'Hot Shower'],
                '_room_cats' => [
                    ['name' => 'Master Bedroom', 'monthly_rate' => 0, 'daily_rate' => 700,  'total_rooms' => 1, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 2],
                    ['name' => 'Bedroom 2',      'monthly_rate' => 0, 'daily_rate' => 600,  'total_rooms' => 1, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 2],
                    ['name' => 'Whole House',    'monthly_rate' => 7000, 'daily_rate' => 2500, 'total_rooms' => 1, 'available_rooms' => 1, 'is_available' => true, 'capacity' => 6],
                ],
            ]),
        ];
    }
}
