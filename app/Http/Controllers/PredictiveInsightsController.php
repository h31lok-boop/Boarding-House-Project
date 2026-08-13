<?php

namespace App\Http\Controllers;

use App\Services\OpenAIService;
use App\Services\PredictiveAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PredictiveInsightsController extends Controller
{
    public function __construct(
        private readonly PredictiveAnalyticsService $analytics,
        private readonly OpenAIService $openAI,
    ) {}

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'months' => ['nullable', 'integer', 'in:4,6,9,12'],
        ]);
        $user = $request->user();
        abort_unless($user?->isSuperAdmin(), 403);

        $data = $this->analytics->build($user, (int) ($validated['months'] ?? 6));
        $data['months'] = (int) ($validated['months'] ?? 6);
        $data['aiInsights'] = $this->aiInsights($user->id, $data);

        return view('admin.predictive-insights', $data);
    }

    private function aiInsights(int $userId, array $data): array
    {
        $base = [
            'configured' => $this->openAI->isConfigured(),
            'success' => false,
            'analysis' => null,
            'provider' => $this->openAI->provider(),
            'model' => $this->openAI->model(),
            'reason' => null,
        ];

        if (! $data['hasHistoricalData']) {
            return array_merge($base, [
                'reason' => 'AI analysis will appear after real reservations, inquiries, occupancy, or payments are recorded.',
            ]);
        }

        if (! $this->openAI->isConfigured()) {
            return array_merge($base, [
                'reason' => $this->openAI->providerLabel().' API key is not configured.',
            ]);
        }

        $payload = [
            'scope' => $data['scope'],
            'labels' => $data['labels'],
            'series' => $data['series'],
            'cards' => $data['cards'],
            'topDemand' => collect($data['topDemand'])->values()->all(),
        ];
        $cacheKey = 'boardmatch:predictive-ai:v1:'.$userId.':'.hash('sha256', json_encode([
            $this->openAI->provider(),
            $this->openAI->model(),
            $data['role'],
            $payload,
        ]));

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return array_merge($base, $cached);
        }

        $result = $this->openAI->analyzePredictiveInsights($payload, $data['role']);
        if ($result['success'] ?? false) {
            Cache::put($cacheKey, $result, now()->addMinutes(15));
        }

        return array_merge($base, $result);
    }
}
