<?php

use App\Models\BoardingHouse;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserNotification;

it('renders the redesigned owner dashboard with real scoped data', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $other = User::factory()->verifiedOwner()->create();

    $house = BoardingHouse::create(['owner_id' => $owner->id, 'name' => 'Sunrise Residences', 'is_active' => true, 'approval_status' => 'approved']);
    $foreign = BoardingHouse::create(['owner_id' => $other->id, 'name' => 'Rival Lodge', 'is_active' => true]);

    $occupied = Room::create(['boarding_house_id' => $house->id, 'room_no' => 'A1', 'status' => 'occupied', 'price' => 3500]);
    Room::create(['boarding_house_id' => $house->id, 'room_no' => 'A2', 'status' => 'Occupied', 'price' => 3500]);
    Room::create(['boarding_house_id' => $house->id, 'room_no' => 'A3', 'status' => 'reserved', 'price' => 3000]);
    Room::create(['boarding_house_id' => $house->id, 'room_no' => 'A4', 'status' => 'available', 'price' => 3000]);
    Room::create(['boarding_house_id' => $foreign->id, 'room_no' => 'Z9', 'status' => 'occupied', 'price' => 9999]);

    $tenantUser = User::factory()->create(['role' => 'user', 'name' => 'Maria Santos']);
    $tenant = Tenant::create([
        'user_id' => $tenantUser->id,
        'boarding_house_id' => $house->id,
        'room_id' => $occupied->id,
        'status' => 'active',
        'move_in_date' => now()->subMonth(),
    ]);

    Reservation::create(['user_id' => $tenantUser->id, 'boarding_house_id' => $house->id, 'room_id' => $occupied->id, 'status' => 'pending', 'check_in_date' => now()->addWeek()]);
    Reservation::create(['user_id' => $tenantUser->id, 'boarding_house_id' => $foreign->id, 'room_id' => $occupied->id, 'status' => 'pending', 'check_in_date' => now()->addWeek()]);

    Payment::create(['tenant_id' => $tenant->id, 'boarding_house_id' => $house->id, 'amount' => 3500, 'status' => 'paid', 'paid_at' => now()->subDays(3)]);
    Payment::create(['tenant_id' => $tenant->id, 'boarding_house_id' => $house->id, 'amount' => 1200, 'status' => 'pending', 'due_date' => now()->subDays(5)]);
    Payment::create(['tenant_id' => $tenant->id, 'boarding_house_id' => $foreign->id, 'amount' => 8888, 'status' => 'pending', 'due_date' => now()]);

    $response = $this->actingAs($owner)->get(route('owner.dashboard'));

    $response->assertOk()
        ->assertSee('dashboard-critical-styles', false)
        ->assertSee('All My Properties')
        ->assertSee('Monthly revenue')
        ->assertSee('Occupancy rate')
        ->assertSee('Active tenants')
        ->assertSee('Available rooms')
        ->assertSee('Pending reservations')
        ->assertSee('Unpaid payments')
        ->assertSee('Occupancy overview')
        ->assertSee('Six-month revenue')
        ->assertSee('Needs Attention')
        ->assertSee('My Properties')
        ->assertSee('Recent Activity')
        ->assertSee('Sunrise Residences')
        ->assertSee('50%')
        ->assertSee('2 of 4 rooms occupied')
        ->assertSee('Maria Santos')
        ->assertSee('Tenant requests need a decision')
        ->assertSee('₱1,200 still needs collection')
        ->assertDontSee('Rival Lodge')
        ->assertDontSee('8,888')
        ->assertDontSee('9,999');
});

it('shows an empty state when the owner has no property', function () {
    $owner = User::factory()->verifiedOwner()->create();

    $this->actingAs($owner)->get(route('owner.dashboard'))
        ->assertOk()
        ->assertSee('No property yet')
        ->assertSee('Add your property');
});

