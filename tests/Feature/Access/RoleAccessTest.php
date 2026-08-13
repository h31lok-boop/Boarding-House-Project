<?php

use App\Models\User;

test('admin dashboard is restricted to admin users', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

test('admin and owner workspaces are strictly separated', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $owner = User::factory()->verifiedOwner()->create();
    $tenant = User::factory()->create([
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($owner)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($tenant)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($admin)->get(route('owner.dashboard'))->assertRedirect(route('admin.dashboard'));
    $this->actingAs($tenant)->get(route('owner.dashboard'))->assertForbidden();
    $this->actingAs($owner)->get(route('owner.dashboard'))->assertOk();
});

test('login account type must match the actual account role', function () {
    $owner = User::factory()->verifiedOwner()->create();

    $this->post(route('login'), [
        'email' => $owner->email,
        'password' => 'password',
        'role' => 'admin',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();

    $this->post(route('login'), [
        'email' => $owner->email,
        'password' => 'password',
        'role' => 'owner',
    ])->assertRedirect(route('owner.dashboard'));

    $this->assertAuthenticatedAs($owner);
});

test('user dashboard is restricted to user accounts', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('user.dashboard'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('user.dashboard'))
        ->assertOk();
});

test('owner operational pages stay inside the owner workspace', function (string $routeName) {
    $owner = User::factory()->verifiedOwner()->create();

    $this->actingAs($owner)
        ->get(route($routeName))
        ->assertOk()
        ->assertSee('Owner portal')
        ->assertDontSee('/admin/', false);
})->with([
    'properties' => 'owner.boarding-houses',
    'rooms' => 'owner.rooms',
    'reservations' => 'owner.reservations',
    'payments' => 'owner.payments',
    'tenants' => 'owner.tenants.index',
    'inquiries' => 'owner.inquiries',
    'messages' => 'owner.messages',
    'notifications' => 'owner.notifications.index',
    'settings' => 'owner.settings.index',
]);
