<?php

use App\Models\Amenity;
use App\Models\BoardingHouse;
use App\Models\RoomCategory;
use App\Models\User;
use App\Models\UserPreference;

function createBrowseUser(?array $preference = []): User
{
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    if ($preference !== null) {
        UserPreference::create(array_merge([
            'user_id' => $user->id,
            'preferred_rental_budget' => 4500,
            'preferred_locations' => ['Poblacion'],
            'distance_from_school' => 3,
            'room_type' => 'bedspace',
            'study_habits' => 'quiet_focus',
            'sleeping_schedule' => 'balanced',
            'cleanliness_level' => 5,
            'noise_tolerance' => 20,
            'amenities' => ['Wi-Fi'],
            'safety_preferences' => ['high'],
            'profile_completion' => 80,
        ], $preference));
    }

    return $user;
}

function createAvailableBrowseHouse(array $attributes = []): BoardingHouse
{
    $roomType = $attributes['room_type_name'] ?? 'Bedspace';
    unset($attributes['room_type_name'], $attributes['property_type']);

    $house = BoardingHouse::factory()->create(array_merge([
        'address' => 'Poblacion, Digos City',
        'price' => 3500,
        'monthly_payment' => '3500',
        'available_rooms' => 2,
        'latitude' => 6.7440000,
        'longitude' => 125.3550000,
        'is_active' => true,
        'status' => 'approved',
        'approval_status' => 'approved',
    ], $attributes));

    if ((int) $house->available_rooms > 0) {
        RoomCategory::create([
            'boarding_house_id' => $house->id,
            'name' => $roomType,
            'monthly_rate' => $house->price,
            'total_rooms' => $house->available_rooms,
            'available_rooms' => $house->available_rooms,
            'is_available' => true,
        ]);
    }

    return $house;
}

test('recommended tab ranks approved available boarding houses using saved preferences', function () {
    $wifi = Amenity::create(['name' => 'Wi-Fi']);
    $user = createBrowseUser();

    $strong = createAvailableBrowseHouse(['name' => 'Poblacion Student Bedspace']);
    $strong->amenities()->sync([$wifi->id]);

    createAvailableBrowseHouse([
        'name' => 'Unrelated Premium Studio',
        'address' => 'Aplaya, Digos City',
        'price' => 12000,
        'monthly_payment' => '12000',
        'room_type_name' => 'Studio Unit',
        'latitude' => 6.7900000,
        'longitude' => 125.4100000,
    ]);

    $response = $this->actingAs($user)->get(route('user.boarding-houses.index'));

    $response->assertOk()
        ->assertSee('2 boarding houses found in Digos City')
        ->assertSee('Poblacion Student Bedspace')
        ->assertSee('Unrelated Premium Studio')
        ->assertSee('% Match')
        ->assertDontSee('bm-map-marker', false)
        ->assertDontSee('Map View')
        ->assertDontSee('Open DSSC Area Map');

    expect(strpos($response->getContent(), 'Poblacion Student Bedspace'))
        ->toBeLessThan(strpos($response->getContent(), 'Unrelated Premium Studio'));
});

test('matchmaking tab renders smart matchmaking dashboard', function () {
    $wifi = Amenity::create(['name' => 'Wi-Fi']);
    $user = createBrowseUser();

    $house = createAvailableBrowseHouse(['name' => 'Smart Match House']);
    $house->amenities()->sync([$wifi->id]);

    $response = $this->actingAs($user)->get(route('user.boarding-houses.index', [
        'tab' => 'matchmaking',
    ]));

    $response->assertOk()
        ->assertSee('Smart Matchmaking')
        ->assertSee('Average Match Score')
        ->assertSee('Smart Match House')
        ->assertDontSee('Discover and compare boarding houses in Digos City');
});

test('recommended tab still returns ranked low compatibility options when listings are available', function () {
    $user = createBrowseUser([
        'preferred_rental_budget' => 1000,
        'preferred_locations' => ['Nonexistent Barangay'],
        'distance_from_school' => 0.5,
        'room_type' => 'studio',
        'amenities' => ['Swimming Pool'],
    ]);

    createAvailableBrowseHouse(['name' => 'Closest Similar House']);
    createAvailableBrowseHouse([
        'name' => 'Second Similar House',
        'address' => 'Aplaya, Digos City',
        'price' => 6000,
        'monthly_payment' => '6000',
        'room_type_name' => 'Private Room',
    ]);

    $response = $this->actingAs($user)->get(route('user.boarding-houses.index'));

    $response->assertOk()
        ->assertSee('Closest Similar House')
        ->assertSee('Second Similar House')
        ->assertDontSee('0 boarding houses found');
});

test('users without preferences see the recommendation empty state and can browse all approved available houses', function () {
    $user = createBrowseUser(null);

    createAvailableBrowseHouse(['name' => 'Approved Available House']);
    createAvailableBrowseHouse([
        'name' => 'Pending House',
        'status' => 'pending',
        'approval_status' => 'pending',
    ]);
    createAvailableBrowseHouse([
        'name' => 'Unavailable House',
        'available_rooms' => 0,
    ]);

    $recommended = $this->actingAs($user)->get(route('user.boarding-houses.index'));

    $recommended->assertOk()
        ->assertSee('Set your preferences first')
        ->assertSee('Set My Preferences')
        ->assertDontSee('0 boarding houses found');

    $all = $this->actingAs($user)->get(route('user.boarding-houses.index', ['tab' => 'all']));

    $all->assertOk()
        ->assertSee('Approved Available House')
        ->assertDontSee('Pending House')
        ->assertDontSee('Unavailable House')
        ->assertSee('1 boarding house found in Digos City');
});

test('manual search uses normal filters without unrelated fallback results', function () {
    $user = createBrowseUser();

    createAvailableBrowseHouse(['name' => 'Preferred Poblacion House']);
    createAvailableBrowseHouse([
        'name' => 'Aplaya Search Result',
        'address' => 'Aplaya, Digos City',
        'price' => 7000,
        'monthly_payment' => '7000',
        'room_type_name' => 'Studio Unit',
    ]);

    $response = $this->actingAs($user)->get(route('user.boarding-houses.index', [
        'q' => 'Aplaya Search Result',
    ]));

    $response->assertOk()
        ->assertSee('Aplaya Search Result')
        ->assertDontSee('Preferred Poblacion House')
        ->assertDontSee('% Match');

    $fallback = $this->actingAs($user)->get(route('user.boarding-houses.index', [
        'q' => 'Nothing Matches This Search',
    ]));

    $fallback->assertOk()
        ->assertSee('0 boarding houses found in Digos City')
        ->assertDontSee('Preferred Poblacion House')
        ->assertDontSee('Aplaya Search Result');
});
