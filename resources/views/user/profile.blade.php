<x-layouts.dashboard>
<x-user.shell>
@php
    $preference = $preference ?? $tenantPreference ?? new \App\Models\UserPreference();
    $profile = $profile ?? new \App\Models\TenantMatchProfile();

    $selectedLocations = collect(old('preferred_locations', $preference->preferred_locations ?? []))
        ->map(fn ($value) => (string) $value)
        ->filter()
        ->values();
    $selectedAmenities = collect(old('amenities', $preference->amenities ?? []))
        ->map(fn ($value) => (string) $value)
        ->filter()
        ->values();
    $selectedSafety = collect(old('safety_preferences', $preference->safety_preferences ?? []))
        ->map(fn ($value) => (string) $value)
        ->filter()
        ->values();
    $selectedHobbies = collect(old('hobbies', $profile->hobbies ?? []))
        ->map(fn ($value) => (string) $value)
        ->filter()
        ->values();

    $availableAmenities = collect($amenities ?? []);
    if ($availableAmenities->isEmpty()) {
        $availableAmenities = collect(['Wi-Fi', 'Study Table', 'Kitchen', 'Laundry', 'Parking', 'CCTV'])
            ->map(fn ($name, $index) => (object) ['id' => $index + 1, 'name' => $name]);
    }

    $dsscLocationOptions = collect([
        'DSSC Main Campus',
        'Matti',
        'Purok 3, Matti',
        'Mahayahay',
        'Tres de Mayo',
        'Poblacion / City Proper',
        'All nearby DSSC areas',
    ]);

    $availableLocations = $dsscLocationOptions
        ->merge(collect($barangays ?? [])->pluck('barangay_name'))
        ->filter()
        ->unique()
        ->values();

    $preferredDistance = old('distance_from_school', $preference->distance_from_school);
    $preferredDistanceValue = $preferredDistance !== null && $preferredDistance !== ''
        ? (string) (float) $preferredDistance
        : '';
    $preferredLandmark = old('preferred_landmark', $preference->preferred_landmark);
    $budgetMin = old('budget_min', $preference->preferred_rental_budget_min);
    $budgetMax = old('budget_max', $preference->preferred_rental_budget_max ?? $preference->preferred_rental_budget);
    $preferredBudget = old('preferred_rental_budget', $budgetMax ?: $budgetMin);
    $aiScore = max(0, min(100, (int) ($aiCompletion ?? 0)));

    $fieldClass = 'h-12 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:ring-blue-500/20';
    $textareaClass = 'min-h-28 w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-medium leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:ring-blue-500/20';

    $roomTypeOptions = [
        'any' => 'Any room type',
        'private' => 'Private room',
        'shared' => 'Shared room',
        'bedspace' => 'Bedspace',
        'studio' => 'Studio',
    ];

    $genderOptions = [
        'no_preference' => 'No preference',
        'female' => 'Female only',
        'male' => 'Male only',
        'mixed' => 'Mixed',
    ];

    $distanceOptions = [
        '0.9' => 'Less than 1 km',
        '2' => '1-2 km',
        '3' => '2-3 km',
        '5' => '3-5 km',
        '100' => 'Any distance',
    ];

    $aiFieldLabels = [
        'preferred_rental_budget' => 'Budget',
        'preferred_locations' => 'Location',
        'distance_from_school' => 'Distance',
        'room_type' => 'Room type',
        'study_habits' => 'Study habits',
        'sleeping_schedule' => 'Sleep schedule',
        'cleanliness_level' => 'Cleanliness',
        'amenities' => 'Amenities',
    ];

    $missingAiLabels = collect($missingAiFields ?? [])
        ->map(fn ($field) => $aiFieldLabels[$field] ?? \Illuminate\Support\Str::headline($field))
        ->values();

    $amenityIcon = function (string $name): string {
        $value = \Illuminate\Support\Str::of($name)->lower();

        return match (true) {
            $value->contains(['wi-fi', 'wifi', 'internet']) => 'wifi',
            $value->contains(['study', 'desk', 'table']) => 'book-open',
            $value->contains(['kitchen', 'cook']) => 'home',
            $value->contains(['parking', 'garage']) => 'truck',
            $value->contains(['cctv', 'security', 'safe', 'guard']) => 'shield-check',
            $value->contains(['aircon', 'air conditioning', 'electric', 'power']) => 'bolt',
            default => 'sparkles',
        };
    };

    $initialState = [
        'budgetMin' => (string) ($budgetMin ?? ''),
        'budgetMax' => (string) ($budgetMax ?? ''),
        'preferredBudget' => (string) ($preferredBudget ?? ''),
        'location' => (string) ($selectedLocations->first() ?? ''),
        'landmark' => (string) ($preferredLandmark ?? ''),
        'roomType' => (string) old('room_type', $preference->room_type ?? ''),
        'distance' => $preferredDistanceValue,
        'gender' => (string) old('gender_preference', $profile->gender_preference ?? 'no_preference'),
        'selectedAmenities' => $selectedAmenities->values()->all(),
    ];
