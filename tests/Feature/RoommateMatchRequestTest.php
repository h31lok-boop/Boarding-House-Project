<?php

use App\Models\RoommateMatchRequest;
use App\Models\TenantMatchProfile;
use App\Models\User;

function createUserWithCompletedMatchProfile(string $name, string $email): User
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => $email,
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

    return $user;
}

test('user can send a roommate match request', function () {
    $sender = createUserWithCompletedMatchProfile('Sender User', 'sender@example.com');
    $recipient = createUserWithCompletedMatchProfile('Recipient User', 'recipient@example.com');

    $this->actingAs($sender)
        ->post(route('user.recommendations.requests.store', $recipient), [
            'message' => 'We look like a strong fit for quiet study nights.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('roommate_match_requests', [
        'sender_id' => $sender->id,
        'recipient_id' => $recipient->id,
        'status' => 'pending',
    ]);
});

test('recipient can accept a roommate match request', function () {
    $sender = createUserWithCompletedMatchProfile('Sender User', 'sender2@example.com');
    $recipient = createUserWithCompletedMatchProfile('Recipient User', 'recipient2@example.com');

    $request = RoommateMatchRequest::create([
        'sender_id' => $sender->id,
        'recipient_id' => $recipient->id,
        'status' => 'pending',
        'message' => 'Let us be roommates.',
    ]);

    $this->actingAs($recipient)
        ->post(route('user.match-requests.accept', $request))
        ->assertRedirect();

    expect($request->fresh()->status)->toBe('accepted');
    expect($request->fresh()->responded_at)->not->toBeNull();
});

test('sender can cancel a pending roommate match request', function () {
    $sender = createUserWithCompletedMatchProfile('Sender User', 'sender3@example.com');
    $recipient = createUserWithCompletedMatchProfile('Recipient User', 'recipient3@example.com');

    $request = RoommateMatchRequest::create([
        'sender_id' => $sender->id,
        'recipient_id' => $recipient->id,
        'status' => 'pending',
    ]);

    $this->actingAs($sender)
        ->post(route('user.match-requests.cancel', $request))
        ->assertRedirect();

    expect($request->fresh()->status)->toBe('cancelled');
});
