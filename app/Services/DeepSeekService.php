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
            return $this->notConfiguredResult();
        }

        return $this->chat([
            [
                'role' => 'system',
                'content' => 'You explain roommate compatibility for a boarding house matchmaking system. Keep the answer concise, practical, and grounded in the provided profile data. Mention strengths, risks, and one recommendation.',
            ],
            [
                'role' => 'user',
                'content' => $this->buildRoommatePrompt($payload),
            ],
        ], 350);
    }

    public function explainBoardingHouseRecommendations(array $payload): array
    {
        if (! $this->isConfigured()) {
            return $this->notConfiguredResult(['explanations' => []]);
        }

        if (empty($payload['recommendations'])) {
            return [
                'success' => true,
                'content' => null,
                'explanations' => [],
                'reason' => null,
                'model' => config('services.deepseek.model'),
            ];
        }

        $result = $this->chat([
            [
                'role' => 'system',
                'content' => 'You are the explanation layer for BoardMatch, a student boarding-house recommendation system. The numeric compatibility score and eligibility checks are already verified by the application. Explain those results without changing scores, approving listings, or inventing facts.',
            ],
            [
                'role' => 'user',
                'content' => $this->buildBoardingHousePrompt($payload),
            ],
        ], 1400, ['type' => 'json_object']);

        if (! $result['success']) {
            return array_merge($result, ['explanations' => []]);
        }

        $decoded = $this->decodeJsonContent((string) $result['content']);
        $rawExplanations = data_get($decoded, 'recommendations', []);
        $explanations = collect(is_array($rawExplanations) ? $rawExplanations : [])
            ->mapWithKeys(function (mixed $value, mixed $key): array {
                if (is_string($value) && trim($value) !== '') {
                    return [(string) $key => trim($value)];
                }

                if (is_array($value)) {
                    $id = $value['id'] ?? $key;
                    $text = $value['explanation'] ?? $value['content'] ?? null;

                    if ($id !== null && is_string($text) && trim($text) !== '') {
                        return [(string) $id => trim($text)];
                    }
                }

                return [];
            })
            ->all();

        if ($explanations === []) {
            return array_merge($result, [
                'success' => false,
                'explanations' => [],
                'reason' => 'DeepSeek returned an invalid recommendation explanation format.',
            ]);
        }

        return array_merge($result, ['explanations' => $explanations]);
    }

    private function chat(array $messages, int $maxTokens, ?array $responseFormat = null): array
    {
        $request = [
            'model' => config('services.deepseek.model'),
            'messages' => $messages,
            'temperature' => 0.3,
            'max_tokens' => $maxTokens,
            'stream' => false,
        ];

        if ($responseFormat !== null) {
            $request['response_format'] = $responseFormat;
        }

        try {
            $response = Http::baseUrl(config('services.deepseek.base_url'))
                ->withToken(config('services.deepseek.api_key'))
                ->timeout((int) config('services.deepseek.timeout', 30))
                ->connectTimeout((int) config('services.deepseek.connect_timeout', 20))
                ->acceptJson()
                ->post('/chat/completions', $request);
        } catch (Throwable $exception) {
            return [
                'success' => false,
                'content' => null,
                'reason' => 'Could not connect to DeepSeek: '.$exception->getMessage(),
                'model' => config('services.deepseek.model'),
            ];
        }

        if ($response->failed()) {
            return [
                'success' => false,
                'content' => null,
                'reason' => data_get($response->json(), 'error.message')
                    ?: 'DeepSeek request failed with HTTP '.$response->status().'.',
                'model' => config('services.deepseek.model'),
            ];
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        return [
            'success' => is_string($content) && trim($content) !== '',
            'content' => is_string($content) ? trim($content) : null,
            'reason' => is_string($content) && trim($content) !== ''
                ? null
                : 'DeepSeek returned an empty response.',
            'model' => data_get($response->json(), 'model', config('services.deepseek.model')),
        ];
    }

    private function notConfiguredResult(array $additional = []): array
    {
        return array_merge([
            'success' => false,
            'content' => null,
            'reason' => 'DeepSeek API key is not configured.',
            'model' => config('services.deepseek.model'),
        ], $additional);
    }

    private function decodeJsonContent(string $content): ?array
    {
        $content = trim($content);
        $content = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $content) ?? $content;
        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function buildBoardingHousePrompt(array $payload): string
    {
        return implode("\n", [
            'Create a short explanation for each ranked boarding house.',
            'Return only one valid JSON object in this exact shape:',
            '{"recommendations":{"BOARDING_HOUSE_ID":"Two or three concise sentences."}}',
            'Rules:',
            '- Keep each explanation below 70 words.',
            '- Treat match_score and verified_reasons as authoritative.',
            '- Mention the strongest preference matches and any meaningful limitation.',
            '- Do not change a score, claim verification, or invent amenities, prices, distances, or availability.',
            '- Write for a student choosing housing near DSSC Main Campus.',
            'Student preferences:',
            json_encode($payload['preferences'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'Ranked recommendations:',
            json_encode($payload['recommendations'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ]);
    }

    private function buildRoommatePrompt(array $payload): string
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
