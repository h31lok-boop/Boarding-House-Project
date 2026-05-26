<?php

use App\Models\Amenity;
use App\Models\BoardingHouse;
use App\Models\TenantMatchProfile;
use App\Models\User;
use App\Services\BoardingHouseRecommendationService;

test('boarding house recommendations rank houses by user preferences and occupant compatibility', function () {
    $wifi = Amenity::create(['name' => 'Wi-Fi']);
    $laundry = Amenity::create(['name' => 'Laundry']);

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
        'preferred_amenity_ids' => [$wifi->id, $laundry->id],
        'completed_at' => now(),
    ]);

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

    $strongOccupant = User::factory()->create([
        'role' => 'user',
        'boarding_house_id' => $strongHouse->id,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    TenantMatchProfile::create([
        'user_id' => $strongOccupant->id,
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

    $weakOccupant = User::factory()->create([
        'role' => 'user',
        'boarding_house_id' => $weakHouse->id,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    TenantMatchProfile::create([
        'user_id' => $weakOccupant->id,
        'budget_min' => 8000,
        'budget_max' => 10000,
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

    $ranked = app(BoardingHouseRecommendationService::class)
        ->rank($user->fresh(), collect([$weakHouse, $strongHouse]), 6.7440000, 125.3550000);

    expect($ranked->first()['house']->is($strongHouse))->toBeTrue();
    expect($ranked->first()['recommendation']['recommendation_percent'])->toBeGreaterThan(
        $ranked->last()['recommendation']['recommendation_percent']
    );
    expect($ranked->first()['recommendation']['reasons'])->toContain('Matches your preferred amenities');
});
