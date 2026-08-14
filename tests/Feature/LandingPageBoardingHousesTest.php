<?php

use App\Models\BoardingHouse;
use App\Models\Room;

test('landing page displays the top three available approved boarding houses from the database', function () {
    $houses = collect([
        ['name' => 'Database House One', 'rooms' => 4, 'image' => 'https://example.test/house-one.jpg'],
        ['name' => 'Database House Two', 'rooms' => 3, 'image' => 'https://example.test/house-two.jpg'],
        ['name' => 'Database House Three', 'rooms' => 2, 'image' => 'https://example.test/house-three.jpg'],
        ['name' => 'Database House Four', 'rooms' => 1, 'image' => 'https://example.test/house-four.jpg'],
    ])->map(function (array $data) {
        $house = BoardingHouse::factory()->create([
            'name' => $data['name'],
            'is_active' => true,
            'approval_status' => 'approved',
            'available_rooms' => $data['rooms'],
            'featured_image' => $data['image'],
        ]);

        foreach (range(1, $data['rooms']) as $roomNumber) {
            Room::create([
                'boarding_house_id' => $house->id,
                'room_no' => $house->id.'-'.$roomNumber,
                'status' => 'available',
                'available_slots' => 1,
                'price' => 3000 + $roomNumber,
            ]);
        }

        return $house;
    });

    BoardingHouse::factory()->create([
        'name' => 'Unapproved Database House',
        'is_active' => true,
        'approval_status' => 'pending',
        'available_rooms' => 99,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee($houses[0]->name)
        ->assertSee($houses[1]->name)
        ->assertSee($houses[2]->name)
        ->assertSee('https://example.test/house-one.jpg', false)
        ->assertDontSee($houses[3]->name)
        ->assertDontSee('Unapproved Database House');
});

test('landing page shows a clean empty state when no approved property has availability', function () {
    BoardingHouse::factory()->create([
        'name' => 'Unavailable Property',
        'is_active' => true,
        'approval_status' => 'approved',
        'available_rooms' => 0,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('No available boarding houses yet')
        ->assertDontSee('Unavailable Property');
});
