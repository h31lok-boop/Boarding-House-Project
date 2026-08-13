<?php

use App\Models\OwnerProfile;
use App\Services\PaymongoService;
use Tests\TestCase;

uses(TestCase::class);

it('uses shared environment credentials for every owner when configured', function () {
    config()->set('services.paymongo.public_key', 'pk_test_shared123');
    config()->set('services.paymongo.secret_key', 'sk_test_shared123');
    config()->set('services.paymongo.webhook_secret', 'whsk_shared123');

    $profile = new OwnerProfile([
        'paymongo_public_key' => 'pk_test_owner123',
        'paymongo_secret_key' => 'sk_test_owner123',
        'paymongo_enabled' => false,
    ]);

    $credentials = app(PaymongoService::class)->credentials($profile);

    expect($credentials['enabled'])->toBeTrue()
        ->and($credentials['source'])->toBe('environment')
        ->and($credentials['public_key'])->toBe('pk_test_shared123')
        ->and($credentials['secret_key'])->toBe('sk_test_shared123')
        ->and(app(PaymongoService::class)->isConfigured($profile))->toBeTrue();
});

it('falls back to owner credentials when shared environment credentials are absent', function () {
    config()->set('services.paymongo.public_key', null);
    config()->set('services.paymongo.secret_key', null);
    config()->set('services.paymongo.webhook_secret', null);

    $profile = new OwnerProfile([
        'paymongo_public_key' => 'pk_test_owner123',
        'paymongo_secret_key' => 'sk_test_owner123',
        'paymongo_enabled' => true,
    ]);

    $credentials = app(PaymongoService::class)->credentials($profile);

    expect($credentials['enabled'])->toBeTrue()
        ->and($credentials['source'])->toBe('owner')
        ->and($credentials['public_key'])->toBe('pk_test_owner123')
        ->and($credentials['secret_key'])->toBe('sk_test_owner123');
});
