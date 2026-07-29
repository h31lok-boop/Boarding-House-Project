<?php

use App\Models\BoardingHouse;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;

function makeAdminAndReservation(): array
{
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $tenant = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $house = BoardingHouse::factory()->create();

    $vacant = Room::create([
        'boarding_house_id' => $house->id,
        'room_number' => 'A-101',
        'status' => 'available',
        'available_slots' => 1,
        'price' => 3500,
    ]);

    $occupied = Room::create([
        'boarding_house_id' => $house->id,
        'room_number' => 'A-102',
        'status' => 'occupied',
        'available_slots' => 0,
        'price' => 4200,
    ]);

    $reservation = Reservation::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'room_id' => $vacant->id,
        'check_in_date' => now()->toDateString(),
        'check_out_date' => now()->addMonth()->toDateString(),
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);

    return [$admin, $house, $vacant, $occupied, $reservation];
}

test('available rooms endpoint returns all rooms with availability flags', function () {
    [$admin, $house, $vacant, $occupied, $reservation] = makeAdminAndReservation();

    $response = $this->actingAs($admin)
        ->getJson(route('admin.api.boarding-houses.available-rooms', [$house, 'reservation_id' => $reservation->id]))
        ->assertOk()
        ->json();

    $rooms = collect($response['rooms']);
    expect($rooms)->toHaveCount(2);
    expect($rooms->firstWhere('id', $vacant->id)['available'])->toBeTrue();
    expect($rooms->firstWhere('id', $occupied->id)['available'])->toBeFalse();
    expect((float) $rooms->firstWhere('id', $vacant->id)['price'])->toBe(3500.0);
});

test('full edit save updates reservation and returns fresh payload', function () {
    [$admin, $house, $vacant, $occupied, $reservation] = makeAdminAndReservation();

    $response = $this->actingAs($admin)
        ->patchJson(route('admin.reservations.update', $reservation), [
            '_full_edit' => '1',
            'room_id' => $vacant->id,
            'check_in_date' => now()->addDays(3)->toDateString(),
            'due_date' => now()->addMonth()->addDays(3)->toDateString(),
            'total_amount' => 3500,
            'status' => 'confirmed',
            'payment_status' => 'partial',
            'house_rules' => "1. No loud music.\n2. Keep clean.",
            'notes' => 'Deposit received in cash.',
        ])
        ->assertOk()
        ->json();

    expect($response['success'])->toBeTrue();
    expect($response['reservation']['status_value'])->toBe('confirmed');
    expect($response['reservation']['payment_label'])->toBe('Partially Paid');
    expect($response['reservation']['amount_formatted'])->toBe('PHP 3,500.00');

    $reservation->refresh();
    expect($reservation->house_rules)->toContain('No loud music');
    expect($reservation->notes)->toBe('Deposit received in cash.');
    expect((float) $reservation->total_amount)->toBe(3500.0);
    expect($reservation->due_date->toDateString())->toBe(now()->addMonth()->addDays(3)->toDateString());
});

test('full edit save rejects missing room and occupied room', function () {
    [$admin, $house, $vacant, $occupied, $reservation] = makeAdminAndReservation();

    // Missing room
    $this->actingAs($admin)
        ->patchJson(route('admin.reservations.update', $reservation), [
            '_full_edit' => '1',
            'room_id' => '',
            'check_in_date' => now()->toDateString(),
            'total_amount' => 3500,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['room_id']);

    // Occupied room (different from currently assigned)
    $this->actingAs($admin)
        ->patchJson(route('admin.reservations.update', $reservation), [
            '_full_edit' => '1',
            'room_id' => $occupied->id,
            'check_in_date' => now()->toDateString(),
            'due_date' => now()->addMonth()->toDateString(),
            'total_amount' => 4200,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'house_rules' => 'Keep clean.',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['room_id']);

    // Missing house rules
    $this->actingAs($admin)
        ->patchJson(route('admin.reservations.update', $reservation), [
            '_full_edit' => '1',
            'room_id' => $vacant->id,
            'check_in_date' => now()->toDateString(),
            'due_date' => now()->addMonth()->toDateString(),
            'total_amount' => 3500,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'house_rules' => '',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['house_rules']);

    // Invalid status value
    $this->actingAs($admin)
        ->patchJson(route('admin.reservations.update', $reservation), [
            '_full_edit' => '1',
            'room_id' => $vacant->id,
            'check_in_date' => now()->toDateString(),
            'due_date' => now()->addMonth()->toDateString(),
            'total_amount' => 3500,
            'status' => 'rejected',
            'payment_status' => 'unpaid',
            'house_rules' => 'Keep clean.',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('legacy quick-action path still works', function () {
    [$admin, $house, $vacant, $occupied, $reservation] = makeAdminAndReservation();

    $this->actingAs($admin)
        ->patch(route('admin.reservations.update', $reservation), [
            'status' => 'approved',
        ])
        ->assertRedirect();

    expect($reservation->refresh()->status)->toBe('approved');
});

test('switching rooms updates occupancy on both rooms', function () {
    [$admin, $house, $vacant, $occupied, $reservation] = makeAdminAndReservation();

    // A second vacant room to switch into
    $newRoom = Room::create([
        'boarding_house_id' => $house->id,
        'room_number' => 'B-201',
        'status' => 'available',
        'available_slots' => 1,
        'price' => 5000,
    ]);

    $this->actingAs($admin)
        ->patchJson(route('admin.reservations.update', $reservation), [
            '_full_edit' => '1',
            'room_id' => $newRoom->id,
            'check_in_date' => now()->toDateString(),
            'due_date' => now()->addMonth()->toDateString(),
            'total_amount' => 5000,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'house_rules' => 'Keep clean.',
        ])
        ->assertOk();

    // New room is now held: slot consumed, status Reserved
    $newRoom->refresh();
    expect((int) $newRoom->available_slots)->toBe(0);
    expect(strtolower($newRoom->status))->toBe('reserved');

    // Old room got its slot back
    $vacant->refresh();
    expect((int) $vacant->available_slots)->toBe(2);

    expect($reservation->refresh()->room_id)->toBe($newRoom->id);
});

test('cancelling releases the held room', function () {
    [$admin, $house, $vacant, $occupied, $reservation] = makeAdminAndReservation();

    $slotsBefore = (int) $vacant->available_slots;

    $this->actingAs($admin)
        ->patchJson(route('admin.reservations.update', $reservation), [
            '_full_edit' => '1',
            'room_id' => $vacant->id,
            'check_in_date' => now()->toDateString(),
            'due_date' => now()->addMonth()->toDateString(),
            'total_amount' => 3500,
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
            'house_rules' => 'Keep clean.',
        ])
        ->assertOk();

    $vacant->refresh();
    expect((int) $vacant->available_slots)->toBe($slotsBefore + 1);
});

test('keeping the same room does not change occupancy', function () {
    [$admin, $house, $vacant, $occupied, $reservation] = makeAdminAndReservation();

    $slotsBefore = (int) $vacant->available_slots;

    $this->actingAs($admin)
        ->patchJson(route('admin.reservations.update', $reservation), [
            '_full_edit' => '1',
            'room_id' => $vacant->id,
            'check_in_date' => now()->toDateString(),
            'due_date' => now()->addMonth()->toDateString(),
            'total_amount' => 3600,
            'status' => 'confirmed',
            'payment_status' => 'partial',
            'house_rules' => 'Keep clean.',
        ])
        ->assertOk();

    $vacant->refresh();
    expect((int) $vacant->available_slots)->toBe($slotsBefore);
});
