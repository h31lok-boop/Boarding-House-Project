<?php

use App\Models\BoardingHouse;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;

test('admin reservations page uses boarding house wording', function () {
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
    $room = Room::create([
        'boarding_house_id' => $house->id,
        'room_number' => 'A-101',
        'status' => 'available',
    ]);

    Reservation::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'check_in_date' => now()->toDateString(),
        'check_out_date' => now()->addMonth()->toDateString(),
        'status' => 'confirmed',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reservations'))
        ->assertOk()
        ->assertSee('Reservations')
        ->assertSee('Reservation No.')
        ->assertSee('Tenant')
        ->assertSee('Boarding House')
        ->assertSee('Room Type')
        ->assertSee('Move-in Date')
        ->assertSee('Reservation Status')
        ->assertSee('Payment Status')
        ->assertSee('Amount')
        ->assertSee('Actions')
        ->assertSee('All')
        ->assertSee('Confirmed')
        ->assertSee('Pending')
        ->assertSee('Cancelled')
        ->assertSee('Export')
        ->assertSee('Filters')
        ->assertDontSee('RESERVATION MANAGEMENT')
        ->assertDontSee('Manage tenant reservations, move-in schedules, and payment status.')
        ->assertDontSee('Owner portal')
        ->assertDontSee('Reservation Queue')
        ->assertDontSee('Review incoming requests, approvals, move-in schedules, and follow-up actions.')
        ->assertDontSee('Search boarding houses, tenants, reservations, payments...')
        ->assertDontSee('Move-out Date')
        ->assertDontSee('Guest')
        ->assertDontSee('Check-in')
        ->assertDontSee('Check-out')
        ->assertDontSee('Checked-in')
        ->assertDontSee('Checked-out');
});

test('admin reservations housing status filters map to stored reservation statuses', function () {
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
    $room = Room::create([
        'boarding_house_id' => $house->id,
        'room_number' => 'B-202',
        'status' => 'occupied',
    ]);

    Reservation::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'check_in_date' => now()->subMonth()->toDateString(),
        'check_out_date' => now()->addMonth()->toDateString(),
        'status' => 'checked-in',
    ]);

    Reservation::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'check_in_date' => now()->subMonths(2)->toDateString(),
        'check_out_date' => now()->subMonth()->toDateString(),
        'status' => 'checked-out',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reservations', ['status' => 'currently-staying']))
        ->assertOk()
        ->assertSee('Currently Staying')
        ->assertDontSee('Completed Stay</span>', false);

    $this->actingAs($admin)
        ->get(route('admin.reservations', ['status' => 'completed-stay']))
        ->assertOk()
        ->assertSee('Completed Stay')
        ->assertDontSee('Currently Staying</span>', false);
});
