<?php

use App\Models\Amenity;
use App\Models\User;

test('user can view the match profile form', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('user.preferences.index'))
        ->assertOk()
        ->assertSee('My Preferences');
});

test('admin cannot access the user match profile form', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('user.preferences.index'))
        ->assertForbidden();
});

test('user can save matchmaking preferences', function () {
    $wifi = Amenity::create(['name' => 'Wi-Fi']);
    $laundry = Amenity::create(['name' => 'Laundry']);

    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->put(route('user.preferences.update'), [
            'budget_min' => 2500,
            'budget_max' => 4500,
            'gender_preference' => 'no_preference',
            'sleep_schedule' => 'balanced',
            'study_habits' => 'quiet_focus',
            'cleanliness_level' => 4,
            'noise_tolerance' => 2,
            'smoking_preference' => 'non_smoker_only',
            'drinking_preference' => 'occasional_ok',
            'pets_preference' => 'no_pets',
            'internet_usage' => 'heavy',
            'hobbies' => ['reading', 'coding'],
            'preferred_amenity_ids' => [$wifi->id, $laundry->id],
            'additional_notes' => 'Prefers quiet evenings and shared cleaning routines.',
        ])
        ->assertRedirect(route('user.preferences.index'));

    $this->assertDatabaseHas('tenant_match_profiles', [
        'user_id' => $user->id,
        'budget_min' => 2500.00,
        'budget_max' => 4500.00,
        'sleep_schedule' => 'balanced',
        'study_habits' => 'quiet_focus',
        'cleanliness_level' => 4,
        'noise_tolerance' => 2,
        'internet_usage' => 'heavy',
    ]);

    expect($user->fresh()->tenantMatchProfile->hobbies)->toBe(['reading', 'coding']);
    expect($user->fresh()->tenantMatchProfile->preferred_amenity_ids)->toBe([$wifi->id, $laundry->id]);
});

test('registration creates a user match profile', function () {
    $this->post(route('register'), [
        'name' => 'Match Ready User',
        'email' => 'match-ready@example.com',
        'role' => 'user',
        'phone' => '09179999999',
        'school' => 'DSSC',
        'course_year' => 'BSIT 3',
        'preferred_location' => 'Digos City',
        'rental_budget' => 2500,
        'budget_min' => 2500,
        'budget_max' => 4500,
        'lifestyle_info' => 'Prefers quiet evenings and reliable internet.',
        'password' => 'BoardSafe9!',
        'password_confirmation' => 'BoardSafe9!',
        'terms' => '1',
    ])->assertRedirect(route('user.dashboard', absolute: false));

    $user = User::query()->where('email', 'match-ready@example.com')->first();

    expect($user)->not->toBeNull();

    $this->assertDatabaseHas('tenant_match_profiles', [
        'user_id' => $user->id,
        'gender_preference' => 'no_preference',
    ]);
});
