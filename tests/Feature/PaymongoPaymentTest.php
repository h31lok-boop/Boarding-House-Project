<?php

use App\Models\BoardingHouse;
use App\Models\OwnerProfile;
use App\Models\Payment;
use App\Models\PaymongoCheckout;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PaymongoService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    config()->set('services.paymongo.public_key', null);
    config()->set('services.paymongo.secret_key', null);
    config()->set('services.paymongo.webhook_secret', null);
});

function paymongoFixture(): array
{
    $owner = User::factory()->create([
        'role' => 'owner',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $tenantUser = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $profile = OwnerProfile::create([
        'user_id' => $owner->id,
        'valid_id_type' => 'business_permit',
        'valid_id_number' => 'PAYMONGO-OWNER-001',
        'valid_id_file' => 'test-permits/paymongo-owner.pdf',
        'proof_of_ownership' => 'test-permits/paymongo-owner.pdf',
        'verification_status' => 'verified',
        'paymongo_public_key' => 'pk_test_owner123',
        'paymongo_secret_key' => 'sk_test_owner123',
        'paymongo_webhook_secret' => 'whsk_test_owner123',
        'paymongo_enabled' => true,
    ]);
    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'owner_profile_id' => $profile->id,
        'is_active' => true,
        'approval_status' => 'approved',
    ]);
    $tenant = Tenant::create([
        'user_id' => $tenantUser->id,
        'boarding_house_id' => $house->id,
        'status' => 'active',
    ]);
    $payment = Payment::create([
        'tenant_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'amount' => 2750.50,
        'due_date' => today(),
        'status' => 'pending',
        'payment_type' => 'rent',
        'reference_no' => 'BILL-TEST-001',
    ]);

    return compact('owner', 'tenantUser', 'profile', 'house', 'tenant', 'payment');
}

it('stores encrypted owner-scoped PayMongo settings without revealing saved secrets', function () {
    $owner = User::factory()->verifiedOwner()->create();

    $this->actingAs($owner)->put(route('owner.payment-settings.update'), [
        'owner_id' => $owner->id,
        'paymongo_public_key' => 'pk_test_public123',
        'paymongo_secret_key' => 'sk_test_secret123',
        'paymongo_webhook_secret' => 'whsk_test_signing123',
        'paymongo_enabled' => '1',
    ])->assertSessionHas('success');

    $profile = OwnerProfile::where('user_id', $owner->id)->firstOrFail();
    expect($profile->paymongo_secret_key)->toBe('sk_test_secret123')
        ->and($profile->paymongo_enabled)->toBeTrue()
        ->and($profile->getRawOriginal('paymongo_secret_key'))->not->toContain('sk_test_secret123');

    $this->actingAs($owner)->get(route('owner.payment-settings'))
        ->assertOk()
        ->assertSee('PayMongo settings')
        ->assertSee('Configured — leave blank to keep')
        ->assertDontSee('sk_test_secret123');

    $this->actingAs($owner)->put(route('owner.payment-settings.update'), [
        'owner_id' => $owner->id,
        'paymongo_enabled' => '1',
    ])->assertSessionHas('success');

    expect($profile->refresh()->paymongo_secret_key)->toBe('sk_test_secret123');
});

it('creates a PayMongo hosted checkout using the server-side bill amount', function () {
    $fixture = paymongoFixture();
    Http::fake([
        'api.paymongo.com/v2/checkout_sessions' => Http::response([
            'data' => [
                'id' => 'cs_test_123',
                'type' => 'checkout_session',
                'attributes' => ['checkout_url' => 'https://checkout.paymongo.com/cs_test_123'],
            ],
        ], 200),
    ]);

    $this->actingAs($fixture['tenantUser'])
        ->post(route('user.paymongo.checkout'), ['payment_id' => $fixture['payment']->id])
        ->assertRedirect('https://checkout.paymongo.com/cs_test_123');

    $checkout = PaymongoCheckout::where('payment_id', $fixture['payment']->id)->firstOrFail();
    expect($checkout->status)->toBe('pending')
        ->and($checkout->checkout_session_id)->toBe('cs_test_123')
        ->and((float) $checkout->amount)->toBe(2750.50);

    Http::assertSent(function (Request $request) use ($checkout) {
        return $request->url() === 'https://api.paymongo.com/v2/checkout_sessions'
            && $request['data']['attributes']['line_items'][0]['amount'] === 275050
            && $request['data']['attributes']['reference_number'] === $checkout->reference_number
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('sk_test_owner123:'));
    });
});

it('shows a pre-payment receipt modal before opening PayMongo checkout', function () {
    $fixture = paymongoFixture();

    $this->actingAs($fixture['tenantUser'])
        ->get(route('user.payments.index'))
        ->assertOk()
        ->assertSee('Pre-payment Receipt')
        ->assertSee('Proceed to PayMongo')
        ->assertSee('BILL-TEST-001')
        ->assertSee('2,750.50')
        ->assertSee('not proof of payment');

    Http::assertNothingSent();
    expect(\App\Models\PaymentReceipt::where('payment_id', $fixture['payment']->id)->exists())->toBeFalse();
});

