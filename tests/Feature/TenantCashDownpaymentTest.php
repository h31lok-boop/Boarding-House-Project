<?php

use App\Models\BoardingHouse;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an authorized owner records one reservation-linked cash downpayment', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $tenantUser = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
    $house = BoardingHouse::factory()->create(['owner_id' => $owner->id]);
    $room = Room::create([
        'boarding_house_id' => $house->id,
        'room_no' => 'D-204',
        'status' => 'Reserved',
        'available_slots' => 0,
        'price' => 3500,
    ]);
    $tenant = Tenant::create([
        'user_id' => $tenantUser->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'status' => 'active',
    ]);
    $reservation = Reservation::create([
        'user_id' => $tenantUser->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'status' => 'approved',
        'approved_at' => now(),
        'payment_status' => 'unpaid',
        'total_amount' => 3500,
    ]);

    $payload = [
        'tenant_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'amount' => 1200,
        'status' => 'paid',
        'payment_type' => 'downpayment',
        'payment_method' => 'cash',
        'paid_at' => '2026-11-08',
        'reference_no' => 'DP-001',
    ];

    $this->actingAs($owner)->post(route('owner.payments.store'), $payload)->assertRedirect();
    $this->actingAs($owner)->post(route('owner.payments.store'), $payload)->assertRedirect();

    expect(Payment::where('reservation_id', $reservation->id)->count())->toBe(1)
        ->and(PaymentReceipt::where('payment_id', Payment::where('reservation_id', $reservation->id)->value('id'))->count())->toBe(1);

    $this->actingAs($tenantUser)
        ->get(route('user.transactions.index'))
        ->assertOk()
        ->assertSee('Downpayment')
        ->assertSee('1,200');

    $this->actingAs($tenantUser)
        ->get(route('user.payments.index'))
        ->assertOk()
        ->assertSee('Downpayment')
        ->assertSee('1,200');
});

test('cash payments cannot be recorded before owner approval', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $tenantUser = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
    $house = BoardingHouse::factory()->create(['owner_id' => $owner->id]);
    $tenant = Tenant::create([
        'user_id' => $tenantUser->id,
        'boarding_house_id' => $house->id,
        'status' => 'active',
    ]);
    Reservation::create([
        'user_id' => $tenantUser->id,
        'boarding_house_id' => $house->id,
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);

    $this->actingAs($owner)
        ->post(route('owner.payments.store'), [
            'tenant_id' => $tenant->id,
            'boarding_house_id' => $house->id,
            'amount' => 1200,
            'status' => 'paid',
            'payment_type' => 'downpayment',
            'payment_method' => 'cash',
        ])
        ->assertStatus(422);

    expect(Payment::count())->toBe(0);
});
