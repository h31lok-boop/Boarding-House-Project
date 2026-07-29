<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\BoardingHouseMatch;
use App\Models\RoommateMatchRequest;
use App\Models\User;
use App\Services\BoardingHouseRecommendationService;
use App\Services\CompatibilityService;
use App\Services\DeepSeekService;
use App\Services\LocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
        private readonly LocationService $locationService,
    ) {}

    public function index(Request $request): View
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isUser(), 403);

        $tenant->loadMissing('tenantMatchProfile', 'boardingHouse:id,name');

        $hasPreferences = $this->boardingHouseRecommendationService->hasPreferences($tenant);
        $profile = $tenant->tenantMatchProfile;
        $preferenceSummary = $this->boardingHouseRecommendationService->preferenceSummary($tenant);
        $preferredLocation = $preferenceSummary['preferred_location_label'] ?: null;
        $lifestyleText = $preferenceSummary['lifestyle_text'] ?: null;
        $houseFilters = $this->houseFilters($request);

        // House recommendations — always available once a tenant has any preferences
        $houseRecommendations = $hasPreferences
            ? $this->applyHouseFilters($this->boardingHouseRecommendationService->rank($tenant), $houseFilters)
            : collect();
        $houseRecommendations = $this->attachDeepSeekExplanations($tenant, $houseRecommendations);

        // Roommate matches — only if match profile is fully completed
        $matches = collect();
        $filteredMatches = collect();
        $filters = $this->matchFilters($request);

        if (Schema::hasTable('tenant_match_profiles') && $profile?->completed_at) {
            $candidates = User::query()
                ->with(['tenantMatchProfile', 'boardingHouse:id,name'])
                ->whereKeyNot($tenant->id)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereIn('role', ['user', 'tenant', 'student'])
                        ->orWhereHas('roles', fn ($rq) => $rq->whereIn('name', ['user', 'tenant', 'student']));
                })
                ->whereHas('tenantMatchProfile', fn ($q) => $q->whereNotNull('completed_at'))
                ->when(Schema::hasColumn('users', 'is_archived'), fn ($q) => $q->where('is_archived', false))
                ->get();

            $requests = Schema::hasTable('roommate_match_requests')
                ? RoommateMatchRequest::query()
                    ->where(fn ($q) => $q->where('sender_id', $tenant->id)->orWhere('recipient_id', $tenant->id))
                    ->get()
                : collect();

            $matches = $candidates
                ->map(fn (User $candidate) => [
                    'candidate' => $candidate,
                    'compatibility' => $this->compatibilityService->score($tenant, $candidate),
                    'context' => $this->candidateContext($tenant, $candidate),
                    'requestState' => $this->resolveRequestState($tenant, $candidate, $requests),
                ])
                ->sortByDesc(fn ($item) => $item['compatibility']['overall_score'])
                ->values();

            $filteredMatches = $matches
                ->filter(fn ($item) => $this->matchPassesFilters($item, $filters))
                ->values();
        }

        return view('user.recommendations', [
            'tenant' => $tenant,
            'hasPreferences' => $hasPreferences,
            'preferredLocation' => $preferredLocation,
            'lifestyleText' => $lifestyleText,
            'preferenceSummary' => $preferenceSummary,
            'houseRecommendations' => $houseRecommendations,
            'houseRecommendationCount' => $houseRecommendations->count(),
            'houseFilters' => $houseFilters,
            'favoriteHouseIds' => $this->favoriteHouseIds($tenant),
            'deepSeekConfigured' => $this->deepSeekService->isConfigured(),
            'matches' => $filteredMatches->take(20),
            'matchCount' => $filteredMatches->count(),
            'totalMatchCount' => $matches->count(),
            'referencePoint' => $this->referencePoint($request),
            'filters' => $filters,
            'filterOptions' => $this->filterOptions(),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isUser(), 403);

        if (! $this->boardingHouseRecommendationService->hasPreferences($tenant)) {
            return redirect()
                ->route('user.preferences.index')
                ->with('error', 'Complete your preferences before generating AI recommendations.');
        }

        $ranked = $this->boardingHouseRecommendationService->generateForUser($tenant);
        $count = $ranked->count();
        $deepSeekResult = $this->deepSeekService->explainBoardingHouseRecommendations(
            $this->boardingHouseAiPayload($tenant, $ranked->take(8))
        );

        if ($deepSeekResult['success'] ?? false) {
            $this->persistDeepSeekExplanations(
                $tenant,
                $deepSeekResult['explanations'] ?? [],
                (string) ($deepSeekResult['model'] ?? config('services.deepseek.model'))
            );
        }

        $redirectRoute = $request->input('redirect_to') === 'boarding_houses'
            ? ['user.boarding-houses.index', ['tab' => 'matchmaking']]
            : ['user.matchmaking.index', []];

        $redirect = redirect()
            ->route($redirectRoute[0], $redirectRoute[1])
            ->with('success', $count > 0
                ? "Generated {$count} ranked boarding house recommendations"
                    .(($deepSeekResult['success'] ?? false) ? ' with DeepSeek AI explanations.' : '.')
                : 'No compatible boarding houses are currently available.');

        if ($count > 0 && $this->deepSeekService->isConfigured() && ! ($deepSeekResult['success'] ?? false)) {
            $redirect->with('warning', 'The verified recommendation scores were generated, but DeepSeek explanations are temporarily unavailable.');
        }

        return $redirect;
    }

    public function show(Request $request, User $candidate): mixed
    {
        $tenant = $request->user();
        $tenant?->loadMissing('tenantMatchProfile');
        if (! $tenant?->tenantMatchProfile?->completed_at) {
            return redirect()->route('user.preferences.index')
                ->with('status', 'Please complete your match profile before viewing recommendations.');
        }

        return $this->renderMatchDetail($request, $candidate);
    }

    public function explain(Request $request, User $candidate): mixed
    {
        $tenant = $request->user();
        $tenant?->loadMissing('tenantMatchProfile');
        if (! $tenant?->tenantMatchProfile?->completed_at) {
            return redirect()->route('user.preferences.index')
                ->with('status', 'Please complete your match profile before viewing recommendations.');
        }

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

    private function houseFilters(Request $request): array
    {
        $sort = $this->allowedFilterValue($request->query('house_sort'), [
            'highest_match',
            'lowest_rent',
            'nearest_location',
        ]) ?? 'highest_match';

        $roomType = $this->allowedFilterValue($request->query('room_type'), [
            'any',
            'private',
            'shared',
            'bedspace',
            'studio',
        ]);
        $dsscArea = $this->allowedFilterValue($request->query('dssc_area'), [
            'near',
            'matti',
            'purok-3-matti',
            'mahayahay',
            'tres-de-mayo',
            'city-proper',
        ]);

        return [
            'house_sort' => $sort,
            'room_type' => $roomType === 'any' ? null : $roomType,
            'dssc_area' => $dsscArea,
            'min_score' => min(max((int) $request->query('house_min_score', 0), 0), 100),
            'available_now' => $request->query('available_now') === '1',
            'within_budget' => $request->query('within_budget') === '1',
        ];
    }

    private function applyHouseFilters($recommendations, array $filters)
    {
        $items = collect($recommendations);

        if (! empty($filters['min_score'])) {
            $items = $items->filter(function ($item) use ($filters): bool {
                $score = $item['recommendation']['recommendation_percent']
                    ?? $item['recommendation']['match_score']
                    ?? 0;

                return (int) round((float) $score) >= (int) $filters['min_score'];
            });
        }

        if (! empty($filters['available_now'])) {
            $items = $items->filter(fn ($item) => $this->houseAvailableSlots($item['house']) > 0);
        }

        if (! empty($filters['within_budget'])) {
            $items = $items->filter(fn ($item) => (float) ($item['recommendation']['scores']['budget'] ?? 0) >= 0.8);
        }

        if (! empty($filters['room_type'])) {
            $items = $items->filter(fn ($item) => $this->houseMatchesRoomType($item['house'], $filters['room_type']));
        }

        if (! empty($filters['dssc_area'])) {
            $items = $items->filter(function ($item) use ($filters): bool {
                $house = $item['house'];
                $distance = $item['recommendation']['distance_from_dssc']
                    ?? $this->locationService->boardingHouseDistance($house);

                if ($filters['dssc_area'] === 'near') {
                    return $house->is_near_dssc
                        || ($distance !== null && $distance <= 5)
                        || $this->locationService->matchesNearbyDsscAddress($house);
                }

                $area = match ($filters['dssc_area']) {
                    'matti' => 'matti',
                    'purok-3-matti' => 'purok 3',
                    'mahayahay' => 'mahayahay',
                    'tres-de-mayo' => 'tres de mayo',
                    'city-proper' => 'city proper',
                };
                $location = strtolower(implode(' ', array_filter([
                    $house->display_barangay,
                    $house->address,
                    $house->full_address,
                ])));

                return str_contains($location, $area)
                    || ($filters['dssc_area'] === 'city-proper' && str_contains($location, 'poblacion'));
            });
        }

        $items = match ($filters['house_sort']) {
            'lowest_rent' => $items->sortBy(fn ($item) => $item['recommendation']['price'] ?? INF),
            'nearest_location' => $items->sortBy(fn ($item) => $item['recommendation']['distance_from_dssc'] ?? INF),
            default => $items->sortByDesc(fn ($item) => $item['recommendation']['overall_score'] ?? 0),
        };

        return $items->values();
    }

    private function attachDeepSeekExplanations(User $tenant, Collection $recommendations): Collection
    {
        $houseIds = $recommendations
            ->pluck('house.id')
            ->filter()
            ->values();

        if ($houseIds->isEmpty() || ! Schema::hasTable('boarding_house_matches')) {
            return $recommendations;
        }

        $matches = BoardingHouseMatch::query()
            ->where('user_id', $tenant->id)
            ->whereIn('boarding_house_id', $houseIds)
            ->whereNotNull('ai_explanation')
            ->get()
            ->keyBy('boarding_house_id');

        return $recommendations->map(function (array $item) use ($matches): array {
            $match = $matches->get($item['house']->id);

            if ($match) {
                $item['recommendation']['deepseek_explanation'] = $match->ai_explanation;
                $item['recommendation']['deepseek_model'] = $match->ai_model;
                $item['recommendation']['deepseek_generated_at'] = $match->ai_generated_at;
            }

            return $item;
        });
    }

    private function boardingHouseAiPayload(User $tenant, Collection $ranked): array
    {
        return [
            'preferences' => $this->boardingHouseRecommendationService->preferenceSummary($tenant),
            'recommendations' => $ranked->map(function (array $item): array {
                $house = $item['house'];
                $recommendation = $item['recommendation'];
                $rates = $house->roomCategories->pluck('monthly_rate')
                    ->merge($house->rooms->pluck('price'))
                    ->filter(fn ($price) => is_numeric($price) && (float) $price > 0)
                    ->map(fn ($price) => (float) $price);

                return [
                    'id' => $house->id,
                    'name' => $house->name,
                    'location' => collect([
                        $house->display_barangay,
                        $house->city?->city_name,
                    ])->filter()->implode(', ') ?: ($house->full_address ?: $house->address),
                    'distance_from_dssc_km' => $recommendation['distance_from_dssc'] ?? null,
                    'monthly_rent_min' => $rates->min() ?? $recommendation['price'],
                    'monthly_rent_max' => $rates->max() ?? $recommendation['price'],
                    'room_types' => $house->roomCategories->pluck('name')->values()->all(),
                    'amenities' => $house->amenities->pluck('name')->values()->all(),
                    'available_slots' => max(
                        (int) ($house->available_rooms ?? 0),
                        (int) ($house->room_categories_available_rooms_sum ?? 0),
                        (int) $house->rooms->sum('available_slots')
                    ),
                    'match_score' => $recommendation['recommendation_percent'],
                    'verified_reasons' => $recommendation['reasons'],
                    'warnings' => $recommendation['warnings'],
                ];
            })->values()->all(),
        ];
    }

    private function persistDeepSeekExplanations(User $tenant, array $explanations, string $model): void
    {
        foreach ($explanations as $boardingHouseId => $explanation) {
            if (! is_numeric($boardingHouseId) || ! is_string($explanation) || trim($explanation) === '') {
                continue;
            }

            BoardingHouseMatch::query()
                ->where('user_id', $tenant->id)
                ->where('boarding_house_id', (int) $boardingHouseId)
                ->update([
                    'ai_explanation' => trim($explanation),
                    'ai_model' => $model,
                    'ai_generated_at' => now(),
                ]);
        }
    }

    private function houseAvailableSlots(BoardingHouse $house): int
    {
        return (int) collect([
            $house->available_rooms ?? 0,
            $house->available_rooms_count ?? 0,
            $house->room_categories_available_rooms_sum ?? 0,
            $house->relationLoaded('rooms') ? $house->rooms->sum('available_slots') : 0,
            $house->relationLoaded('roomCategories') ? $house->roomCategories->sum('available_rooms') : 0,
        ])->max();
    }

    private function houseMatchesRoomType(BoardingHouse $house, string $roomType): bool
    {
        $needle = match ($roomType) {
            'private' => ['private', 'single'],
            'shared' => ['shared'],
            'bedspace' => ['bed', 'bedspace'],
            'studio' => ['studio', 'apartment'],
            default => [],
        };

        if ($needle === []) {
            return true;
        }

        $text = strtolower(trim(implode(' ', array_filter([
            $house->property_type ?? null,
            $house->roomCategories?->pluck('name')->implode(' '),
        ]))));

        foreach ($needle as $term) {
            if (str_contains($text, $term)) {
                return true;
            }
        }

        return false;
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

    private function favoriteHouseIds(User $tenant): array
    {
        if (! Schema::hasTable('favorites')) {
            return [];
        }

        return DB::table('favorites')
            ->where('user_id', $tenant->id)
            ->pluck('boarding_house_id')
            ->map(fn ($id) => (int) $id)
            ->all();
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
