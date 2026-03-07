<?php

use App\Models\User;

test('guest is redirected when visiting superduperadmin dashboard', function () {
    $response = $this->get(route('superduperadmin.dashboard'));

    $response->assertRedirect(route('login'));
});

test('non superduperadmin receives forbidden on superduperadmin dashboard', function () {
    $user = User::factory()->create([
        'role' => 'tenant',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('superduperadmin.dashboard'));

    $response->assertForbidden();
});

test('superduperadmin can access superduperadmin dashboard', function () {
    $user = User::factory()->create([
        'role' => 'superduperadmin',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('superduperadmin.dashboard'));

    $response->assertOk();
});

test('owner routes are restricted to owners only', function () {
    $tenant = User::factory()->create([
        'role' => 'tenant',
        'is_active' => true,
    ]);

    $owner = User::factory()->create([
        'role' => 'owner',
        'is_active' => true,
    ]);

    $this->actingAs($tenant)
        ->get(route('owner.rooms'))
        ->assertForbidden();

    $this->actingAs($owner)
        ->get(route('owner.rooms'))
        ->assertOk();
});

test('tenant dashboard is restricted to tenant users', function () {
    $owner = User::factory()->create([
        'role' => 'owner',
        'is_active' => true,
    ]);

    $tenant = User::factory()->create([
        'role' => 'tenant',
        'is_active' => true,
    ]);

    $this->actingAs($owner)
        ->get(route('tenant.dashboard'))
        ->assertForbidden();

    $this->actingAs($tenant)
        ->get(route('tenant.dashboard'))
        ->assertOk();
});
