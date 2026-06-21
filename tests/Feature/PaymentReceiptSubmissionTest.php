<?php

use App\Models\PaymentReceipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('cash payment submissions do not require a receipt, reference number, or transaction id', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->post(route('user.payment-receipts.store'), [
            'payment_method' => 'Cash Payment',
            'amount' => '3000',
            'payment_date' => now()->toDateString(),
            'notes' => 'Paid directly to the landlord.',
        ])
        ->assertRedirect(route('user.payments.index'))
        ->assertSessionHasNoErrors();

    $receipt = PaymentReceipt::query()->firstOrFail();

    expect($receipt->payment_method)->toBe('Cash Payment')
        ->and($receipt->receipt_path)->toBeNull()
        ->and($receipt->reference_number)->toBeNull()
        ->and($receipt->transaction_id)->toBeNull()
        ->and($receipt->status)->toBe(PaymentReceipt::STATUS_PENDING_REVIEW);
});

test('digital wallet submissions require a receipt and reference number', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->from(route('user.payments.index'))
        ->post(route('user.payment-receipts.store'), [
            'payment_method' => 'GCash',
            'amount' => '3000',
            'payment_date' => now()->toDateString(),
        ])
        ->assertRedirect(route('user.payments.index'))
        ->assertSessionHasErrors(['receipt', 'reference_number']);
});

test('bank transfer submissions require a transaction id and store receipt files', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->from(route('user.payments.index'))
        ->post(route('user.payment-receipts.store'), [
            'payment_method' => 'Bank Transfer',
            'amount' => '3000',
            'reference_number' => 'BM-BANK-001',
            'payment_date' => now()->toDateString(),
            'receipt' => UploadedFile::fake()->image('bank-transfer.jpg'),
        ])
        ->assertRedirect(route('user.payments.index'))
        ->assertSessionHasErrors(['transaction_id']);

    expect(PaymentReceipt::query()->count())->toBe(0);

    $this->actingAs($user)
        ->post(route('user.payment-receipts.store'), [
            'payment_method' => 'Bank Transfer',
            'amount' => '3000',
            'reference_number' => 'BM-BANK-001',
            'transaction_id' => 'TXN-20260620-001',
            'payment_date' => now()->toDateString(),
            'receipt' => UploadedFile::fake()->image('bank-transfer.jpg'),
        ])
        ->assertRedirect(route('user.payments.index'))
        ->assertSessionHasNoErrors();

    $receipt = PaymentReceipt::query()->firstOrFail();

    expect($receipt->transaction_id)->toBe('TXN-20260620-001')
        ->and($receipt->receipt_path)->not->toBeNull();

    Storage::disk('public')->assertExists($receipt->receipt_path);
});
