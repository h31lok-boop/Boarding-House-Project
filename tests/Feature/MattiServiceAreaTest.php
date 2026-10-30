<?php

use App\Models\BoardingHouse;
use App\Models\Reservation;
use App\Models\TenantMatchProfile;
use App\Models\User;
use App\Services\BoardingHouseRecommendationService;
use App\Services\MattiServiceArea;
use App\Services\TenantPreferenceRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('Matti accepts an interior property and preserves its persisted location', function () {
    $house = BoardingHouse::factory()->create([
        'latitude' => 6.7683100,
        'longitude' => 125.3078200,
    ]);

    $saved = BoardingHouse::query()->findOrFail($house->id);

    expect($saved->latitude)->toBe('6.7683100')
        ->and($saved->longitude)->toBe('125.3078200')
        ->and($saved->barangay)->toBe('Matti')
        ->and(app(MattiServiceArea::class)->contains($saved->latitude, $saved->longitude))->toBeTrue();
});

test('Matti rejects invalid coordinates through the service boundary', function ($latitude, $longitude) {
    expect(fn () => app(MattiServiceArea::class)->validate($latitude, $longitude))
        ->toThrow(ValidationException::class);

    $this->assertDatabaseCount('boarding_houses', 0);
})->with([
    'Digos city outside Matti' => [6.744, 125.355],
    'western concavity inside bounding rectangle' => [6.75, 125.30],
    'northeastern area inside bounding rectangle' => [6.78, 125.317],
    'missing latitude' => [null, 125.3062],
    'missing longitude' => [6.76778, null],
    'missing location' => [null, null],
]);

test('legacy records may be edited outside Matti but leave public service-area queries', function () {
    $house = BoardingHouse::factory()->create();

    $house->update([
        'latitude' => 6.75,
        'longitude' => 125.30,
    ]);

    $this->assertDatabaseHas('boarding_houses', [
        'id' => $house->id,
        'latitude' => 6.75,
        'longitude' => 125.30,
    ]);
    expect(BoardingHouse::query()->whereKey($house->id)->exists())->toBeTrue()
        ->and(BoardingHouse::query()->inServiceArea()->whereKey($house->id)->exists())->toBeFalse();
});

test('legacy properties remain available to internal queries but are excluded from both map APIs', function (string $role, string $mapRoute) {
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $inside = BoardingHouse::factory()->create([
        'name' => 'Eligible Matti Listing',
        'is_active' => true,
        'status' => 'approved',
    ]);

    foreach ([[6.75, 125.30], [6.78, 125.317], [6.744, 125.355], [null, null]] as [$latitude, $longitude]) {
        DB::table('boarding_houses')->insert(BoardingHouse::factory()->raw([
            'name' => 'Legacy Outside Listing',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_active' => true,
            'status' => 'approved',
        ]));
    }

    expect(BoardingHouse::query()->pluck('id')->all())->toHaveCount(5)
        ->and(BoardingHouse::query()->inServiceArea()->pluck('id')->all())->toBe([$inside->id]);
    $this->assertDatabaseCount('boarding_houses', 5);

    $response = $this->actingAs($user)->getJson(route($mapRoute));
    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.id'))->toBe($inside->id);

    $this->getJson(route($mapRoute, ['q' => 'Legacy Outside']))
        ->assertOk()->assertJsonCount(0, 'data');
})->with([
    'tenant map' => ['user', 'map.user.listings'],
    'administrator map' => ['admin', 'map.admin.listings'],
]);

test('eligibility changes immediately when legacy coordinates change outside model events', function () {
    $house = BoardingHouse::factory()->create();
    expect(BoardingHouse::query()->whereKey($house->id)->exists())->toBeTrue()
        ->and(BoardingHouse::query()->inServiceArea()->whereKey($house->id)->exists())->toBeTrue();

    DB::table('boarding_houses')->where('id', $house->id)->update([
        'latitude' => 6.75,
        'longitude' => 125.30,
    ]);

    expect(BoardingHouse::query()->whereKey($house->id)->exists())->toBeTrue()
        ->and(BoardingHouse::query()->inServiceArea()->whereKey($house->id)->exists())->toBeFalse();

    DB::table('boarding_houses')->where('id', $house->id)->update([
        'latitude' => 6.746,
        'longitude' => 125.308,
    ]);

    expect(BoardingHouse::query()->whereKey($house->id)->exists())->toBeTrue()
        ->and(BoardingHouse::query()->inServiceArea()->whereKey($house->id)->exists())->toBeTrue();
});

