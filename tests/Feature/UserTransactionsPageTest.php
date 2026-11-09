<?php

use App\Models\PaymentReceipt;
use App\Models\BoardingHouse;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('transactions page is dedicated to transaction history only', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->get(route('user.transactions.index'))
        ->assertOk()
        ->assertSee('Transactions')
        ->assertSee('View your payment history, receipts, and transaction records.')
        ->assertSee('Total Transactions')
        ->assertSee('Selected reservation scope')
        ->assertSee('Selected reservation total')
        ->assertSee('No transactions yet')
        ->assertSee('Your cash payment records will appear here after the owner or authorized staff records receiving them.')
        ->assertDontSee('Total Payments')
        ->assertDontSee('Paid Amount')
        ->assertDontSee('Pending Amount')
        ->assertDontSee('Next Payment Due')
        ->assertDontSee('Upload Proof of Payment');
});

test('payments page no longer contains recent transactions history', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->get(route('user.payments.index'))
        ->assertOk()
        ->assertSee('Payment details will appear after owner approval.')
        ->assertDontSee('Payment Schedule');
});

test('transactions page excludes receipts that are not linked to the authoritative reservation', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => 'user']);
    $other = User::factory()->create(['role' => 'user']);
    $house = BoardingHouse::factory()->create(['approval_status' => 'approved', 'status' => 'approved']);

    Reservation::create([
        'user_id' => $user->id,
        'boarding_house_id' => $house->id,
        'status' => 'approved',
        'payment_status' => 'unpaid',
    ]);

    PaymentReceipt::create([
        'user_id' => $user->id,
        'payment_method' => 'Cash Payment',
        'amount' => 3000,
        'reference_number' => 'GC123456789',
        'payment_date' => '2026-06-05',
        'receipt_path' => 'payment-receipts/cash-june.jpg',
        'original_filename' => 'cash-june.jpg',
        'mime_type' => 'image/jpeg',
        'status' => PaymentReceipt::STATUS_APPROVED,
    ]);

    PaymentReceipt::create([
        'user_id' => $user->id,
        'payment_method' => 'Cash Payment',
        'amount' => 3000,
        'reference_number' => 'MYA987654321',
        'payment_date' => '2026-05-05',
        'receipt_path' => 'payment-receipts/cash-may.pdf',
        'original_filename' => 'cash-may.pdf',
        'mime_type' => 'application/pdf',
        'status' => PaymentReceipt::STATUS_PENDING_REVIEW,
    ]);

    PaymentReceipt::create([
        'user_id' => $other->id,
        'payment_method' => 'Cash Payment',
        'amount' => 3000,
        'reference_number' => 'OTHER123',
        'payment_date' => '2026-04-05',
        'status' => PaymentReceipt::STATUS_APPROVED,
    ]);

    $this->actingAs($user)
        ->get(route('user.transactions.index'))
        ->assertOk()
        ->assertDontSee('GC123456789')
        ->assertDontSee('MYA987654321')
        ->assertDontSee('OTHER123');

    $this->actingAs($user)
        ->get(route('user.transactions.index', ['status' => 'pending_review']))
        ->assertOk()
        ->assertDontSee('MYA987654321')
        ->assertDontSee('GC123456789');
});
