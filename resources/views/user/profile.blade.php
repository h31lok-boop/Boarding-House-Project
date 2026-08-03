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

    $fieldClass = 'h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-blue-400 focus:ring-4 focus:ring-blue-100';
    $labelClass = 'mb-1.5 block text-xs font-semibold text-slate-600';
    $cardClass = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/50 sm:p-6';
    $sectionTitleClass = 'text-sm font-bold text-slate-900';
    $sectionHintClass = 'mt-0.5 text-xs text-slate-400';

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

    $lifestyleChipGroups = [
        [
            'name' => 'study_habits',
            'label' => 'Study habits',
            'value' => (string) old('study_habits', $preference->study_habits ?? 'quiet_focus'),
            'options' => ['quiet_focus' => 'Quiet focus', 'flexible' => 'Flexible', 'group_study' => 'Group study'],
        ],
        [
            'name' => 'sleeping_schedule',
            'label' => 'Sleep schedule',
            'value' => (string) old('sleeping_schedule', $preference->sleeping_schedule ?? 'balanced'),
            'options' => ['early_bird' => 'Early bird', 'balanced' => 'Balanced', 'night_owl' => 'Night owl'],
        ],
        [
            'name' => 'smoking_preference',
            'label' => 'Smoking',
            'value' => (string) old('smoking_preference', $profile->smoking_preference ?? 'non_smoker_only'),
            'options' => ['non_smoker_only' => 'Non-smokers only', 'outdoor_only' => 'Outdoor only', 'smoker_ok' => 'Smoker OK'],
        ],
        [
            'name' => 'drinking_preference',
            'label' => 'Drinking',
            'value' => (string) old('drinking_preference', $profile->drinking_preference ?? 'occasional_ok'),
            'options' => ['no_alcohol' => 'No alcohol', 'occasional_ok' => 'Occasional OK', 'flexible' => 'Flexible'],
        ],
        [
            'name' => 'pets_preference',
            'label' => 'Pets',
            'value' => (string) old('pets_preference', $profile->pets_preference ?? 'no_pets'),
            'options' => ['no_pets' => 'No pets', 'cat_ok' => 'Cats OK', 'dog_ok' => 'Dogs OK', 'pet_friendly' => 'Pet friendly'],
        ],
        [
            'name' => 'internet_usage',
            'label' => 'Internet usage',
            'value' => (string) old('internet_usage', $profile->internet_usage ?? 'moderate'),
            'options' => ['light' => 'Light', 'moderate' => 'Moderate', 'heavy' => 'Heavy', 'remote_work' => 'Remote work'],
        ],
        [
            'name' => 'social_style',
            'label' => 'Roommate social style',
            'value' => (string) old('social_style', $profile->social_style ?? 'balanced'),
            'options' => ['talkative' => 'Talkative', 'balanced' => 'Balanced', 'introvert' => 'Introvert / quiet'],
        ],
        [
            'name' => 'cooking_habit',
            'label' => 'Cooking habits',
            'value' => (string) old('cooking_habit', $profile->cooking_habit ?? 'occasional_cooking'),
            'options' => ['enjoys_cooking' => 'Enjoys cooking', 'occasional_cooking' => 'Sometimes cooks', 'rarely_cooks' => 'Rarely cooks'],
        ],
        [
            'name' => 'work_schedule',
            'label' => 'Work / study schedule',
            'value' => (string) old('work_schedule', $profile->work_schedule ?? 'flexible_schedule'),
            'options' => ['day_schedule' => 'Day schedule', 'flexible_schedule' => 'Flexible', 'night_shift' => 'Night shift / night owl'],
        ],
        [
            'name' => 'guest_preference',
            'label' => 'Guests in the room',
            'value' => (string) old('guest_preference', $profile->guest_preference ?? 'occasional_guests'),
            'options' => ['no_guests' => 'No guests', 'occasional_guests' => 'Occasional guests', 'guests_welcome' => 'Guests welcome'],
        ],
        [
            'name' => 'sharing_style',
            'label' => 'Shared items and space',
            'value' => (string) old('sharing_style', $profile->sharing_style ?? 'ask_first'),
            'options' => ['shares_easily' => 'Shares easily', 'ask_first' => 'Ask first', 'personal_space' => 'Personal space'],
        ],
    ];

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
        'lifestyle' => collect($lifestyleChipGroups)->mapWithKeys(fn ($group) => [$group['name'] => $group['value']])->all(),
        'lifestyleNotes' => (string) old('lifestyle_notes', $preference->lifestyle_notes ?? ''),
    ];

    $hiddenCleanlinessLevel = old('cleanliness_level', $preference->cleanliness_level ?? 3);
    $hiddenNoiseTolerance = old('noise_tolerance', $preference->noise_tolerance ?? 40);
