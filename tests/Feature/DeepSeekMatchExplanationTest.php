<?php

use App\Models\BoardingHouse;
use App\Models\TenantMatchProfile;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Http;

test('user can open deepseek explanation page without an api key', function () {
    config()->set('services.deepseek.api_key', null);

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

    $candidate = User::factory()->create([
        'name' => 'DeepSeek Candidate',
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    TenantMatchProfile::create([
        'user_id' => $candidate->id,
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

    $this->actingAs($user)
        ->get(route('user.recommendations.explain', $candidate))
        ->assertOk()
        ->assertSee('DeepSeek Match Explanation')
        ->assertSee('DeepSeek API key is not configured.');
});

test('generate recommendations stores and displays DeepSeek boarding house explanations', function () {
    config()->set('services.deepseek.api_key', 'test-deepseek-key');
    config()->set('services.deepseek.model', 'deepseek-v4-flash');

    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    UserPreference::create([
        'user_id' => $user->id,
        'preferred_rental_budget' => 3000,
        'preferred_locations' => ['Matti'],
        'preferred_landmark' => 'DSSC Main Campus',
        'distance_from_school' => 3,
        'room_type' => 'any',
        'study_habits' => 'quiet_focus',
        'sleeping_schedule' => 'balanced',
        'cleanliness_level' => 4,
        'noise_tolerance' => 2,
        'amenities' => ['Wi-Fi'],
    ]);

    $house = BoardingHouse::factory()->create([
        'name' => 'DeepSeek DSSC Test House',
        'address' => 'Matti, Digos City',
        'price' => 2500,
        'available_rooms' => 2,
        'distance_from_dssc' => 0.8,
        'is_near_dssc' => true,
        'is_active' => true,
        'approval_status' => 'approved',
        'status' => 'approved',
    ]);

    $explanation = 'This listing is close to DSSC and fits the selected budget. Its recorded availability makes it a practical option.';

    Http::fake([
        'api.deepseek.com/chat/completions' => Http::response([
            'model' => 'deepseek-v4-flash',
            'choices' => [[
                'message' => [
                    'role' => 'assistant',
                    'content' => json_encode([
                        'recommendations' => [
                            (string) $house->id => $explanation,
                        ],
                    ]),
                ],
            ]],
        ]),
    ]);

    $this->actingAs($user)
        ->post(route('user.matchmaking.generate'))
        ->assertRedirect(route('user.matchmaking.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('boarding_house_matches', [
        'user_id' => $user->id,
        'boarding_house_id' => $house->id,
        'ai_explanation' => $explanation,
        'ai_model' => 'deepseek-v4-flash',
    ]);

    Http::assertSent(fn ($request) => $request->url() === 'https://api.deepseek.com/chat/completions'
        && $request['model'] === 'deepseek-v4-flash'
        && data_get($request->data(), 'response_format.type') === 'json_object');

    $this->actingAs($user)
        ->get(route('user.matchmaking.index'))
        ->assertOk()
        ->assertSee('DeepSeek AI insight')
        ->assertSee($explanation);
});
