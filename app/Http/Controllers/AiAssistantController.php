<?php

namespace App\Http\Controllers;

use App\Services\OpenAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AiAssistantController extends Controller
{
    public function __construct(private readonly OpenAIService $openAIService) {}

    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:2', 'max:1200'],
            'history' => ['sometimes', 'array', 'max:8'],
            'history.*.role' => ['required', Rule::in(['user', 'assistant'])],
            'history.*.content' => ['required', 'string', 'max:1600'],
        ]);

        $user = $request->user();
        abort_unless($user, 401);

        $role = match (true) {
            $user->isSuperAdmin() => 'administrator',
            $user->isStrictOwner() => 'property owner',
            default => 'tenant',
        };

        $result = $this->openAIService->answerQuestion(
            question: trim($validated['question']),
            role: $role,
            history: $validated['history'] ?? [],
            safetyIdentifier: hash_hmac('sha256', 'boardmatch-user-'.$user->getKey(), (string) config('app.key')),
        );

        if (! ($result['success'] ?? false)) {
            return response()->json([
                'message' => $result['reason'] ?? 'The AI assistant is temporarily unavailable.',
            ], 503);
        }

        return response()->json([
            'answer' => $result['content'],
            'model' => $result['model'],
        ]);
    }
}
