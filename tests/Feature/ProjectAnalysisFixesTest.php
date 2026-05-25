<?php

use App\Models\BoardingHouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('owner users are redirected to the owner dashboard', function () {
    $owner = User::factory()->create([
        'role' => 'owner',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertRedirect(route('owner.dashboard'));
});

test('favorites are forbidden for non-tenant users and do not create tenant profiles', function () {
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
        ->post(route('user.favorites.store', $boardingHouse))
        ->assertForbidden();

    $this->assertDatabaseMissing('tenant_profiles', [
        'user_id' => $admin->id,
    ]);
});

test('tenant registration creates a tenant profile', function () {
    $response = $this->post(route('register'), [
        'name' => 'Tenant Example',
        'email' => 'tenant@example.com',
        'phone' => '09170000000',
        'institution_name' => 'DSSC',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));

    $userId = User::query()->where('email', 'tenant@example.com')->value('id');

    expect($userId)->not->toBeNull();

    $this->assertDatabaseHas('tenant_profiles', [
        'user_id' => $userId,
        'school_company' => 'DSSC',
    ]);
});

test('caretaker tenants page includes tenants assigned via spatie roles', function () {
    Role::findOrCreate('caretaker', 'web');
    Role::findOrCreate('tenant', 'web');

    $caretaker = User::factory()->create([
        'role' => null,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $caretaker->syncRoles(['caretaker']);

    $tenant = User::factory()->create([
        'role' => null,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $tenant->syncRoles(['tenant']);

    $this->actingAs($caretaker)
        ->get(route('caretaker.tenants.index'))
        ->assertOk()
        ->assertSee($tenant->name);
});
