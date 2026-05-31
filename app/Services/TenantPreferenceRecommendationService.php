<?php

namespace App\Services;

use App\Models\BoardingHouse;
use App\Models\TenantMatchProfile;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TenantPreferenceRecommendationService
{
    private const WEIGHTS = [
        'budget'       => 0.40,
        'location'     => 0.35,
        'lifestyle'    => 0.15,
        'availability' => 0.10,
    ];

    /**
     * Score and rank approved boarding houses against a tenant's saved preferences.
     * Works whether or not completed_at is set — uses budget, preferred_location,
     * and lifestyle text extracted from additional_notes.
     */
    public function rank(User $tenant, ?Collection $houses = null): Collection
    {
        if (! Schema::hasTable('boarding_houses')) {
            return collect();
        }

        $tenant->loadMissing('tenantMatchProfile');
        $profile = $tenant->tenantMatchProfile;

        $houses ??= $this->candidates();

        [$budgetMin, $budgetMax, $preferredLocation, $lifestyleText] = $this->parseProfile($profile);

        return $houses
            ->map(function (BoardingHouse $house) use ($budgetMin, $budgetMax, $preferredLocation, $lifestyleText) {
                $price = $this->housePrice($house);

                if ($price === null || $price <= 0) {
                    return null;
                }

                $scores = [
                    'budget'       => $this->scoreBudget($price, $budgetMin, $budgetMax),
                    'location'     => $this->scoreLocation($preferredLocation, $house),
                    'lifestyle'    => $this->scoreLifestyle($lifestyleText, $house),
                    'availability' => $this->scoreAvailability($house),
                ];

                $overall = array_sum(array_map(
                    fn ($key) => $scores[$key] * self::WEIGHTS[$key],
                    array_keys(self::WEIGHTS)
                ));

                $percent = (int) round($overall * 100);

                return [
                    'house'          => $house,
                    'image_url'      => $this->imageUrl($house),
                    'recommendation' => [
                        'overall_score'          => round($overall, 4),
                        'recommendation_percent' => $percent,
                        'match_label'            => $this->matchLabel($percent),
                        'ai_reason'              => $this->buildAiReason($scores, $preferredLocation, $budgetMin, $budgetMax, $price),
                        'reasons'                => $this->buildReasons($scores),
                        'scores'                 => $scores,
                        'price'                  => $price,
                    ],
                ];
            })
            ->filter()
            ->sortByDesc(fn ($item) => $item['recommendation']['overall_score'])
            ->values();
    }

    /** Standard approved-listing query shared by dashboard and matchmaking page. */
    public function candidates(): Collection
    {
        $query = BoardingHouse::query()
            ->with([
                'amenities:id,name',
                'rooms:id,boarding_house_id,price,status,available_slots',
                'roomCategories:id,boarding_house_id,monthly_rate,available_rooms,is_available',
                'images:id,boarding_house_id,image_path,is_primary',
            ])
            ->withCount([
                'rooms as available_rooms_count' => fn ($q) => $q->where('status', 'available'),
            ])
            ->withSum('roomCategories as room_categories_available_rooms_sum', 'available_rooms');

        if (Schema::hasColumn('boarding_houses', 'is_active')) {
            $query->where('is_active', true);
        }

        if (Schema::hasColumn('boarding_houses', 'approval_status')) {
            $query->where('approval_status', 'approved');
        } elseif (Schema::hasColumn('boarding_houses', 'status')) {
            $query->where('status', 'approved');
        }

        return $query->orderBy('name')->limit(50)->get();
    }

    // ── Parsing ──────────────────────────────────────────────────────────────

    private function parseProfile(?TenantMatchProfile $profile): array
    {
        $notes           = (string) ($profile?->additional_notes ?? '');
        $preferredLoc    = $this->extractPreferredLocation($notes);
        $lifestyleText   = $this->extractLifestyleText($notes);

        return [
            $this->toFloat($profile?->budget_min),
            $this->toFloat($profile?->budget_max),
            $preferredLoc,
            $lifestyleText,
        ];
    }

    /**
     * Parse "Preferred Location: ..." written by RegisteredUserController
     * (or MatchProfileController via additional_notes).
     */
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

        return trim($text) ?: null;
    }

    // ── Scoring ──────────────────────────────────────────────────────────────

    private function scoreBudget(float $price, ?float $min, ?float $max): float
    {
        if ($min === null && $max === null) {
            return 0.5;
        }

        if ($min !== null && $max !== null) {
            if ($price >= $min && $price <= $max) {
                return 1.0;
            }

            if ($price < $min) {
                return 0.80;
            }

            return max(0.0, 1.0 - (($price - $max) / max($max - $min, 1000.0)));
        }

        if ($max !== null) {
            return $price <= $max ? 1.0 : max(0.0, 1.0 - (($price - $max) / max($max, 1000.0)));
        }

        // Only min provided
        return $price >= $min ? 1.0 : max(0.0, $price / max($min, 1.0));
    }

    private function scoreLocation(?string $preferredLocation, BoardingHouse $house): float
    {
        if (! $preferredLocation || strlen(trim($preferredLocation)) < 2) {
            return 0.5;
        }

        $houseAddress = strtolower(trim(implode(' ', array_filter([
            $house->address,
            $house->full_address,
        ]))));

        if (! $houseAddress) {
            return 0.5;
        }

        $words = array_values(array_filter(
            preg_split('/[\s,\/\-\.]+/', strtolower($preferredLocation)),
            fn ($w) => strlen($w) >= 3
        ));

        if (empty($words)) {
            return 0.5;
        }

        $matched = count(array_filter($words, fn ($word) => str_contains($houseAddress, $word)));

        if ($matched === 0) {
            return 0.0;
        }

        return min(1.0, 0.5 + ($matched / count($words)) * 0.5);
    }

    private function scoreLifestyle(?string $lifestyleText, BoardingHouse $house): float
    {
        if (! $lifestyleText || strlen(trim($lifestyleText)) < 8) {
            return 0.5;
        }

        $amenityNames = $house->relationLoaded('amenities')
            ? $house->amenities->pluck('name')->implode(' ')
            : '';

        $houseText = strtolower(trim(implode(' ', array_filter([
            $house->description,
            $house->house_rules,
            $amenityNames,
        ]))));

        if (! $houseText) {
            return 0.5;
        }

        $keywordGroups = [
            [
                'tenant' => ['quiet', 'peaceful', 'silent', 'no noise', 'no loud'],
                'house'  => ['quiet', 'peaceful', 'study', 'no noise', 'no loud', 'silent'],
            ],
            [
                'tenant' => ['clean', 'tidy', 'hygienic', 'cleanliness'],
                'house'  => ['clean', 'tidy', 'hygienic', 'sanitary', 'cleanliness'],
            ],
            [
                'tenant' => ['safe', 'security', 'cctv', 'gated'],
                'house'  => ['safe', 'security', 'cctv', 'guard', 'gated'],
            ],
            [
                'tenant' => ['wifi', 'internet', 'broadband'],
                'house'  => ['wifi', 'internet', 'broadband', 'wi-fi'],
            ],
            [
                'tenant' => ['non-smoker', 'no smoking', 'smoke-free', 'nonsmoker', 'non smoker'],
                'house'  => ['no smoking', 'non-smoking', 'smoke-free', 'smoking prohibited'],
            ],
            [
                'tenant' => ['furnished', 'aircon', 'air conditioning', 'air con'],
                'house'  => ['furnished', 'aircon', 'air conditioning', 'ac', 'air-conditioned'],
            ],
            [
                'tenant' => ['female', 'women', 'girls', 'ladies'],
                'house'  => ['female', 'women', 'girls', 'ladies only', 'female only'],
            ],
            [
                'tenant' => ['male', 'men', 'boys', 'gents'],
                'house'  => ['male', 'men', 'boys', 'gents only', 'male only'],
            ],
        ];

        $lifeLower     = strtolower($lifestyleText);
        $totalGroups   = 0;
        $matchedGroups = 0;

        foreach ($keywordGroups as $group) {
            $tenantMentions = ! empty(array_filter(
                $group['tenant'],
                fn ($kw) => str_contains($lifeLower, $kw)
            ));

            if ($tenantMentions) {
                $totalGroups++;
                $houseMentions = ! empty(array_filter(
                    $group['house'],
                    fn ($kw) => str_contains($houseText, $kw)
                ));

                if ($houseMentions) {
                    $matchedGroups++;
                }
            }
        }

        if ($totalGroups === 0) {
            return 0.5;
        }

        return min(1.0, $matchedGroups / $totalGroups);
    }

    private function scoreAvailability(BoardingHouse $house): float
    {
        $counts = [
            (int) ($house->available_rooms ?? 0),
            (int) ($house->available_rooms_count ?? 0),
            (int) ($house->room_categories_available_rooms_sum ?? 0),
        ];

        if ($house->relationLoaded('rooms')) {
            $counts[] = $house->rooms
                ->filter(fn ($r) => strtolower((string) $r->status) === 'available')
                ->count();
        }

        if ($house->relationLoaded('roomCategories')) {
            $counts[] = $house->roomCategories
                ->filter(fn ($c) => $c->is_available || (int) ($c->available_rooms ?? 0) > 0)
                ->count();
        }

        $available = max($counts);

        if ($available <= 0) {
            return 0.0;
        }

        return $available <= 2 ? 0.75 : 1.0;
    }

    // ── Price resolution ─────────────────────────────────────────────────────

    public function housePrice(BoardingHouse $house): ?float
    {
        $prices = collect();

        foreach (['price', 'monthly_payment'] as $col) {
            $v = $this->toFloat($house->{$col} ?? null);
            if ($v !== null) {
                $prices->push($v);
            }
        }

        if ($house->relationLoaded('rooms')) {
            $house->rooms->pluck('price')->each(function ($p) use ($prices) {
                $v = $this->toFloat($p);
                if ($v !== null) {
                    $prices->push($v);
                }
            });
        }

        if ($house->relationLoaded('roomCategories')) {
            $house->roomCategories->pluck('monthly_rate')->each(function ($p) use ($prices) {
                $v = $this->toFloat($p);
                if ($v !== null) {
                    $prices->push($v);
                }
            });
        }

        return $prices->filter(fn ($p) => $p > 0)->min();
    }

    // ── Label / reason helpers ───────────────────────────────────────────────

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

    private function buildAiReason(
        array $scores,
        ?string $preferredLocation,
        ?float $budgetMin,
        ?float $budgetMax,
        float $price
    ): string {
        $parts = [];

        // Budget
        if ($scores['budget'] >= 0.9) {
            $budgetRange = $this->formatBudgetRange($budgetMin, $budgetMax);
            $parts[] = "fits your budget" . ($budgetRange ? " ({$budgetRange})" : '');
        } elseif ($scores['budget'] >= 0.6) {
            $parts[] = 'is reasonably priced for your budget';
        }

        // Location
        if ($scores['location'] >= 0.8) {
            $parts[] = "is in your preferred area" . ($preferredLocation ? " ({$preferredLocation})" : '');
        } elseif ($scores['location'] >= 0.5) {
            $parts[] = 'is near your preferred location';
        }

        // Lifestyle
        if ($scores['lifestyle'] >= 0.75) {
            $parts[] = 'matches your quiet and clean lifestyle preferences';
        } elseif ($scores['lifestyle'] >= 0.5) {
            $parts[] = 'aligns with your lifestyle preferences';
        }

        // Availability
        if ($scores['availability'] >= 0.75) {
            $parts[] = 'has available rooms';
        }

        if (empty($parts)) {
            return 'A boarding house worth exploring based on your profile.';
        }

        $first = ucfirst(array_shift($parts));

        if (empty($parts)) {
            return "This boarding house {$first}.";
        }

        $last   = array_pop($parts);
        $middle = implode(', ', $parts);

        return $middle
            ? "This boarding house {$first}, {$middle}, and {$last}."
            : "This boarding house {$first} and {$last}.";
    }

    private function buildReasons(array $scores): array
    {
        $reasons = [];

        if ($scores['budget'] >= 0.8) {
            $reasons[] = 'Fits your budget range';
        }

        if ($scores['location'] >= 0.8) {
            $reasons[] = 'Matches your preferred location';
        } elseif ($scores['location'] >= 0.5) {
            $reasons[] = 'Near your preferred area';
        }

        if ($scores['lifestyle'] >= 0.7) {
            $reasons[] = 'Matches your lifestyle';
        }

        if ($scores['availability'] >= 0.75) {
            $reasons[] = 'Rooms available';
        }

        return $reasons;
    }

    private function formatBudgetRange(?float $min, ?float $max): string
    {
        if ($min !== null && $max !== null) {
            return '₱' . number_format($min) . '–₱' . number_format($max);
        }

        if ($max !== null) {
            return 'up to ₱' . number_format($max);
        }

        if ($min !== null) {
            return 'from ₱' . number_format($min);
        }

        return '';
    }

    // ── Utility ──────────────────────────────────────────────────────────────

    public function imageUrl(BoardingHouse $house): ?string
    {
        $path = null;

        if ($house->relationLoaded('images') && $house->images->isNotEmpty()) {
            $img  = $house->images->firstWhere('is_primary', true)
                 ?? $house->images->sortBy('sort_order')->first()
                 ?? $house->images->first();
            $path = $img?->image_path ?: null;
        }

        if (! $path) {
            foreach (['featured_image', 'exterior_image', 'room_image'] as $col) {
                if (! empty($house->{$col})) {
                    $path = $house->{$col};
                    break;
                }
            }
        }

        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return \Illuminate\Support\Facades\Storage::url($path);
    }

    /** Check whether the tenant has enough preferences to run matching. */
    public function hasPreferences(User $tenant): bool
    {
        $tenant->loadMissing('tenantMatchProfile');
        $profile = $tenant->tenantMatchProfile;

        if (! $profile) {
            return false;
        }

        $notes = (string) ($profile->additional_notes ?? '');

        return $this->toFloat($profile->budget_min) !== null
            || $this->toFloat($profile->budget_max) !== null
            || $this->extractPreferredLocation($notes) !== null
            || strlen(trim($this->extractLifestyleText($notes) ?? '')) >= 8;
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
