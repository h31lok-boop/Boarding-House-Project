<?php

use App\Models\RoommateMatchRequest;
use App\Models\TenantMatchProfile;
use App\Models\User;

test('admin dashboard keeps matchmaking analytics off the simplified dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('Total Match Requests')
        ->assertDontSee('Acceptance Rate');
});

test('user dashboard stays simple while retaining real roommate request counts', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $other = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    TenantMatchProfile::create([
        'user_id' => $user->id,
        'gender_preference' => 'no_preference',
        'completed_at' => now(),
    ]);

    TenantMatchProfile::create([
        'user_id' => $other->id,
        'gender_preference' => 'no_preference',
        'completed_at' => now(),
    ]);

    RoommateMatchRequest::create([
        'sender_id' => $other->id,
        'recipient_id' => $user->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertSee('Student dashboard')
        ->assertSee('Your reservation, payment, and best match in one place.')
        ->assertDontSee('Roommate Match Status')
        ->assertDontSee('Incoming Pending');

    expect($response->viewData('incomingPendingCount'))->toBe(1)
        ->and($response->viewData('outgoingPendingCount'))->toBe(0);
});
