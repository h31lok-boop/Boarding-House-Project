<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\BoardingHouse;
use App\Models\Room;
use Illuminate\Database\Seeder;

class CapstoneSampleSeeder extends Seeder
{
    public function run(): void
    {
        $amenityIds = Amenity::pluck('id');

        BoardingHouse::query()->each(function (BoardingHouse $house) use ($amenityIds) {
            if ($house->approval_status !== 'approved') {
                $house->approval_status = 'approved';
            }

            if ($house->latitude === null || $house->longitude === null) {
                $house->latitude = fake()->randomFloat(7, 6.7000000, 6.7900000);
                $house->longitude = fake()->randomFloat(7, 125.3000000, 125.4100000);
            }

            $normalizedMonthly = is_numeric($house->monthly_payment)
                ? (float) $house->monthly_payment
                : (float) str_replace(',', '', (string) $house->monthly_payment);

            if ($normalizedMonthly <= 0) {
                $house->monthly_payment = fake()->numberBetween(2500, 7000);
            } else {
                $house->monthly_payment = $normalizedMonthly;
            }

            if ($house->contact_name === null) {
                $house->contact_name = 'Property Manager';
            }

            $house->save();

            if ($amenityIds->isNotEmpty() && $house->amenities()->count() === 0) {
                $selected = $amenityIds->shuffle()->take(rand(3, min(6, $amenityIds->count())))->all();
                $house->amenities()->syncWithoutDetaching($selected);
            }

            if ($house->rooms()->count() === 0) {
                $roomsToCreate = rand(4, 10);
                for ($i = 1; $i <= $roomsToCreate; $i++) {
                    $capacity = rand(1, 4);
                    $slots = rand(0, $capacity);

                    Room::create([
                        'boarding_house_id' => $house->id,
                        'room_no' => 'R-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                        'name' => 'Room ' . $i,
                        'price' => rand(1500, 5500),
                        'capacity' => $capacity,
                        'available_slots' => $slots,
                        'status' => $slots > 0 ? 'Available' : 'Occupied',
                        'description' => 'Sample room data for capstone demo.',
                    ]);
                }
            }
        });
    }
}
