<?php

namespace App\Services;

use App\Models\Amenity;
use App\Models\BoardingHouse;
use App\Models\BoardingHouseMatch;
use App\Models\TenantMatchProfile;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BoardingHouseRecommendationService
{
    private const DEFAULT_LAT = 6.7587400;

    private const DEFAULT_LNG = 125.3090900;

    private const WEIGHTS = [
        'budget' => 0.25,
        'location' => 0.20,
        'room_type' => 0.15,
        'amenities' => 0.15,
        'availability' => 0.10,
        'lifestyle' => 0.10,
        'other' => 0.05,
    ];

    public function __construct(
        private readonly LocationService $locationService,
    ) {}

    public function rank(
        User $tenant,
        ?Collection $houses = null,
        ?float $referenceLat = null,
        ?float $referenceLng = null,
        bool $persist = true
    ): Collection {
        if (! $this->hasPreferences($tenant)) {
            return collect();
        }

        $houses ??= $this->candidates();

        return $houses
            ->map(function (BoardingHouse $house) use ($tenant, $referenceLat, $referenceLng, $persist) {
                return [
                    'house' => $house,
                    'image_url' => $this->imageUrl($house),
                    'recommendation' => $this->score($tenant, $house, $referenceLat, $referenceLng, $persist),
                ];
            })
            ->sortByDesc(fn (array $item) => $item['recommendation']['overall_score'])
            ->values();
    }

    public function generateForUser(User $tenant, ?Collection $houses = null): Collection
    {
        return $this->rank($tenant, $houses, self::DEFAULT_LAT, self::DEFAULT_LNG, true);
    }

    /**
     * Separate genuine preference matches from practical fallback suggestions.
     * Suggestions are shown only when no listing satisfies the tenant's core
     * preferences and are ranked by published ratings, features, and availability.
     */
    public function classifyRecommendations(User $tenant, Collection $ranked): array
    {
        $this->loadTenantPreferences($tenant);
        $preference = $this->preferencePayload($tenant);

        $matches = $ranked
            ->filter(fn (array $item): bool => $this->isPreferenceMatch(
                $preference,
                (array) ($item['recommendation'] ?? [])
            ))
            ->map(function (array $item): array {
                $item['recommendation']['result_type'] = 'match';

                return $item;
            })
            ->values();

        $suggestions = $matches->isEmpty()
            ? $ranked
                ->map(function (array $item): array {
                    $house = $item['house'];
                    $item['recommendation']['result_type'] = 'suggestion';
                    $item['recommendation']['suggestion_score'] = $this->suggestionQualityScore($house);
                    $item['recommendation']['suggestion_reasons'] = $this->suggestionReasons($house);

                    return $item;
                })
                ->sortByDesc(fn (array $item): float => (float) data_get($item, 'recommendation.suggestion_score', 0))
                ->values()
            : collect();

        return [
            'matches' => $matches,
            'suggestions' => $suggestions,
            'scanned_count' => $ranked->count(),
            'match_threshold' => (int) config('matchmaking.boarding_house_match_threshold', 70),
        ];
    }

    public function score(
        User $tenant,
        BoardingHouse $house,
        ?float $referenceLat = null,
        ?float $referenceLng = null,
        bool $persist = false
    ): array {
        $this->loadTenantPreferences($tenant);
        $this->loadHouseRecommendationData($house);

        $preference = $this->preferencePayload($tenant);

        if (! $this->payloadHasPreferences($preference)) {
            return [
                'overall_score' => 0.0,
                'recommendation_percent' => 0,
                'match_label' => 'No Preferences',
                'ai_reason' => 'Complete your preferences to unlock AI-powered recommendations.',
                'breakdown' => [],
                'scores' => [],
                'reasons' => [],
                'warnings' => ['Complete your preferences to get personalized boarding house recommendations.'],
                'price' => $this->housePrice($house),
                'distance_from_dssc' => $this->distanceFromDssc($house),
                'distance_from_dssc_label' => $this->locationService->distanceLabel($this->distanceFromDssc($house)),
            ];
        }

        $scores = [
            'budget' => $this->scoreBudget($preference, $house),
            'location' => $this->scoreLocation($preference, $house),
            'room_type' => $this->scoreRoomType($preference, $house),
            'amenities' => $this->scoreAmenities($preference, $house),
            'availability' => $this->scoreAvailability($house),
            'lifestyle' => $this->scoreLifestyle($preference, $house),
            'other' => $this->scoreOtherPreferences($preference, $house, $referenceLat, $referenceLng),
        ];

        $overall = 0.0;
        $breakdown = [];

        foreach (self::WEIGHTS as $criterion => $weight) {
            $score = (float) ($scores[$criterion] ?? 0.0);
            $weighted = $score * $weight;
            $overall += $weighted;

            $breakdown[$criterion] = [
                'score' => round($score, 4),
                'weighted_score' => round($weighted, 4),
                'weight' => $weight,
                'label' => $this->criterionLabel($criterion),
            ];
        }

        $percent = (int) round(max(0.0, min(1.0, $overall)) * 100);
        $reasons = $this->matchReasons($preference, $house, $scores);
        $warnings = $this->matchWarnings($scores);

        $result = [
            'overall_score' => round($overall, 4),
            'recommendation_percent' => $percent,
            'match_score' => $percent,
            'match_label' => $this->matchLabel($percent),
            'ai_reason' => $this->buildAiReason($reasons, $warnings),
            'breakdown' => $breakdown,
            'scores' => array_map(fn ($score) => round((float) $score, 4), $scores),
            'reasons' => $reasons,
            'match_reasons' => $reasons,
            'warnings' => $warnings,
            'price' => $this->housePrice($house),
            'distance_from_dssc' => $this->distanceFromDssc($house),
            'distance_from_dssc_label' => $this->locationService->distanceLabel($this->distanceFromDssc($house)),
        ];

        if ($persist) {
            $this->persistMatch($tenant, $house, $result);
        }

        return $result;
    }

    private function isPreferenceMatch(array $preference, array $recommendation): bool
    {
        $percent = (int) ($recommendation['recommendation_percent'] ?? 0);
        $scores = (array) ($recommendation['scores'] ?? []);

        if ($percent < (int) config('matchmaking.boarding_house_match_threshold', 70)) {
            return false;
        }

        $coreChecks = [];

        if ($preference['preferred_rental_budget'] !== null
            || $preference['budget_min'] !== null
            || $preference['budget_max'] !== null) {
            $coreChecks[] = (float) ($scores['budget'] ?? 0) >= 0.65;
        }

        if ($preference['preferred_locations'] !== [] || filled($preference['preferred_landmark'])) {
            $coreChecks[] = (float) ($scores['location'] ?? 0) >= 0.65;
        }

        if (filled($preference['room_type']) && $preference['room_type'] !== 'any') {
            $coreChecks[] = (float) ($scores['room_type'] ?? 0) >= 0.65;
        }

        if ($preference['amenities'] !== []) {
            $coreChecks[] = (float) ($scores['amenities'] ?? 0) >= 0.50;
        }

        return ! in_array(false, $coreChecks, true)
            && (float) ($scores['availability'] ?? 0) > 0;
    }

    private function suggestionQualityScore(BoardingHouse $house): float
    {
        $rating = max(0.0, min(5.0, (float) ($house->reviews_avg_rating ?? 0)));
        $reviewConfidence = min(1.0, ((int) ($house->reviews_count ?? 0)) / 5);
        $ratingScore = ($rating / 5) * (0.65 + (0.35 * $reviewConfidence));

        $amenityCount = $house->relationLoaded('amenities') ? $house->amenities->count() : 0;
        $roomTypeCount = $house->relationLoaded('roomCategories') ? $house->roomCategories->count() : 0;
        $featureScore = min(1.0, (($amenityCount * 1.5) + $roomTypeCount) / 10);
        $availabilityScore = $this->scoreAvailability($house);

        return round(($ratingScore * 0.55) + ($featureScore * 0.30) + ($availabilityScore * 0.15), 4);
    }

    private function suggestionReasons(BoardingHouse $house): array
    {
        $reasons = [];
        $rating = (float) ($house->reviews_avg_rating ?? 0);
        $reviewCount = (int) ($house->reviews_count ?? 0);
        $amenities = $house->relationLoaded('amenities')
            ? $house->amenities->pluck('name')->filter()->take(3)->values()
            : collect();

        if ($rating > 0 && $reviewCount > 0) {
            $reasons[] = number_format($rating, 1).'/5 rating from '.$reviewCount.' published '
                .Str::plural('review', $reviewCount).'.';
        }

        if ($amenities->isNotEmpty()) {
            $reasons[] = 'Features include '.$amenities->implode(', ').'.';
        }

        if ($this->scoreAvailability($house) > 0) {
            $reasons[] = 'Currently records available rooms or slots.';
        }

        return $reasons ?: ['Approved active listing with details available for review.'];
    }

    public function candidates(int $limit = 100): Collection
    {
        if (! Schema::hasTable('boarding_houses')) {
            return collect();
        }

        $query = BoardingHouse::query()
            ->with([
                'amenities:id,name',
                'rooms:id,boarding_house_id,room_no,room_number,price,status,available_slots,capacity',
                'roomCategories:id,boarding_house_id,name,monthly_rate,available_rooms,is_available',
                'images:id,boarding_house_id,image_path,is_primary,sort_order',
                'city:id,city_name',
                'barangayReference:id,barangay_name',
                'province:id,province_name',
            ])
            ->withCount([
                'rooms',
                'rooms as available_rooms_count' => fn ($query) => $query->available(),
                'roomCategories',
                'reviews',
            ])
            ->withSum('roomCategories as room_categories_available_rooms_sum', 'available_rooms')
            ->withAvg('reviews', 'rating');

        if (Schema::hasColumn('boarding_houses', 'is_active')) {
            $query->where('is_active', true);
        }

        if (Schema::hasColumn('boarding_houses', 'approval_status')) {
            $query->where('approval_status', 'approved');
        } elseif (Schema::hasColumn('boarding_houses', 'status')) {
            $query->where('status', 'approved');
        }

        $query->where(function ($availableQuery) {
            $availableQuery->where('available_rooms', '>', 0)
                ->orWhereHas('rooms', fn ($roomQuery) => $roomQuery->available())
                ->orWhereHas('roomCategories', fn ($categoryQuery) => $categoryQuery->where('available_rooms', '>', 0));
        });

        return $query->orderBy('name')->limit($limit)->get();
    }

    public function hasPreferences(User $tenant): bool
    {
        $this->loadTenantPreferences($tenant);

        return $this->payloadHasPreferences($this->preferencePayload($tenant));
    }

    public function preferenceSummary(User $tenant): array
    {
        $this->loadTenantPreferences($tenant);
        $payload = $this->preferencePayload($tenant);

        return [
            'budget_min' => $payload['budget_min'],
            'budget_max' => $payload['budget_max'],
            'preferred_rental_budget' => $payload['preferred_rental_budget'],
            'preferred_locations' => $payload['preferred_locations'],
            'preferred_location_label' => collect($payload['preferred_locations'])->filter()->implode(', '),
            'preferred_landmark' => $payload['preferred_landmark'],
            'dssc_selected' => $this->preferenceTargetsDssc($payload),
            'distance_from_school' => $payload['distance_from_school'],
            'lifestyle_text' => $payload['lifestyle_notes'],
            'room_type' => $payload['room_type'],
            'study_habits' => $payload['study_habits'],
            'sleeping_schedule' => $payload['sleeping_schedule'],
            'cleanliness_level' => $payload['cleanliness_level'],
            'amenities' => $payload['amenities'],
            'safety_preferences' => $payload['safety_preferences'],
            'gender_preference' => $payload['gender_preference'],
            'smoking_preference' => $payload['smoking_preference'],
            'drinking_preference' => $payload['drinking_preference'],
            'pets_preference' => $payload['pets_preference'],
            'internet_usage' => $payload['internet_usage'],
            'hobbies' => $payload['hobbies'],
            'ai_ready' => $this->payloadIsAiReady($payload),
            'ai_completion_percentage' => $this->aiCompletionPercentage($payload),
        ];
    }

    public function extractPreferredLocation(string $notes): ?string
    {
        if (preg_match('/Preferred Location:\s*(.+?)(?:\n|$)/i', $notes, $m)) {
            return trim($m[1]) ?: null;
        }

        return null;
    }

    public function extractLifestyleText(string $notes): ?string
    {
        $text = preg_replace('/^Preferred Location:.*?(\n|$)/im', '', $notes);

        return trim((string) $text) ?: null;
    }

    public function housePrice(BoardingHouse $house): ?float
    {
        $prices = collect([
            $this->toFloat($house->price ?? null),
            $this->toFloat($house->monthly_payment ?? null),
        ]);

        if ($house->relationLoaded('rooms')) {
            $prices = $prices->merge($house->rooms->pluck('price')->map(fn ($price) => $this->toFloat($price)));
        }

        if ($house->relationLoaded('roomCategories')) {
            $prices = $prices->merge($house->roomCategories->pluck('monthly_rate')->map(fn ($price) => $this->toFloat($price)));
        }

        return $prices->filter(fn ($price) => $price !== null && $price > 0)->min();
    }

    public function distanceFromDssc(BoardingHouse $house): ?float
    {
        return $this->locationService->boardingHouseDistance($house);
    }

    public function imageUrl(BoardingHouse $house): ?string
    {
        $path = null;

        if ($house->relationLoaded('images') && $house->images->isNotEmpty()) {
            $image = $house->images->firstWhere('is_primary', true)
                ?? $house->images->sortBy('sort_order')->first()
                ?? $house->images->first();
            $path = $image?->image_path;
        }

        if (! $path) {
            foreach (['featured_image', 'exterior_image', 'room_image'] as $column) {
                if (! empty($house->{$column})) {
                    $path = $house->{$column};
                    break;
                }
            }
        }

        if (! $path) {
            return asset('images/boarding-house-placeholder.svg');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::url($path);
    }

    public function matchLabel(int $percent): string
    {
        if ($percent >= 80) {
            return 'High Match';
        }

        return $percent >= 60 ? 'Medium Match' : 'Low Match';
    }

    private function loadTenantPreferences(User $tenant): void
    {
        $relations = [];

        if (Schema::hasTable('user_preferences')) {
            $relations[] = 'preference';
        }

        if (Schema::hasTable('tenant_match_profiles')) {
            $relations[] = 'tenantMatchProfile';
        }

        if ($relations !== []) {
            $tenant->loadMissing($relations);
        }
    }

    private function loadHouseRecommendationData(BoardingHouse $house): void
    {
        $house->loadMissing([
            'amenities:id,name',
            'rooms:id,boarding_house_id,room_no,room_number,price,status,available_slots,capacity',
            'roomCategories:id,boarding_house_id,name,monthly_rate,available_rooms,is_available',
            'images:id,boarding_house_id,image_path,is_primary,sort_order',
            'city:id,city_name',
            'barangayReference:id,barangay_name',
            'province:id,province_name',
        ]);
    }

    private function preferencePayload(User $tenant): array
    {
        $preference = Schema::hasTable('user_preferences') ? $tenant->preference : null;
        $profile = Schema::hasTable('tenant_match_profiles') ? $tenant->tenantMatchProfile : null;

        if ($preference instanceof UserPreference) {
            return [
                'preferred_rental_budget' => $this->toFloat($preference->preferred_rental_budget),
                'budget_min' => $this->toFloat($preference->preferred_rental_budget_min),
                'budget_max' => $this->toFloat($preference->preferred_rental_budget_max)
                    ?? $this->toFloat($preference->preferred_rental_budget),
                'preferred_locations' => $this->arrayValues($preference->preferred_locations),
                'preferred_landmark' => $preference->preferred_landmark,
                'distance_from_school' => $this->toFloat($preference->distance_from_school),
                'room_type' => $preference->room_type,
                'study_habits' => $preference->study_habits,
                'sleeping_schedule' => $preference->sleeping_schedule,
                'cleanliness_level' => $preference->cleanliness_level,
                'noise_tolerance' => $preference->noise_tolerance,
                'safety_preferences' => $this->arrayValues($preference->safety_preferences),
                'amenities' => $this->arrayValues($preference->amenities),
                'lifestyle_notes' => trim((string) $preference->lifestyle_notes),
                'gender_preference' => $profile?->gender_preference,
                'smoking_preference' => $profile?->smoking_preference,
                'drinking_preference' => $profile?->drinking_preference,
                'pets_preference' => $profile?->pets_preference,
                'internet_usage' => $profile?->internet_usage,
                'hobbies' => $this->arrayValues($profile?->hobbies),
            ];
        }

        if (! $profile instanceof TenantMatchProfile) {
            return $this->emptyPreferencePayload();
        }

        $notes = (string) ($profile->additional_notes ?? '');
        $amenities = $this->amenityNamesFromIds($profile->preferred_amenity_ids ?? []);
        $location = $this->extractPreferredLocation($notes);

        return [
            'preferred_rental_budget' => $this->toFloat($profile->budget_max) ?? $this->toFloat($profile->budget_min),
            'budget_min' => $this->toFloat($profile->budget_min),
            'budget_max' => $this->toFloat($profile->budget_max),
            'preferred_locations' => $location ? [$location] : [],
            'preferred_landmark' => $this->extractPreferredLandmark($notes),
            'distance_from_school' => null,
            'room_type' => null,
            'study_habits' => $profile->study_habits,
            'sleeping_schedule' => $profile->sleep_schedule,
            'cleanliness_level' => $profile->cleanliness_level,
            'noise_tolerance' => $profile->noise_tolerance,
            'safety_preferences' => array_values(array_filter([
                $profile->smoking_preference === 'non_smoker_only' ? 'no smoking' : null,
            ])),
            'amenities' => $amenities,
            'lifestyle_notes' => $this->extractLifestyleText($notes) ?: '',
            'gender_preference' => $profile->gender_preference,
            'smoking_preference' => $profile->smoking_preference,
            'drinking_preference' => $profile->drinking_preference,
            'pets_preference' => $profile->pets_preference,
            'internet_usage' => $profile->internet_usage,
            'hobbies' => $this->arrayValues($profile->hobbies),
        ];
    }

    private function emptyPreferencePayload(): array
    {
        return [
            'preferred_rental_budget' => null,
            'budget_min' => null,
            'budget_max' => null,
            'preferred_locations' => [],
            'preferred_landmark' => null,
            'distance_from_school' => null,
            'room_type' => null,
            'study_habits' => null,
            'sleeping_schedule' => null,
            'cleanliness_level' => null,
            'noise_tolerance' => null,
            'safety_preferences' => [],
            'amenities' => [],
            'lifestyle_notes' => '',
            'gender_preference' => null,
            'smoking_preference' => null,
            'drinking_preference' => null,
            'pets_preference' => null,
            'internet_usage' => null,
            'hobbies' => [],
        ];
    }

    private function payloadHasPreferences(array $payload): bool
    {
        return $payload['preferred_rental_budget'] !== null
            || $payload['budget_min'] !== null
            || $payload['budget_max'] !== null
            || $payload['preferred_locations'] !== []
            || $payload['preferred_landmark']
            || $payload['room_type']
            || $payload['amenities'] !== []
            || $payload['safety_preferences'] !== []
            || trim((string) $payload['lifestyle_notes']) !== ''
            || $payload['study_habits']
            || $payload['sleeping_schedule']
            || $payload['cleanliness_level'] !== null
            || $payload['noise_tolerance'] !== null;
    }

    private function scoreBudget(array $preference, BoardingHouse $house): float
    {
        $price = $this->housePrice($house);
        $min = $preference['budget_min'];
        $max = $preference['budget_max'];

        if ($price === null || ($min === null && $max === null)) {
            return 0.5;
        }

        if ($min !== null && $max !== null) {
            if ($price >= $min && $price <= $max) {
                return 1.0;
            }

            if ($price < $min) {
                return 0.85;
            }

            $range = max($max - $min, 1000.0);

            return max(0.0, 1.0 - (($price - $max) / ($range * 1.5)));
        }

        if ($max !== null) {
            return $price <= $max ? 1.0 : max(0.0, 1.0 - (($price - $max) / max($max, 1000.0)));
        }

        return $price >= $min ? 1.0 : max(0.0, $price / max($min, 1.0));
    }

    private function scoreLocation(array $preference, BoardingHouse $house): float
    {
        $locations = collect($preference['preferred_locations'])
            ->map(fn ($location) => $this->normalizeText($location))
            ->filter(fn ($location) => strlen($location) >= 2)
            ->values();

        if ($locations->isEmpty() && ! $this->preferenceTargetsDssc($preference)) {
            return 0.5;
        }

        $houseText = $this->normalizeText(implode(' ', array_filter([
            $house->display_barangay,
            $house->city?->city_name,
            $house->province?->province_name,
            $house->nearby_landmark,
            $house->full_address,
            $house->address,
        ])));

        if ($houseText === '') {
            return 0.3;
        }

        $best = 0.0;
        $genericDsscSelection = $locations->contains(
            fn (string $location) => str_contains($location, 'dssc')
                || str_contains($location, 'all nearby dssc')
        ) || str_contains($this->normalizeText($preference['preferred_landmark']), 'dssc');
        $specificNearbySelection = $locations->contains(
            fn (string $location) => collect(config('dssc.areas', []))
                ->map(fn ($area) => $this->normalizeText($area))
                ->contains($location)
        );

        foreach ($locations as $location) {
            if (str_contains($location, 'dssc') || str_contains($location, 'all nearby dssc')) {
                continue;
            }

            if (str_contains($houseText, $location)) {
                $best = max($best, 1.0);

                continue;
            }

            $words = collect(preg_split('/\s+/', $location) ?: [])
                ->filter(fn ($word) => strlen($word) >= 3)
                ->values();

            if ($words->isEmpty()) {
                continue;
            }

            $matched = $words->filter(fn ($word) => str_contains($houseText, $word))->count();
            $best = max($best, $matched > 0 ? 0.4 + (($matched / $words->count()) * 0.4) : 0.0);
        }

        if ($this->preferenceTargetsDssc($preference)) {
            $dsscScore = $this->dsscLocationScore($house, $houseText);
            $best = max($best, $genericDsscSelection || ! $specificNearbySelection
                ? $dsscScore
                : $dsscScore * 0.85);
        }

        return $best;
    }

    private function scoreRoomType(array $preference, BoardingHouse $house): float
    {
        $preferred = $this->normalizeRoomType($preference['room_type']);

        if (! $preferred || $preferred === 'any') {
            return 0.5;
        }

        $houseTypes = $this->houseRoomTypes($house);

        if ($houseTypes->isEmpty()) {
            return 0.4;
        }

        if ($houseTypes->contains($preferred)) {
            return 1.0;
        }

        if ($preferred === 'private' && $houseTypes->intersect(['boarding_house', 'studio'])->isNotEmpty()) {
            return 0.7;
        }

        if ($preferred === 'shared' && $houseTypes->intersect(['bedspace', 'dormitory'])->isNotEmpty()) {
            return 0.7;
        }

        return 0.1;
    }

    private function scoreAmenities(array $preference, BoardingHouse $house): float
    {
        $wanted = collect($preference['amenities'])
            ->map(fn ($amenity) => $this->normalizeText($amenity))
            ->filter()
            ->unique()
            ->values();

        if ($wanted->isEmpty()) {
            return 0.5;
        }

        $available = $house->amenities
            ->pluck('name')
            ->map(fn ($amenity) => $this->normalizeText($amenity))
            ->filter()
            ->unique()
            ->values();

        if ($available->isEmpty()) {
            return 0.0;
        }

        $matched = $wanted->filter(function ($wantedAmenity) use ($available) {
            return $available->contains(fn ($availableAmenity) => $availableAmenity === $wantedAmenity
                || str_contains($availableAmenity, $wantedAmenity)
                || str_contains($wantedAmenity, $availableAmenity));
        })->count();

        return $matched / max($wanted->count(), 1);
    }

    private function scoreSafety(array $preference, BoardingHouse $house): float
    {
        $safety = collect($preference['safety_preferences'])->map(fn ($item) => $this->normalizeText($item))->filter();

        if ($safety->isEmpty()) {
            return 0.5;
        }

        $houseText = $this->houseText($house);
        $keywords = [
            'cctv' => ['cctv', 'camera', 'surveillance'],
            'gate' => ['gate', 'gated', 'secure gate'],
            'guard' => ['guard', 'security guard', 'watchman'],
            'curfew' => ['curfew', 'quiet hours'],
            'well lit' => ['well lit', 'well-lit', 'lighting', 'lighted'],
            'emergency' => ['emergency', 'fire exit', 'first aid'],
            'secure' => ['secure', 'security', 'safe', 'lock'],
            'no smoking' => ['no smoking', 'non smoking', 'smoke free'],
        ];

        $requested = $safety->flatMap(function ($item) {
            if (str_contains($item, 'very high') || str_contains($item, 'high')) {
                return ['cctv', 'gate', 'secure'];
            }

            if (str_contains($item, 'standard')) {
                return ['secure'];
            }

            return [$item];
        })->unique()->values();

        if ($requested->isEmpty()) {
            return 0.5;
        }

        $matched = $requested->filter(function ($item) use ($houseText, $keywords) {
            $terms = $keywords[$item] ?? [$item];

            return $this->containsAny($houseText, $terms);
        })->count();

        return $matched > 0 ? $matched / $requested->count() : 0.15;
    }

    private function scoreLifestyle(array $preference, BoardingHouse $house): float
    {
        $checks = [];
        $houseText = $this->houseText($house);
        $studyHabits = $this->normalizeText($preference['study_habits']);
        $sleepingSchedule = $this->normalizeText($preference['sleeping_schedule']);
        $notes = $this->normalizeText($preference['lifestyle_notes']);

        if ($studyHabits) {
            $checks[] = str_contains($studyHabits, 'quiet')
                ? ($this->containsAny($houseText, ['quiet', 'study', 'peaceful', 'no loud', 'no noise']) ? 1.0 : 0.35)
                : 0.65;
        }

        if (is_numeric($preference['cleanliness_level'])) {
            $cleanliness = (int) $preference['cleanliness_level'];
            $checks[] = $cleanliness >= 4
                ? ($this->containsAny($houseText, ['clean', 'cleanliness', 'tidy', 'sanitary']) ? 1.0 : 0.45)
                : 0.65;
        }

        if (is_numeric($preference['noise_tolerance'])) {
            $noise = (int) $preference['noise_tolerance'];
            $checks[] = $noise <= 2
                ? ($this->containsAny($houseText, ['quiet', 'curfew', 'no loud', 'study']) ? 1.0 : 0.35)
                : 0.65;
        }

        if ($sleepingSchedule) {
            $checks[] = str_contains($sleepingSchedule, 'night')
                ? ($this->containsAny($houseText, ['no curfew', 'flexible']) ? 1.0 : 0.55)
                : ($this->containsAny($houseText, ['curfew', 'quiet hours', 'no loud']) ? 0.85 : 0.6);
        }

        if ($preference['smoking_preference'] === 'non_smoker_only') {
            $checks[] = $this->containsAny($houseText, ['no smoking', 'non smoking', 'smoke free']) ? 1.0 : 0.35;
        } elseif ($preference['smoking_preference'] === 'outdoor_only') {
            $checks[] = $this->containsAny($houseText, ['outdoor smoking', 'designated smoking']) ? 1.0 : 0.55;
        }

        if ($preference['drinking_preference'] === 'no_alcohol') {
            $checks[] = $this->containsAny($houseText, ['no alcohol', 'no drinking', 'liquor prohibited']) ? 1.0 : 0.45;
        }

        if ($preference['pets_preference'] === 'no_pets') {
            $checks[] = $this->containsAny($houseText, ['no pets', 'pets prohibited']) ? 1.0 : 0.65;
        } elseif (in_array($preference['pets_preference'], ['cat_ok', 'dog_ok', 'pet_friendly'], true)) {
            $checks[] = $this->containsAny($houseText, ['pets allowed', 'pet friendly', 'cats allowed', 'dogs allowed']) ? 1.0 : 0.4;
        }

        if (in_array($preference['internet_usage'], ['heavy', 'remote_work'], true)) {
            $checks[] = $this->containsAny($houseText, ['wi fi', 'wifi', 'internet', 'fiber']) ? 1.0 : 0.25;
        }

        if (strlen($notes) >= 8) {
            $noteWords = collect(preg_split('/\s+/', $notes) ?: [])
                ->filter(fn ($word) => strlen($word) >= 4)
                ->take(12)
                ->values();

            if ($noteWords->isNotEmpty()) {
                $matches = $noteWords->filter(fn ($word) => str_contains($houseText, $word))->count();
                $checks[] = min(1.0, 0.35 + ($matches / $noteWords->count()));
            }
        }

        if ($checks === []) {
            return 0.5;
        }

        return array_sum($checks) / count($checks);
    }

    private function scoreOtherPreferences(
        array $preference,
        BoardingHouse $house,
        ?float $referenceLat,
        ?float $referenceLng
    ): float {
        $checks = [
            $this->scoreDistance($preference, $house, $referenceLat, $referenceLng),
            $this->scoreSafety($preference, $house),
        ];

        $hobbies = collect($preference['hobbies'])
            ->map(fn ($hobby) => $this->normalizeText($hobby))
            ->filter()
            ->values();

        if ($hobbies->isNotEmpty()) {
            $houseText = $this->houseText($house);
            $matched = $hobbies->filter(fn ($hobby) => str_contains($houseText, $hobby))->count();
            $checks[] = $matched > 0 ? min(1.0, 0.5 + ($matched / $hobbies->count())) : 0.5;
        }

        return array_sum($checks) / count($checks);
    }

    private function scoreDistance(array $preference, BoardingHouse $house, ?float $referenceLat, ?float $referenceLng): float
    {
        $preferredDistance = $this->toFloat($preference['distance_from_school']);

        if ($preferredDistance === null || $preferredDistance <= 0) {
            return 0.5;
        }

        $distance = $this->preferenceTargetsDssc($preference)
            ? $this->distanceFromDssc($house)
            : $this->toFloat($house->distance_from_school ?? null);

        if ($distance === null) {
            $lat = $referenceLat ?? self::DEFAULT_LAT;
            $lng = $referenceLng ?? self::DEFAULT_LNG;
            $distance = $this->distanceKm($lat, $lng, $house->latitude, $house->longitude);
        }

        if ($distance === null) {
            return 0.5;
        }

        if ($distance <= $preferredDistance) {
            return 1.0;
        }

        return max(0.0, 1.0 - (($distance - $preferredDistance) / max($preferredDistance, 5.0)));
    }

    private function scoreHouseRules(array $preference, BoardingHouse $house): float
    {
        $rules = $this->normalizeText($house->house_rules ?? '');

        if ($rules === '') {
            return 0.5;
        }

        $checks = [];
        $safety = collect($preference['safety_preferences'])->map(fn ($item) => $this->normalizeText($item));

        if ($safety->contains(fn ($item) => str_contains($item, 'no smoking'))) {
            $checks[] = $this->containsAny($rules, ['no smoking', 'non smoking', 'smoke free']) ? 1.0 : 0.35;
        }

        if ($safety->contains(fn ($item) => str_contains($item, 'curfew'))) {
            $checks[] = str_contains($rules, 'curfew') ? 1.0 : 0.45;
        }

        if (is_numeric($preference['noise_tolerance']) && (int) $preference['noise_tolerance'] <= 40) {
            $checks[] = $this->containsAny($rules, ['quiet hours', 'no loud', 'no noise', 'curfew']) ? 1.0 : 0.4;
        }

        if ($this->normalizeText($preference['study_habits']) === 'quiet focus') {
            $checks[] = $this->containsAny($rules, ['quiet', 'study', 'no loud', 'no noise']) ? 1.0 : 0.45;
        }

        return $checks === [] ? 0.65 : array_sum($checks) / count($checks);
    }

    private function scoreAvailability(BoardingHouse $house): float
    {
        $available = collect([
            (int) ($house->available_rooms ?? 0),
            (int) ($house->available_rooms_count ?? 0),
            (int) ($house->room_categories_available_rooms_sum ?? 0),
        ])->max();

        if ($house->relationLoaded('rooms')) {
            $available = max(
                $available,
                $house->rooms
                    ->filter(fn ($room) => strtolower((string) $room->status) === 'available'
                        || (int) ($room->available_slots ?? 0) > 0)
                    ->count()
            );
        }

        if ($house->relationLoaded('roomCategories')) {
            $available = max(
                $available,
                (int) $house->roomCategories->sum('available_rooms'),
                $house->roomCategories->where('is_available', true)->count()
            );
        }

        return match (true) {
            $available <= 0 => 0.0,
            $available <= 2 => 0.75,
            default => 1.0,
        };
    }

    private function matchReasons(array $preference, BoardingHouse $house, array $scores): array
    {
        $reasons = [];

        $distanceFromDssc = $this->distanceFromDssc($house);
        if ($this->preferenceTargetsDssc($preference) && $distanceFromDssc !== null) {
            if ($distanceFromDssc <= 1) {
                $reasons[] = 'Very near DSSC Main Campus.';
            } elseif ($distanceFromDssc <= 3) {
                $reasons[] = 'Within a short trip of DSSC Main Campus.';
            } elseif ($distanceFromDssc <= 5) {
                $reasons[] = 'Within 5 km of DSSC Main Campus.';
            }
        }

        if (($scores['budget'] ?? 0) >= 0.8) {
            $reasons[] = 'Matches your preferred budget range.';
        }

        if (($scores['location'] ?? 0) >= 0.8) {
            $reasons[] = 'Located near your selected barangay.';
        }

        if (($scores['room_type'] ?? 0) >= 0.8) {
            $reasons[] = 'Offers your preferred room type.';
        }

        if (($scores['amenities'] ?? 0) >= 0.65) {
            $reasons[] = 'Matches your preferred amenities';
            $available = $house->amenities
                ->pluck('name')
                ->filter(fn ($name) => collect($preference['amenities'])->contains(
                    fn ($wanted) => str_contains($this->normalizeText($name), $this->normalizeText($wanted))
                        || str_contains($this->normalizeText($wanted), $this->normalizeText($name))
                ))
                ->take(3)
                ->implode(', ');

            if ($available !== '') {
                $reasons[] = 'Includes preferred amenities: '.$available.'.';
            }
        }

        if (($scores['lifestyle'] ?? 0) >= 0.65) {
            $reasons[] = 'House rules fit your lifestyle preferences.';
        }

        if (($scores['availability'] ?? 0) >= 0.75) {
            $reasons[] = 'Has available rooms or slots.';
        }

        if (($scores['other'] ?? 0) >= 0.75) {
            $reasons[] = 'Matches your distance, safety, or other preferences.';
        }

        return $reasons ?: ['Worth reviewing based on your saved preferences.'];
    }

    private function matchWarnings(array $scores): array
    {
        $warnings = [];

        if (($scores['budget'] ?? 1) <= 0.25) {
            $warnings[] = 'Rental price may be outside your budget.';
        }

        if (($scores['amenities'] ?? 1) <= 0.25) {
            $warnings[] = 'Missing most selected amenities.';
        }

        if (($scores['location'] ?? 1) <= 0.25) {
            $warnings[] = 'Location may not match your preferred area.';
        }

        if (($scores['availability'] ?? 1) <= 0.0) {
            $warnings[] = 'No available rooms are currently recorded.';
        }

        return $warnings;
    }

    private function buildAiReason(array $reasons, array $warnings): string
    {
        $main = collect($reasons)
            ->reject(fn ($reason) => str_starts_with($reason, 'Worth reviewing'))
            ->take(3)
            ->map(fn ($reason) => rtrim($reason, '.'))
            ->values();

        if ($main->isNotEmpty()) {
            return 'AI-powered recommendation: '.$main->implode(', ').'.';
        }

        if ($warnings !== []) {
            return 'AI-powered recommendation: Some preferences may not fully match, but this listing may still be worth checking.';
        }

        return 'AI-powered recommendation based on your saved preferences.';
    }

    private function persistMatch(User $tenant, BoardingHouse $house, array $result): void
    {
        if (! Schema::hasTable('boarding_house_matches')) {
            return;
        }

        $match = BoardingHouseMatch::firstOrNew([
            'user_id' => $tenant->id,
            'boarding_house_id' => $house->id,
        ]);
        $aiIsStale = $match->exists
            && (
                (int) round((float) $match->match_score) !== (int) $result['recommendation_percent']
                || ($match->match_reasons ?? []) !== $result['reasons']
            );

        $match->fill([
            'match_score' => $result['recommendation_percent'],
            'match_reasons' => $result['reasons'],
            'score_breakdown' => $result['breakdown'],
        ]);

        if ($aiIsStale) {
            $match->fill([
                'ai_explanation' => null,
                'ai_model' => null,
                'ai_generated_at' => null,
            ]);
        }

        $match->save();
    }

    private function houseText(BoardingHouse $house): string
    {
        $amenities = $house->relationLoaded('amenities')
            ? $house->amenities->pluck('name')->implode(' ')
            : '';

        $roomCategories = $house->relationLoaded('roomCategories')
            ? $house->roomCategories->pluck('name')->implode(' ')
            : '';

        return $this->normalizeText(implode(' ', array_filter([
            $house->name,
            $house->description,
            $house->house_rules,
            $house->address,
            $house->full_address,
            $house->nearby_landmark,
            $house->property_type ?? null,
            $house->display_barangay,
            $house->city?->city_name,
            $amenities,
            $roomCategories,
        ])));
    }

    private function houseRoomTypes(BoardingHouse $house): Collection
    {
        $types = collect([$house->property_type ?? null]);

        if ($house->relationLoaded('roomCategories')) {
            $types = $types->merge($house->roomCategories->pluck('name'));
        }

        if ($house->relationLoaded('rooms')) {
            $types = $types->merge($house->rooms->pluck('type'));
        }

        return $types
            ->map(fn ($type) => $this->normalizeRoomType($type))
            ->filter()
            ->unique()
            ->values();
    }

    private function normalizeRoomType(?string $value): ?string
    {
        $value = $this->normalizeText($value);

        if ($value === '') {
            return null;
        }

        return match (true) {
            str_contains($value, 'any') => 'any',
            str_contains($value, 'bed') => 'bedspace',
            str_contains($value, 'share') => 'shared',
            str_contains($value, 'private') || str_contains($value, 'single') => 'private',
            str_contains($value, 'studio') || str_contains($value, 'apartment') => 'studio',
            str_contains($value, 'dorm') => 'dormitory',
            str_contains($value, 'boarding') => 'boarding_house',
            default => $value,
        };
    }

    private function amenityNamesFromIds(mixed $ids): array
    {
        $ids = collect($this->arrayValues($ids))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty() || ! Schema::hasTable('amenities')) {
            return [];
        }

        return Amenity::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    private function criterionLabel(string $criterion): string
    {
        return match ($criterion) {
            'budget' => 'Budget Match',
            'location' => 'Location Match',
            'room_type' => 'Room Type Match',
            'amenities' => 'Amenities Match',
            'availability' => 'Availability',
            'lifestyle' => 'House Rules / Lifestyle',
            'other' => 'Other Preferences',
            default => Str::headline($criterion),
        };
    }

    private function extractPreferredLandmark(string $notes): ?string
    {
        if (preg_match('/Preferred Landmark:\s*(.+?)(?:\n|$)/i', $notes, $matches)) {
            return trim($matches[1]) ?: null;
        }

        return null;
    }

    private function preferenceTargetsDssc(array $preference): bool
    {
        if (str_contains($this->normalizeText($preference['preferred_landmark'] ?? ''), 'dssc')) {
            return true;
        }

        $nearbyAreas = collect(config('dssc.areas', []))
            ->map(fn ($area) => $this->normalizeText($area))
            ->push('dssc main campus')
            ->push('all nearby dssc areas');

        return collect($preference['preferred_locations'] ?? [])
            ->map(fn ($location) => $this->normalizeText($location))
            ->contains(fn ($location) => $nearbyAreas->contains($location));
    }

    private function dsscLocationScore(BoardingHouse $house, ?string $houseText = null): float
    {
        $houseText ??= $this->houseText($house);
        $distance = $this->distanceFromDssc($house);

        if (str_contains($houseText, 'purok 3') && str_contains($houseText, 'matti')) {
            return 0.95;
        }

        if (str_contains($houseText, 'matti')) {
            return 1.0;
        }

        if (str_contains($houseText, 'mahayahay')) {
            return 0.82;
        }

        if (str_contains($houseText, 'tres de mayo')) {
            return 0.68;
        }

        if (str_contains($houseText, 'poblacion') || str_contains($houseText, 'city proper')) {
            return $distance !== null && $distance <= 3 ? 0.65 : 0.52;
        }

        return match (true) {
            $distance === null => 0.2,
            $distance <= 1 => 0.92,
            $distance <= 2 => 0.84,
            $distance <= 3 => 0.72,
            $distance <= 5 => 0.55,
            default => 0.15,
        };
    }

    private function payloadIsAiReady(array $payload): bool
    {
        return collect(UserPreference::AI_REQUIRED_FIELDS)
            ->every(function (string $field) use ($payload): bool {
                $value = match ($field) {
                    'preferred_rental_budget' => $payload['preferred_rental_budget']
                        ?? $payload['budget_max']
                        ?? $payload['budget_min'],
                    default => $payload[$field] ?? null,
                };

                return is_array($value)
                    ? collect($value)->filter(fn ($item) => $item !== null && $item !== '')->isNotEmpty()
                    : $value !== null && $value !== '';
            });
    }

    private function aiCompletionPercentage(array $payload): int
    {
        $filled = collect(UserPreference::AI_REQUIRED_FIELDS)
            ->filter(function (string $field) use ($payload): bool {
                $value = match ($field) {
                    'preferred_rental_budget' => $payload['preferred_rental_budget']
                        ?? $payload['budget_max']
                        ?? $payload['budget_min'],
                    default => $payload[$field] ?? null,
                };

                return is_array($value)
                    ? collect($value)->filter(fn ($item) => $item !== null && $item !== '')->isNotEmpty()
                    : $value !== null && $value !== '';
            })
            ->count();

        return (int) round(($filled / count(UserPreference::AI_REQUIRED_FIELDS)) * 100);
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $this->normalizeText($needle))) {
                return true;
            }
        }

        return false;
    }

    private function distanceKm(?float $fromLat, ?float $fromLng, mixed $toLat, mixed $toLng): ?float
    {
        if ($fromLat === null || $fromLng === null || ! is_numeric($toLat) || ! is_numeric($toLng)) {
            return null;
        }

        $earthRadius = 6371;
        $latFrom = deg2rad($fromLat);
        $lngFrom = deg2rad($fromLng);
        $latTo = deg2rad((float) $toLat);
        $lngTo = deg2rad((float) $toLng);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)
        ));

        return round($angle * $earthRadius, 2);
    }

    private function normalizeText(mixed $value): string
    {
        return trim((string) Str::of((string) $value)
            ->lower()
            ->replaceMatches('/[^a-z0-9\s\-]/', ' ')
            ->replaceMatches('/\s+/', ' '));
    }

    private function arrayValues(mixed $value): array
    {
        if ($value instanceof Collection) {
            return $value->values()->all();
        }

        if (is_array($value)) {
            return array_values(array_filter($value, fn ($item) => $item !== null && $item !== ''));
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            if (is_array($decoded)) {
                return array_values(array_filter($decoded, fn ($item) => $item !== null && $item !== ''));
            }

            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return [];
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = preg_replace('/[^0-9.]/', '', (string) $value);

        return $normalized !== '' && is_numeric($normalized) ? (float) $normalized : null;
    }
}
