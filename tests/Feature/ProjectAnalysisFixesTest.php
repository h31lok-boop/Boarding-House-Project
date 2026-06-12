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
        'role' => 'user',
        'phone' => '09170000000',
        'school' => 'DSSC',
        'course_year' => 'BSIT 3',
        'preferred_location' => 'Digos City',
        'rental_budget' => 3500,
        'lifestyle_info' => 'Quiet student who prefers a tidy boarding house.',
        'password' => 'BoardSafe9!',
        'password_confirmation' => 'BoardSafe9!',
        'terms' => '1',
    ]);

    $response->assertRedirect(route('user.dashboard', absolute: false));

    $userId = User::query()->where('email', 'user-example@example.com')->value('id');

    expect($userId)->not->toBeNull();

    $this->assertDatabaseHas('tenant_profiles', [
        'user_id' => $userId,
        'school_company' => 'DSSC',
        'school_university' => 'DSSC',
        'rental_budget' => 3500,
    ]);
});