@endphp

<div
    x-data="preferenceDesigner({
        initial: @js($initialState),
        roomTypeLabels: @js($roomTypeOptions),
        genderLabels: @js($genderOptions),
        distanceLabels: @js($distanceOptions)
    })"
    class="mx-auto w-full max-w-5xl space-y-4"
>
    {{-- Header --}}
    <header>
        <h1 class="text-xl font-bold tracking-tight text-slate-900">My Preferences</h1>
        <p class="mt-0.5 text-[13px] text-slate-500">Keep your housing criteria in one place for smarter matching.</p>
    </header>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
            Please review the highlighted preference fields.
        </div>
    @endif

    <form x-ref="form" method="POST" action="{{ route('user.preferences.update') }}">
        @csrf
        @method('PUT')

        @if (request('return_to') === 'matchmaking')
            <input type="hidden" name="return_to" value="matchmaking">
        @endif

        <input type="hidden" name="family_monthly_income" value="{{ old('family_monthly_income', $preference->family_monthly_income) }}">
        <input type="hidden" name="monthly_allowance" value="{{ old('monthly_allowance', $preference->monthly_allowance) }}">
        <input id="preferred_rental_budget" name="preferred_rental_budget" type="hidden" :value="budgetMax || budgetMin" value="{{ $preferredBudget }}">
        <input type="hidden" name="cleanliness_level" value="{{ $hiddenCleanlinessLevel }}">
        <input type="hidden" name="noise_tolerance" value="{{ $hiddenNoiseTolerance }}">
        @foreach ($selectedSafety as $item)
            <input type="hidden" name="safety_preferences[]" value="{{ $item }}">
        @endforeach
        @foreach ($selectedHobbies as $hobby)
            <input type="hidden" name="hobbies[]" value="{{ $hobby }}">
        @endforeach

        <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,1fr)_290px]">
            <div class="space-y-4">
                {{-- Budget --}}
                <section class="{{ $cardClass }}">
                    <h2 class="{{ $sectionTitleClass }}">Budget</h2>
                    <p class="{{ $sectionHintClass }}">Monthly rent range in PHP.</p>

                    <div class="mt-4 grid gap-3.5 sm:grid-cols-2">
                        <div>
                            <label for="budget_min" class="{{ $labelClass }}">Minimum</label>
                            <input id="budget_min" name="budget_min" type="number" min="0" step="100" value="{{ $budgetMin }}" x-model="budgetMin" class="{{ $fieldClass }}" placeholder="2500">
                            @error('budget_min')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="budget_max" class="{{ $labelClass }}">Maximum</label>
                            <input id="budget_max" name="budget_max" type="number" min="0" step="100" value="{{ $budgetMax }}" x-model="budgetMax" class="{{ $fieldClass }}" placeholder="5000">
                            @error('budget_max')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                            @error('preferred_rental_budget')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                {{-- Preferred Location --}}
                <section class="{{ $cardClass }}">
                    <h2 class="{{ $sectionTitleClass }}">Preferred Location</h2>
                    <p class="{{ $sectionHintClass }}">The DSSC areas that fit your routine.</p>

                    <div class="mt-4 grid gap-3.5 sm:grid-cols-2">
                        <div>
                            <label for="preferred_location" class="{{ $labelClass }}">Area</label>
                            <select id="preferred_location" name="preferred_locations[]" x-model="location" class="{{ $fieldClass }}">
                                <option value="">Select a DSSC-area location</option>
                                @foreach ($availableLocations as $locationOption)
                                    <option value="{{ $locationOption }}" @selected($selectedLocations->contains($locationOption))>{{ $locationOption }}</option>
                                @endforeach
                                @foreach ($selectedLocations->filter(fn ($locationOption) => ! $availableLocations->contains($locationOption)) as $locationOption)
                                    <option value="{{ $locationOption }}" selected>{{ $locationOption }}</option>
                                @endforeach
                            </select>
                            @error('preferred_locations')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                            @error('preferred_locations.*')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="preferred_landmark" class="{{ $labelClass }}">Landmark</label>
                            <select
                                id="preferred_landmark"
                                name="preferred_landmark"
                                x-model="landmark"
                                x-on:change="if (landmark === 'DSSC Main Campus' && !location) location = 'All nearby DSSC areas'"
                                class="{{ $fieldClass }}"
                            >
                                <option value="">Select landmark or school</option>
                                @foreach (['DSSC Main Campus', 'Digos City Proper', 'Other'] as $landmarkOption)
                                    <option value="{{ $landmarkOption }}" @selected($preferredLandmark === $landmarkOption)>{{ $landmarkOption }}</option>
                                @endforeach
                            </select>
                            @error('preferred_landmark')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                {{-- Room Preferences --}}
                <section class="{{ $cardClass }}">
                    <h2 class="{{ $sectionTitleClass }}">Room Preferences</h2>
                    <p class="{{ $sectionHintClass }}">Setup and occupancy that suit you.</p>

                    <div class="mt-4 grid gap-3.5 sm:grid-cols-2">
                        <div>
                            <label for="room_type" class="{{ $labelClass }}">Room type</label>
                            <select id="room_type" name="room_type" x-model="roomType" class="{{ $fieldClass }}">
                                <option value="">Select room type</option>
                                @foreach ($roomTypeOptions as $value => $text)
                                    <option value="{{ $value }}" @selected(old('room_type', $preference->room_type) === $value)>{{ $text }}</option>
                                @endforeach
                            </select>
                            @error('room_type')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="gender_preference" class="{{ $labelClass }}">Occupancy</label>
                            <select id="gender_preference" name="gender_preference" x-model="gender" class="{{ $fieldClass }}">
                                @foreach ($genderOptions as $value => $text)
                                    <option value="{{ $value }}" @selected(old('gender_preference', $profile->gender_preference ?? 'no_preference') === $value)>{{ $text }}</option>
                                @endforeach
                            </select>
                            @error('gender_preference')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                {{-- Distance --}}
                <section class="{{ $cardClass }}">
                    <h2 class="{{ $sectionTitleClass }}">Distance</h2>
                    <p class="{{ $sectionHintClass }}">How far from DSSC you're willing to stay.</p>

                    <div class="mt-4">
                        <label for="distance_from_school" class="sr-only">Distance limit</label>
                        <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="Maximum distance from DSSC">
                            @foreach ($distanceOptions as $value => $text)
                                <label class="cursor-pointer">
                                    <input
                                        type="radio"
                                        name="distance_choice"
                                        value="{{ $value }}"
                                        x-model="distance"
                                        class="peer sr-only"
                                        @checked((float) $preferredDistance === (float) $value)
                                    >
                                    <span class="inline-flex h-9 items-center rounded-full px-4 text-[13px] font-semibold ring-1 transition peer-checked:bg-blue-600 peer-checked:text-white peer-checked:ring-blue-600 peer-focus-visible:ring-4 peer-focus-visible:ring-blue-200 bg-white text-slate-600 ring-slate-200 hover:bg-slate-50 hover:ring-slate-300">
                                        {{ $text }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <input type="hidden" name="distance_from_school" :value="distance" value="{{ $preferredDistanceValue }}">
                        @error('distance_from_school')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </section>

                {{-- Amenities --}}
                <section class="{{ $cardClass }}">
                    <div class="flex items-baseline justify-between gap-3">
                        <div>
                            <h2 class="{{ $sectionTitleClass }}">Amenities</h2>
                            <p class="{{ $sectionHintClass }}">Essentials to include in your shortlist.</p>
                        </div>
                        <span class="text-xs font-medium text-slate-400" x-text="selectedAmenities.length + ' selected'"></span>
                    </div>

                    <div class="mt-4 grid gap-2.5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($availableAmenities as $amenity)
                            <x-user.amenity-card
                                :name="$amenity->name"
                                :icon="$amenityIcon($amenity->name)"
                                :selected="$selectedAmenities->contains($amenity->name)"
                            />
                        @endforeach
                    </div>
                    @error('amenities')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    @error('amenities.*')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </section>

                {{-- Lifestyle (Optional) --}}
                <section class="{{ $cardClass }}" x-data="{ open: {{ $errors->hasAny(['study_habits', 'sleeping_schedule', 'smoking_preference', 'drinking_preference', 'pets_preference', 'internet_usage', 'social_style', 'cooking_habit', 'work_schedule', 'guest_preference', 'sharing_style', 'lifestyle_notes']) ? 'true' : 'false' }} }">
                    <button type="button" @click="open = !open" class="flex w-full items-center justify-between gap-3 text-left" :aria-expanded="open">
                        <div>
                            <h2 class="{{ $sectionTitleClass }}">Lifestyle <span class="font-medium text-slate-400">(Optional)</span></h2>
                            <p class="{{ $sectionHintClass }}">Habits that help match compatible housemates.</p>
                        </div>
                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-cloak class="mt-4 space-y-4">
                        @foreach ($lifestyleChipGroups as $group)
                            <div>
                                <p class="{{ $labelClass }}">{{ $group['label'] }}</p>
                                <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="{{ $group['label'] }}">
                                    @foreach ($group['options'] as $value => $text)
                                        <label class="cursor-pointer">
                                            <input
                                                type="radio"
                                                name="{{ $group['name'] }}"
                                                value="{{ $value }}"
                                                x-model="lifestyle.{{ $group['name'] }}"
                                                class="peer sr-only"
                                                @checked($group['value'] === $value)
                                            >
                                            <span class="inline-flex h-8 items-center rounded-full px-3 text-xs font-semibold ring-1 transition peer-checked:bg-blue-600 peer-checked:text-white peer-checked:ring-blue-600 peer-focus-visible:ring-4 peer-focus-visible:ring-blue-200 bg-white text-slate-600 ring-slate-200 hover:bg-slate-50 hover:ring-slate-300">
                                                {{ $text }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                @error($group['name'])<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        @endforeach

                        <div>
                            <label for="lifestyle_notes" class="{{ $labelClass }}">Notes</label>
                            <textarea id="lifestyle_notes" name="lifestyle_notes" rows="2" x-model="lifestyleNotes" class="{{ $fieldClass }} h-auto resize-none py-2.5" placeholder="Anything else owners or housemates should know…">{{ old('lifestyle_notes', $preference->lifestyle_notes ?? '') }}</textarea>
                            @error('lifestyle_notes')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>
            </div>

            {{-- Matching Summary --}}
            <aside class="space-y-4 xl:sticky xl:top-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/50">
                    <h2 class="text-sm font-bold text-slate-900">Matching Summary</h2>

                    <dl class="mt-3 space-y-2.5 text-xs">
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Budget</dt><dd class="text-right font-semibold text-slate-900" x-text="budgetLabel"></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Location</dt><dd class="truncate text-right font-semibold text-slate-900" x-text="locationSummary"></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Room</dt><dd class="text-right font-semibold text-slate-900" x-text="roomTypeLabels[roomType] || 'Not set'"></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Occupancy</dt><dd class="text-right font-semibold text-slate-900" x-text="genderLabels[gender] || 'Not set'"></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Distance</dt><dd class="text-right font-semibold text-slate-900" x-text="distanceLabels[distance] || distanceFallback"></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Amenities</dt><dd class="text-right font-semibold text-slate-900" x-text="selectedAmenities.length ? selectedAmenities.length + ' selected' : 'Not set'"></dd></div>
                    </dl>

                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold text-slate-600">Match quality</p>
                            <p class="text-xs font-bold" :class="matchQuality.tone" x-text="matchQuality.label"></p>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-blue-600 transition-all duration-500" :style="'width:' + matchQuality.percent + '%'"></div>
                        </div>
                        <p class="mt-2 text-[11px] leading-4 text-slate-400" x-text="matchQuality.hint"></p>
                    </div>
                </section>
            </aside>
        </div>

        {{-- Sticky action bar --}}
        <div class="sticky bottom-0 z-40 mt-4 rounded-2xl border border-slate-200 bg-white/95 shadow-[0_-8px_30px_rgba(15,23,42,0.08)] backdrop-blur">
            <div class="mx-auto flex w-full max-w-5xl items-center justify-between gap-3 px-4 py-3 sm:px-6">
                <button type="button" x-on:click="resetPreferences($refs.form)" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl px-4 text-[13px] font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100">
                    <x-user.preference-icon name="arrow-path" class="h-4 w-4" />
                    Reset Preferences
                </button>

                <button type="submit" name="intent" value="save" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 text-sm font-bold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/30 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200">
                    <x-user.preference-icon name="check-circle" class="h-4 w-4" />
                    Save Preferences
                </button>
            </div>
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
            lifestyle: snapshot.lifestyle || {},
            lifestyleNotes: snapshot.lifestyleNotes || '',
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

            get locationSummary() {
                if (this.location && this.landmark) {
                    return `${this.location} • ${this.landmark}`;
                }

                return this.location || this.landmark || 'Not set';
            },

            get distanceFallback() {
                if (!this.distance) {
                    return 'Not set';
                }

                const amount = Number(this.distance);

                if (!Number.isFinite(amount)) {
                    return 'Not set';
                }

                return amount >= 100 ? 'Any distance' : `${amount} km max`;
            },

            get matchQuality() {
                let score = 0;
                const total = 6;

                if (this.budgetMin || this.budgetMax) score += 1;
                if (this.location) score += 1;
                if (this.roomType) score += 1;
                if (this.distance) score += 1;
                if (this.selectedAmenities.length) score += 1;
                if (this.gender && this.gender !== 'no_preference') score += 0.5;
                if (this.selectedAmenities.length >= 3) score += 0.5;

                const percent = Math.min(100, Math.round((score / total) * 100));

                if (percent >= 85) {
                    return { percent, label: 'Excellent', tone: 'text-emerald-600', hint: 'Your preferences are detailed enough for highly targeted matches.' };
                }

                if (percent >= 60) {
                    return { percent, label: 'Good', tone: 'text-blue-600', hint: 'Solid criteria. Add amenities or distance to sharpen results.' };
                }

                if (percent >= 35) {
                    return { percent, label: 'Fair', tone: 'text-amber-600', hint: 'Set a budget, location, and room type for better matches.' };
                }

                return { percent: Math.max(percent, 8), label: 'Basic', tone: 'text-slate-500', hint: 'Fill in a few preferences to start getting tailored matches.' };
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
                this.lifestyle = JSON.parse(JSON.stringify(this.initial.lifestyle || {}));
                this.lifestyleNotes = this.initial.lifestyleNotes || '';
            },
        };
    };
</script>
</x-user.shell>
</x-layouts.dashboard>
