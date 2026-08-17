<?php

use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\Reservation;

it('exposes only cash payment workflows', function () {
    $root = dirname(__DIR__, 2);
    $routes = file_get_contents($root.'/routes/web.php');
    $services = file_get_contents($root.'/config/services.php');
    $environment = file_get_contents($root.'/.env.example');
    $staffController = file_get_contents($root.'/app/Http/Controllers/AdminOwnerController.php');
    $tenantPayments = file_get_contents($root.'/resources/views/user/payments.blade.php');

    expect($routes)
        ->not->toContain('/webhooks/paymongo')
        ->not->toContain('/payments/paymongo')
        ->not->toContain('/payment-methods')
        ->and($services)->not->toContain("'paymongo' =>")
        ->and($environment)->not->toContain('PAYMONGO_')
        ->and($staffController)->toContain("Rule::in(['cash'])")
        ->and($tenantPayments)
        ->toContain('Cash payment only')
        ->not->toContain('checkout');
});

it('normalizes payment records to cash at the model boundary', function () {
    expect((new Payment(['payment_method' => 'online']))->payment_method)->toBe('cash')
        ->and((new Reservation(['payment_method' => 'online']))->payment_method)->toBe('cash')
        ->and((new Booking(['payment_method' => 'online']))->payment_method)->toBe('cash')
        ->and((new PaymentReceipt(['payment_method' => 'online']))->payment_method)->toBe('Cash Payment');
});
