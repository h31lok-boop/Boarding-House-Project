<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantMatchProfileUpdateRequest;
use App\Models\TenantMatchProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantMatchProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isTenant(), 403);

        $profile = TenantMatchProfile::firstOrCreate(
            ['user_id' => $tenant->id],
            ['gender_preference' => 'no_preference']
        );

        return view('tenant.match-profile', [
            'tenant' => $tenant,
            'profile' => $profile,
            'fieldOptions' => $this->fieldOptions(),
        ]);
    }

    public function update(TenantMatchProfileUpdateRequest $request): RedirectResponse
    {
        $tenant = $request->user();

        $profile = TenantMatchProfile::firstOrCreate(
            ['user_id' => $tenant->id],
            ['gender_preference' => 'no_preference']
        );

        $validated = $request->validated();
        $validated['hobbies'] = array_values($validated['hobbies'] ?? []);
        $validated['completed_at'] = now();

        $profile->fill($validated)->save();

        return redirect()
            ->route('tenant.match-profile.edit')
            ->with('status', 'tenant-match-profile-updated');
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
