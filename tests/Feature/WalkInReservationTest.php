<?php

use App\Models\BoardingHouse;
use App\Models\BoardingHouseService;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;

it('allows an owner to record a paid walk-in with a room, payment, receipt, and services', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $tenantUser = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'is_active' => true,
        'approval_status' => 'approved',
    ]);
    $room = Room::query()->create([
        'boarding_house_id' => $house->id,
        'room_no' => 'W-101',
        'status' => 'Available',
        'capacity' => 2,
        'available_slots' => 1,
        'price' => 2500,
    ]);
    $tenant = Tenant::query()->create([
        'user_id' => $tenantUser->id,
        'boarding_house_id' => $house->id,
        'status' => 'active',
    ]);
    $service = BoardingHouseService::query()->create([
        'boarding_house_id' => $house->id,
        'name' => 'Laundry',
        'price' => 250,
        'billing_type' => 'per_use',
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner)
        ->from(route('owner.reservations'))
        ->post(route('owner.reservations.walk-in.store'), [
            'tenant_id' => $tenant->id,
            'boarding_house_id' => $house->id,
            'room_id' => $room->id,
            'check_in_date' => today()->toDateString(),
            'total_amount' => 2500,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'payment_reference' => 'WALKIN-001',
            'service_ids' => [$service->id],
        ]);

    $response->assertRedirect(route('owner.reservations'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('reservations', [
        'user_id' => $tenantUser->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'booking_type' => 'walk_in',
        'payment_status' => 'paid',
        'payment_method' => 'cash',
        'total_amount' => 2750,
    ]);
    $this->assertDatabaseHas('payments', [
        'tenant_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'status' => 'paid',
        'amount' => 2750,
    ]);
    $this->assertDatabaseHas('bookings', [
        'user_id' => $tenantUser->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'booking_type' => 'walk_in',
        'payment_status' => 'paid',
        'total_amount' => 2750,
    ]);
    $this->assertDatabaseHas('payment_receipts', [
        'user_id' => $tenantUser->id,
        'payment_method' => 'Cash Payment',
        'status' => 'approved',
        'amount' => 2750,
    ]);
    $this->assertDatabaseHas('reservation_services', [
        'boarding_house_service_id' => $service->id,
        'quantity' => 1,
        'total_price' => 250,
    ]);
    $this->assertDatabaseHas('rooms', [
        'id' => $room->id,
        'available_slots' => 0,
        'status' => 'Reserved',
    ]);
});

it('allows an admin to record an unpaid walk-in without creating a payment receipt', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $tenantUser = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $house = BoardingHouse::factory()->create(['is_active' => true]);
    $tenant = Tenant::query()->create([
        'user_id' => $tenantUser->id,
        'boarding_house_id' => $house->id,
        'status' => 'active',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.reservations'))
        ->post(route('admin.reservations.walk-in.store'), [
            'tenant_id' => $tenant->id,
            'boarding_house_id' => $house->id,
            'check_in_date' => today()->toDateString(),
            'total_amount' => 1800,
            'payment_status' => 'unpaid',
            'payment_method' => 'cash',
        ])
        ->assertRedirect(route('admin.reservations'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('reservations', [
        'user_id' => $tenantUser->id,
        'boarding_house_id' => $house->id,
        'booking_type' => 'walk_in',
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'total_amount' => 1800,
    ]);
    $this->assertDatabaseHas('bookings', [
        'user_id' => $tenantUser->id,
        'boarding_house_id' => $house->id,
        'booking_type' => 'walk_in',
        'payment_status' => 'unpaid',
        'total_amount' => 1800,
    ]);
    $this->assertDatabaseMissing('payments', [
        'tenant_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'amount' => 1800,
    ]);
    $this->assertDatabaseMissing('payment_receipts', [
        'user_id' => $tenantUser->id,
        'amount' => 1800,
    ]);
});

it('returns a visible walk-in form error when the tenant belongs to another property', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $tenantUser = User::factory()->create(['role' => 'user', 'is_active' => true, 'email_verified_at' => now()]);
    $ownedHouse = BoardingHouse::factory()->create(['owner_id' => $owner->id]);
    $otherHouse = BoardingHouse::factory()->create();
    $tenant = Tenant::query()->create([
        'user_id' => $tenantUser->id,
        'boarding_house_id' => $otherHouse->id,
        'status' => 'active',
    ]);

    $this->actingAs($owner)
        ->from(route('owner.reservations'))
        ->post(route('owner.reservations.walk-in.store'), [
            'tenant_id' => $tenant->id,
            'boarding_house_id' => $ownedHouse->id,
            'total_amount' => 2500,
            'payment_status' => 'unpaid',
            'payment_method' => 'cash',
        ])
        ->assertRedirect(route('owner.reservations'))
        ->assertSessionHasErrors(['tenant_id'], null, 'walkIn')
        ->assertSessionHasInput('boarding_house_id', (string) $ownedHouse->id);

    $this->assertDatabaseMissing('reservations', [
        'user_id' => $tenantUser->id,
        'boarding_house_id' => $ownedHouse->id,
        'booking_type' => 'walk_in',
    ]);
});

it('renders a property-aware walk-in form and reopens it with validation errors', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $house = BoardingHouse::factory()->create(['owner_id' => $owner->id, 'name' => 'Walk-in House']);
    $tenantUser = User::factory()->create(['role' => 'user', 'name' => 'Walk-in Tenant']);
    Tenant::query()->create([
        'user_id' => $tenantUser->id,
        'boarding_house_id' => $house->id,
        'status' => 'active',
    ]);

    $this->actingAs($owner)
        ->from(route('owner.reservations'))
        ->post(route('owner.reservations.walk-in.store'), [])
        ->assertRedirect(route('owner.reservations'))
        ->assertSessionHasErrors(['tenant_id', 'boarding_house_id', 'total_amount', 'payment_status', 'payment_method'], null, 'walkIn');

    $this->actingAs($owner)
        ->get(route('owner.reservations'))
        ->assertOk()
        ->assertSee('walkInOpen: true', false)
        ->assertSee('The walk-in reservation was not saved.')
        ->assertSee('x-model="walkIn.boarding_house_id"', false)
        ->assertSee('x-model="walkIn.tenant_id"', false)
        ->assertSee('Walk-in House')
        ->assertSee('Walk-in Tenant');
});
