<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\RoommateMatchRequest;
use App\Models\User;
use App\Services\BoardingHouseRecommendationService;
use App\Services\CompatibilityService;
use App\Services\DeepSeekService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class RecommendationController extends Controller
{
    private const DEFAULT_LAT = 6.7440000;

    private const DEFAULT_LNG = 125.3550000;

    public function __construct(
        private readonly CompatibilityService $compatibilityService,
        private readonly BoardingHouseRecommendationService $boardingHouseRecommendationService,
        private readonly DeepSeekService $deepSeekService,
    ) {}

    public function index(Request $request): View
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isUser(), 403);

        if (! Schema::hasTable('tenant_match_profiles')) {
            return view('user.recommendations', [
                'tenant' => $tenant,
                'matches' => collect(),
                'matchCount' => 0,
                'totalMatchCount' => 0,
                'houseRecommendations' => collect(),
                'houseRecommendationCount' => 0,
                'referencePoint' => $this->referencePoint($request),
                'filters' => $this->matchFilters($request),
                'filterOptions' => $this->filterOptions(),
            ]);
        }

        $tenant->loadMissing('tenantMatchProfile', 'boardingHouse:id,name');

        abort_unless($tenant->tenantMatchProfile?->completed_at, 403, 'Complete your match profile first.');

        $filters = $this->matchFilters($request);

        $candidates = User::query()
            ->with(['tenantMatchProfile', 'boardingHouse:id,name'])
            ->whereKeyNot($tenant->id)
            ->where('is_archived', false)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereIn('role', ['user', 'tenant', 'student'])
                    ->orWhereHas('roles', function ($roleQuery) {
                        $roleQuery->whereIn('name', ['user', 'tenant', 'student']);
                    });
            })
            ->whereHas('tenantMatchProfile', fn ($query) => $query->whereNotNull('completed_at'))
            ->get();

        $requests = Schema::hasTable('roommate_match_requests')
            ? RoommateMatchRequest::query()
                ->where(function ($query) use ($tenant) {
                    $query->where('sender_id', $tenant->id)
                        ->orWhere('recipient_id', $tenant->id);
                })
                ->get()
            : collect();

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

        $filteredMatches = $matches
            ->filter(fn (array $item) => $this->matchPassesFilters($item, $filters))
            ->values();

        $referencePoint = $this->referencePoint($request);
        $houseRecommendations = $this->boardingHouseRecommendationService
            ->rank(
                $tenant,
                $this->boardingHouseCandidates(),
                $referencePoint['lat'],
                $referencePoint['lng']
            )
            ->take(6);

        return view('user.recommendations', [
            'tenant' => $tenant,
            'matches' => $filteredMatches->take(20),
            'matchCount' => $filteredMatches->count(),
            'totalMatchCount' => $matches->count(),
            'houseRecommendations' => $houseRecommendations,
            'houseRecommendationCount' => $houseRecommendations->count(),
            'referencePoint' => $referencePoint,
            'filters' => $filters,
            'filterOptions' => $this->filterOptions(),
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

        return view('user.recommendation-show', $match);
    }

    private function renderMatchDetail(Request $request, User $candidate): View
    {
        return view('user.recommendation-show', $this->matchDetailData($request, $candidate));
    }

    private function candidateContext(User $tenant, User $candidate): string
    {
        if ($tenant->boarding_house_id && $tenant->boarding_house_id === $candidate->boarding_house_id) {
            return 'Same boarding house';
        }

        if ($candidate->boardingHouse?->name) {
            return 'Stays at '.$candidate->boardingHouse->name;
        }

        return 'Active user candidate';
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

    private function matchFilters(Request $request): array
    {
        $boardingHouseId = (int) $request->query('boarding_house_id', 0);

        return [
            'min_score' => min(max((int) $request->query('min_score', 0), 0), 100),
            'budget_min' => $this->normalizePrice($request->query('budget_min')),
            'budget_max' => $this->normalizePrice($request->query('budget_max')),
            'boarding_house_id' => $boardingHouseId > 0 ? $boardingHouseId : null,
            'gender_preference' => $this->allowedFilterValue($request->query('gender_preference'), [
                'male',
                'female',
                'mixed',
                'no_preference',
            ]),
            'sleep_schedule' => $this->allowedFilterValue($request->query('sleep_schedule'), [
                'early_bird',
                'balanced',
                'night_owl',
            ]),
            'study_habits' => $this->allowedFilterValue($request->query('study_habits'), [
                'quiet_focus',
                'flexible',
                'group_study',
            ]),
        ];
    }

    private function matchPassesFilters(array $item, array $filters): bool
    {
        $candidate = $item['candidate'];
        $profile = $candidate->tenantMatchProfile;

        if ((int) $filters['min_score'] > 0 && (int) $item['compatibility']['compatibility_percent'] < (int) $filters['min_score']) {
            return false;
        }

        if (($filters['budget_min'] !== null || $filters['budget_max'] !== null) && ! $this->budgetOverlaps($profile, $filters['budget_min'], $filters['budget_max'])) {
            return false;
        }

        if ($filters['boarding_house_id'] !== null && (int) $candidate->boarding_house_id !== (int) $filters['boarding_house_id']) {
            return false;
        }

        foreach (['gender_preference', 'sleep_schedule', 'study_habits'] as $field) {
            if ($filters[$field] !== null && $profile?->{$field} !== $filters[$field]) {
                return false;
            }
        }

        return true;
    }

    private function budgetOverlaps(?object $profile, ?float $minimum, ?float $maximum): bool
    {
        if (! $profile) {
            return false;
        }

        $candidateMin = $this->normalizePrice($profile->budget_min) ?? 0.0;
        $candidateMax = $this->normalizePrice($profile->budget_max) ?? INF;
        $filterMin = $minimum ?? 0.0;
        $filterMax = $maximum ?? INF;

        return $candidateMax >= $filterMin && $candidateMin <= $filterMax;
    }

    private function filterOptions(): array
    {
        return [
            'boardingHouses' => BoardingHouse::query()
                ->whereNotNull('name')
                ->orderBy('name')
                ->get(['id', 'name']),
            'gender_preference' => [
                'male' => 'Male only',
                'female' => 'Female only',
                'mixed' => 'Mixed is okay',
                'no_preference' => 'No preference',
            ],
            'sleep_schedule' => [
                'early_bird' => 'Early bird',
                'balanced' => 'Balanced routine',
                'night_owl' => 'Night owl',
            ],
            'study_habits' => [
                'quiet_focus' => 'Quiet focus',
                'flexible' => 'Flexible',
                'group_study' => 'Group study',
            ],
        ];
    }

    private function boardingHouseCandidates()
    {
        return BoardingHouse::query()
            ->with([
                'amenities:id,name',
                'rooms:id,boarding_house_id,room_no,price,status,available_slots',
                'roomCategories:id,boarding_house_id,name,monthly_rate,available_rooms,is_available',
                'tenants:id,name,boarding_house_id,is_active,role',
                'tenants.tenantMatchProfile',
            ])
            ->withCount([
                'rooms',
                'rooms as available_rooms_count' => fn ($query) => $query->available(),
                'roomCategories',
                'reviews',
            ])
            ->withSum('roomCategories as room_categories_available_rooms_sum', 'available_rooms')
            ->withAvg('reviews', 'rating')
            ->when(Schema::hasColumn('boarding_houses', 'is_active'), fn ($query) => $query->where('is_active', true))
            ->when(
                Schema::hasColumn('boarding_houses', 'approval_status') || Schema::hasColumn('boarding_houses', 'status'),
                function ($query) {
                    $query->where(function ($statusQuery) {
                        if (Schema::hasColumn('boarding_houses', 'approval_status')) {
                            $statusQuery->where('approval_status', 'approved');
                        }

                        if (Schema::hasColumn('boarding_houses', 'status')) {
                            $method = Schema::hasColumn('boarding_houses', 'approval_status') ? 'orWhere' : 'where';
                            $statusQuery->{$method}('status', 'approved');
                        }
                    });
                }
            )
            ->orderBy('name')
            ->limit(100)
            ->get();
    }

    private function referencePoint(Request $request): array
    {
        return [
            'lat' => $this->normalizeCoordinate($request->query('lat'), -90, 90) ?? self::DEFAULT_LAT,
            'lng' => $this->normalizeCoordinate($request->query('lng'), -180, 180) ?? self::DEFAULT_LNG,
        ];
    }

    private function normalizeCoordinate(mixed $value, float $min, float $max): ?float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        $coordinate = (float) $value;

        return $coordinate >= $min && $coordinate <= $max ? $coordinate : null;
    }

    private function normalizePrice(mixed $value): ?float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        return $number < 0 ? null : $number;
    }

    private function allowedFilterValue(mixed $value, array $allowed): ?string
    {
        $value = is_string($value) ? $value : null;

        return in_array($value, $allowed, true) ? $value : null;
    }

    private function matchDetailData(Request $request, User $candidate): array
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isUser(), 403);
        abort_unless(Schema::hasTable('tenant_match_profiles'), 404);

        $tenant->loadMissing('tenantMatchProfile', 'boardingHouse:id,name');
        $candidate->loadMissing('tenantMatchProfile', 'boardingHouse:id,name');

        abort_unless($candidate->isUser(), 404);
        abort_unless($tenant->tenantMatchProfile?->completed_at, 403, 'Complete your match profile first.');
        abort_unless($candidate->tenantMatchProfile?->completed_at, 404);

        $compatibility = $this->compatibilityService->score($tenant, $candidate);
        $requests = Schema::hasTable('roommate_match_requests')
            ? RoommateMatchRequest::query()
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
                ->get()
            : collect();

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
