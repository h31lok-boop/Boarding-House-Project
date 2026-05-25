<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\RoommateMatchRequest;
use App\Models\User;
use App\Services\CompatibilityService;
use App\Services\DeepSeekService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantMatchController extends Controller
{
    public function __construct(
        private readonly CompatibilityService $compatibilityService,
        private readonly DeepSeekService $deepSeekService,
    ) {}

    public function index(Request $request): View
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isTenant(), 403);

        $tenant->loadMissing('tenantMatchProfile', 'boardingHouse:id,name');

        abort_unless($tenant->tenantMatchProfile?->completed_at, 403, 'Complete your match profile first.');

        $candidates = User::query()
            ->with(['tenantMatchProfile', 'boardingHouse:id,name'])
            ->whereKeyNot($tenant->id)
            ->where('is_archived', false)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereRaw('LOWER(role) = ?', ['tenant'])
                    ->orWhereHas('roles', function ($roleQuery) {
                        $roleQuery->whereRaw('LOWER(name) = ?', ['tenant']);
                    });
            })
            ->whereHas('tenantMatchProfile', fn ($query) => $query->whereNotNull('completed_at'))
            ->get();

        $requests = RoommateMatchRequest::query()
            ->where(function ($query) use ($tenant) {
                $query->where('sender_id', $tenant->id)
                    ->orWhere('recipient_id', $tenant->id);
            })
            ->get();

        $matches = $candidates
            ->map(function (User $candidate) use ($tenant, $requests) {
                $compatibility = $this->compatibilityService->score($tenant, $candidate);

                return [
                    'candidate' => $candidate,
                    'compatibility' => $compatibility,
                    'context' => $this->candidateContext($tenant, $candidate),
                    'requestState' => $this->resolveRequestState($tenant, $candidate, $requests),
                ];
            })
            ->sortByDesc(fn (array $item) => $item['compatibility']['overall_score'])
            ->values();

        return view('tenant.matches.index', [
            'tenant' => $tenant,
            'matches' => $matches->take(20),
            'matchCount' => $matches->count(),
        ]);
    }

    public function show(Request $request, User $candidate): View
    {
        return $this->renderMatchDetail($request, $candidate);
    }

    public function explain(Request $request, User $candidate): View
    {
        $match = $this->matchDetailData($request, $candidate);
        $match['aiExplanation'] = $this->deepSeekService->explainRoommateMatch([
            'tenant' => $this->profilePayload($match['tenant']),
            'candidate' => $this->profilePayload($match['candidate']),
            'compatibility' => [
                'compatibility_percent' => $match['compatibility']['compatibility_percent'],
                'highlights' => $match['compatibility']['highlights'],
                'conflicts' => $match['compatibility']['conflicts'],
            ],
        ]);

        return view('tenant.matches.show', $match);
    }

    private function renderMatchDetail(Request $request, User $candidate): View
    {
        return view('tenant.matches.show', $this->matchDetailData($request, $candidate));
    }

    private function candidateContext(User $tenant, User $candidate): string
    {
        if ($tenant->boarding_house_id && $tenant->boarding_house_id === $candidate->boarding_house_id) {
            return 'Same boarding house';
        }

        if ($candidate->boardingHouse?->name) {
            return 'Stays at '.$candidate->boardingHouse->name;
        }

        return 'Active tenant candidate';
    }

    private function resolveRequestState(User $tenant, User $candidate, $requests): array
    {
        $latest = $requests
            ->first(function (RoommateMatchRequest $request) use ($tenant, $candidate) {
                return ($request->sender_id === $tenant->id && $request->recipient_id === $candidate->id)
                    || ($request->sender_id === $candidate->id && $request->recipient_id === $tenant->id);
            });

        if (! $latest) {
            return [
                'status' => 'none',
                'direction' => null,
                'request' => null,
            ];
        }

        return [
            'status' => $latest->status,
            'direction' => $latest->sender_id === $tenant->id ? 'outgoing' : 'incoming',
            'request' => $latest,
        ];
    }

    private function matchDetailData(Request $request, User $candidate): array
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isTenant(), 403);

        $tenant->loadMissing('tenantMatchProfile', 'boardingHouse:id,name');
        $candidate->loadMissing('tenantMatchProfile', 'boardingHouse:id,name');

        abort_unless($candidate->isTenant(), 404);
        abort_unless($tenant->tenantMatchProfile?->completed_at, 403, 'Complete your match profile first.');
        abort_unless($candidate->tenantMatchProfile?->completed_at, 404);

        $compatibility = $this->compatibilityService->score($tenant, $candidate);
        $requests = RoommateMatchRequest::query()
            ->where(function ($query) use ($tenant, $candidate) {
                $query->where(function ($inner) use ($tenant, $candidate) {
                    $inner->where('sender_id', $tenant->id)
                        ->where('recipient_id', $candidate->id);
                })->orWhere(function ($inner) use ($tenant, $candidate) {
                    $inner->where('sender_id', $candidate->id)
                        ->where('recipient_id', $tenant->id);
                });
            })
            ->latest()
            ->get();

        return [
            'tenant' => $tenant,
            'candidate' => $candidate,
            'compatibility' => $compatibility,
            'context' => $this->candidateContext($tenant, $candidate),
            'requestState' => $this->resolveRequestState($tenant, $candidate, $requests),
            'aiExplanation' => null,
            'deepSeekConfigured' => $this->deepSeekService->isConfigured(),
        ];
    }

    private function profilePayload(User $user): array
    {
        $profile = $user->tenantMatchProfile;

        return [
            'name' => $user->name,
            'boarding_house' => $user->boardingHouse?->name,
            'budget_min' => $profile?->budget_min,
            'budget_max' => $profile?->budget_max,
            'gender_preference' => $profile?->gender_preference,
            'sleep_schedule' => $profile?->sleep_schedule,
            'study_habits' => $profile?->study_habits,
            'cleanliness_level' => $profile?->cleanliness_level,
            'noise_tolerance' => $profile?->noise_tolerance,
            'smoking_preference' => $profile?->smoking_preference,
            'drinking_preference' => $profile?->drinking_preference,
            'pets_preference' => $profile?->pets_preference,
            'internet_usage' => $profile?->internet_usage,
            'hobbies' => $profile?->hobbies,
            'additional_notes' => $profile?->additional_notes,
        ];
    }
}