@endphp

<div
    x-data="preferenceDesigner({
        initial: @js($initialState),
        roomTypeLabels: @js($roomTypeOptions),
        genderLabels: @js($genderOptions),
        distanceLabels: @js($distanceOptions)
    })"
    class="space-y-7"
>
    <x-user.page-header
        eyebrow="My Preferences"
        title="Design your ideal boarding house match"
        subtitle="A focused profile helps BoardMatch rank approved homes by rent, location, room setup, amenities, and everyday fit."
    />

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-800 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200">
            Please review the highlighted preference fields.
        </div>
    @endif

    <form x-ref="form" method="POST" action="{{ route('user.preferences.update') }}" class="space-y-7">
        @csrf
        @method('PUT')
        <input type="hidden" name="family_monthly_income" value="{{ old('family_monthly_income', $preference->family_monthly_income) }}">
        <input type="hidden" name="monthly_allowance" value="{{ old('monthly_allowance', $preference->monthly_allowance) }}">
        <input id="preferred_rental_budget" name="preferred_rental_budget" type="hidden" :value="budgetMax || budgetMin" value="{{ $preferredBudget }}">

        <div class="grid gap-7 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="min-w-0 space-y-7">
                <div class="grid gap-5 lg:grid-cols-[340px_minmax(0,1fr)]">
                    <section class="rounded-lg border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-950">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.16em] text-blue-600 dark:text-blue-300">AI Readiness Score</p>
                                <p class="mt-3 text-5xl font-black tracking-normal text-slate-950 dark:text-white">{{ $aiScore }}%</p>
                            </div>
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-400/20">
                                <x-user.preference-icon name="chart-bar" class="h-6 w-6" />
                            </span>
                        </div>

                        <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded-full bg-blue-600 transition-all duration-500 dark:bg-blue-400" style="width: {{ $aiScore }}%"></div>
                        </div>

                        <p class="mt-4 text-sm leading-6 text-slate-500 dark:text-slate-400">
                            @if ($aiScore >= 100)
                                Your preferences are ready for high-confidence AI matching.
                            @elseif ($aiScore >= 70)
                                You are close. A few more details can sharpen the match ranking.
                            @else
                                Add the essentials so AI can compare listings with more confidence.
                            @endif
                        </p>

                        @if ($missingAiLabels->isNotEmpty())
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($missingAiLabels as $label)
                                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-100 dark:bg-amber-400/10 dark:text-amber-200 dark:ring-amber-400/20">{{ $label }}</span>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <x-user.preference-panel
                        title="Why we need your preferences"
                        subtitle="BoardMatch uses your choices to compare each approved boarding house against the things that actually shape daily student life."
                        icon="information-circle"
                    >
                        <div class="grid gap-5 md:grid-cols-3">
                            @foreach ([
                                ['icon' => 'sparkles', 'title' => 'Smarter ranking', 'copy' => 'AI can prioritize listings that fit your budget, distance, and non-negotiables first.'],
                                ['icon' => 'shield-check', 'title' => 'Cleaner shortlist', 'copy' => 'Weak matches are pushed down before you spend time opening every listing.'],
                                ['icon' => 'heart', 'title' => 'Better fit signals', 'copy' => 'Amenity and lifestyle details help explain why a house is worth considering.'],
                            ] as $benefit)
                                <div class="flex gap-3">
                                    <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-900 dark:text-slate-300">
                                        <x-user.preference-icon :name="$benefit['icon']" class="h-4 w-4" />
                                    </span>
                                    <div>
                                        <p class="text-sm font-black text-slate-900 dark:text-white">{{ $benefit['title'] }}</p>
                                        <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $benefit['copy'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-user.preference-panel>
                </div>

                <x-user.preference-panel
                    title="Housing Preferences"
                    subtitle="Start with the essentials BoardMatch uses to filter and rank your housing options."
                    icon="home"
                >
                    <div class="grid gap-5 md:grid-cols-2 2xl:grid-cols-3">
                        <x-user.preference-field label="Budget Range" for="budget_min" required class="md:col-span-2 2xl:col-span-1">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="budget_min" class="mb-1.5 block text-xs font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500">Minimum</label>
                                    <input id="budget_min" name="budget_min" type="number" min="0" step="100" value="{{ $budgetMin }}" x-model="budgetMin" class="{{ $fieldClass }}" placeholder="2500">
                                    @error('budget_min')<p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="budget_max" class="mb-1.5 block text-xs font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500">Maximum</label>
                                    <input id="budget_max" name="budget_max" type="number" min="0" step="100" value="{{ $budgetMax }}" x-model="budgetMax" class="{{ $fieldClass }}" placeholder="5000">
                                    @error('budget_max')<p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
                                    @error('preferred_rental_budget')<p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </x-user.preference-field>

                        <x-user.preference-field label="Preferred Location" for="preferred_location" required>
                            <select id="preferred_location" name="preferred_locations[]" x-model="location" class="{{ $fieldClass }}">
                                <option value="">Select a DSSC-area location</option>
                                @foreach ($availableLocations as $location)
                                    <option value="{{ $location }}" @selected($selectedLocations->contains($location))>{{ $location }}</option>
                                @endforeach
                                @foreach ($selectedLocations->filter(fn ($location) => ! $availableLocations->contains($location)) as $location)
                                    <option value="{{ $location }}" selected>{{ $location }}</option>
                                @endforeach
                            </select>
                            @error('preferred_locations')<p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
                            @error('preferred_locations.*')<p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
                        </x-user.preference-field>

                        <x-user.preference-field label="Landmark/School" for="preferred_landmark">
                            <select
                                id="preferred_landmark"
                                name="preferred_landmark"
                                x-model="landmark"
                                x-on:change="if (landmark === 'DSSC Main Campus' && !location) location = 'All nearby DSSC areas'"
                                class="{{ $fieldClass }}"
                            >
                                <option value="">Select landmark or school</option>
                                @foreach (['DSSC Main Campus', 'Digos City Proper', 'Other'] as $landmark)
                                    <option value="{{ $landmark }}" @selected($preferredLandmark === $landmark)>{{ $landmark }}</option>
                                @endforeach
                            </select>
                            <p x-show="landmark === 'DSSC Main Campus'" x-cloak class="text-xs leading-5 text-blue-700 dark:text-blue-300">
                                Suggested nearby areas include Matti, Mahayahay, Tres de Mayo, and City Proper.
                            </p>
                            @error('preferred_landmark')<p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
                        </x-user.preference-field>

                        <x-user.preference-field label="Room Type" for="room_type" required>
                            <select id="room_type" name="room_type" x-model="roomType" class="{{ $fieldClass }}">
                                <option value="">Select room type</option>
                                @foreach ($roomTypeOptions as $value => $text)
                                    <option value="{{ $value }}" @selected(old('room_type', $preference->room_type) === $value)>{{ $text }}</option>
                                @endforeach
                            </select>
                            @error('room_type')<p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
                        </x-user.preference-field>

                        <x-user.preference-field label="Maximum Distance" for="distance_from_school" required>
                            <select id="distance_from_school" name="distance_from_school" x-model="distance" class="{{ $fieldClass }}">
                                <option value="">Select maximum distance</option>
                                @if ($preferredDistance && ! in_array((float) $preferredDistance, [0.9, 2.0, 3.0, 5.0, 100.0], true))
                                    <option value="{{ (float) $preferredDistance }}" selected>{{ number_format((float) $preferredDistance, 1) }} km</option>
                                @endif
                                @foreach ($distanceOptions as $value => $text)
                                    <option value="{{ $value }}" @selected((float) $preferredDistance === (float) $value)>{{ $text }}</option>
                                @endforeach
                            </select>
                            @error('distance_from_school')<p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
                        </x-user.preference-field>

                        <x-user.preference-field label="Gender Preference" for="gender_preference">
                            <select id="gender_preference" name="gender_preference" x-model="gender" class="{{ $fieldClass }}">
                                @foreach ($genderOptions as $value => $text)
                                    <option value="{{ $value }}" @selected(old('gender_preference', $profile->gender_preference ?? 'no_preference') === $value)>{{ $text }}</option>
                                @endforeach
                            </select>
                            @error('gender_preference')<p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
                        </x-user.preference-field>
                    </div>
                </x-user.preference-panel>

                <x-user.preference-panel
                    title="Amenities"
                    subtitle="Choose the must-have comforts and safety features that make a place practical for you."
                    icon="sparkles"
                >
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                        @foreach ($availableAmenities as $amenity)
                            <x-user.amenity-card
                                :name="$amenity->name"
                                :icon="$amenityIcon($amenity->name)"
                                :selected="$selectedAmenities->contains($amenity->name)"
                            />
                        @endforeach
                    </div>
                    @error('amenities')<p class="mt-3 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
                    @error('amenities.*')<p class="mt-3 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
                </x-user.preference-panel>

                <x-user.preference-panel
                    title="Match Tuning"
                    subtitle="These lifestyle signals help AI compare house rules, study needs, and daily routines without adding clutter to your summary."
                    icon="light-bulb"
                >
                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                        <x-user.preference-field label="Study Habits" for="study_habits" required>
                            <select id="study_habits" name="study_habits" class="{{ $fieldClass }}">
                                <option value="">Select study habit</option>
                                @foreach (['quiet_focus' => 'Quiet focus', 'group_study' => 'Group study', 'flexible' => 'Flexible'] as $value => $text)
                                    <option value="{{ $value }}" @selected(old('study_habits', $preference->study_habits) === $value)>{{ $text }}</option>
                                @endforeach
                            </select>
                            @error('study_habits')<p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
                        </x-user.preference-field>

                        <x-user.preference-field label="Sleep Schedule" for="sleeping_schedule" required>
                            <select id="sleeping_schedule" name="sleeping_schedule" class="{{ $fieldClass }}">
                                <option value="">Select schedule</option>
                                @foreach (['early_bird' => 'Early bird', 'balanced' => 'Balanced', 'night_owl' => 'Night owl'] as $value => $text)
                                    <option value="{{ $value }}" @selected(old('sleeping_schedule', $preference->sleeping_schedule) === $value)>{{ $text }}</option>
                                @endforeach
                            </select>
                            @error('sleeping_schedule')<p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
                        </x-user.preference-field>

                        <x-user.preference-field label="Cleanliness" for="cleanliness_level" required>
                            <select id="cleanliness_level" name="cleanliness_level" class="{{ $fieldClass }}">
                                <option value="">Select level</option>
                                @foreach ([1 => 'Flexible', 2 => 'Basic', 3 => 'Moderate', 4 => 'Clean', 5 => 'Very clean'] as $value => $text)
                                    <option value="{{ $value }}" @selected((int) old('cleanliness_level', $preference->cleanliness_level) === $value)>{{ $value }} - {{ $text }}</option>
                                @endforeach
                            </select>
                            @error('cleanliness_level')<p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
                        </x-user.preference-field>

                        <x-user.preference-field label="Noise Tolerance" for="noise_tolerance" required>
                            <select id="noise_tolerance" name="noise_tolerance" class="{{ $fieldClass }}">
                                <option value="">Select tolerance</option>
                                @foreach ([1 => 'Very quiet', 2 => 'Quiet', 3 => 'Moderate', 4 => 'Social', 5 => 'High tolerance'] as $value => $text)
                                    <option value="{{ $value }}" @selected((int) old('noise_tolerance', $preference->noise_tolerance) === $value)>{{ $value }} - {{ $text }}</option>
                                @endforeach
                            </select>
                            @error('noise_tolerance')<p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
                        </x-user.preference-field>
                    </div>

                    <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        <x-user.preference-field label="Smoking" for="smoking_preference">
                            <select id="smoking_preference" name="smoking_preference" class="{{ $fieldClass }}">
                                @foreach (['non_smoker_only' => 'Non-smoking only', 'outdoor_only' => 'Outdoor only', 'smoker_ok' => 'Smoking is acceptable'] as $value => $text)
                                    <option value="{{ $value }}" @selected(old('smoking_preference', $profile->smoking_preference ?? 'non_smoker_only') === $value)>{{ $text }}</option>
                                @endforeach
                            </select>
                        </x-user.preference-field>

                        <x-user.preference-field label="Drinking" for="drinking_preference">
                            <select id="drinking_preference" name="drinking_preference" class="{{ $fieldClass }}">
                                @foreach (['no_alcohol' => 'No alcohol', 'occasional_ok' => 'Occasional is acceptable', 'flexible' => 'Flexible'] as $value => $text)
                                    <option value="{{ $value }}" @selected(old('drinking_preference', $profile->drinking_preference ?? 'occasional_ok') === $value)>{{ $text }}</option>
                                @endforeach
                            </select>
                        </x-user.preference-field>

                        <x-user.preference-field label="Internet Usage" for="internet_usage">
                            <select id="internet_usage" name="internet_usage" class="{{ $fieldClass }}">
                                @foreach (['light' => 'Light', 'moderate' => 'Moderate', 'heavy' => 'Heavy / streaming', 'remote_work' => 'Online classes / remote work'] as $value => $text)
                                    <option value="{{ $value }}" @selected(old('internet_usage', $profile->internet_usage ?? 'moderate') === $value)>{{ $text }}</option>
                                @endforeach
                            </select>
                        </x-user.preference-field>

                        <x-user.preference-field label="Safety" for="safety_preference">
                            <select id="safety_preference" name="safety_preference" class="{{ $fieldClass }}">
                                @foreach (['standard' => 'Standard', 'high' => 'High', 'very_high' => 'Very high'] as $value => $text)
                                    <option value="{{ $value }}" @selected($selectedSafety->contains($value))>{{ $text }}</option>
                                @endforeach
                            </select>
                        </x-user.preference-field>

                        <x-user.preference-field label="Hobbies" class="md:col-span-2">
                            <div class="flex flex-wrap gap-2">
                                @foreach (['reading', 'coding', 'music', 'sports', 'gaming', 'cooking', 'arts', 'travel'] as $hobby)
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="hobbies[]" value="{{ $hobby }}" @checked($selectedHobbies->contains($hobby)) class="peer sr-only">
                                        <span class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 transition peer-checked:border-blue-600 peer-checked:bg-blue-600 peer-checked:text-white dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:peer-checked:border-blue-400 dark:peer-checked:bg-blue-400 dark:peer-checked:text-slate-950">{{ \Illuminate\Support\Str::headline($hobby) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </x-user.preference-field>

                        <x-user.preference-field label="Additional Notes" for="lifestyle_notes" class="md:col-span-2 xl:col-span-3">
                            <textarea id="lifestyle_notes" name="lifestyle_notes" rows="4" maxlength="1500" class="{{ $textareaClass }}" placeholder="Add curfew, accessibility, study-space, visitor, or house-rule preferences.">{{ old('lifestyle_notes', $preference->lifestyle_notes) }}</textarea>
                            @error('lifestyle_notes')<p class="mt-1 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
                        </x-user.preference-field>
                    </div>
                </x-user.preference-panel>

                <div class="flex flex-col gap-3 rounded-lg border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-950 sm:flex-row sm:items-center sm:justify-between">
                    <button type="button" x-on:click="resetPreferences($refs.form)" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900">
                        <x-user.preference-icon name="arrow-path" class="h-4 w-4" />
                        Reset
                    </button>

                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('user.dashboard') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900">
                            <x-user.preference-icon name="x-mark" class="h-4 w-4" />
                            Cancel
                        </a>
                        <button type="submit" name="intent" value="save" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-black text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:bg-blue-500 dark:text-white dark:shadow-blue-500/20 dark:hover:bg-blue-400 dark:focus:ring-blue-500/20">
                            <x-user.preference-icon name="check-circle" class="h-4 w-4" />
                            Save Preferences
                        </button>
                    </div>
                </div>
            </div>

            <aside class="space-y-5 xl:sticky xl:top-6 xl:self-start">
                <section class="rounded-lg border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <x-user.preference-icon name="clipboard-check" class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="text-base font-black text-slate-950 dark:text-white">Preference Summary</h2>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Updates as you edit</p>
                        </div>
                    </div>

                    <dl class="mt-5 divide-y divide-slate-100 dark:divide-slate-800">
                        <div class="grid gap-1 py-3">
                            <dt class="text-xs font-black uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Budget</dt>
                            <dd class="text-sm font-black text-slate-900 dark:text-white" x-text="budgetLabel"></dd>
                        </div>
                        <div class="grid gap-1 py-3">
                            <dt class="text-xs font-black uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Location</dt>
                            <dd class="text-sm font-black text-slate-900 dark:text-white" x-text="location || 'Not set'"></dd>
                        </div>
                        <div class="grid gap-1 py-3">
                            <dt class="text-xs font-black uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Room Type</dt>
                            <dd class="text-sm font-black text-slate-900 dark:text-white" x-text="roomTypeLabels[roomType] || 'Not set'"></dd>
                        </div>
                        <div class="grid gap-1 py-3">
                            <dt class="text-xs font-black uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Gender</dt>
                            <dd class="text-sm font-black text-slate-900 dark:text-white" x-text="genderLabels[gender] || 'No preference'"></dd>
                        </div>
                    </dl>

                    <div class="mt-4">
                        <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Selected Amenities</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <template x-if="selectedAmenities.length === 0">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500 dark:bg-slate-900 dark:text-slate-400">None selected</span>
                            </template>
                            <template x-for="amenity in selectedAmenities" :key="amenity">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700 ring-1 ring-blue-100 dark:bg-blue-500/10 dark:text-blue-200 dark:ring-blue-400/20" x-text="amenity"></span>
                            </template>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-blue-100 bg-blue-50 p-5 shadow-sm shadow-blue-900/5 dark:border-blue-400/20 dark:bg-blue-500/10">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-blue-600 shadow-sm dark:bg-slate-950 dark:text-blue-300">
                            <x-user.preference-icon name="light-bulb" class="h-5 w-5" />
                        </span>
                        <h2 class="text-base font-black text-slate-950 dark:text-white">AI Tips</h2>
                    </div>
                    <ul class="mt-5 space-y-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        <li class="flex gap-3">
                            <x-user.preference-icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0 text-blue-600 dark:text-blue-300" />
                            Set a real rent ceiling so strong listings are not hidden by unrealistic budgets.
                        </li>
                        <li class="flex gap-3">
                            <x-user.preference-icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0 text-blue-600 dark:text-blue-300" />
                            Pick must-have amenities only; too many nice-to-haves can narrow your shortlist.
                        </li>
                        <li class="flex gap-3">
                            <x-user.preference-icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0 text-blue-600 dark:text-blue-300" />
                            Complete match tuning to improve AI explanations and ranking confidence.
                        </li>
                    </ul>
                </section>
            </aside>
        </div>
    </form>
</div>

<script>
    window.preferenceDesigner = function preferenceDesigner(config) {
        const snapshot = JSON.parse(JSON.stringify(config.initial || {}));

        return {
            budgetMin: snapshot.budgetMin || '',
            budgetMax: snapshot.budgetMax || '',
            preferredBudget: snapshot.preferredBudget || '',
            location: snapshot.location || '',
            landmark: snapshot.landmark || '',
            roomType: snapshot.roomType || '',
            distance: snapshot.distance || '',
            gender: snapshot.gender || 'no_preference',
            selectedAmenities: Array.isArray(snapshot.selectedAmenities) ? snapshot.selectedAmenities : [],
            roomTypeLabels: config.roomTypeLabels || {},
            genderLabels: config.genderLabels || {},
            distanceLabels: config.distanceLabels || {},
            initial: snapshot,

            get budgetLabel() {
                const min = this.formatMoney(this.budgetMin);
                const max = this.formatMoney(this.budgetMax);

                if (min && max) {
                    return `${min} - ${max}`;
                }

                if (max) {
                    return `Up to ${max}`;
                }

                if (min) {
                    return `From ${min}`;
                }

                return 'Not set';
            },

            formatMoney(value) {
                const amount = Number(value);

                if (!Number.isFinite(amount) || amount <= 0) {
                    return '';
                }

                return `PHP ${new Intl.NumberFormat('en-PH', { maximumFractionDigits: 0 }).format(amount)}`;
            },

            resetPreferences(form) {
                if (form) {
                    form.reset();
                }

                this.budgetMin = this.initial.budgetMin || '';
                this.budgetMax = this.initial.budgetMax || '';
                this.preferredBudget = this.initial.preferredBudget || '';
                this.location = this.initial.location || '';
                this.landmark = this.initial.landmark || '';
                this.roomType = this.initial.roomType || '';
                this.distance = this.initial.distance || '';
                this.gender = this.initial.gender || 'no_preference';
                this.selectedAmenities = Array.isArray(this.initial.selectedAmenities)
                    ? [...this.initial.selectedAmenities]
                    : [];
            },
        };
    };
</script>
</x-user.shell>
</x-layouts.dashboard>
