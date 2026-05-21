<?php

use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\OwnerProfile;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('owner', 'web');
    Role::findOrCreate('tenant', 'web');
});

test('owner listing workspace only shows owned boarding houses', function () {
    $owner = makeOwnerUser('owner-scope@example.com');
    $otherOwner = makeOwnerUser('owner-scope-other@example.com');

    $ownedHouse = BoardingHouse::factory()->create([
        'name' => 'Owner Scope House',
        'owner_id' => $owner->id,
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    BoardingHouse::factory()->create([
        'name' => 'Other Scope House',
        'owner_id' => $otherOwner->id,
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner)->get(route('owner.boarding-houses'));

    $response->assertOk()
        ->assertSee($ownedHouse->name)
        ->assertDontSee('Other Scope House');
});

test('owner cannot edit another owners boarding house', function () {
    $owner = makeOwnerUser('owner-edit@example.com');
    $otherOwner = makeOwnerUser('owner-edit-other@example.com');

    $otherHouse = BoardingHouse::factory()->create([
        'owner_id' => $otherOwner->id,
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.boarding-houses.edit', $otherHouse))
        ->assertForbidden();
});

test('tenant reservation requests create mirrored booking records', function () {
    $owner = makeOwnerUser('reservation-owner@example.com');
    $tenant = User::factory()->create([
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $tenant->syncRoles(['tenant']);

    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $room = Room::create([
        'boarding_house_id' => $house->id,
        'room_no' => 'A-101',
        'room_number' => 'A-101',
        'name' => 'Standard',
        'price' => 3500,
        'capacity' => 2,
        'available_slots' => 1,
        'status' => 'Available',
    ]);

    $response = $this->actingAs($tenant)->post(route('user.reservations.store', $house), [
        'room_id' => $room->id,
        'check_in_date' => now()->addWeek()->toDateString(),
        'check_out_date' => now()->addWeeks(2)->toDateString(),
        'notes' => 'Interested in moving in soon.',
    ]);

    $response->assertSessionDoesntHaveErrors()->assertRedirect();

    $reservation = Reservation::query()->latest()->first();

    $this->assertNotNull($reservation);
    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'status' => 'pending',
    ]);
    $this->assertDatabaseHas('bookings', [
        'reservation_id' => $reservation->id,
        'user_id' => $tenant->id,
        'room_id' => $room->id,
        'status' => 'Pending',
    ]);
});

test('owner confirmation assigns the tenant to the confirmed boarding house and room', function () {
    $owner = makeOwnerUser('owner-confirm@example.com');
    $tenant = User::factory()->create([
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $tenant->syncRoles(['tenant']);

    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $room = Room::create([
        'boarding_house_id' => $house->id,
        'room_no' => 'B-201',
        'room_number' => 'B-201',
        'name' => 'Deluxe',
        'price' => 4200,
        'capacity' => 2,
        'available_slots' => 1,
        'status' => 'Available',
    ]);

    $reservation = Reservation::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'check_in_date' => now()->addDays(10)->toDateString(),
        'check_out_date' => now()->addDays(40)->toDateString(),
        'status' => 'pending',
        'notes' => 'Ready to move in.',
    ]);

    Booking::create([
        'reservation_id' => $reservation->id,
        'room_id' => $room->id,
        'user_id' => $tenant->id,
        'status' => 'Pending',
        'start_date' => $reservation->check_in_date,
        'end_date' => $reservation->check_out_date,
        'notes' => $reservation->notes,
    ]);

    $response = $this->actingAs($owner)->patch(route('owner.reservations.update', $reservation), [
        'status' => 'confirmed',
        'owner_notes' => 'Approved for move-in.',
    ]);

    $response->assertRedirect(route('owner.bookings.index'));

    $tenant->refresh();

    expect((int) $tenant->boarding_house_id)->toBe($house->id)
        ->and($tenant->room_number)->toBe('B-201');

    $this->assertDatabaseHas('tenants', [
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'status' => 'active',
    ]);

    $this->assertDatabaseHas('bookings', [
        'reservation_id' => $reservation->id,
        'status' => 'Confirmed',
    ]);
});

test('owner profile workspace uses owner routes and updates owner details', function () {
    Storage::fake('public');

    $owner = makeOwnerUser('owner-profile@example.com');

    $this->actingAs($owner)
        ->get(route('owner.profile'))
        ->assertOk()
        ->assertSee('Owner Profile');

    $response = $this->actingAs($owner)->patch(route('owner.profile.update'), [
        'name' => 'Updated Owner',
        'email' => 'updated-owner@example.com',
        'phone' => '09171234567',
        'company_name' => 'Updated Boarding Co.',
        'business_permit_number' => 'BP-2026-001',
        'valid_id_type' => 'passport',
        'valid_id_number' => 'P1234567',
        'profile_image' => UploadedFile::fake()->image('owner-profile.jpg'),
    ]);

    $response->assertRedirect(route('owner.profile'));
    $response->assertSessionHas('status', 'profile-updated');

    $owner->refresh();

    expect($owner->name)->toBe('Updated Owner')
        ->and($owner->email)->toBe('updated-owner@example.com')
        ->and($owner->phone)->toBe('09171234567')
        ->and($owner->contact_number)->toBe('09171234567')
        ->and($owner->profile_image)->not->toBeNull();

    expect($owner->ownerProfile)->not->toBeNull()
        ->and($owner->ownerProfile->company_name)->toBe('Updated Boarding Co.')
        ->and($owner->ownerProfile->business_permit_number)->toBe('BP-2026-001')
        ->and($owner->ownerProfile->valid_id_type)->toBe('passport')
        ->and($owner->ownerProfile->valid_id_number)->toBe('P1234567');

    Storage::disk('public')->assertExists($owner->profile_image);
});

test('owner can manage users from the owner workspace with scoped records only', function () {
    $owner = makeOwnerUser('owner-users@example.com');
    $otherOwner = makeOwnerUser('owner-users-other@example.com');

    $ownedHouse = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $otherHouse = BoardingHouse::factory()->create([
        'owner_id' => $otherOwner->id,
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $tenant = User::factory()->create([
        'name' => 'Scoped Tenant',
        'role' => 'tenant',
        'boarding_house_id' => $ownedHouse->id,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $tenant->syncRoles(['tenant']);

    $otherTenant = User::factory()->create([
        'name' => 'Outside Tenant',
        'role' => 'tenant',
        'boarding_house_id' => $otherHouse->id,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $otherTenant->syncRoles(['tenant']);

    $response = $this->actingAs($owner)->get(route('owner.users'));

    $response->assertOk()
        ->assertSee('Scoped Tenant')
        ->assertDontSee('Outside Tenant');
});

test('owner cannot edit a user outside their workspace from owner routes', function () {
    $owner = makeOwnerUser('owner-users-edit@example.com');
    $otherOwner = makeOwnerUser('owner-users-edit-other@example.com');

    $otherHouse = BoardingHouse::factory()->create([
        'owner_id' => $otherOwner->id,
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $otherTenant = User::factory()->create([
        'role' => 'tenant',
        'boarding_house_id' => $otherHouse->id,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $otherTenant->syncRoles(['tenant']);

    $this->actingAs($owner)
        ->get(route('owner.users.edit', $otherTenant))
        ->assertForbidden();
});

test('owner tenant history is scoped to owned boarding houses', function () {
    $owner = makeOwnerUser('owner-history@example.com');
    $otherOwner = makeOwnerUser('owner-history-other@example.com');

    $ownedHouse = BoardingHouse::factory()->create([
        'name' => 'History House',
        'owner_id' => $owner->id,
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $otherHouse = BoardingHouse::factory()->create([
        'name' => 'Other History House',
        'owner_id' => $otherOwner->id,
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $activeTenant = User::factory()->create([
        'name' => 'History Active Tenant',
        'role' => 'tenant',
        'boarding_house_id' => $ownedHouse->id,
        'room_number' => 'C-101',
        'move_in_date' => now()->subMonth(),
        'is_active' => true,
    ]);
    $activeTenant->syncRoles(['tenant']);

    $pastTenant = User::factory()->create([
        'name' => 'History Past Tenant',
        'role' => 'tenant',
        'boarding_house_id' => $ownedHouse->id,
        'room_number' => 'C-102',
        'move_in_date' => now()->subMonths(3),
        'is_active' => false,
    ]);
    $pastTenant->syncRoles(['tenant']);

    $outsideTenant = User::factory()->create([
        'name' => 'History Outside Tenant',
        'role' => 'tenant',
        'boarding_house_id' => $otherHouse->id,
        'room_number' => 'Z-999',
        'move_in_date' => now()->subMonth(),
        'is_active' => true,
    ]);
    $outsideTenant->syncRoles(['tenant']);

    $response = $this->actingAs($owner)->get(route('owner.tenant-history'));

    $response->assertOk()
        ->assertSee('History Active Tenant')
        ->assertSee('History Past Tenant')
        ->assertDontSee('History Outside Tenant');
});

function makeOwnerUser(string $email): User
{
    $owner = User::factory()->create([
        'email' => $email,
        'role' => 'owner',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $owner->syncRoles(['owner']);

    return $owner;
}
