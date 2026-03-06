<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
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
        'role' => 'tenant',
    ]);

    expect(User::where('email', 'test@example.com')->first()?->is_active)->toBeTrue();
});
