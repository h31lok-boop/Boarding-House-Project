<?php

use App\Models\BoardingHouse;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tenant dashboard and default reservation tracker share the same primary reservation', function () {
    $tenant = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $owner = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Authoritative House',
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);
    $room = Room::query()->create([
        'boarding_house_id' => $house->id,
        'room_no' => 'AUTH-1',
        'price' => 4200,
        'status' => 'Available',
        'capacity' => 1,
        'available_slots' => 1,
    ]);

    $primary = Reservation::query()->create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'check_in_date' => now()->addDays(3)->toDateString(),
        'total_amount' => 4200,
    ]);
    Reservation::query()->create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'check_in_date' => now()->addMonths(2)->toDateString(),
        'total_amount' => 5100,
    ]);

    $dashboard = $this->actingAs($tenant)->get(route('user.dashboard'))->assertOk();
    $tracker = $this->actingAs($tenant)->get(route('user.reservations.index'))->assertOk();

    expect($dashboard->viewData('activeReservation')['id'])->toBe($primary->id)
        ->and($tracker->viewData('trackingReservation')->id)->toBe($primary->id)
        ->and($dashboard->viewData('activeReservation')['status_label'])->toBe('Pending owner approval')
        ->and($dashboard->viewData('activeReservation')['room_no'])->toBe('AUTH-1')
        ->and($dashboard->viewData('bookingInfo')['boarding_house'])->toBe('No approved reservation yet');

    $dashboard->assertSee(route('user.reservations.index', ['reservation' => $primary->id]), false);
});
