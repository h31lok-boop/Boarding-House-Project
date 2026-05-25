<?php

use App\Models\TenantMatchProfile;
use App\Models\User;

test('tenant matches page requires a completed match profile', function () {
    $tenant = User::factory()->create([
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    TenantMatchProfile::create([
        'user_id' => $tenant->id,
        'gender_preference' => 'no_preference',
    ]);

    $this->actingAs($tenant)
        ->get(route('tenant.matches.index'))
        ->assertForbidden();
});

test('tenant matches page ranks compatible candidates', function () {
    $tenant = User::factory()->create([
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    TenantMatchProfile::create([
        'user_id' => $tenant->id,
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
        'completed_at' => now(),
    ]);

    $strong = User::factory()->create([
        'name' => 'Strong Match',
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    TenantMatchProfile::create([
        'user_id' => $strong->id,
        'budget_min' => 2600,
        'budget_max' => 4300,
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
        'completed_at' => now(),
    ]);

    $weak = User::factory()->create([
        'name' => 'Weak Match',
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    TenantMatchProfile::create([
        'user_id' => $weak->id,
        'budget_min' => 7000,
        'budget_max' => 9000,
        'gender_preference' => 'male',
        'sleep_schedule' => 'night_owl',
        'study_habits' => 'group_study',
        'cleanliness_level' => 1,
        'noise_tolerance' => 5,
        'smoking_preference' => 'smoker_ok',
        'drinking_preference' => 'flexible',
        'pets_preference' => 'pet_friendly',
        'internet_usage' => 'light',
        'hobbies' => ['travel'],
        'completed_at' => now(),
    ]);

    $response = $this->actingAs($tenant)->get(route('tenant.matches.index'));

    $response->assertOk()
        ->assertSee('Recommended Roommates')
        ->assertSee('Strong Match')
        ->assertSee('Weak Match');

    $content = $response->getContent();
    expect(strpos($content, 'Strong Match'))->toBeLessThan(strpos($content, 'Weak Match'));
});
