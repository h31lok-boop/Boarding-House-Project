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

test('it uses the DeepSeek chat completions API when selected', function () {
    config()->set('services.ai_evaluation.provider', 'deepseek');
    config()->set('services.deepseek.enabled', true);
    config()->set('services.deepseek.api_key', 'test-deepseek-key');
    config()->set('services.deepseek.base_url', 'https://api.deepseek.com');
    config()->set('services.deepseek.model', 'deepseek-v4-flash');

    Http::fake([
        'api.deepseek.com/chat/completions' => Http::response([
            'model' => 'deepseek-v4-flash',
            'choices' => [[
                'finish_reason' => 'stop',
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Open Payments, review the receipt, and continue to PayMongo.',
                ],
            ]],
        ], 200, ['x-request-id' => 'ds_req_test']),
    ]);

    $result = app(OpenAIService::class)->answerQuestion(
        question: 'How do I pay?',
        role: 'tenant',
        history: [['role' => 'assistant', 'content' => 'I can help with BoardMatch.']],
        safetyIdentifier: 'privacy-safe-user-hash',
        systemContext: '{"my_reservations":{"pending":1}}',
    );

    expect($result)
        ->success->toBeTrue()
        ->provider->toBe('deepseek')
        ->model->toBe('deepseek-v4-flash')
        ->request_id->toBe('ds_req_test');

    Http::assertSent(function ($request) {
        $messages = $request['messages'];

        return $request->url() === 'https://api.deepseek.com/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer test-deepseek-key')
            && $request['model'] === 'deepseek-v4-flash'
            && $request['stream'] === false
            && $request['max_tokens'] === 900
            && data_get($request->data(), 'thinking.type') === 'disabled'
            && $request['user_id'] === 'privacy-safe-user-hash'
            && data_get($messages, '0.role') === 'system'
            && str_starts_with(data_get($messages, '0.content'), '[BoardMatch prompt v1]')
            && str_contains(data_get($messages, '0.content'), '"pending":1')
            && data_get($messages, '2.content') === 'How do I pay?';
    });
});

test('it rejects incomplete DeepSeek responses', function () {
    config()->set('services.ai_evaluation.provider', 'deepseek');
    config()->set('services.deepseek.enabled', true);
    config()->set('services.deepseek.api_key', 'test-deepseek-key');
    config()->set('services.deepseek.base_url', 'https://api.deepseek.com');
    config()->set('services.deepseek.model', 'deepseek-v4-flash');

    Http::fake([
        'api.deepseek.com/chat/completions' => Http::response([
            'choices' => [[
                'finish_reason' => 'length',
                'message' => ['content' => 'Truncated'],
            ]],
        ]),
    ]);

    $result = app(OpenAIService::class)->answerQuestion('Help me', 'admin');

    expect($result)
        ->success->toBeFalse()
        ->reason->toContain('incomplete response');
});

test('it uses the Groq chat completions API when selected', function () {
    config()->set('services.ai_evaluation.provider', 'groq');
    config()->set('services.groq.enabled', true);
    config()->set('services.groq.api_key', 'test-groq-key');
    config()->set('services.groq.base_url', 'https://api.groq.com/openai/v1');
    config()->set('services.groq.model', 'openai/gpt-oss-20b');

    Http::fake([
        'api.groq.com/openai/v1/chat/completions' => Http::response([
            'model' => 'openai/gpt-oss-20b',
            'choices' => [[
                'finish_reason' => 'stop',
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Open Payments, review the receipt, and continue to PayMongo.',
                ],
            ]],
        ], 200, ['x-request-id' => 'groq_req_test']),
    ]);

    $result = app(OpenAIService::class)->answerQuestion(
        question: 'How do I pay?',
        role: 'tenant',
        history: [['role' => 'assistant', 'content' => 'I can help with BoardMatch.']],
        safetyIdentifier: 'privacy-safe-user-hash',
    );

    expect($result)
        ->success->toBeTrue()
        ->provider->toBe('groq')
        ->model->toBe('openai/gpt-oss-20b')
        ->request_id->toBe('groq_req_test');

    Http::assertSent(function ($request) {
        $messages = $request['messages'];

        return $request->url() === 'https://api.groq.com/openai/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer test-groq-key')
            && $request['model'] === 'openai/gpt-oss-20b'
            && $request['stream'] === false
            && $request['include_reasoning'] === false
            && $request['reasoning_effort'] === 'low'
            && $request['max_completion_tokens'] === 900
            && $request['user'] === 'privacy-safe-user-hash'
            && ! isset($request['response_format'])
            && data_get($messages, '0.role') === 'system'
            && str_starts_with(data_get($messages, '0.content'), '[BoardMatch prompt v1]')
            && data_get($messages, '2.content') === 'How do I pay?';
    });
});

test('it rejects incomplete Groq responses', function () {
    config()->set('services.ai_evaluation.provider', 'groq');
    config()->set('services.groq.enabled', true);
    config()->set('services.groq.api_key', 'test-groq-key');
    config()->set('services.groq.base_url', 'https://api.groq.com/openai/v1');
    config()->set('services.groq.model', 'openai/gpt-oss-20b');

    Http::fake([
        'api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [[
                'finish_reason' => 'length',
                'message' => ['content' => 'Truncated'],
            ]],
        ]),
    ]);

    $result = app(OpenAIService::class)->answerQuestion('Help me', 'admin');

    expect($result)
        ->success->toBeFalse()
        ->reason->toContain('incomplete response');
});

test('it enables Groq JSON mode for structured analytics', function () {
    config()->set('services.ai_evaluation.provider', 'groq');
    config()->set('services.groq.enabled', true);
    config()->set('services.groq.api_key', 'test-groq-key');
    config()->set('services.groq.base_url', 'https://api.groq.com/openai/v1');
    config()->set('services.groq.model', 'openai/gpt-oss-20b');

    Http::fake([
        'api.groq.com/openai/v1/chat/completions' => Http::response([
            'model' => 'openai/gpt-oss-20b',
            'choices' => [[
                'finish_reason' => 'stop',
                'message' => ['content' => json_encode([
                    'summary' => 'Demand is stable.',
                    'highlights' => ['Demand is 2.', 'Payment risk is 0%.'],
                    'action' => 'Continue monitoring verified records.',
                ])],
            ]],
        ]),
    ]);

    $result = app(OpenAIService::class)->analyzePredictiveInsights([
        'scope' => 'Test records',
        'labels' => ['Aug 2026'],
        'series' => [
            'demand' => [2],
            'reservations' => [1],
            'occupancy' => [50],
            'payment_risk' => [0],
        ],
        'cards' => [],
        'topDemand' => [],
    ], 'admin');

    expect($result)
        ->success->toBeTrue()
        ->analysis->summary->toBe('Demand is stable.');

    Http::assertSent(fn ($request) => data_get($request->data(), 'response_format.type') === 'json_object');
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
