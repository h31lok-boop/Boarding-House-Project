<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('legacy tenant payment proof submissions are disabled in favor of PayMongo', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->post(route('user.payment-receipts.store'), [
            'payment_method' => 'GCash',
            'amount' => '3000',
            'payment_date' => now()->toDateString(),
            'reference_number' => 'LEGACY-001',
        ])
        ->assertRedirect(route('user.payments.index'))
        ->assertSessionHas('error', 'Manual payment proof submissions are disabled. Please use PayMongo secure checkout.');

    $this->assertDatabaseCount('payment_receipts', 0);
});
