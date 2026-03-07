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
        $cities = [
            'Quezon City, Metro Manila',
            'Makati City, Metro Manila',
            'Taguig City, Metro Manila',
            'Pasig City, Metro Manila',
            'Cebu City, Cebu',
            'Davao City, Davao del Sur',
            'Baguio City, Benguet',
            'Iloilo City, Iloilo',
            'Bacolod City, Negros Occidental',
            'Cagayan de Oro, Misamis Oriental',
        ];

        $city = $this->faker->randomElement($cities);

        return [
            'name' => $this->faker->company.' Boarding House',
            'address' => 'Brgy. '.$this->faker->streetName.', '.$city,
            'latitude' => $this->faker->randomFloat(7, 6.7000000, 6.7900000),
            'longitude' => $this->faker->randomFloat(7, 125.3000000, 125.4100000),
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
