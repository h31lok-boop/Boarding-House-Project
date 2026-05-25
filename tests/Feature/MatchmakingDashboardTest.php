<?php

use App\Models\RoommateMatchRequest;
use App\Models\TenantMatchProfile;
use App\Models\User;

test('admin dashboard shows matchmaking analytics cards', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Total Match Requests')
        ->assertSee('Acceptance Rate');
});

test('tenant dashboard shows roommate match status section', function () {
    $tenant = User::factory()->create([
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $other = User::factory()->create([
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    TenantMatchProfile::create([
        'user_id' => $tenant->id,
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
        'recipient_id' => $tenant->id,
        'status' => 'pending',
    ]);

    $this->actingAs($tenant)
        ->get(route('tenant.dashboard'))
        ->assertOk()
        ->assertSee('Roommate Match Status')
        ->assertSee('Incoming Pending');
});
