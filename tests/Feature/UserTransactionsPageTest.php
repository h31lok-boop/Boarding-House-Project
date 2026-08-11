<?php

use App\Models\PaymentReceipt;
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
        ->assertSee('All-time transactions')
        ->assertSee('Total amount paid')
        ->assertSee('Awaiting verification')
        ->assertSee('Most recent payment')
        ->assertSee('No transactions yet')
        ->assertSee('Your payment records will appear here after successful payment submissions.')
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
        ->assertSee('PayMongo secure checkout')
        ->assertSee('Payment Schedule')
        ->assertSee('Payment Status Guide')
        ->assertDontSee('Upload Proof of Payment')
        ->assertDontSee('Recent Transactions')
        ->assertDontSee('Latest rent and deposit activity.')
        ->assertDontSee('View All');
});

test('transactions page lists payment receipts and filters by status', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => 'user']);
    $other = User::factory()->create(['role' => 'user']);

    PaymentReceipt::create([
        'user_id' => $user->id,
        'payment_method' => 'GCash',
        'amount' => 3000,
        'reference_number' => 'GC123456789',
        'payment_date' => '2026-06-05',
        'receipt_path' => 'payment-receipts/gcash-june.jpg',
        'original_filename' => 'gcash-june.jpg',
        'mime_type' => 'image/jpeg',
        'status' => PaymentReceipt::STATUS_APPROVED,
    ]);

    PaymentReceipt::create([
        'user_id' => $user->id,
        'payment_method' => 'Maya',
        'amount' => 3000,
        'reference_number' => 'MYA987654321',
        'payment_date' => '2026-05-05',
        'receipt_path' => 'payment-receipts/maya-may.pdf',
        'original_filename' => 'maya-may.pdf',
        'mime_type' => 'application/pdf',
        'status' => PaymentReceipt::STATUS_PENDING_REVIEW,
    ]);

    PaymentReceipt::create([
        'user_id' => $other->id,
        'payment_method' => 'GCash',
        'amount' => 3000,
        'reference_number' => 'OTHER123',
        'payment_date' => '2026-04-05',
        'status' => PaymentReceipt::STATUS_APPROVED,
    ]);

    $this->actingAs($user)
        ->get(route('user.transactions.index'))
        ->assertOk()
        ->assertSee('GC123456789')
        ->assertSee('MYA987654321')
        ->assertDontSee('OTHER123');

    $this->actingAs($user)
        ->get(route('user.transactions.index', ['status' => 'pending_review']))
        ->assertOk()
        ->assertSee('MYA987654321')
        ->assertDontSee('GC123456789');
});