test('legacy outside IDs cannot open property details or submit a reservation', function () {
    $tenant = User::factory()->create(['role' => 'user', 'is_active' => true]);
    $house = BoardingHouse::factory()->create(['is_active' => true, 'status' => 'approved']);
    DB::table('boarding_houses')->where('id', $house->id)->update([
        'latitude' => 6.75,
        'longitude' => 125.30,
    ]);

    $this->actingAs($tenant)->get(route('user.boarding-houses.show', $house->id))->assertNotFound();
    $this->postJson(route('user.boarding-houses.reservations.store', $house->id), [
        'check_in_date' => now()->addWeek()->toDateString(),
        'occupants' => 1,
        'terms_accepted' => '1',
    ])->assertNotFound();

    $this->assertDatabaseCount('reservations', 0);
});

test('recommendation collections exclude outside properties while retaining eligible scores', function () {
    $tenant = User::factory()->create(['role' => 'user']);
    TenantMatchProfile::query()->create([
        'user_id' => $tenant->id,
        'budget_min' => 2500,
        'budget_max' => 4500,
        'preferred_location' => 'Matti',
        'completed_at' => now(),
    ]);
    $inside = BoardingHouse::factory()->create([
        'name' => 'Matti Match',
        'price' => 3500,
        'monthly_payment' => '3500',
        'available_rooms' => 2,
        'is_active' => true,
        'status' => 'approved',
    ]);
    $legacy = BoardingHouse::factory()->create([
        'price' => 3000,
        'monthly_payment' => '3000',
        'available_rooms' => 5,
        'is_active' => true,
        'status' => 'approved',
    ]);
    DB::table('boarding_houses')->where('id', $legacy->id)->update([
        'latitude' => 6.75,
        'longitude' => 125.30,
    ]);
    $outside = BoardingHouse::withoutGlobalScopes()->findOrFail($legacy->id);

    foreach ([BoardingHouseRecommendationService::class, TenantPreferenceRecommendationService::class] as $serviceClass) {
        $service = app($serviceClass);
        $baseline = $service->rank($tenant, collect([$inside]));
        $mixed = $service->rank($tenant, collect([$outside, $inside]));

        expect($baseline)->toHaveCount(1)
            ->and($mixed)->toHaveCount(1)
            ->and($mixed->first()['house']->id)->toBe($inside->id)
            ->and($mixed->first()['recommendation'])->toEqual($baseline->first()['recommendation']);
    }

    $this->assertDatabaseMissing('boarding_house_matches', [
        'boarding_house_id' => $outside->id,
    ]);
});

test('new reservations cannot bypass Matti eligibility through model creation', function () {
    $tenant = User::factory()->create();
    $house = BoardingHouse::factory()->create();
    DB::table('boarding_houses')->where('id', $house->id)->update([
        'latitude' => 6.75,
        'longitude' => 125.30,
    ]);

    expect(fn () => Reservation::query()->create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'status' => 'pending',
    ]))->toThrow(ValidationException::class);

    $this->assertDatabaseCount('reservations', 0);
});

test('historical outside reservations can be cancelled but cannot be approved', function () {
    $tenant = User::factory()->create();
    $house = BoardingHouse::factory()->create();
    $reservation = Reservation::query()->create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'status' => 'pending',
    ]);
    DB::table('boarding_houses')->where('id', $house->id)->update([
        'latitude' => 6.75,
        'longitude' => 125.30,
    ]);

    expect(fn () => $reservation->update(['status' => 'approved']))->toThrow(ValidationException::class);
    expect($reservation->fresh()->status)->toBe('pending');

    $reservation->refresh()->update(['status' => 'cancelled']);
    expect($reservation->fresh()->status)->toBe('cancelled');
    $this->assertDatabaseCount('reservations', 1);
});

test('the source polygon accepts exact boundary points without admitting adjacent exterior points', function () {
    $area = app(MattiServiceArea::class);
    $ring = $area->mapConfig()['geometry']['coordinates'][0];
    [$longitude, $latitude] = $ring[0];
    [$nextLongitude, $nextLatitude] = $ring[1];
    $midLatitude = ($latitude + $nextLatitude) / 2;
    $midLongitude = ($longitude + $nextLongitude) / 2;

    expect($area->contains($latitude, $longitude))->toBeTrue()
        ->and($area->contains($midLatitude, $midLongitude))->toBeTrue()
        ->and($area->contains($midLatitude, $midLongitude - 0.00001))->toBeTrue()
        ->and($area->contains($midLatitude, $midLongitude + 0.00001))->toBeFalse()
        ->and($area->contains(6.76778, 125.3062))->toBeTrue()
        ->and($area->contains(6.746, 125.308))->toBeTrue();
});

test('the service area uses the supplied Matti locality reference', function () {
    $mapConfig = app(MattiServiceArea::class)->mapConfig();

    expect(config('service-area.name'))
        ->toBe('Matti, Digos City, Davao del Sur, Davao Region')
        ->and($mapConfig['center'])->toEqual([125.3054672, 6.7718373])
        ->and($mapConfig['referenceUrl'])->toContain('google.com/maps/place/Matti');
});
