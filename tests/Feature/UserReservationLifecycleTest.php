<?php

use App\Models\BoardingHouse;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\TenantPaymentMethod;
use App\Models\User;
use App\Services\ReservationLifecycleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('submitted reservations receive a 48 hour expiration and hold the selected room', function () {
    $tenant = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $owner = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $room = Room::query()->create([
        'boarding_house_id' => $house->id,
        'room_no' => 'A-101',
        'price' => 3500,
        'status' => 'Available',
        'capacity' => 2,
        'available_slots' => 1,
    ]);

    $this->actingAs($tenant)
        ->post(route('user.boarding-houses.reservations.store', $house), [
            'room_id' => $room->id,
            'check_in_date' => now()->addWeek()->toDateString(),
            'occupants' => 1,
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_number' => '09123456789',
            'notes' => 'Near campus preferred.',
            'terms_accepted' => '1',
        ])
        ->assertRedirect(route('user.reservations.index'));

    $reservation = Reservation::query()->latest('id')->first();

    expect($reservation)->not->toBeNull();
    expect(strtolower((string) $reservation->status))->toBe('pending');
    expect($reservation->expires_at)->not->toBeNull();
    expect((int) abs($reservation->expires_at->diffInHours($reservation->created_at, false)))->toBe(48);
    expect((int) $reservation->occupants)->toBe(1);
    expect($reservation->emergency_contact_name)->toBe('Jane Doe');
    expect($reservation->payment_status)->toBe('unpaid');

    $room->refresh();
    expect((int) $room->available_slots)->toBe(0);
    expect(strtolower((string) $room->status))->toBe('reserved');

    $this->actingAs($tenant)
        ->get(route('user.reservations.index', ['house' => $house->id]))
        ->assertOk()
        ->assertSee('Reserve Your Boarding House Room')
        ->assertSee('Turn-by-Turn Directions')
        ->assertSee('Reservation Form');
});

test('expiration command expires stale reservations, releases rooms, and blocks payments', function () {
    Carbon::setTestNow('2026-07-06 10:00:00');

    $tenant = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $owner = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $room = Room::query()->create([
        'boarding_house_id' => $house->id,
        'room_no' => 'B-201',
        'price' => 4200,
        'status' => 'Reserved',
        'capacity' => 2,
        'available_slots' => 0,
    ]);

    $reservation = Reservation::query()->create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'expires_at' => now()->subMinute(),
        'total_amount' => 4200,
    ]);

    Artisan::call('reservations:expire');

    $reservation->refresh();
    $room->refresh();

    expect(strtolower((string) $reservation->status))->toBe('expired');
    expect($reservation->expired_at)->not->toBeNull();
    expect(strtolower((string) $reservation->payment_status))->toBe('expired');
    expect((int) $room->available_slots)->toBe(1);
    expect(strtolower((string) $room->status))->toBe('available');

    $method = TenantPaymentMethod::query()->create([
        'user_id' => $tenant->id,
        'type' => 'gcash',
        'account_number' => '09123456789',
        'account_name' => 'Hazel Tenant',
        'is_default' => true,
    ]);

    $this->actingAs($tenant)
        ->post(route('user.payments.confirm'), [
            'payment_method_id' => $method->id,
            'payment_amount' => 4200,
        ])
        ->assertRedirect(route('user.reservations.index'))
        ->assertSessionHas('error');

    if (Schema::hasTable('notifications')) {
        $tenantNotified = \App\Models\UserNotification::query()
            ->where('user_id', $tenant->id)
            ->where('reference_id', 'reservation:'.$reservation->id.':expired:user')
            ->exists();
        $ownerNotified = \App\Models\UserNotification::query()
            ->where('user_id', $owner->id)
            ->where('reference_id', 'reservation:'.$reservation->id.':expired:owner')
            ->exists();

        expect($tenantNotified)->toBeTrue();
        expect($ownerNotified)->toBeTrue();
    }

    Carbon::setTestNow();
});

test('approved reservations do not expire once the lifecycle runs', function () {
    $tenant = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $owner = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $reservation = Reservation::query()->create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'status' => 'approved',
        'payment_status' => 'unpaid',
        'approved_at' => now(),
        'expires_at' => now()->subDay(),
    ]);

    app(ReservationLifecycleService::class)->expireStaleReservations();

    $reservation->refresh();
    expect(strtolower((string) $reservation->status))->toBe('approved');
    expect($reservation->expired_at)->toBeNull();
});
