<?php

use App\Models\BoardingHouse;
use App\Models\Room;
use App\Models\User;

test('owner can categorize the room list by one of their boarding houses', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $otherOwner = User::factory()->verifiedOwner()->create();

    $firstHouse = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Owner Alpha House',
    ]);
    $secondHouse = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Owner Beta House',
    ]);
    $foreignHouse = BoardingHouse::factory()->create([
        'owner_id' => $otherOwner->id,
        'name' => 'Foreign Owner House',
    ]);

    Room::create(['boarding_house_id' => $firstHouse->id, 'room_no' => 'ALPHA-ONLY', 'status' => 'available']);
    Room::create(['boarding_house_id' => $secondHouse->id, 'room_no' => 'BETA-ONLY', 'status' => 'available']);
    Room::create(['boarding_house_id' => $foreignHouse->id, 'room_no' => 'FOREIGN-ONLY', 'status' => 'available']);

    $this->actingAs($owner)
        ->get(route('owner.rooms', ['boarding_house_id' => $firstHouse->id]))
        ->assertOk()
        ->assertSee('All My Boarding Houses')
        ->assertSee('Owner Alpha House')
        ->assertSee('Owner Beta House')
        ->assertDontSee('Foreign Owner House')
        ->assertSee('ALPHA-ONLY')
        ->assertDontSee('BETA-ONLY')
        ->assertDontSee('FOREIGN-ONLY');
});
