<?php

use App\Models\BoardingHouse;
use App\Models\Reservation;
use App\Models\TenantMatchProfile;
use App\Models\User;
use App\Services\BoardingHouseRecommendationService;

test('reservation compatibility uses the tenant reservation and recalculates after preference changes', function () {
    $tenant = User::factory()->create(['role' => 'user', 'is_active' => true, 'email_verified_at' => now()]);
    $profile = TenantMatchProfile::create(['user_id' => $tenant->id, 'budget_min' => 2500, 'budget_max' => 4500, 'completed_at' => now()]);
    $house = BoardingHouse::factory()->create(['price' => 3500, 'monthly_payment' => '3500', 'available_rooms' => 2]);
    $reservation = Reservation::create(['user_id' => $tenant->id, 'boarding_house_id' => $house->id, 'status' => 'pending', 'payment_status' => 'unpaid']);
    $otherTenant = User::factory()->create(['role' => 'user']);
    $otherHouse = BoardingHouse::factory()->create();
    Reservation::create(['user_id' => $otherTenant->id, 'boarding_house_id' => $otherHouse->id, 'status' => 'pending']);

    $expected = app(BoardingHouseRecommendationService::class)->score($tenant->fresh(), $house->fresh());
    $response = $this->actingAs($tenant)->get(route('user.reservations.index'))
        ->assertOk()
        ->assertViewHas('currentReservation', fn ($current) => $current->id === $reservation->id)
        ->assertViewHas('reservationCompatibility', $expected)
        ->assertSee('Why This Matches You')
        ->assertSee('data-boardmatch-location-map', false)
        ->assertSee(route('user.reservations.cancel', $reservation), false);

    foreach ($expected['breakdown'] as $criterion => $factor) {
        $response->assertSee('data-match-factor="'.$criterion.'"', false);
    }
    expect((int) round(array_sum(array_column($expected['breakdown'], 'weighted_score')) * 100))
        ->toBe($expected['recommendation_percent']);
    $this->assertDatabaseCount('boarding_house_matches', 0);

    $profile->update(['budget_min' => 500, 'budget_max' => 1000]);
    $updated = app(BoardingHouseRecommendationService::class)->score($tenant->fresh(), $house->fresh());
    expect($updated['recommendation_percent'])->toBeLessThan($expected['recommendation_percent']);
    $this->actingAs($tenant->fresh())->get(route('user.reservations.index'))
        ->assertOk()
        ->assertViewHas('reservationCompatibility', $updated)
        ->assertSee('Rental price may be outside your budget.');
    expect($reservation->fresh()->status)->toBe('pending');
    expect($reservation->fresh()->payment_status)->toBe('unpaid');
});

test('reservation compatibility prompts for missing preferences without showing a zero percent match', function () {
    $tenant = User::factory()->create(['role' => 'user', 'is_active' => true, 'email_verified_at' => now()]);
    $house = BoardingHouse::factory()->create();
    Reservation::create(['user_id' => $tenant->id, 'boarding_house_id' => $house->id, 'status' => 'pending']);

    $this->actingAs($tenant)->get(route('user.reservations.index'))
        ->assertOk()
        ->assertSee('Save your preferences to see a personalized compatibility score and breakdown.')
        ->assertDontSee('data-reservation-match-score', false);
});

test('reservation preview scores the displayed property and empty reservations have no score', function () {
    $tenant = User::factory()->create(['role' => 'user', 'is_active' => true, 'email_verified_at' => now()]);
    $this->actingAs($tenant)->get(route('user.reservations.index'))
        ->assertOk()->assertViewHas('reservationCompatibility', null)
        ->assertDontSee('data-reservation-compatibility', false);

    TenantMatchProfile::create(['user_id' => $tenant->id, 'budget_min' => 2500, 'budget_max' => 4500, 'completed_at' => now()]);
    $reservedHouse = BoardingHouse::factory()->create(['price' => 9000, 'monthly_payment' => '9000']);
    Reservation::create(['user_id' => $tenant->id, 'boarding_house_id' => $reservedHouse->id, 'status' => 'pending']);
    $preview = BoardingHouse::factory()->create(['price' => 3500, 'monthly_payment' => '3500', 'is_active' => true]);
    $expected = app(BoardingHouseRecommendationService::class)->score($tenant->fresh(), $preview->fresh());
    $this->actingAs($tenant->fresh())->get(route('user.reservations.index', ['house' => $preview->id]))
        ->assertOk()->assertViewHas('reservationCompatibility', $expected);
});
