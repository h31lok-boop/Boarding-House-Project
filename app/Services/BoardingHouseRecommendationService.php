<?php

namespace App\Services;

use App\Models\BoardingHouse;
use App\Models\TenantMatchProfile;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class BoardingHouseRecommendationService
{
    public function __construct(
        private readonly CompatibilityService $compatibilityService,
    ) {}

    public function rank(User $tenant, Collection $houses, ?float $referenceLat = null, ?float $referenceLng = null): Collection
    {
        if (! $this->matchProfilesAvailable()) {
            return collect();
        }

        $tenant->loadMissing('tenantMatchProfile');

        if (method_exists($houses, 'loadMissing')) {
            $houses->loadMissing([
                'amenities:id,name',
                'rooms:id,boarding_house_id,price,status,available_slots',
                'roomCategories:id,boarding_house_id,monthly_rate,available_rooms,is_available',
                'tenants:id,name,boarding_house_id,is_active,role',
                'tenants.tenantMatchProfile',
            ]);
        }

        return $houses
            ->map(fn (BoardingHouse $house) => [
                'house' => $house,
                'recommendation' => $this->score($tenant, $house, $referenceLat, $referenceLng),
            ])
            ->sortByDesc(fn (array $item) => $item['recommendation']['overall_score'])
            ->values();
    }

    public function score(User $tenant, BoardingHouse $house, ?float $referenceLat = null, ?float $referenceLng = null): array
    {
        if (! $this->matchProfilesAvailable()) {
            return $this->unavailableRecommendation();
        }

        $tenant->loadMissing('tenantMatchProfile');
        $house->loadMissing([
            'amenities:id,name',
            'rooms:id,boarding_house_id,price,status,available_slots',
            'roomCategories:id,boarding_house_id,monthly_rate,available_rooms,is_available',
            'tenants:id,name,boarding_house_id,is_active,role',
            'tenants.tenantMatchProfile',
        ]);

        $profile = $tenant->tenantMatchProfile;

        if (! $profile?->completed_at) {
            return [
                'overall_score' => 0.0,
                'recommendation_percent' => 0,
                'breakdown' => [],
                'reasons' => [],
                'warnings' => ['Complete your match profile to unlock housing recommendations.'],
            ];
        }

        $weights = config('matchmaking.boarding_house_weights', []);
        $scores = [
            'budget' => $this->scoreBudget($profile, $house),
            'amenities' => $this->scoreAmenities($profile, $house),
            'distance' => $this->scoreDistance($house, $referenceLat, $referenceLng),
            'availability' => $this->scoreAvailability($house),
            'occupant_compatibility' => $this->scoreOccupantCompatibility($tenant, $house),
        ];

        $breakdown = [];
        $total = 0.0;
        $weightTotal = 0.0;

        foreach ($scores as $criterion => $score) {
            $weight = (float) ($weights[$criterion] ?? 0);
            $weighted = $score * $weight;
            $total += $weighted;
            $weightTotal += $weight;

            $breakdown[$criterion] = [
                'score' => round($score, 4),
                'weighted_score' => round($weighted, 4),
                'weight' => $weight,
                'label' => $this->criterionLabel($criterion),
            ];
        }

        $overall = $weightTotal > 0 ? $total / $weightTotal : 0.0;

        return [
            'overall_score' => round($overall, 4),
            'recommendation_percent' => (int) round($overall * 100),
            'breakdown' => $breakdown,
            'reasons' => $this->summaries($breakdown, true),
            'warnings' => $this->summaries($breakdown, false),
        ];
    }

    private function scoreBudget(TenantMatchProfile $profile, BoardingHouse $house): float
    {
        $price = $this->housePrice($house);
        $min = $this->toFloat($profile->budget_min);
        $max = $this->toFloat($profile->budget_max);

        if ($price === null || ($min === null && $max === null)) {
            return 0.5;
        }

        if ($min !== null && $max !== null && $price >= $min && $price <= $max) {
            return 1.0;
        }

        if ($min !== null && $max === null) {
            return $price >= $min ? 1.0 : max(0.0, $price / max($min, 1.0));
        }

        if ($max !== null && $min === null) {
            if ($price <= $max) {
                return 1.0;
            }

            return max(0.0, 1.0 - (($price - $max) / max($max, 1.0)));
        }

        if ($price < $min) {
            return 0.75;
        }

        $range = max($max - $min, 1000.0);

        return max(0.0, 1.0 - (($price - $max) / $range));
    }

    private function scoreAmenities(TenantMatchProfile $profile, BoardingHouse $house): float
    {
        $preferredIds = collect($profile->preferred_amenity_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($preferredIds->isEmpty()) {
            return 0.5;
        }

        $houseAmenityIds = $house->amenities
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique();

        if ($houseAmenityIds->isEmpty()) {
            return 0.0;
        }

        return $preferredIds->intersect($houseAmenityIds)->count() / $preferredIds->count();
    }

    private function scoreDistance(BoardingHouse $house, ?float $referenceLat, ?float $referenceLng): float
    {
        if ($referenceLat === null || $referenceLng === null || ! is_numeric($house->latitude) || ! is_numeric($house->longitude)) {
            return 0.5;
        }

        $distance = $this->distanceKm($referenceLat, $referenceLng, (float) $house->latitude, (float) $house->longitude);
        if ($distance <= 1.0) {
            return 1.0;
        }

        $maxDistance = (float) config('matchmaking.max_recommendation_distance_km', 8);

        return max(0.0, 1.0 - (($distance - 1.0) / max($maxDistance - 1.0, 1.0)));
    }

    private function scoreAvailability(BoardingHouse $house): float
    {
        $availableRooms = $this->availableRoomCount($house);

        if ($availableRooms <= 0) {
            return 0.0;
        }

        if ($availableRooms <= 2) {
            return 0.75;
        }

        return 1.0;
    }

    private function scoreOccupantCompatibility(User $tenant, BoardingHouse $house): float
    {
        if (! $this->matchProfilesAvailable()) {
            return 0.5;
        }

        $occupants = $house->tenants
            ->filter(fn (User $occupant) => $occupant->id !== $tenant->id)
            ->filter(fn (User $occupant) => $occupant->tenantMatchProfile?->completed_at)
            ->values();

        if ($occupants->isEmpty()) {
            return 0.5;
        }

        $scores = $occupants
            ->map(fn (User $occupant) => (float) $this->compatibilityService->score($tenant, $occupant)['overall_score'])
            ->filter(fn ($score) => is_numeric($score));

        return $scores->isNotEmpty() ? (float) $scores->avg() : 0.5;
    }

    private function housePrice(BoardingHouse $house): ?float
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

        return $prices
            ->filter(fn ($price) => $price !== null && $price > 0)
            ->min();
    }

    private function availableRoomCount(BoardingHouse $house): int
    {
        $counts = collect([
            (int) ($house->available_rooms ?? 0),
            (int) ($house->available_rooms_count ?? 0),
            (int) ($house->room_categories_available_rooms_sum ?? 0),
        ]);

        if ($house->relationLoaded('rooms')) {
            $roomSlots = $house->rooms
                ->filter(fn ($room) => strtolower((string) $room->status) === 'available')
                ->sum(fn ($room) => max((int) ($room->available_slots ?? 0), 1));
            $counts->push((int) $roomSlots);
        }

        if ($house->relationLoaded('roomCategories')) {
            $categorySlots = $house->roomCategories
                ->filter(fn ($category) => $category->is_available || (int) ($category->available_rooms ?? 0) > 0)
                ->sum(fn ($category) => (int) ($category->available_rooms ?? 0));
            $counts->push((int) $categorySlots);
        }

        return (int) max($counts->all());
    }

    private function summaries(array $breakdown, bool $positive): array
    {
        $items = [];

        foreach ($breakdown as $criterion => $item) {
            $score = (float) $item['score'];

            if ($positive && $score >= 0.8) {
                $items[] = $this->positiveSummary($criterion);
            }

            if (! $positive && $score <= 0.2) {
                $items[] = $this->warningSummary($criterion);
            }
        }

        return array_values(array_unique(array_filter($items)));
    }

    private function positiveSummary(string $criterion): ?string
    {
        return match ($criterion) {
            'budget' => 'Fits your saved budget range',
            'amenities' => 'Matches your preferred amenities',
            'distance' => 'Close to your reference location',
            'availability' => 'Has current room availability',
            'occupant_compatibility' => 'Current occupants look compatible',
            default => null,
        };
    }

    private function warningSummary(string $criterion): ?string
    {
        return match ($criterion) {
            'budget' => 'Rental price may be outside your budget',
            'amenities' => 'Missing most preferred amenities',
            'distance' => 'Far from your reference location',
            'availability' => 'No available rooms detected',
            'occupant_compatibility' => 'Existing occupants may be a weak fit',
            default => null,
        };
    }

    private function criterionLabel(string $criterion): string
    {
        return match ($criterion) {
            'budget' => 'Budget',
            'amenities' => 'Preferred Amenities',
            'distance' => 'Distance',
            'availability' => 'Availability',
            'occupant_compatibility' => 'Occupant Compatibility',
            default => ucfirst(str_replace('_', ' ', $criterion)),
        };
    }

    private function distanceKm(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $earthRadius = 6371;
        $latFrom = deg2rad($fromLat);
        $lngFrom = deg2rad($fromLng);
        $latTo = deg2rad($toLat);
        $lngTo = deg2rad($toLng);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)
        ));

        return round($angle * $earthRadius, 2);
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

    private function matchProfilesAvailable(): bool
    {
        return Schema::hasTable('tenant_match_profiles');
    }

    private function unavailableRecommendation(): array
    {
        return [
            'overall_score' => 0.0,
            'recommendation_percent' => 0,
            'breakdown' => [],
            'reasons' => [],
            'warnings' => ['Tenant match profiles are not available yet.'],
        ];
    }
}
