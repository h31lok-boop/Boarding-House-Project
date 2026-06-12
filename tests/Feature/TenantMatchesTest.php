<?php

use App\Models\Amenity;
use App\Models\BoardingHouse;
use App\Models\TenantMatchProfile;
use App\Models\User;

test('user matchmaking page prompts for saved preferences when none exist', function () {
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
        ->get(route('user.matchmaking.index'))
        ->assertOk()
        ->assertSee('No preferences saved yet')
        ->assertSee('Set Preferences');
});

test('user matchmaking page ranks boarding house recommendations', function () {
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
        'additional_notes' => "Preferred Location: Digos City\n\nPrefers quiet evenings and reliable internet.",
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

    $response = $this->actingAs($user)->get(route('user.matchmaking.index'));

    $response->assertOk()
        ->assertSee('Recommended for You')
        ->assertSee('Strong Fit House')
        ->assertSee('Weak Fit House')
        ->assertSee(route('user.boarding-houses.show', $strongHouse), false);

    $content = $response->getContent();

    expect(strpos($content, 'Strong Fit House'))->toBeLessThan(strpos($content, 'Weak Fit House'));
});

