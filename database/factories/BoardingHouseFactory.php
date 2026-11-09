<?php

namespace Database\Factories;

use App\Models\BoardingHouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BoardingHouse>
 */
class BoardingHouseFactory extends Factory
{
    protected $model = BoardingHouse::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company.' Boarding House',
            'address' => $this->faker->buildingNumber.' Matti, Digos City, Davao del Sur, Davao Region',
            'latitude' => 6.7677800,
            'longitude' => 125.3062000,
            'description' => $this->faker->randomElement([
                'Near university belt with free Wi-Fi and study lounge.',
                'Walking distance to transport hubs; includes weekly cleaning.',
                'Secure compound with CCTV, shared kitchen, and laundry area.',
                'Quiet neighborhood, furnished rooms, and strong fiber internet.',
            ]),
            'house_rules' => 'No smoking. Quiet hours from 10PM to 6AM.',
            'monthly_payment' => $this->faker->numberBetween(2500, 7000),
            'capacity' => $this->faker->numberBetween(15, 80),
            'is_active' => $this->faker->boolean(90),
            'approval_status' => 'approved',
        ];
    }
}
