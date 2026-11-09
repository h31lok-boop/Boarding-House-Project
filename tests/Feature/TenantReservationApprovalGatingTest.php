<?php

use App\Models\BoardingHouse;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function approvalGatingActors(): array
{
    $tenant = User::factory()->create(['role' => 'user', 'is_active' => true]);
    $owner = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Approval Gate House',
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);
    $room = Room::query()->create([
        'boarding_house_id' => $house->id,
        'room_no' => 'G-101',
        'price' => 2800,
        'status' => 'Available',
        'capacity' => 1,
        'available_slots' => 1,
    ]);

    return [$tenant, $house, $room];
}

test('pending reservations stay out of tenant financial and active-property views', function () {
    [$tenant, $house, $room] = approvalGatingActors();

    Reservation::query()->create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'total_amount' => 2800,
    ]);

    $dashboard = $this->actingAs($tenant)->get(route('user.dashboard'))->assertOk();
    expect($dashboard->viewData('bookingInfo')['boarding_house'])->toBe('No approved reservation yet');

    $this->actingAs($tenant)
        ->get(route('user.payments.index'))
        ->assertOk()
        ->assertSee('Payment details will appear after owner approval.')
        ->assertDontSee('Payment Schedule');

    $this->actingAs($tenant)
        ->get(route('user.transactions.index'))
        ->assertOk()
        ->assertSee('No transactions yet');

    $this->actingAs($tenant)
        ->get(route('user.reservations.index'))
        ->assertOk()
        ->assertSee('Approval Gate House');
});

test('approved reservations become the source for the tenant dashboard', function () {
    [$tenant, $house, $room] = approvalGatingActors();

    Reservation::query()->create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'status' => 'approved',
        'approved_at' => now(),
        'payment_status' => 'unpaid',
        'total_amount' => 2800,
    ]);

    $dashboard = $this->actingAs($tenant)->get(route('user.dashboard'))->assertOk();
    expect($dashboard->viewData('bookingInfo')['boarding_house'])->toBe('Approval Gate House');

    $this->actingAs($tenant)
        ->get(route('user.payments.index'))
        ->assertOk()
        ->assertDontSee('Payment details will appear once the owner approves your reservation.');
});
