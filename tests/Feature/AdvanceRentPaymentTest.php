<?php

use App\Models\AdvanceRentAllocation;
use App\Models\BoardingHouse;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function advanceRentFixture(): array
{
    $owner = User::factory()->verifiedOwner()->create();
    $tenantUser = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
    $house = BoardingHouse::factory()->create(['owner_id' => $owner->id]);
    $room = Room::create([
        'boarding_house_id' => $house->id,
        'room_no' => 'S-01',
        'status' => 'Reserved',
        'available_slots' => 0,
        'price' => 2800,
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
        'total_amount' => 2800,
        'check_in_date' => '2026-11-22',
    ]);

    $firstBill = Payment::create([
        'tenant_id' => $tenant->id,
        'reservation_id' => $reservation->id,
        'boarding_house_id' => $house->id,
        'amount' => 2800,
        'due_date' => '2026-11-22',
        'status' => 'unpaid',
        'payment_method' => 'cash',
        'payment_type' => 'rent',
    ]);
    $secondBill = Payment::create([
        'tenant_id' => $tenant->id,
        'reservation_id' => $reservation->id,
        'boarding_house_id' => $house->id,
        'amount' => 2800,
        'due_date' => '2026-12-22',
        'status' => 'unpaid',
        'payment_method' => 'cash',
        'payment_type' => 'rent',
    ]);

    return compact('owner', 'tenantUser', 'tenant', 'house', 'reservation', 'firstBill', 'secondBill');
}

test('owner records one advance-rent receipt and tenant sees two covered periods everywhere', function () {
    $fixture = advanceRentFixture();

    $payload = [
        'tenant_id' => $fixture['tenant']->id,
        'boarding_house_id' => $fixture['house']->id,
        'reservation_id' => $fixture['reservation']->id,
        'amount' => 5600,
        'status' => 'paid',
        'payment_type' => 'advance_rent',
        'payment_method' => 'cash',
        'paid_at' => '2026-11-08',
        'reference_no' => 'ADV-001',
        'allocation_json' => json_encode([
            ['billing_payment_id' => $fixture['firstBill']->id, 'period_start' => '2026-11-22', 'period_end' => '2026-12-21', 'amount' => 2800],
            ['billing_payment_id' => $fixture['secondBill']->id, 'period_start' => '2026-12-22', 'period_end' => '2027-01-21', 'amount' => 2800],
        ]),
    ];

    $this->actingAs($fixture['owner'])->post(route('owner.payments.store'), $payload)->assertRedirect();
    $this->actingAs($fixture['owner'])->post(route('owner.payments.store'), $payload)->assertRedirect();

    $advancePayment = Payment::where('reservation_id', $fixture['reservation']->id)->where('payment_type', 'advance_rent')->firstOrFail();
    expect(Payment::where('reservation_id', $fixture['reservation']->id)->where('payment_type', 'advance_rent')->count())->toBe(1)
        ->and(AdvanceRentAllocation::where('payment_id', $advancePayment->id)->count())->toBe(2)
        ->and(PaymentReceipt::where('payment_id', $advancePayment->id)->count())->toBe(1)
        ->and(Transaction::where('payment_id', $advancePayment->id)->count())->toBe(1);

    $this->actingAs($fixture['tenantUser'])->get(route('user.dashboard'))
        ->assertOk()
        ->assertSee('2 months advance rent recorded')
        ->assertSee('Rent covered through Jan 21, 2027');
    $this->actingAs($fixture['tenantUser'])->get(route('user.payments.index', ['reservation' => $fixture['reservation']->id]))
        ->assertOk()
        ->assertSee('2 months advance rent recorded')
        ->assertSee('Paid');
    $this->actingAs($fixture['tenantUser'])->get(route('user.transactions.index', ['reservation' => $fixture['reservation']->id]))
        ->assertOk()
        ->assertSee('Advance Rent — 2 months')
        ->assertSee('5,600');
});

test('an advance rent receipt is not inferred from an empty billing schedule', function () {
    $fixture = advanceRentFixture();
    $fixture['firstBill']->delete();
    $fixture['secondBill']->delete();

    $this->actingAs($fixture['tenantUser'])->get(route('user.dashboard'))
        ->assertOk()
        ->assertSee('Billing schedule awaiting owner setup.')
        ->assertSee('No payments recorded yet');

    expect(Payment::where('reservation_id', $fixture['reservation']->id)->where('payment_type', 'advance_rent')->exists())->toBeFalse();
});
