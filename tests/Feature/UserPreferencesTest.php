<?php

use App\Models\Amenity;
use App\Models\BoardingHouse;
use App\Models\User;
use App\Models\UserPreference;

test('preferences page exposes the requested responsive preference controls', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('user.preferences.index'))
        ->assertOk()
        ->assertSee('name="family_monthly_income"', false)
        ->assertSee('name="preferred_rental_budget"', false)
        ->assertSee('name="preferred_locations[]"', false)
        ->assertSee('name="distance_from_school"', false)
        ->assertSee('name="room_type"', false)
        ->assertSee('name="amenities[]"', false)
        ->assertSee('Save Preferences')
        ->assertSee('AI Readiness');
});

test('user preferences are saved and used to refresh boarding house matches', function () {
    $wifi = Amenity::create(['name' => 'Wi-Fi']);
    $house = BoardingHouse::factory()->create([
        'name' => 'Poblacion Study House',
        'address' => 'Poblacion, Digos City',
        'price' => 3500,
        'available_rooms' => 3,
        'is_active' => true,
        'approval_status' => 'approved',
        'status' => 'approved',
    ]);
    $house->amenities()->sync([$wifi->id]);

    $user = User::factory()->create([
        'role' => 'user',
        'phone' => '09171234567',
        'is_active' => true,
        'email_verified_at' => now(),
        'allow_matchmaking_data' => true,
        'allow_owner_messages' => true,
        'show_profile_to_owners' => true,
    ]);

    $this->actingAs($user)
        ->put(route('user.preferences.update'), [
            'family_monthly_income' => '20000_50000',
            'monthly_allowance' => '5000_10000',
            'preferred_rental_budget' => 4500,
            'preferred_landmark' => 'DSSC Main Campus',
            'preferred_locations' => ['Poblacion'],
            'distance_from_school' => 3,
            'room_type' => 'private',
            'study_habits' => 'quiet_focus',
            'sleeping_schedule' => 'balanced',
            'cleanliness_level' => 5,
            'noise_tolerance' => 20,
            'safety_preferences' => ['high', 'CCTV'],
            'amenities' => ['Wi-Fi'],
            'lifestyle_notes' => 'Needs quiet hours and a clean study-friendly house.',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('user.preferences.index'));

    $preference = UserPreference::whereBelongsTo($user)->firstOrFail();

    expect($preference->preferred_locations)->toBe(['Poblacion']);
    expect($preference->preferred_landmark)->toBe('DSSC Main Campus');
    expect($preference->amenities)->toBe(['Wi-Fi']);
    expect($preference->isAiReady())->toBeTrue();
    expect($preference->profile_completion)->toBeGreaterThan(0);

    $this->assertDatabaseHas('boarding_house_matches', [
        'user_id' => $user->id,
        'boarding_house_id' => $house->id,
    ]);
});

test('dashboard prompts users without saved preferences to complete them', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertSee('Complete your preferences first to get better AI recommendations.');
});

test('preference arrays and numeric limits are validated', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->from(route('user.preferences.index'))
        ->put(route('user.preferences.update'), [
            'preferred_rental_budget' => -1,
            'preferred_locations' => 'Poblacion',
            'distance_from_school' => 500,
            'cleanliness_level' => 8,
            'amenities' => 'Wi-Fi',
        ])
        ->assertRedirect(route('user.preferences.index'))
        ->assertSessionHasErrors([
            'preferred_rental_budget',
            'preferred_locations',
            'distance_from_school',
            'cleanliness_level',
            'amenities',
        ]);
});

test('generating matches requires the core recommendation fields', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->from(route('user.preferences.index'))
        ->put(route('user.preferences.update'), [
            'intent' => 'generate',
            'budget_min' => 2500,
        ])
        ->assertRedirect(route('user.preferences.index'))
        ->assertSessionHasErrors([
            'budget_max',
            'preferred_locations',
            'distance_from_school',
            'room_type',
            'study_habits',
            'sleeping_schedule',
            'cleanliness_level',
            'noise_tolerance',
            'amenities',
        ]);
});

test('generate recommendations endpoint redirects users without preferences to setup', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('user.matchmaking.generate'))
        ->assertRedirect(route('user.preferences.index'))
        ->assertSessionHas('error');
});

test('generate recommendations endpoint refreshes saved matches', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    UserPreference::create([
        'user_id' => $user->id,
        'preferred_rental_budget' => 5000,
        'preferred_rental_budget_min' => 2500,
        'preferred_rental_budget_max' => 5000,
        'preferred_locations' => ['Poblacion'],
        'distance_from_school' => 5,
        'room_type' => 'any',
        'study_habits' => 'quiet_focus',
        'sleeping_schedule' => 'balanced',
        'cleanliness_level' => 4,
        'noise_tolerance' => 2,
        'amenities' => ['Wi-Fi'],
    ]);

    $house = BoardingHouse::factory()->create([
        'name' => 'Available Poblacion House',
        'address' => 'Poblacion, Digos City',
        'available_rooms' => 2,
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    $this->actingAs($user)
        ->post(route('user.matchmaking.generate'))
        ->assertRedirect(route('user.matchmaking.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('boarding_house_matches', [
        'user_id' => $user->id,
        'boarding_house_id' => $house->id,
    ]);
});
