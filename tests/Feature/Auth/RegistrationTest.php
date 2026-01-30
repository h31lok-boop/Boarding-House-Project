<?php

test('registration route redirects to login', function () {
    $response = $this->get('/register');

    $response->assertRedirect(route('login', absolute: false));
});

test('registration is disabled', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $response->assertStatus(404);
});