it('confirms a returned PayMongo payment when a webhook is not configured', function () {
    $fixture = paymongoFixture();
    $fixture['profile']->update(['paymongo_webhook_secret' => null]);

    $gateway = app(PaymongoService::class);
    expect($gateway->isConfigured($fixture['profile']->refresh()))->toBeTrue()
        ->and($gateway->hasWebhookSecret($fixture['profile']))->toBeFalse();

    Http::fake(function (Request $request) {
        if ($request->method() === 'POST') {
            return Http::response([
                'data' => [
                    'id' => 'cs_test_return',
                    'type' => 'checkout_session',
                    'attributes' => ['checkout_url' => 'https://checkout.paymongo.com/cs_test_return'],
                ],
            ]);
        }

        return Http::response([
            'data' => [
                'id' => 'cs_test_return',
                'type' => 'checkout_session',
                'attributes' => [
                    'status' => 'active',
                    'payments' => [[
                        'id' => 'pay_test_return',
                        'attributes' => ['status' => 'paid', 'amount' => 275050],
                    ]],
                ],
            ],
        ]);
    });

    $this->actingAs($fixture['tenantUser'])
        ->post(route('user.paymongo.checkout'), ['payment_id' => $fixture['payment']->id])
        ->assertRedirect('https://checkout.paymongo.com/cs_test_return');

    $checkout = PaymongoCheckout::where('payment_id', $fixture['payment']->id)->firstOrFail();
    $returnUrl = URL::temporarySignedRoute(
        'user.paymongo.return',
        now()->addHour(),
        ['checkout' => $checkout],
    );

    $this->actingAs($fixture['tenantUser'])
        ->get($returnUrl)
        ->assertRedirect(route('user.payments.index'))
        ->assertSessionHas('payment_confirmed', true);

    expect($fixture['payment']->refresh()->status)->toBe('paid')
        ->and($checkout->refresh()->status)->toBe('paid')
        ->and($checkout->paymongo_payment_id)->toBe('pay_test_return');
    $this->assertDatabaseHas('payment_receipts', [
        'payment_id' => $fixture['payment']->id,
        'payment_method' => 'PayMongo',
        'status' => 'approved',
    ]);
});

it('prevents a tenant from checking out another tenants bill', function () {
    $fixture = paymongoFixture();
    $otherTenant = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($otherTenant)
        ->post(route('user.paymongo.checkout'), ['payment_id' => $fixture['payment']->id])
        ->assertNotFound();

    Http::assertNothingSent();
    expect(PaymongoCheckout::count())->toBe(0);
});

it('rejects invalid webhook signatures and settles valid paid webhooks idempotently', function () {
    $fixture = paymongoFixture();
    $checkout = PaymongoCheckout::create([
        'user_id' => $fixture['tenantUser']->id,
        'tenant_id' => $fixture['tenant']->id,
        'owner_id' => $fixture['owner']->id,
        'boarding_house_id' => $fixture['house']->id,
        'payment_id' => $fixture['payment']->id,
        'checkout_session_id' => 'cs_test_webhook',
        'reference_number' => 'BM-PM-WEBHOOK123',
        'amount' => 2750.50,
        'currency' => 'PHP',
        'status' => 'pending',
    ]);
    $payload = [
        'data' => [
            'type' => 'checkout_session.payment.paid',
            'livemode' => false,
            'data' => [
                'id' => 'cs_test_webhook',
                'type' => 'checkout_session',
                'attributes' => [
                    'reference_number' => $checkout->reference_number,
                    'payments' => [[
                        'id' => 'pay_test_123',
                        'attributes' => ['status' => 'paid', 'amount' => 275050],
                    ]],
                ],
            ],
        ],
    ];
    $raw = json_encode($payload, JSON_UNESCAPED_SLASHES);

    $this->call('POST', route('paymongo.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_PAYMONGO_SIGNATURE' => 't='.time().',te=invalid',
    ], $raw)->assertUnauthorized();
    expect($fixture['payment']->refresh()->status)->toBe('pending');

    $timestamp = time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$raw, 'whsk_test_owner123');
    $headers = [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_PAYMONGO_SIGNATURE' => "t={$timestamp},te={$signature}",
    ];

    $this->call('POST', route('paymongo.webhook'), [], [], [], $headers, $raw)->assertOk();
    $this->call('POST', route('paymongo.webhook'), [], [], [], $headers, $raw)->assertOk();

    expect($fixture['payment']->refresh()->status)->toBe('paid')
        ->and($fixture['payment']->payment_method)->toBe('paymongo')
        ->and($checkout->refresh()->status)->toBe('paid')
        ->and($checkout->paymongo_payment_id)->toBe('pay_test_123');
    $this->assertDatabaseHas('payment_receipts', [
        'payment_id' => $fixture['payment']->id,
        'payment_method' => 'PayMongo',
        'status' => 'approved',
    ]);
    $this->assertDatabaseHas('notifications', [
        'user_id' => $fixture['tenantUser']->id,
        'title' => 'PayMongo payment confirmed',
        'reference_id' => 'paymongo:'.$checkout->id.':tenant',
    ]);
    $this->assertDatabaseHas('notifications', [
        'user_id' => $fixture['owner']->id,
        'title' => 'Tenant payment received',
        'reference_id' => 'paymongo:'.$checkout->id.':owner',
    ]);
    expect(\App\Models\PaymentReceipt::where('payment_id', $fixture['payment']->id)->count())->toBe(1);
});
