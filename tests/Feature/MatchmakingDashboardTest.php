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
        ->assertDontSee('Acceptance Rate')
        ->assertSee('Latest Reservations');
});

test('user dashboard shows roommate match status section', function () {
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

    $this->actingAs($user)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertSee('Roommate Match Status')
        ->assertSee('Incoming Pending');
});
