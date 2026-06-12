<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantMatchProfileUpdateRequest;
use App\Models\Amenity;
use App\Models\TenantMatchProfile;
use App\Models\TenantPreference;
use App\Services\BoardingHouseRecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MatchProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isUser(), 403);

        $hasMatchProfiles = Schema::hasTable('tenant_match_profiles');
        $profile = $hasMatchProfiles
            ? TenantMatchProfile::firstOrCreate(
                ['user_id' => $tenant->id],
                ['gender_preference' => 'no_preference']
            )
            : new TenantMatchProfile(['gender_preference' => 'no_preference']);

        $tenantPreference = Schema::hasTable('tenant_preferences')
            ? TenantPreference::firstOrNew(['user_id' => $tenant->id])
            : null;

        return view('user.profile', [
            'tenant' => $tenant,
            'profile' => $profile,
            'tenantPreference' => $tenantPreference,
            'fieldOptions' => $this->fieldOptions(),
            'amenities' => Amenity::query()->orderBy('name')->get(['id', 'name']),
            'matchProfilesAvailable' => $hasMatchProfiles,
        ]);
    }

    public function store(TenantMatchProfileUpdateRequest $request): RedirectResponse
    {
        return $this->update($request);
    }

    public function update(TenantMatchProfileUpdateRequest $request): RedirectResponse
    {
        $tenant = $request->user();

        if (! Schema::hasTable('tenant_match_profiles')) {
            return back()->with('error', 'Tenant match profiles are not available yet.');
        }

        $profile = TenantMatchProfile::firstOrCreate(
            ['user_id' => $tenant->id],
            ['gender_preference' => 'no_preference']
        );

        $validated = $request->validated();
        [$budgetMin, $budgetMax] = $this->budgetRange(
            $validated['rental_budget'] ?? null,
            $validated['budget_min'] ?? null,
            $validated['budget_max'] ?? null
        );
        $preferredLocations = $this->listFromCsv($validated['preferred_location'] ?? null);
        $amenityNames = $this->amenityNames($validated);
        $noiseScale = $this->normalizeNoiseToFivePointScale($validated['noise_tolerance'] ?? null);

        $profileData = [
            'budget_min' => $budgetMin,
            'budget_max' => $budgetMax,
            'gender_preference' => $validated['gender_preference'] ?? 'no_preference',
            'sleep_schedule' => $validated['sleep_schedule'] ?? null,
            'study_habits' => $validated['study_habits'] ?? null,
            'cleanliness_level' => $validated['cleanliness_level'] ?? null,
            'noise_tolerance' => $noiseScale,
            'smoking_preference' => $validated['smoking_preference'] ?? null,
            'drinking_preference' => $validated['drinking_preference'] ?? null,
            'pets_preference' => $validated['pets_preference'] ?? null,
            'internet_usage' => $validated['internet_usage'] ?? null,
            'hobbies' => array_values($validated['hobbies'] ?? []),
            'preferred_amenity_ids' => $this->amenityIds($validated),
            'additional_notes' => $this->buildLifestyleNotes($validated, $preferredLocations, $amenityNames),
            'completed_at' => now(),
        ];

        $profile->fill($profileData)->save();

        if (Schema::hasTable('tenant_preferences')) {
            TenantPreference::updateOrCreate(
                ['user_id' => $tenant->id],
                [
                    'family_monthly_income' => $validated['family_income'] ?? null,
                    'monthly_allowance' => $validated['monthly_allowance'] ?? null,
                    'preferred_rental_budget_min' => $budgetMin,
                    'preferred_rental_budget_max' => $budgetMax,
                    'preferred_locations' => $preferredLocations,
                    'distance_from_school' => $validated['preferred_distance'] ?? null,
                    'room_type' => $validated['room_type'] ?? null,
                    'study_habits' => $validated['study_habits'] ?? null,
                    'sleeping_schedule' => $validated['sleep_schedule'] ?? null,
                    'cleanliness_level' => $validated['cleanliness_level'] ?? null,
                    'noise_tolerance' => $noiseScale,
                    'safety_preferences' => $this->safetyPreferences($validated, $amenityNames),
                    'amenities' => $amenityNames,
                    'lifestyle_notes' => $profileData['additional_notes'],
                ]
            );
        }

        app(BoardingHouseRecommendationService::class)->generateForUser($tenant->fresh());

        return redirect()
            ->route('user.preferences.index')
            ->with('success', 'Your preferences have been saved. New boarding house recommendations are ready.')
            ->with('status', 'tenant-match-profile-updated');
    }

    private function budgetRange(mixed $range, mixed $explicitMin = null, mixed $explicitMax = null): array
    {
        $min = $this->toFloat($explicitMin);
        $max = $this->toFloat($explicitMax);

        if ($min !== null || $max !== null) {
            return [$min, $max];
        }

        $text = strtolower((string) $range);
        $numbers = [];

        if (preg_match_all('/[0-9]+(?:,[0-9]{3})*(?:\.[0-9]+)?|[0-9]+(?:\.[0-9]+)?/', $text, $matches)) {
            $numbers = array_map(fn ($value) => (float) str_replace(',', '', $value), $matches[0]);
        }

        if ($numbers === []) {
            return [null, null];
        }

        if (str_contains($text, 'below') || str_contains($text, 'under') || str_contains($text, 'up to')) {
            return [null, $numbers[0]];
        }

        if (str_contains($text, 'above') || str_contains($text, 'from')) {
            return [$numbers[0], null];
        }

        return [min($numbers), count($numbers) > 1 ? max($numbers) : $numbers[0]];
    }

    private function listFromCsv(?string $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function amenityIds(array $validated): array
    {
        $ids = collect($validated['preferred_amenity_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0);

        $names = collect($validated['preferred_amenities'] ?? [])->map(fn ($name) => trim((string) $name))->filter();

        if ($names->isNotEmpty() && Schema::hasTable('amenities')) {
            $ids = $ids->merge(Amenity::query()->whereIn('name', $names)->pluck('id'));
        }

        return $ids->unique()->values()->all();
    }

    private function amenityNames(array $validated): array
    {
        $names = collect($validated['preferred_amenities'] ?? [])
            ->map(fn ($name) => trim((string) $name))
            ->filter();

        $ids = collect($validated['preferred_amenity_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0);

        if ($ids->isNotEmpty() && Schema::hasTable('amenities')) {
            $names = $names->merge(Amenity::query()->whereIn('id', $ids)->pluck('name'));
        }

        return $names->unique()->values()->all();
    }

    private function safetyPreferences(array $validated, array $amenityNames): array
    {
        $safety = collect([
            $validated['safety_preference'] ?? null,
            $validated['curfew_preference'] ?? null,
            ($validated['smoking_preference'] ?? null) === 'non_smoker_only' ? 'no smoking' : null,
        ]);

        $amenities = collect($amenityNames)
            ->filter(fn ($name) => preg_match('/cctv|security|gate|guard|lock|well.?lit/i', (string) $name));

        return $safety->merge($amenities)->filter()->unique()->values()->all();
    }

    private function buildLifestyleNotes(array $validated, array $preferredLocations, array $amenityNames): string
    {
        $lines = [];

        if ($preferredLocations !== []) {
            $lines[] = 'Preferred Location: '.implode(', ', $preferredLocations);
        }

        $lines[] = 'Room Type: '.($validated['room_type'] ?? 'any');
        $lines[] = 'Study Habits: '.($validated['study_habits'] ?? 'flexible');
        $lines[] = 'Sleeping Schedule: '.($validated['sleep_schedule'] ?? 'balanced');
        $lines[] = 'Cleanliness Level: '.($validated['cleanliness_level'] ?? 'not set');
        $lines[] = 'Noise Tolerance: '.($validated['noise_tolerance'] ?? 'not set');
        $lines[] = 'Safety Preference: '.($validated['safety_preference'] ?? 'standard');

        if ($amenityNames !== []) {
            $lines[] = 'Amenities: '.implode(', ', $amenityNames);
        }

        if (! empty($validated['additional_notes'])) {
            $lines[] = trim((string) $validated['additional_notes']);
        }

        return implode("\n", $lines);
    }

    private function normalizeNoiseToFivePointScale(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = (int) $value;

        if ($number <= 5) {
            return max(1, min(5, $number));
        }

        return max(1, min(5, (int) ceil($number / 20)));
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

    private function fieldOptions(): array
    {
        return [
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
            'smoking_preference' => [
                'non_smoker_only' => 'Non-smoker only',
                'smoker_ok' => 'Smoker okay',
                'outdoor_only' => 'Outdoor only',
            ],
            'drinking_preference' => [
                'no_alcohol' => 'No alcohol',
                'occasional_ok' => 'Occasional okay',
                'flexible' => 'Flexible',
            ],
            'pets_preference' => [
                'no_pets' => 'No pets',
                'cat_ok' => 'Cats okay',
                'dog_ok' => 'Dogs okay',
                'pet_friendly' => 'Pet friendly',
            ],
            'internet_usage' => [
                'light' => 'Light use',
                'moderate' => 'Moderate use',
                'heavy' => 'Heavy use',
                'remote_work' => 'Remote work / streaming',
            ],
            'hobbies' => [
                'reading' => 'Reading',
                'gaming' => 'Gaming',
                'sports' => 'Sports',
                'music' => 'Music',
                'cooking' => 'Cooking',
                'fitness' => 'Fitness',
                'movies' => 'Movies',
                'travel' => 'Travel',
                'art' => 'Art',
                'coding' => 'Coding',
            ],
        ];
    }
}
