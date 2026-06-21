<?php

use App\Models\BoardingHouse;
use App\Models\User;
use App\Models\UserPreference;
use App\Services\BoardingHouseRecommendationService;
use App\Services\LocationService;
use Database\Seeders\DsscBoardingHouseSeeder;
use Illuminate\Support\Facades\Schema;

test('DSSC location fields and sample listings are available', function () {
    expect(Schema::hasColumns('boarding_houses', [
        'nearby_landmark',
        'distance_from_dssc',
        'is_near_dssc',
        'barangay',
        'location_status',
    ]))->toBeTrue();
    expect(Schema::hasColumn('user_preferences', 'preferred_landmark'))->toBeTrue();

    $this->seed(DsscBoardingHouseSeeder::class);

    expect(BoardingHouse::query()
        ->whereIn('name', [
            'Matti Student Boarding House',
            'Purok 3 Boarding House',
            'DSSC Ladies Boarding House',
            'Mahayahay Student Home',
            'Tres de Mayo Boarding House',
            'City Proper Student Dorm',
        ])
        ->count())->toBe(6);

    $this->assertDatabaseHas('boarding_houses', [
        'name' => 'Matti Student Boarding House',
        'barangay' => 'Matti',
        'nearby_landmark' => 'DSSC Main Campus',
        'distance_from_dssc' => 0.50,
        'is_near_dssc' => true,
        'approval_status' => 'approved',
        'location_status' => 'approximate',
    ]);
});

test('location service calculates DSSC distance labels and returns only nearby available listings', function () {
    $service = app(LocationService::class);

    expect($service->calculateDistanceFromDSSC(config('dssc.latitude'), config('dssc.longitude')))->toBe(0.0)
        ->and($service->isNearDSSC(4.99))->toBeTrue()
        ->and($service->isNearDSSC(5.01))->toBeFalse()
        ->and($service->distanceLabel(0.7))->toBe('Very near DSSC Main Campus')
        ->and($service->distanceLabel(2.4))->toBe('Within 3 km of DSSC Main Campus')
        ->and($service->distanceLabel(7))->toBe('Far from DSSC Main Campus');

    $nearby = BoardingHouse::factory()->create([
        'name' => 'Location Service Nearby House',
        'barangay' => 'Matti',
        'distance_from_dssc' => 0.9,
        'available_rooms' => 1,
        'is_active' => true,
        'approval_status' => 'approved',
        'status' => 'approved',
    ]);
    BoardingHouse::factory()->create([
        'name' => 'Location Service Far House',
        'distance_from_dssc' => 6.2,
        'available_rooms' => 1,
        'is_active' => true,
        'approval_status' => 'approved',
        'status' => 'approved',
    ]);

    expect($service->getNearbyBoardingHouses(5)->pluck('id'))
        ->toContain($nearby->id);
});

test('DSSC preference gives closer Matti listings a higher location score', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    UserPreference::create([
        'user_id' => $user->id,
        'preferred_landmark' => 'DSSC Main Campus',
        'preferred_locations' => ['All nearby DSSC areas'],
        'preferred_rental_budget' => 3000,
        'distance_from_school' => 5,
        'room_type' => 'any',
        'cleanliness_level' => 3,
        'noise_tolerance' => 3,
    ]);

    $matti = BoardingHouse::factory()->create([
        'name' => 'Near Matti Test House',
        'address' => 'Matti, Digos City',
        'price' => 2500,
        'available_rooms' => 2,
        'nearby_landmark' => 'DSSC Main Campus',
        'distance_from_dssc' => 0.5,
        'is_near_dssc' => true,
        'is_active' => true,
        'approval_status' => 'approved',
        'status' => 'approved',
    ]);

    $cityProper = BoardingHouse::factory()->create([
        'name' => 'City Proper Test House',
        'address' => 'Poblacion / City Proper, Digos City',
        'price' => 2500,
        'available_rooms' => 2,
        'nearby_landmark' => 'DSSC Main Campus',
        'distance_from_dssc' => 4,
        'is_near_dssc' => true,
        'is_active' => true,
        'approval_status' => 'approved',
        'status' => 'approved',
    ]);

    $service = app(BoardingHouseRecommendationService::class);
    $nearScore = $service->score($user, $matti);
    $farScore = $service->score($user, $cityProper);

    expect($nearScore['scores']['location'])->toBeGreaterThan($farScore['scores']['location']);
    expect($nearScore['recommendation_percent'])->toBeGreaterThan($farScore['recommendation_percent']);
    expect($nearScore['distance_from_dssc'])->toBe(0.5);
});

