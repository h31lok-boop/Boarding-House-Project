<?php

use App\Services\OpenAIService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config()->set('services.ai_evaluation.provider', 'openai');
    config()->set('services.ai_evaluation.prompt_version', 'v1');
    config()->set('services.ai_evaluation.temperature', 0.2);
});

test('it degrades safely when an OpenAI API key is not configured', function () {
    config()->set('services.openai.api_key', null);

    $result = app(OpenAIService::class)->explainRoommateMatch([
        'tenant' => [],
        'candidate' => [],
        'compatibility' => [],
    ]);

    expect($result)
        ->success->toBeFalse()
        ->reason->toBe('OpenAI API key is not configured.');
});

test('it uses the FreeModel Responses API when selected', function () {
    config()->set('services.ai_evaluation.provider', 'freemodel');
    config()->set('services.ai_evaluation.prompt_version', 'v1');
    config()->set('services.ai_evaluation.temperature', 0.2);
    config()->set('services.freemodel.enabled', true);
    config()->set('services.freemodel.api_key', 'test-freemodel-key');
    config()->set('services.freemodel.base_url', 'https://api.freemodel.dev');
    config()->set('services.freemodel.model', 'gpt-5.4');

    Http::fake([
        'api.freemodel.dev/v1/responses' => Http::response([
            'model' => 'gpt-5.4',
            'output_text' => 'Use the Payments page to review the receipt and continue to PayMongo.',
        ], 200, ['x-request-id' => 'fm_req_test']),
    ]);

    $result = app(OpenAIService::class)->answerQuestion('How do I pay?', 'tenant');

    expect($result)
        ->success->toBeTrue()
        ->provider->toBe('freemodel')
        ->model->toBe('gpt-5.4')
        ->request_id->toBe('fm_req_test');

    Http::assertSent(fn ($request) => $request->url() === 'https://api.freemodel.dev/v1/responses'
        && $request->hasHeader('Authorization', 'Bearer test-freemodel-key')
        && $request['model'] === 'gpt-5.4'
        && $request['temperature'] === 0.2
        && str_starts_with($request['instructions'], '[BoardMatch prompt v1]'));
});

test('it calls the OpenAI Responses API without storing the response', function () {
    config()->set('services.openai.api_key', 'test-openai-key');
    config()->set('services.openai.base_url', 'https://api.openai.com/v1');
    config()->set('services.openai.model', 'gpt-5.6-luna');

    Http::fake([
        'api.openai.com/v1/responses' => Http::response([
            'id' => 'resp_test',
            'model' => 'gpt-5.6-luna',
            'output' => [[
                'type' => 'message',
                'content' => [[
                    'type' => 'output_text',
                    'text' => "Strengths: Similar routines.\nRisks: Confirm expectations.\nRecommendation: Talk first.",
                ]],
            ]],
        ], 200, ['x-request-id' => 'req_test']),
    ]);

    $result = app(OpenAIService::class)->explainRoommateMatch([
        'tenant' => ['name' => 'Tenant'],
        'candidate' => ['name' => 'Candidate'],
        'compatibility' => ['compatibility_percent' => 91],
    ]);

    expect($result)
        ->success->toBeTrue()
        ->content->toContain('Similar routines')
        ->model->toBe('gpt-5.6-luna')
        ->request_id->toBe('req_test');

    Http::assertSent(fn ($request) => $request->url() === 'https://api.openai.com/v1/responses'
        && $request->hasHeader('Authorization', 'Bearer test-openai-key')
        && $request['model'] === 'gpt-5.6-luna'
        && $request['store'] === false
        && data_get($request->data(), 'reasoning.effort') === 'low');
});

test('it sends role-aware Q and A messages with a privacy-safe identifier', function () {
    config()->set('services.openai.api_key', 'test-openai-key');
    config()->set('services.openai.base_url', 'https://api.openai.com/v1');
    config()->set('services.openai.model', 'gpt-5.6-luna');

    Http::fake([
        'api.openai.com/v1/responses' => Http::response([
            'model' => 'gpt-5.6-luna',
            'output_text' => 'Open Payments, choose an unpaid charge, review the receipt, and continue to PayMongo.',
        ]),
    ]);

    $result = app(OpenAIService::class)->answerQuestion(
        question: 'How do I pay?',
        role: 'tenant',
        history: [
            ['role' => 'user', 'content' => 'I have an unpaid reservation.'],
            ['role' => 'assistant', 'content' => 'I can guide you through it.'],
        ],
        safetyIdentifier: 'privacy-safe-user-hash',
    );

    expect($result)
        ->success->toBeTrue()
        ->content->toContain('PayMongo');

    Http::assertSent(function ($request) {
        $input = $request['input'];

        return $request['safety_identifier'] === 'privacy-safe-user-hash'
            && $request['store'] === false
            && data_get($input, '0.role') === 'user'
            && data_get($input, '2.content') === 'How do I pay?'
            && str_contains($request['instructions'], 'current user role is: tenant');
    });
});

test('it summarizes only verified predictive metrics for the insights page', function () {
    config()->set('services.openai.api_key', 'test-openai-key');
    config()->set('services.openai.base_url', 'https://api.openai.com/v1');
    config()->set('services.openai.model', 'gpt-5.6');

    Http::fake([
        'api.openai.com/v1/responses' => Http::response([
            'status' => 'completed',
            'model' => 'gpt-5.6',
            'output_text' => json_encode([
                'summary' => 'Demand increased while payment risk remained low.',
                'highlights' => [
                    'The latest verified demand value is 7.',
                    'The verified occupancy forecast is 75%.',
                ],
                'action' => 'Review room availability before the next reservation cycle.',
            ]),
        ]),
    ]);

    $result = app(OpenAIService::class)->analyzePredictiveInsights([
        'scope' => 'Platform-wide historical records',
        'labels' => ['Jul 2026', 'Aug 2026'],
        'series' => [
            'demand' => [4, 7],
            'reservations' => [2, 3],
            'occupancy' => [60, 70],
            'payment_risk' => [20, 10],
        ],
        'cards' => [[
            'title' => 'Occupancy Trend',
            'current' => 70,
            'prediction' => 75,
            'unit' => '%',
            'direction' => 'up',
        ]],
        'topDemand' => [['name' => 'Sunrise House', 'score' => 7]],
    ], 'admin');

    expect($result)
        ->success->toBeTrue()
        ->analysis->summary->toContain('payment risk')
        ->analysis->highlights->toHaveCount(2)
        ->analysis->action->toContain('room availability');

    Http::assertSent(function ($request) {
        $input = (string) $request['input'];

        return $request['model'] === 'gpt-5.6'
            && str_contains($request['instructions'], 'Use only the verified metrics')
            && str_contains($input, 'Platform-wide historical records')
            && str_contains($input, 'Sunrise House')
            && ! str_contains($input, 'email');
    });
});

test('it rejects malformed predictive AI output instead of displaying it', function () {
    config()->set('services.openai.api_key', 'test-openai-key');
    config()->set('services.openai.base_url', 'https://api.openai.com/v1');
    config()->set('services.openai.model', 'gpt-5.6');

    Http::fake([
        'api.openai.com/v1/responses' => Http::response([
            'status' => 'completed',
            'output_text' => 'This is not valid JSON.',
        ]),
    ]);

    $result = app(OpenAIService::class)->analyzePredictiveInsights([
        'labels' => ['Aug 2026'],
        'series' => [],
        'cards' => [],
        'topDemand' => [],
    ], 'owner');

    expect($result)
        ->success->toBeFalse()
        ->analysis->toBeNull()
        ->reason->toContain('invalid analytics format');
});
