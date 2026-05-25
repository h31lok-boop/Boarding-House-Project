<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class DeepSeekService
{
    public function isConfigured(): bool
    {
        return filled(config('services.deepseek.api_key'));
    }

    public function explainRoommateMatch(array $payload): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'content' => null,
                'reason' => 'DeepSeek API key is not configured.',
                'model' => config('services.deepseek.model'),
            ];
        }

        $response = Http::baseUrl(config('services.deepseek.base_url'))
            ->withToken(config('services.deepseek.api_key'))
            ->timeout((int) config('services.deepseek.timeout', 30))
            ->acceptJson()
            ->post('/chat/completions', [
                'model' => config('services.deepseek.model'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You explain roommate compatibility for a boarding house matchmaking system. Keep the answer concise, practical, and grounded in the provided profile data. Mention strengths, risks, and one recommendation.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $this->buildPrompt($payload),
                    ],
                ],
                'temperature' => 0.4,
                'max_tokens' => 350,
            ]);

        try {
            $response->throw();
        } catch (Throwable $exception) {
            return [
                'success' => false,
                'content' => null,
                'reason' => $exception->getMessage(),
                'model' => config('services.deepseek.model'),
            ];
        }

        return [
            'success' => true,
            'content' => data_get($response->json(), 'choices.0.message.content'),
            'reason' => null,
            'model' => data_get($response->json(), 'model', config('services.deepseek.model')),
        ];
    }

    private function buildPrompt(array $payload): string
    {
        return implode("\n", [
            'Explain this roommate recommendation.',
            'Current tenant:',
            json_encode($payload['tenant'], JSON_PRETTY_PRINT),
            'Candidate tenant:',
            json_encode($payload['candidate'], JSON_PRETTY_PRINT),
            'Compatibility summary:',
            json_encode($payload['compatibility'], JSON_PRETTY_PRINT),
            'Requirements:',
            '- Write 3 short sections: strengths, risks, recommendation.',
            '- Do not invent facts beyond the payload.',
            '- Mention the compatibility percent.',
        ]);
    }
}
