<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200)
        ->assertSee('Admin')
        ->assertSee('User')
        ->assertDontSee('Caretaker')
        ->assertDontSee('OSAS');
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+639991234567',
        'institution_name' => 'GeoBoard University',
        'move_in_date' => now()->addWeek()->toDateString(),
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'terms' => '1',
    ]);

    $response->assertSessionDoesntHaveErrors();
    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'role' => 'user',
    ]);

    expect(User::where('email', 'test@example.com')->first()?->is_active)->toBeTrue();
});

test('admins can register from the public form', function () {
    $response = $this->post('/register', [
        'name' => 'Admin User',
        'email' => 'admin-user@example.com',
        'role' => 'admin',
        'phone' => '+639991111111',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'terms' => '1',
    ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertRedirect(route('dashboard', absolute: false));

    $admin = User::where('email', 'admin-user@example.com')->first();

    expect($admin?->role)->toBe('admin');
    expect($admin?->isAdmin())->toBeTrue();

    $this->assertDatabaseHas('owner_profiles', [
        'user_id' => $admin->id,
        'verification_status' => 'pending',
    ]);
    $this->assertDatabaseMissing('tenant_profiles', [
        'user_id' => $admin->id,
    ]);
});
