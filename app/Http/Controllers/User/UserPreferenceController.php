<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserPreferenceUpdateRequest;
use App\Models\Amenity;
use App\Models\Barangay;
use App\Models\TenantMatchProfile;
use App\Models\UserPreference;
use App\Services\BoardingHouseRecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class UserPreferenceController extends Controller
{
    public function __construct(
        private readonly BoardingHouseRecommendationService $recommendationService,
    ) {}

    public function edit(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $user->isUser(), 403);
        $preference = Schema::hasTable('user_preferences')
            ? UserPreference::firstOrNew(['user_id' => $user->id])
            : new UserPreference(['user_id' => $user->id]);

        $profile = Schema::hasTable('tenant_match_profiles')
            ? TenantMatchProfile::firstOrNew(['user_id' => $user->id])
            : new TenantMatchProfile;

        $profileCompletion = $preference->calculateProfileCompletion($user);

        if ($preference->exists && (int) $preference->profile_completion !== $profileCompletion) {
            $preference->forceFill(['profile_completion' => $profileCompletion])->save();
        }

        return view('user.profile', [
            'tenant' => $user,
            'profile' => $profile,
            'preference' => $preference,
            'tenantPreference' => $preference,
            'amenities' => Schema::hasTable('amenities')
                ? Amenity::query()->orderBy('name')->get(['id', 'name'])
                : collect(),
            'barangays' => Schema::hasTable('barangays')
                ? Barangay::query()->orderBy('barangay_name')->get(['id', 'barangay_name'])
                : collect(),
            'matchProfilesAvailable' => Schema::hasTable('tenant_match_profiles'),
            'preferencesAvailable' => Schema::hasTable('user_preferences'),
            'profileCompletion' => $profileCompletion,
            'completionSections' => $preference->completionSections($user),
            'aiReady' => $preference->isAiReady(),
            'aiCompletion' => $preference->aiCompletionPercentage(),
            'missingAiFields' => $preference->missingAiFields(),
        ]);
    }

    public function store(UserPreferenceUpdateRequest $request): RedirectResponse
    {
        return $this->update($request);
    }

    public function update(UserPreferenceUpdateRequest $request): RedirectResponse
    {
        if (! Schema::hasTable('user_preferences')) {
            return back()->with('error', 'User preferences are not available yet.');
        }

        $user = $request->user();
        $validated = $request->validated();
        $locations = $this->locations($validated);
        $amenities = $this->amenityNames($validated);
        [$budgetMin, $budgetMax, $budget] = $this->budgetValues($validated);
        $safetyPreferences = $this->safetyPreferences($validated);
        $sleepingSchedule = $validated['sleeping_schedule'] ?? $validated['sleep_schedule'] ?? null;
        $distance = $validated['distance_from_school'] ?? $validated['preferred_distance'] ?? null;
        $lifestyleNotes = trim((string) ($validated['lifestyle_notes'] ?? $validated['additional_notes'] ?? ''));
        $noiseTolerance = isset($validated['noise_tolerance'])
            ? max(0, min(100, (int) $validated['noise_tolerance']))
            : null;

        DB::transaction(function () use (
            $user,
            $validated,
            $locations,
            $amenities,
            $budgetMin,
            $budgetMax,
            $budget,
            $safetyPreferences,
            $sleepingSchedule,
            $distance,
            $lifestyleNotes,
            $noiseTolerance
        ): void {
            $preference = UserPreference::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'family_monthly_income' => $validated['family_monthly_income'] ?? $validated['family_income'] ?? null,
                    'monthly_allowance' => $validated['monthly_allowance'] ?? null,
                    'preferred_rental_budget' => $budget,
                    'preferred_rental_budget_min' => $budgetMin,
                    'preferred_rental_budget_max' => $budgetMax,
                    'preferred_locations' => $locations,
                    'preferred_landmark' => $validated['preferred_landmark'] ?? null,
                    'distance_from_school' => $distance,
                    'room_type' => $validated['room_type'] ?? null,
                    'study_habits' => $validated['study_habits'] ?? null,
                    'sleeping_schedule' => $sleepingSchedule,
                    'cleanliness_level' => $validated['cleanliness_level'] ?? null,
                    'noise_tolerance' => $noiseTolerance,
                    'safety_preferences' => $safetyPreferences,
                    'amenities' => $amenities,
                    'lifestyle_notes' => $lifestyleNotes !== '' ? $lifestyleNotes : null,
                ]
            );

            $preference->profile_completion = $preference->calculateProfileCompletion($user);
            $preference->save();

            $this->syncTenantMatchProfile($user->id, $preference, $validated);
        });

        $this->recommendationService->generateForUser($user->fresh());

        if (($validated['intent'] ?? 'save') === 'generate') {
            return redirect()
                ->route('user.boarding-houses.index', ['tab' => 'recommended'])
                ->with('success', 'Preferences saved. Your AI recommendations have been generated.');
        }

        if ($request->input('return_to') === 'matchmaking') {
            return redirect()
                ->route('user.matchmaking.index')
                ->with('success', 'Preferences saved. Your matches have been refreshed.');
        }

        return redirect()
            ->route('user.preferences.index')
            ->with('success', 'Preferences saved successfully. Your recommendations have been refreshed.')
            ->with('status', 'tenant-match-profile-updated');
    }

    private function syncTenantMatchProfile(int $userId, UserPreference $preference, array $validated): void
    {
        if (! Schema::hasTable('tenant_match_profiles')) {
            return;
        }

        $amenityIds = collect($validated['preferred_amenity_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($amenityIds->isEmpty() && Schema::hasTable('amenities')) {
            $amenityIds = Amenity::query()
                ->whereIn('name', $preference->amenities ?? [])
                ->orderBy('id')
                ->pluck('id');
        }

        $profile = TenantMatchProfile::firstOrNew(['user_id' => $userId]);
        $profile->fill([
            'budget_min' => $preference->preferred_rental_budget_min,
            'budget_max' => $preference->preferred_rental_budget_max ?: $preference->preferred_rental_budget,
            'gender_preference' => $validated['gender_preference'] ?? $profile->gender_preference ?? 'no_preference',
            'sleep_schedule' => $preference->sleeping_schedule,
            'study_habits' => $preference->study_habits,
            'cleanliness_level' => $preference->cleanliness_level,
            'noise_tolerance' => $this->noiseOnFivePointScale($preference->noise_tolerance),
            'smoking_preference' => $validated['smoking_preference'] ?? $profile->smoking_preference ?? 'non_smoker_only',
            'drinking_preference' => $validated['drinking_preference'] ?? $profile->drinking_preference ?? 'occasional_ok',
            'pets_preference' => $validated['pets_preference'] ?? $profile->pets_preference ?? 'no_pets',
            'internet_usage' => $validated['internet_usage'] ?? $profile->internet_usage ?? 'moderate',
            'social_style' => $validated['social_style'] ?? $profile->social_style ?? 'balanced',
            'cooking_habit' => $validated['cooking_habit'] ?? $profile->cooking_habit ?? 'occasional_cooking',
            'work_schedule' => $validated['work_schedule'] ?? $profile->work_schedule ?? 'flexible_schedule',
            'guest_preference' => $validated['guest_preference'] ?? $profile->guest_preference ?? 'occasional_guests',
            'sharing_style' => $validated['sharing_style'] ?? $profile->sharing_style ?? 'ask_first',
            'hobbies' => array_values($validated['hobbies'] ?? $profile->hobbies ?? []),
            'preferred_amenity_ids' => $amenityIds->all(),
            'preferred_roommate_traits' => array_values(array_filter([
                $validated['social_style'] ?? $profile->social_style ?? null,
                $validated['cooking_habit'] ?? $profile->cooking_habit ?? null,
                $validated['work_schedule'] ?? $profile->work_schedule ?? null,
                $validated['guest_preference'] ?? $profile->guest_preference ?? null,
                $validated['sharing_style'] ?? $profile->sharing_style ?? null,
            ])),
            'additional_notes' => $this->profileNotes($preference),
            'completed_at' => $preference->isAiReady() ? now() : null,
        ]);
        $profile->save();
    }

    private function locations(array $validated): array
    {
        $locations = collect($validated['preferred_locations'] ?? []);

        if ($locations->isEmpty() && ! empty($validated['preferred_location'])) {
            $locations = collect(explode(',', (string) $validated['preferred_location']));
        }

        return $locations
            ->map(fn (mixed $location) => trim((string) $location))
            ->filter()
            ->unique(fn (string $location) => strtolower($location))
            ->values()
            ->all();
    }

    private function amenityNames(array $validated): array
    {
        $names = collect($validated['amenities'] ?? $validated['preferred_amenities'] ?? [])
            ->map(fn (mixed $name) => trim((string) $name))
            ->filter();

        $ids = collect($validated['preferred_amenity_ids'] ?? [])
            ->map(fn (mixed $id) => (int) $id)
            ->filter();

        if ($ids->isNotEmpty() && Schema::hasTable('amenities')) {
            $names = $names->merge(Amenity::query()->whereIn('id', $ids)->pluck('name'));
        }

        return $names->unique(fn (string $name) => strtolower($name))->values()->all();
    }

    private function safetyPreferences(array $validated): array
    {
        return collect($validated['safety_preferences'] ?? [])
            ->when(
                empty($validated['safety_preferences']) && ! empty($validated['safety_preference']),
                fn ($items) => $items->push($validated['safety_preference'])
            )
            ->when(
                ($validated['smoking_preference'] ?? null) === 'non_smoker_only',
                fn ($items) => $items->push('no smoking')
            )
            ->when(
                ! empty($validated['curfew_preference']),
                fn ($items) => $items->push($validated['curfew_preference'])
            )
            ->map(fn (mixed $item) => trim((string) $item))
            ->filter()
            ->unique(fn (string $item) => strtolower($item))
            ->values()
            ->all();
    }

    private function budgetValues(array $validated): array
    {
        $min = $this->toFloat($validated['budget_min'] ?? null);
        $max = $this->toFloat($validated['budget_max'] ?? null);
        $budget = $this->toFloat($validated['preferred_rental_budget'] ?? null);

        if ($budget === null && ! empty($validated['rental_budget'])) {
            [$parsedMin, $parsedMax] = $this->parseBudgetRange((string) $validated['rental_budget']);
            $min ??= $parsedMin;
            $max ??= $parsedMax;
        }

        $budget ??= $max ?? $min;
        $max ??= $budget;

        return [$min, $max, $budget];
    }

    private function parseBudgetRange(string $value): array
    {
        preg_match_all('/\d+(?:,\d{3})*(?:\.\d+)?/', $value, $matches);
        $numbers = collect($matches[0] ?? [])
            ->map(fn (string $number) => (float) str_replace(',', '', $number))
            ->values();

        if ($numbers->isEmpty()) {
            return [null, null];
        }

        $lower = strtolower($value);

        if (str_contains($lower, 'below') || str_contains($lower, 'under') || str_contains($lower, 'up to')) {
            return [null, $numbers->first()];
        }

        if (str_contains($lower, 'above') || str_contains($lower, 'from')) {
            return [$numbers->first(), null];
        }

        return [$numbers->min(), $numbers->max()];
    }

    private function profileNotes(UserPreference $preference): string
    {
        $lines = [];

        if ($preference->preferred_locations) {
            $lines[] = 'Preferred Location: '.implode(', ', $preference->preferred_locations);
        }

        if ($preference->preferred_landmark) {
            $lines[] = 'Preferred Landmark: '.$preference->preferred_landmark;
        }

        if ($preference->room_type) {
            $lines[] = 'Room Type: '.$preference->room_type;
        }

        if ($preference->safety_preferences) {
            $lines[] = 'Safety Preferences: '.implode(', ', $preference->safety_preferences);
        }

        if ($preference->amenities) {
            $lines[] = 'Amenities: '.implode(', ', $preference->amenities);
        }

        if ($preference->lifestyle_notes) {
            $lines[] = $preference->lifestyle_notes;
        }

        return implode("\n", $lines);
    }

    private function noiseOnFivePointScale(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $value = (int) $value;

        return $value <= 5 ? max(1, $value) : max(1, min(5, (int) ceil($value / 20)));
    }

    private function toFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
