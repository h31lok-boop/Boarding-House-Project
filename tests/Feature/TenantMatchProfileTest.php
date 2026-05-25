<?php

use App\Models\User;

test('tenant can view the match profile form', function () {
    $tenant = User::factory()->create([
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($tenant)
        ->get(route('tenant.match-profile.edit'))
        ->assertOk()
        ->assertSee('Match Profile');
});

test('non-tenant cannot access the match profile form', function () {
    $owner = User::factory()->create([
        'role' => 'owner',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('tenant.match-profile.edit'))
        ->assertForbidden();
});

test('tenant can save matchmaking preferences', function () {
    $tenant = User::factory()->create([
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($tenant)
        ->put(route('tenant.match-profile.update'), [
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
            'additional_notes' => 'Prefers quiet evenings and shared cleaning routines.',
        ])
        ->assertRedirect(route('tenant.match-profile.edit'));

    $this->assertDatabaseHas('tenant_match_profiles', [
        'user_id' => $tenant->id,
        'budget_min' => 2500.00,
        'budget_max' => 4500.00,
        'sleep_schedule' => 'balanced',
        'study_habits' => 'quiet_focus',
        'cleanliness_level' => 4,
        'noise_tolerance' => 2,
        'internet_usage' => 'heavy',
    ]);

    expect($tenant->fresh()->tenantMatchProfile->hobbies)->toBe(['reading', 'coding']);
});

test('registration creates a tenant match profile', function () {
    $this->post(route('register'), [
        'name' => 'Match Ready Tenant',
        'email' => 'match-ready@example.com',
        'phone' => '09179999999',
        'institution_name' => 'DSSC',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'match-ready@example.com')->first();

    expect($user)->not->toBeNull();

    $this->assertDatabaseHas('tenant_match_profiles', [
        'user_id' => $user->id,
        'gender_preference' => 'no_preference',
    ]);
});
