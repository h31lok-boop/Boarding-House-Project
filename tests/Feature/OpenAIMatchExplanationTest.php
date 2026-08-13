<?php

use App\Models\BoardingHouse;
use App\Models\TenantMatchProfile;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Http;

test('user can open OpenAI explanation page without an API key', function () {
    config()->set('services.ai_evaluation.provider', 'openai');
    config()->set('services.openai.api_key', null);

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
        'name' => 'OpenAI Candidate',
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
        ->assertSee('AI Match Explanation')
        ->assertSee('OpenAI API key is not configured.');
});

test('generate recommendations stores and displays OpenAI boarding house explanations', function () {
    config()->set('services.ai_evaluation.provider', 'openai');
    config()->set('services.openai.api_key', 'test-openai-key');
    config()->set('services.openai.model', 'gpt-5.6-luna');

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
        'name' => 'OpenAI DSSC Test House',
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
        'api.openai.com/v1/responses' => Http::response([
            'id' => 'resp_test',
            'model' => 'gpt-5.6-luna',
            'output' => [[
                'type' => 'message',
                'content' => [[
                    'type' => 'output_text',
                    'text' => json_encode([
                        'recommendations' => [
                            (string) $house->id => $explanation,
                        ],
                    ]),
                ]],
            ]],
        ], 200, ['x-request-id' => 'req_test']),
    ]);

    $this->actingAs($user)
        ->post(route('user.matchmaking.generate'))
        ->assertRedirect(route('user.matchmaking.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('boarding_house_matches', [
        'user_id' => $user->id,
        'boarding_house_id' => $house->id,
        'ai_explanation' => $explanation,
        'ai_model' => 'gpt-5.6-luna',
    ]);

    Http::assertSent(fn ($request) => $request->url() === 'https://api.openai.com/v1/responses'
        && $request->hasHeader('Authorization', 'Bearer test-openai-key')
        && $request['model'] === 'gpt-5.6-luna'
        && $request['store'] === false
        && is_string($request['input']));

    $this->actingAs($user)
        ->get(route('user.matchmaking.index'))
        ->assertOk()
        ->assertSee('AI insight')
        ->assertSee($explanation);
});
