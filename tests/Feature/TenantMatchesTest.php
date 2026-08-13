<?php

use App\Models\Amenity;
use App\Models\BoardingHouse;
use App\Models\Review;
use App\Models\TenantMatchProfile;
use App\Models\User;
use App\Models\UserPreference;

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
        ->assertSee('Set My Preferences');
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
        ->assertDontSee('Weak Fit House')
        ->assertSee(route('user.boarding-houses.show', $strongHouse), false);
});

test('tenant matchmaking clearly falls back to rating and feature suggestions when preferences do not match', function () {
    $wifi = Amenity::create(['name' => 'Wi-Fi']);
    $laundry = Amenity::create(['name' => 'Laundry']);
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    UserPreference::create([
        'user_id' => $user->id,
        'preferred_rental_budget' => 1000,
        'preferred_rental_budget_min' => 500,
        'preferred_rental_budget_max' => 1000,
        'preferred_locations' => ['Nonexistent Barangay'],
        'distance_from_school' => 0.5,
        'room_type' => 'studio',
        'study_habits' => 'quiet_focus',
        'sleeping_schedule' => 'balanced',
        'cleanliness_level' => 5,
        'noise_tolerance' => 10,
        'amenities' => ['Swimming Pool'],
    ]);

    $reviewer = User::factory()->create(['role' => 'user']);
    $ratedHouse = BoardingHouse::factory()->create([
        'name' => 'Highly Rated Practical House',
        'address' => 'Aplaya, Digos City',
        'price' => 6500,
        'monthly_payment' => '6500',
        'available_rooms' => 3,
        'is_active' => true,
        'approval_status' => 'approved',
        'status' => 'approved',
    ]);
    $ratedHouse->amenities()->sync([$wifi->id, $laundry->id]);
    Review::create([
        'user_id' => $reviewer->id,
        'boarding_house_id' => $ratedHouse->id,
        'rating' => 5,
        'comment' => 'Excellent student housing.',
        'status' => 'published',
    ]);

    $response = $this->actingAs($user)->get(route('user.matchmaking.index'));

    $response->assertOk()
        ->assertSee('Suggested Alternatives')
        ->assertSee('Not a Preference Match')
        ->assertSee('Highly Rated Practical House')
        ->assertSee('5.0/5')
        ->assertDontSee('Best Match For You');
});

test('tenant can run an ai scan and receives a visible scan result', function () {
    config()->set('services.ai_evaluation.provider', 'openai');
    config()->set('services.openai.api_key', null);

    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    UserPreference::create([
        'user_id' => $user->id,
        'preferred_rental_budget' => 4500,
        'preferred_locations' => ['Poblacion'],
        'room_type' => 'any',
        'amenities' => ['Wi-Fi'],
    ]);

    BoardingHouse::factory()->create([
        'name' => 'AI Scan House',
        'address' => 'Poblacion, Digos City',
        'price' => 3500,
        'available_rooms' => 2,
        'is_active' => true,
        'approval_status' => 'approved',
        'status' => 'approved',
    ]);

    $response = $this->actingAs($user)
        ->post(route('user.matchmaking.generate'))
        ->assertRedirect(route('user.matchmaking.index'))
        ->assertSessionHas('ai_scan_status')
        ->assertSessionHas('success');

    $this->actingAs($user)
        ->withSession([
            'ai_scan_status' => $response->getSession()->get('ai_scan_status'),
            'success' => $response->getSession()->get('success'),
        ])
        ->get(route('user.matchmaking.index'))
        ->assertOk()
        ->assertSee('Weighted scan completed; AI explanations were unavailable')
        ->assertSee('approved listings scanned against your saved preferences');
});
