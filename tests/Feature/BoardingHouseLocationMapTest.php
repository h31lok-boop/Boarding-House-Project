<?php

use App\Models\BoardingHouse;
use App\Models\User;

test('tenant boarding house details renders the interactive route planner and map fallbacks', function () {
    $tenant = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $house = BoardingHouse::factory()->create([
        'name' => 'Navigation Ready House',
        'address' => 'Matti, Digos City',
        'nearby_landmark' => 'DSSC Main Campus',
        'latitude' => 6.7587400,
        'longitude' => 125.3090900,
        'distance_from_dssc' => 1.2,
        'price' => 3500,
        'available_rooms' => 2,
        'is_active' => true,
        'status' => 'approved',
        'approval_status' => 'approved',
    ]);

    $this->actingAs($tenant)
        ->get(route('user.boarding-houses.show', $house))
        ->assertOk()
        ->assertSee('Live Navigation to Boarding House')
        ->assertSee('data-boardmatch-location-map', false)
        ->assertSee('data-route-current', false)
        ->assertSee('data-route-dssc', false)
        ->assertSee('Use My Current Location')
        ->assertSee('Route From DSSC Main Campus')
        ->assertSee('Open in OpenStreetMap')
        ->assertSee('Reset Map')
        ->assertSee('Route Options')
        ->assertSee('"dssc":', false)
        ->assertSee('data-travel-mode="WALKING"', false)
        ->assertSee('data-travel-mode="DRIVING"', false)
        ->assertSee('data-travel-mode="TWO_WHEELER"', false)
        ->assertSee('data-travel-mode="TRANSIT"', false)
        ->assertSee('Turn-by-Turn Directions')
        ->assertSee('data-route-steps', false)
        ->assertSee('https://www.openstreetmap.org/', false);

    $mapScript = file_get_contents(resource_path('js/boarding-house-map.js'));
    expect($mapScript)
        ->toContain('Enable location services to see live routes from your current location. The map is centered on the boarding house for now.')
        ->toContain('Route could not be generated right now. Please try again in a moment.')
        ->toContain('Map reset. Open the reservation panel or tap Reserve Room to route again.')
        ->toContain('autoLocateFromReservationFlow')
        ->toContain('routeFromDssc');
});

test('details page keeps a map unavailable state when coordinates are missing', function () {
    $tenant = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $house = BoardingHouse::factory()->create([
        'name' => 'Address Only House',
        'address' => 'Poblacion, Digos City',
        'latitude' => null,
        'longitude' => null,
        'available_rooms' => 1,
        'is_active' => true,
        'status' => 'approved',
        'approval_status' => 'approved',
    ]);

    $this->actingAs($tenant)
        ->get(route('user.boarding-houses.show', $house))
        ->assertOk()
        ->assertSee('Map route is unavailable because this boarding house has no saved coordinates.')
        ->assertSee('"latitude":null', false)
        ->assertSee('"longitude":null', false);
});