it('sends owners from /dashboard to the owner dashboard', function () {
    $owner = User::factory()->verifiedOwner()->create();

    $this->actingAs($owner)->get('/dashboard')->assertRedirect(route('owner.dashboard'));
});

it('filters by an owned property and refuses foreign property scopes', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $otherOwner = User::factory()->verifiedOwner()->create();

    $first = BoardingHouse::create(['owner_id' => $owner->id, 'name' => 'First Owner House', 'is_active' => true]);
    $second = BoardingHouse::create(['owner_id' => $owner->id, 'name' => 'Second Owner House', 'is_active' => true]);
    $foreign = BoardingHouse::create(['owner_id' => $otherOwner->id, 'name' => 'Foreign Secret House', 'is_active' => true]);

    Room::create(['boarding_house_id' => $first->id, 'room_no' => 'F1', 'status' => 'available', 'price' => 2500]);
    Room::create(['boarding_house_id' => $second->id, 'room_no' => 'S1', 'status' => 'occupied', 'price' => 3500]);
    Room::create(['boarding_house_id' => $foreign->id, 'room_no' => 'X1', 'status' => 'occupied', 'price' => 9999]);

    $this->actingAs($owner)
        ->get(route('owner.dashboard', ['property' => $first->id]))
        ->assertOk()
        ->assertSee('First Owner House')
        ->assertSee('data-property-row="'.$first->id.'"', false)
        ->assertDontSee('data-property-row="'.$second->id.'"', false)
        ->assertDontSee('Foreign Secret House');

    $this->actingAs($owner)
        ->get(route('owner.dashboard', ['property' => $foreign->id]))
        ->assertOk()
        ->assertSee('First Owner House')
        ->assertSee('Second Owner House')
        ->assertSee('data-property-row="'.$first->id.'"', false)
        ->assertSee('data-property-row="'.$second->id.'"', false)
        ->assertDontSee('Foreign Secret House')
        ->assertDontSee('9,999');
});

it('uses the selected month for revenue and scopes notification counts to the owner', function () {
    $this->travelTo('2026-07-10 12:00:00');

    $owner = User::factory()->verifiedOwner()->create();
    $otherOwner = User::factory()->verifiedOwner()->create();
    $house = BoardingHouse::create(['owner_id' => $owner->id, 'name' => 'Monthly House', 'is_active' => true]);
    $room = Room::create(['boarding_house_id' => $house->id, 'room_no' => 'M1', 'status' => 'occupied', 'price' => 4200]);
    $tenantUser = User::factory()->create(['role' => 'user']);
    $tenant = Tenant::create(['user_id' => $tenantUser->id, 'boarding_house_id' => $house->id, 'room_id' => $room->id, 'status' => 'active']);

    Payment::create(['tenant_id' => $tenant->id, 'boarding_house_id' => $house->id, 'amount' => 4200, 'status' => 'paid', 'paid_at' => '2026-06-04 08:00:00']);
    Payment::create(['tenant_id' => $tenant->id, 'boarding_house_id' => $house->id, 'amount' => 6800, 'status' => 'paid', 'paid_at' => '2026-07-08 08:00:00']);

    UserNotification::create([
        'user_id' => $owner->id,
        'type' => 'dashboard-test',
        'title' => 'Owner alert',
        'message' => 'Owner-only alert',
        'reference_id' => 'owner-alert',
    ]);
    UserNotification::create([
        'user_id' => $otherOwner->id,
        'type' => 'dashboard-test',
        'title' => 'Other alert',
        'message' => 'Foreign alert',
        'reference_id' => 'foreign-alert',
    ]);

    $this->actingAs($owner)
        ->get(route('owner.dashboard', ['month' => '2026-06']))
        ->assertOk()
        ->assertSee('₱4,200')
        ->assertDontSee('₱6,800')
        ->assertSee('Open notifications, 1 unread', false)
        ->assertDontSee('Foreign alert');
});
