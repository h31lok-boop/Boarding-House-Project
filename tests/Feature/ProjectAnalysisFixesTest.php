<?php

use App\Models\BoardingHouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin users are redirected to the admin dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

test('admin users cannot access user-only inquiry actions', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $boardingHouse = BoardingHouse::factory()->create([
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    $this->actingAs($admin)
        ->post(route('user.inquiries.store', $boardingHouse), [
            'message' => 'Can I visit this boarding house?',
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('tenant_profiles', [
        'user_id' => $admin->id,
    ]);
});

test('user registration creates a tenant profile', function () {
    $response = $this->post(route('register'), [
        'name' => 'User Example',
        'email' => 'user-example@example.com',
        'phone' => '09170000000',
        'institution_name' => 'DSSC',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));

    $userId = User::query()->where('email', 'user-example@example.com')->value('id');

    expect($userId)->not->toBeNull();

    $this->assertDatabaseHas('tenant_profiles', [
        'user_id' => $userId,
        'school_company' => 'DSSC',
    ]);
});
