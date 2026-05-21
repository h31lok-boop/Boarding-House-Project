<?php

use App\Models\User;
use App\Support\LoginSecurityChallenge;

function currentLoginSecurityAnswer(): string
{
    $question = session(LoginSecurityChallenge::QUESTION_KEY);

    if (! is_string($question) || ! preg_match('/(\d+) \+ (\d+)/', $question, $matches)) {
        throw new RuntimeException('Login security question was not generated.');
    }

    return (string) ((int) $matches[1] + (int) $matches[2]);
}

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200)
        ->assertSee('Sign In')
        ->assertSee('Access your DSSC Boarding account to manage your listings, rooms, and inquiries.')
        ->assertSee('Email Address')
        ->assertSee('Enter your email')
<<<<<<< Updated upstream
        ->assertSeeText('Security Check')
        ->assertSeeText(session(LoginSecurityChallenge::QUESTION_KEY))
=======
>>>>>>> Stashed changes
        ->assertSee('Forgot password?')
        ->assertSee('Register here');
});

test('tenants are redirected to their dashboard after login', function () {
    // Capitalized role to confirm case-insensitive matching.
    $user = User::factory()->create(['role' => 'Tenant']);

    $this->get('/login');

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'security_answer' => currentLoginSecurityAnswer(),
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route($user->dashboardRouteName(), absolute: false));
});

test('owners are redirected to their dashboard after login', function () {
    $user = User::factory()->create(['role' => 'owner']);

<<<<<<< Updated upstream
    $this->get('/login');

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'security_answer' => currentLoginSecurityAnswer(),
=======
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
>>>>>>> Stashed changes
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('owner.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

<<<<<<< Updated upstream
    $this->get('/login');

=======
>>>>>>> Stashed changes
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
        'security_answer' => currentLoginSecurityAnswer(),
    ]);

    $response->assertSessionHasErrors([
        'password' => 'Incorrect email or password. Please try again.',
    ]);
    $this->assertGuest();
});

<<<<<<< Updated upstream
test('users can not authenticate with invalid security answer', function () {
    $user = User::factory()->create();

    $this->get('/login');

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'security_answer' => '999',
    ])->assertSessionHasErrors('security_answer');

    $this->assertGuest();
});

test('login password must be at least eight characters', function () {
    $this->get('/login');

    $response = $this->post('/login', [
        'email' => 'tenant@example.com',
        'password' => 'short',
        'security_answer' => currentLoginSecurityAnswer(),
=======
test('login password must be at least eight characters', function () {
    $response = $this->post('/login', [
        'email' => 'tenant@example.com',
        'password' => 'short',
>>>>>>> Stashed changes
    ]);

    $response->assertSessionHasErrors([
        'password' => 'Password must be at least 8 characters long.',
    ]);
    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
