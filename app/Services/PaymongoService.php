<?php

namespace App\Services;

use App\Models\OwnerProfile;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaymongoService
{
    public function usesSharedCredentials(): bool
    {
        return filled(config('services.paymongo.public_key'))
            && filled(config('services.paymongo.secret_key'));
    }

    public function credentials(?OwnerProfile $profile): array
    {
        if ($this->usesSharedCredentials()) {
            return [
                'enabled' => true,
                'public_key' => config('services.paymongo.public_key'),
                'secret_key' => config('services.paymongo.secret_key'),
                'webhook_secret' => config('services.paymongo.webhook_secret'),
                'source' => 'environment',
            ];
        }

        return [
            'enabled' => $profile
                ? (bool) $profile->paymongo_enabled
                : filled(config('services.paymongo.secret_key')),
            'public_key' => $profile?->paymongo_public_key ?: config('services.paymongo.public_key'),
            'secret_key' => $profile?->paymongo_secret_key ?: config('services.paymongo.secret_key'),
            'webhook_secret' => $profile?->paymongo_webhook_secret ?: config('services.paymongo.webhook_secret'),
            'source' => $profile ? 'owner' : 'environment',
        ];
    }

    public function isConfigured(?OwnerProfile $profile): bool
    {
        $credentials = $this->credentials($profile);

        return $credentials['enabled']
            && filled($credentials['secret_key']);
    }

    public function hasWebhookSecret(?OwnerProfile $profile): bool
    {
        return filled($this->credentials($profile)['webhook_secret']);
    }

    public function createCheckoutSession(string $secretKey, array $attributes): array
    {
        $response = $this->client($secretKey)
            ->post($this->endpoint('/v2/checkout_sessions'), [
                'data' => ['attributes' => $attributes],
            ]);

        if ($response->failed()) {
            throw new RuntimeException($this->errorMessage($response->json()));
        }

        $session = $response->json('data');
        if (! is_array($session) || blank(Arr::get($session, 'id')) || blank(Arr::get($session, 'attributes.checkout_url'))) {
            throw new RuntimeException('PayMongo did not return a valid checkout session.');
        }

        return $session;
    }

    public function retrieveCheckoutSession(string $secretKey, string $sessionId): array
    {
        $response = $this->client($secretKey)
            ->get($this->endpoint('/v2/checkout_sessions/'.rawurlencode($sessionId)));

        if ($response->failed()) {
            throw new RuntimeException($this->errorMessage($response->json()));
        }

        $session = $response->json('data');
        if (! is_array($session)) {
            throw new RuntimeException('PayMongo did not return the checkout session.');
        }

        return $session;
    }

    public function verifyWebhookSignature(string $rawBody, string $signatureHeader, string $webhookSecret, bool $liveMode): bool
    {
        $parts = [];
        foreach (explode(',', $signatureHeader) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($key && $value) {
                $parts[$key] = $value;
            }
        }

        $timestamp = isset($parts['t']) ? (int) $parts['t'] : 0;
        $signature = $parts[$liveMode ? 'li' : 'te'] ?? null;
        if ($timestamp <= 0 || ! is_string($signature) || abs(time() - $timestamp) > 300) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$rawBody, $webhookSecret);

        return hash_equals($expected, $signature);
    }

    public function isPaid(array $session): bool
    {
        $attributes = Arr::get($session, 'attributes', []);
        $statuses = [
            Arr::get($attributes, 'status'),
            Arr::get($attributes, 'payment_intent.attributes.status'),
            Arr::get($attributes, 'payments.0.attributes.status'),
        ];

        return collect($statuses)
            ->filter()
            ->map(fn ($status) => strtolower((string) $status))
            ->contains(fn ($status) => in_array($status, ['paid', 'succeeded'], true));
    }

    public function paymentId(array $session): ?string
    {
        return Arr::get($session, 'attributes.payments.0.id')
            ?: Arr::get($session, 'attributes.payment_intent.id');
    }

    public function paidAmountInCentavos(array $session): ?int
    {
        $amount = Arr::get($session, 'attributes.payments.0.attributes.amount')
            ?? Arr::get($session, 'attributes.payment_intent.attributes.amount');

        return is_numeric($amount) ? (int) $amount : null;
    }

    private function client(string $secretKey): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->withBasicAuth($secretKey, '')
            ->timeout(30)
            ->connectTimeout(10);
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('services.paymongo.base_url'), '/').$path;
    }

    private function errorMessage(mixed $payload): string
    {
        $detail = Arr::get(is_array($payload) ? $payload : [], 'errors.0.detail');

        return $detail ? 'PayMongo: '.$detail : 'PayMongo could not process the request. Please try again.';
    }
}
