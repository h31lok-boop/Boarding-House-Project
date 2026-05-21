<?php

use App\Models\BoardingHouse;
use App\Models\User;
use App\Models\ValidationTask;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['owner', 'tenant', 'caretaker', 'osas', 'superduperadmin'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

test('owner sees all users connected to their boarding house workspace', function () {
    $owner = makeWorkspaceOwner('owner-a@example.com');
    $house = makeOwnedBoardingHouse($owner, 'Owner House');

    $tenantOne = User::factory()->create([
        'name' => 'Tenant One',
        'role' => 'tenant',
        'boarding_house_id' => $house->id,
        'is_active' => true,
    ]);

    $tenantTwo = User::factory()->create([
        'name' => 'Tenant Two',
        'role' => 'tenant',
        'boarding_house_id' => $house->id,
        'is_active' => true,
    ]);

    $caretaker = User::factory()->create([
        'name' => 'Caretaker One',
        'role' => 'caretaker',
        'boarding_house_id' => $house->id,
        'is_active' => true,
    ]);
    $caretaker->syncRoles(['caretaker']);

    $validator = User::factory()->create([
        'name' => 'OSAS Validator',
        'role' => 'osas',
        'is_active' => true,
    ]);
    $validator->syncRoles(['osas']);

    ValidationTask::create([
        'validator_id' => $validator->id,
        'boarding_house_id' => $house->id,
        'status' => 'assigned',
        'scheduled_at' => now()->addDay()->toDateString(),
        'priority' => 'High',
    ]);

    $otherOwner = makeWorkspaceOwner('owner-b@example.com');
    $otherHouse = makeOwnedBoardingHouse($otherOwner, 'Other House');
    $otherTenant = User::factory()->create([
        'name' => 'Other Tenant',
        'role' => 'tenant',
        'boarding_house_id' => $otherHouse->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner)->get(route('superduperadmin.users'));

    $response->assertOk()
        ->assertSee('Tenant One')
        ->assertSee('Tenant Two')
        ->assertSee('Caretaker One')
        ->assertSee('OSAS Validator')
        ->assertDontSee('Other Tenant');

    expect($response->viewData('activeUsersCount'))->toBe(5);
    expect(collect($response->viewData('users')->items())->pluck('id')->all())
        ->toContain($owner->id, $tenantOne->id, $tenantTwo->id, $caretaker->id, $validator->id)
        ->not->toContain($otherTenant->id);
});

test('owner does not see tenants from another boarding house', function () {
    $owner = makeWorkspaceOwner('owner-main@example.com');
    $house = makeOwnedBoardingHouse($owner, 'Main House');

    $otherOwner = makeWorkspaceOwner('owner-other@example.com');
    $otherHouse = makeOwnedBoardingHouse($otherOwner, 'Other House');

    $tenant = User::factory()->create([
        'name' => 'Outside Tenant',
        'role' => 'tenant',
        'boarding_house_id' => $otherHouse->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner)->get(route('superduperadmin.users'));

    $response->assertOk()->assertDontSee('Outside Tenant');
    expect($response->viewData('activeUsersCount'))->toBe(1);
    expect(collect($response->viewData('users')->items())->pluck('id')->all())
        ->toContain($owner->id)
        ->not->toContain($tenant->id);
});

test('newly registered tenant appears in owner manage users when linked to the boarding house', function () {
    $owner = makeWorkspaceOwner('owner-register@example.com');
    $house = makeOwnedBoardingHouse($owner, 'Registration House');

    $response = $this->post(route('register'), [
        'name' => 'Fresh Tenant',
        'email' => 'fresh-tenant@example.com',
        'phone' => '+639991111111',
        'boarding_house_id' => $house->id,
        'institution_name' => 'GeoBoard University',
        'move_in_date' => now()->addWeek()->toDateString(),
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'terms' => '1',
    ]);

    $response->assertSessionDoesntHaveErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $tenant = User::where('email', 'fresh-tenant@example.com')->firstOrFail();

    $this->assertDatabaseHas('boarding_house_applications', [
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'status' => 'pending',
    ]);

    $usersResponse = $this->actingAs($owner)->get(route('superduperadmin.users'));

    $usersResponse->assertOk()->assertSee('Fresh Tenant');
    expect($usersResponse->viewData('activeUsersCount'))->toBe(2);
    expect(collect($usersResponse->viewData('users')->items())->pluck('id')->all())
        ->toContain($owner->id, $tenant->id);
});

test('archived tenants move to archived users within the same boarding house workspace', function () {
    $owner = makeWorkspaceOwner('owner-archive@example.com');
    $house = makeOwnedBoardingHouse($owner, 'Archive House');

    $tenant = User::factory()->create([
        'name' => 'Archived Tenant',
        'role' => 'tenant',
        'boarding_house_id' => $house->id,
        'is_active' => true,
    ]);

    $this->actingAs($owner)
        ->put(route('superduperadmin.users.archive', $tenant))
        ->assertRedirect(route('superduperadmin.users'));

    $this->assertDatabaseHas('users', [
        'id' => $tenant->id,
        'is_archived' => true,
        'is_active' => false,
    ]);

    $response = $this->actingAs($owner)->get(route('superduperadmin.users'));

    expect($response->viewData('activeUsersCount'))->toBe(1);
    expect($response->viewData('archivedUsersCount'))->toBe(1);
    expect(collect($response->viewData('archivedUsers')->items())->pluck('id')->all())
        ->toContain($tenant->id);
});

test('role filter matches both tenant and student users in the owner workspace', function () {
    $owner = makeWorkspaceOwner('owner-filter@example.com');
    $house = makeOwnedBoardingHouse($owner, 'Filter House');

    $tenant = User::factory()->create([
        'name' => 'Tenant Role User',
        'role' => 'tenant',
        'boarding_house_id' => $house->id,
        'is_active' => true,
    ]);

    $student = User::factory()->create([
        'name' => 'Student Role User',
        'role' => 'student',
        'boarding_house_id' => $house->id,
        'is_active' => true,
    ]);

    $caretaker = User::factory()->create([
        'name' => 'Caretaker Filter User',
        'role' => 'caretaker',
        'boarding_house_id' => $house->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner)->get(route('superduperadmin.users', ['role' => 'tenant']));

    $response->assertOk()
        ->assertSee('Tenant Role User')
        ->assertSee('Student Role User')
        ->assertDontSee('Caretaker Filter User');

    expect(collect($response->viewData('users')->items())->pluck('id')->all())
        ->toContain($tenant->id, $student->id)
        ->not->toContain($caretaker->id, $owner->id);
});

function makeWorkspaceOwner(string $email): User
{
    $owner = User::factory()->create([
        'name' => 'Workspace Owner',
        'email' => $email,
        'role' => 'owner',
        'is_active' => true,
        'is_archived' => false,
    ]);

    $owner->syncRoles(['owner', 'superduperadmin']);

    return $owner;
}

function makeOwnedBoardingHouse(User $owner, string $name): BoardingHouse
{
    return BoardingHouse::factory()->create([
        'name' => $name,
        'owner_id' => $owner->id,
        'is_active' => true,
        'approval_status' => 'approved',
    ]);
}
