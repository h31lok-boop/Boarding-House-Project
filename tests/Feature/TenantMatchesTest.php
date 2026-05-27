<?php

use App\Models\Amenity;
use App\Models\BoardingHouse;
use App\Models\TenantMatchProfile;
use App\Models\User;

test('user recommendations page requires a completed match profile', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    TenantMatchProfile::create([
        'user_id' => $user->id,
        'gender_preference' => 'no_preference',
    ]);

    $this->actingAs($user)
        ->get(route('user.recommendations'))
        ->assertForbidden();
});

test('user recommendations page ranks compatible candidates', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    TenantMatchProfile::create([
        'user_id' => $user->id,
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
        'role' => 'user',
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
        'role' => 'user',
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

    $response = $this->actingAs($user)->get(route('user.recommendations'));

    $response->assertOk()
        ->assertSee('Recommended Roommates')
        ->assertSee('Strong Match')
        ->assertSee('Weak Match');

    $content = $response->getContent();
    expect(strpos($content, 'Strong Match'))->toBeLessThan(strpos($content, 'Weak Match'));
});

test('user recommendations page applies smart filters', function () {
    $createProfile = function (User $user, array $overrides = []): void {
        TenantMatchProfile::create(array_merge([
            'user_id' => $user->id,
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
        ], $overrides));
    };

    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $createProfile($user);

    $balancedCandidate = User::factory()->create([
        'name' => 'Balanced Candidate',
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $createProfile($balancedCandidate);

    $nightOwlCandidate = User::factory()->create([
        'name' => 'Night Owl Candidate',
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $createProfile($nightOwlCandidate, [
        'budget_min' => 7000,
        'budget_max' => 9000,
        'sleep_schedule' => 'night_owl',
        'study_habits' => 'group_study',
    ]);

    $this->actingAs($user)
        ->get(route('user.recommendations', ['sleep_schedule' => 'night_owl', 'budget_min' => 6500]))
        ->assertOk()
        ->assertSee('Night Owl Candidate')
        ->assertDontSee('Balanced Candidate');
});

test('user recommendations page shows ranked boarding house recommendations', function () {
    $wifi = Amenity::create(['name' => 'Wi-Fi']);
    $laundry = Amenity::create(['name' => 'Laundry']);

    $createProfile = function (User $user, array $overrides = []) use ($wifi, $laundry): void {
        TenantMatchProfile::create(array_merge([
            'user_id' => $user->id,
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
            'completed_at' => now(),
        ], $overrides));
    };

    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $createProfile($user);

    $strongHouse = BoardingHouse::factory()->create([
        'name' => 'Strong Fit House',
        'price' => 3500,
        'monthly_payment' => '3500',
        'available_rooms' => 3,
        'latitude' => 6.7440000,
        'longitude' => 125.3550000,
        'is_active' => true,
        'approval_status' => 'approved',
        'status' => 'approved',
    ]);
    $strongHouse->amenities()->sync([$wifi->id, $laundry->id]);

    $weakHouse = BoardingHouse::factory()->create([
        'name' => 'Weak Fit House',
        'price' => 9000,
        'monthly_payment' => '9000',
        'available_rooms' => 0,
        'latitude' => 6.9000000,
        'longitude' => 125.5500000,
        'is_active' => true,
        'approval_status' => 'approved',
        'status' => 'approved',
    ]);

    $response = $this->actingAs($user)->get(route('user.recommendations'));

    $response->assertOk()
        ->assertSee('11. Recommended Boarding Houses')
        ->assertSee('Strong Fit House')
        ->assertSee('Weak Fit House');

    $content = $response->getContent();
    expect(strpos($content, 'Strong Fit House'))->toBeLessThan(strpos($content, 'Weak Fit House'));
});
