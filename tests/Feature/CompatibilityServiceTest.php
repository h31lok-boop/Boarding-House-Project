<?php

use App\Models\TenantMatchProfile;
use App\Models\User;
use App\Services\CompatibilityService;

test('compatibility service gives higher scores to closer profiles', function () {
    $service = app(CompatibilityService::class);

    $user = new User(['id' => 1, 'role' => 'user']);
    $user->setRelation('tenantMatchProfile', new TenantMatchProfile([
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
    ]));

    $strongCandidate = new User(['id' => 2, 'role' => 'user']);
    $strongCandidate->setRelation('tenantMatchProfile', new TenantMatchProfile([
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
        'hobbies' => ['coding', 'reading'],
    ]));

    $weakCandidate = new User(['id' => 3, 'role' => 'user']);
    $weakCandidate->setRelation('tenantMatchProfile', new TenantMatchProfile([
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
    ]));

    $strong = $service->score($user, $strongCandidate);
    $weak = $service->score($user, $weakCandidate);

    expect($strong['compatibility_percent'])->toBeGreaterThan($weak['compatibility_percent']);
    expect($strong['compatibility_percent'])->toBeGreaterThan(80);
    expect($weak['compatibility_percent'])->toBeLessThan(35);
});
