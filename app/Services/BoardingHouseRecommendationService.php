<?php

namespace App\Services;

use App\Models\Amenity;
use App\Models\BoardingHouse;
use App\Models\BoardingHouseMatch;
use App\Models\TenantMatchProfile;
use App\Models\TenantPreference;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BoardingHouseRecommendationService
{
    private const DEFAULT_LAT = 6.7440000;

    private const DEFAULT_LNG = 125.3550000;

    private const WEIGHTS = [
        'budget' => 0.25,
        'location' => 0.20,
        'room_type' => 0.15,
        'amenities' => 0.15,
        'safety' => 0.10,
        'lifestyle' => 0.10,
        'distance' => 0.05,
    ];

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
            ];
        }

        $scores = [
            'budget' => $this->scoreBudget($preference, $house),
            'location' => $this->scoreLocation($preference, $house),
            'room_type' => $this->scoreRoomType($preference, $house),
            'amenities' => $this->scoreAmenities($preference, $house),
            'safety' => $this->scoreSafety($preference, $house),
            'lifestyle' => $this->scoreLifestyle($preference, $house),
            'distance' => $this->scoreDistance($preference, $house, $referenceLat, $referenceLng),
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
        $reasons = $this->matchReasons($scores);
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
        ];

        if ($persist) {
            $this->persistMatch($tenant, $house, $result);
        }

        return $result;
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
                'barangay:id,barangay_name',
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

        if (Schema::hasColumn('boarding_houses', 'approval_status') || Schema::hasColumn('boarding_houses', 'status')) {
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
            'preferred_locations' => $payload['preferred_locations'],
            'preferred_location_label' => collect($payload['preferred_locations'])->filter()->implode(', '),
            'lifestyle_text' => $payload['lifestyle_notes'],
            'room_type' => $payload['room_type'],
            'amenities' => $payload['amenities'],
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
        if ($percent >= 90) {
            return 'Top Match';
        }

        if ($percent >= 75) {
            return 'Great Match';
        }

        if ($percent >= 50) {
            return 'Good Match';
        }

        return 'Low Match';
    }

    private function loadTenantPreferences(User $tenant): void
    {
        $relations = [];

        if (Schema::hasTable('tenant_preferences')) {
            $relations[] = 'tenantPreference';
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
            'barangay:id,barangay_name',
            'province:id,province_name',
        ]);
    }

    private function preferencePayload(User $tenant): array
    {
        $preference = Schema::hasTable('tenant_preferences') ? $tenant->tenantPreference : null;

        if ($preference instanceof TenantPreference) {
            return [
                'budget_min' => $this->toFloat($preference->preferred_rental_budget_min),
                'budget_max' => $this->toFloat($preference->preferred_rental_budget_max),
                'preferred_locations' => $this->arrayValues($preference->preferred_locations),
                'distance_from_school' => $this->toFloat($preference->distance_from_school),
                'room_type' => $preference->room_type,
                'study_habits' => $preference->study_habits,
                'sleeping_schedule' => $preference->sleeping_schedule,
                'cleanliness_level' => $preference->cleanliness_level,
                'noise_tolerance' => $preference->noise_tolerance,
                'safety_preferences' => $this->arrayValues($preference->safety_preferences),
                'amenities' => $this->arrayValues($preference->amenities),
                'lifestyle_notes' => trim((string) $preference->lifestyle_notes),
            ];
        }

        $profile = Schema::hasTable('tenant_match_profiles') ? $tenant->tenantMatchProfile : null;

        if (! $profile instanceof TenantMatchProfile) {
            return $this->emptyPreferencePayload();
        }

        $notes = (string) ($profile->additional_notes ?? '');
        $amenities = $this->amenityNamesFromIds($profile->preferred_amenity_ids ?? []);
        $location = $this->extractPreferredLocation($notes);

        return [
            'budget_min' => $this->toFloat($profile->budget_min),
            'budget_max' => $this->toFloat($profile->budget_max),
            'preferred_locations' => $location ? [$location] : [],
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
        ];
    }

    private function emptyPreferencePayload(): array
    {
        return [
            'budget_min' => null,
            'budget_max' => null,
            'preferred_locations' => [],
            'distance_from_school' => null,
            'room_type' => null,
            'study_habits' => null,
            'sleeping_schedule' => null,
            'cleanliness_level' => null,
            'noise_tolerance' => null,
            'safety_preferences' => [],
            'amenities' => [],
            'lifestyle_notes' => '',
        ];
    }

    private function payloadHasPreferences(array $payload): bool
    {
        return $payload['budget_min'] !== null
            || $payload['budget_max'] !== null
            || $payload['preferred_locations'] !== []
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

        if ($locations->isEmpty()) {
            return 0.5;
        }

        $houseText = $this->normalizeText(implode(' ', array_filter([
            $house->barangay?->barangay_name,
            $house->city?->city_name,
            $house->province?->province_name,
            $house->full_address,
            $house->address,
        ])));

        if ($houseText === '') {
            return 0.3;
        }

        $best = 0.0;

        foreach ($locations as $location) {
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

        $requested = $safety->flatMap(function ($item) use ($keywords) {
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

    private function scoreDistance(array $preference, BoardingHouse $house, ?float $referenceLat, ?float $referenceLng): float
    {
        $preferredDistance = $this->toFloat($preference['distance_from_school']);

        if ($preferredDistance === null || $preferredDistance <= 0) {
            return 0.5;
        }

        $distance = $this->toFloat($house->distance_from_school ?? null);

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

    private function matchReasons(array $scores): array
    {
        $reasons = [];

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
            $reasons[] = 'Includes several of your preferred amenities.';
        }

        if (($scores['safety'] ?? 0) >= 0.65) {
            $reasons[] = 'Matches your safety preferences.';
        }

        if (($scores['lifestyle'] ?? 0) >= 0.65) {
            $reasons[] = 'Fits your study and lifestyle preferences.';
        }

        if (($scores['distance'] ?? 0) >= 0.8) {
            $reasons[] = 'Within your preferred school distance.';
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

        BoardingHouseMatch::updateOrCreate(
            [
                'user_id' => $tenant->id,
                'boarding_house_id' => $house->id,
            ],
            [
                'match_score' => $result['recommendation_percent'],
                'match_reasons' => $result['reasons'],
                'score_breakdown' => $result['breakdown'],
            ]
        );
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
            $house->property_type ?? null,
            $house->barangay?->barangay_name,
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
            'safety' => 'Safety Match',
            'lifestyle' => 'Lifestyle Match',
            'distance' => 'Distance Match',
            default => Str::headline($criterion),
        };
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