test('near DSSC browse filter only shows approved active available listings', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    BoardingHouse::factory()->create([
        'name' => 'Eligible DSSC House',
        'available_rooms' => 2,
        'distance_from_dssc' => 0.8,
        'is_near_dssc' => true,
        'is_active' => true,
        'approval_status' => 'approved',
        'status' => 'approved',
    ]);

    BoardingHouse::factory()->create([
        'name' => 'Pending DSSC House',
        'available_rooms' => 2,
        'distance_from_dssc' => 0.7,
        'is_near_dssc' => true,
        'is_active' => true,
        'approval_status' => 'pending',
        'status' => 'pending',
    ]);

    BoardingHouse::factory()->create([
        'name' => 'Unavailable DSSC House',
        'available_rooms' => 0,
        'distance_from_dssc' => 0.6,
        'is_near_dssc' => true,
        'is_active' => true,
        'approval_status' => 'approved',
        'status' => 'approved',
    ]);

    $this->actingAs($user)
        ->get(route('user.boarding-houses.index', [
            'tab' => 'all',
            'dssc_area' => 'near',
            'available_only' => 1,
        ]))
        ->assertOk()
        ->assertSee('Eligible DSSC House')
        ->assertDontSee('Pending DSSC House')
        ->assertDontSee('Unavailable DSSC House')
        ->assertSee('DSSC Main Campus')
        ->assertSee('All nearby DSSC areas');
});

test('DSSC radius filter and interactive browse map use the filtered available listings', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    BoardingHouse::factory()->create([
        'name' => 'Within One Kilometer House',
        'latitude' => 6.7626,
        'longitude' => 125.3114,
        'distance_from_dssc' => 0.5,
        'is_near_dssc' => true,
        'available_rooms' => 2,
        'is_active' => true,
        'approval_status' => 'approved',
        'status' => 'approved',
    ]);
    BoardingHouse::factory()->create([
        'name' => 'Beyond One Kilometer House',
        'latitude' => 6.774,
        'longitude' => 125.3255,
        'distance_from_dssc' => 2.5,
        'is_near_dssc' => true,
        'available_rooms' => 2,
        'is_active' => true,
        'approval_status' => 'approved',
        'status' => 'approved',
    ]);

    $this->actingAs($user)
        ->get(route('user.boarding-houses.index', [
            'tab' => 'all',
            'dssc_area' => 'near',
            'dssc_radius' => 1,
        ]))
        ->assertOk()
        ->assertSee('Within One Kilometer House')
        ->assertDontSee('Beyond One Kilometer House')
        ->assertSee('Within 1 km')
        ->assertSee('data-boardmatch-browse-map', false)
        ->assertSee('data-boardmatch-browse-map-config', false);
});

test('admin location form saves map coordinates and computes DSSC distance', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.boarding-houses.create'))
        ->assertOk()
        ->assertSee('Pick Location on Map')
        ->assertSee('name="barangay"', false)
        ->assertSee('name="nearby_landmark"', false)
        ->assertSee('name="location_status"', false);

    $this->actingAs($admin)
        ->post(route('admin.listings.store'), [
            'name' => 'Map Picked DSSC House',
            'address' => 'Near DSSC Main Campus, Matti, Digos City',
            'barangay' => 'Matti',
            'nearby_landmark' => 'DSSC Main Campus',
            'latitude' => config('dssc.latitude'),
            'longitude' => config('dssc.longitude'),
            'distance_from_dssc' => 99,
            'is_near_dssc' => 0,
            'location_status' => 'exact',
            'available_rooms' => 2,
            'is_active' => 1,
            'approval_status' => 'approved',
        ])
        ->assertRedirect(route('admin.listings'));

    $this->assertDatabaseHas('boarding_houses', [
        'name' => 'Map Picked DSSC House',
        'barangay' => 'Matti',
        'distance_from_dssc' => 0,
        'is_near_dssc' => true,
        'location_status' => 'exact',
    ]);
});

test('preference form exposes DSSC landmark areas and distance choices', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('user.preferences.index'))
        ->assertOk()
        ->assertSee('name="preferred_landmark"', false)
        ->assertSee('DSSC Main Campus')
        ->assertSee('Purok 3, Matti')
        ->assertSee('All nearby DSSC areas')
        ->assertSee('Maximum Distance from DSSC');
});
