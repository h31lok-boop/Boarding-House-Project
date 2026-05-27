<x-layouts.dashboard>
<x-user.shell>
    @php
        $options = $fieldOptions ?? [];
        $matchProfilesAvailable = $matchProfilesAvailable ?? true;
        $selectedHobbies = old('hobbies', $profile->hobbies ?? ['reading', 'coding']);
        $selectedAmenityIds = collect(old('preferred_amenity_ids', $profile->preferred_amenity_ids ?? []))
            ->map(fn ($id) => (int) $id)
            ->all();
        $fallbackAmenities = collect([
            'Wi-Fi',
            'Air Conditioning',
            'Study Area',
            'Kitchen Access',
            'Laundry Area',
            'CCTV',
            'Water Included',
            'Electricity Included',
            'Security Guard',
            '24/7 Access',
            'Pet Friendly',
        ]);
        $visibleAmenities = ($amenities ?? collect())->isNotEmpty()
            ? $amenities->values()
            : $fallbackAmenities->map(fn ($name, $index) => (object) ['id' => $index + 1, 'name' => $name, 'disabled' => true]);
        $summaryBudgetMin = old('budget_min', $profile->budget_min ?? 5000);
        $summaryBudgetMax = old('budget_max', $profile->budget_max ?? 8000);
        $selectedAmenityNames = $visibleAmenities
            ->filter(fn ($amenity, $index) => in_array((int) $amenity->id, $selectedAmenityIds, true) || ($amenities ?? collect())->isEmpty() && $index < 9)
            ->pluck('name')
            ->values();
        if ($selectedAmenityNames->isEmpty()) {
            $selectedAmenityNames = $visibleAmenities->take(9)->pluck('name')->values();
        }
    @endphp

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">My Preferences</h1>
            <p class="mt-2 text-sm ui-muted">Manage your preferences to get better matches. Match Profile settings are used for compatibility scoring.</p>
        </div>

        @if (! $matchProfilesAvailable)
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Tenant match profiles are not available yet. Run the pending migrations to enable saving this page.
            </div>
        @endif

        <div class="flex gap-8 border-b ui-border">
            <a href="{{ route('user.profile') }}" class="border-b-2 border-indigo-600 px-6 py-3 text-sm font-semibold text-indigo-700">Preferences</a>
            <a href="#lifestyle-fields" class="px-6 py-3 text-sm ui-muted hover:text-[color:var(--text)]">Lifestyle</a>
        </div>

        <form method="POST" action="{{ route('user.profile.update') }}" class="grid gap-6 xl:grid-cols-[1fr_390px]">
            @csrf
            @method('PUT')

            <fieldset @disabled(! $matchProfilesAvailable) class="space-y-0">
                <div class="ui-card overflow-hidden">
                    <section class="p-6">
                        <h2 class="text-lg font-semibold">Basic Preferences</h2>
                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <label class="text-sm">
                                <span class="ui-muted">Preferred Location</span>
                                <select class="ui-input mt-2" name="preferred_location">
                                    <option>Cagayan de Oro City</option>
                                    <option>Cogon, CDO</option>
                                    <option>Lapasan, CDO</option>
                                </select>
                            </label>

                            <div class="grid grid-cols-2 gap-3">
                                <label class="text-sm">
                                    <span class="ui-muted">Budget Min</span>
                                    <input name="budget_min" type="number" min="0" step="0.01" value="{{ $summaryBudgetMin }}" class="ui-input mt-2">
                                </label>
                                <label class="text-sm">
                                    <span class="ui-muted">Budget Max</span>
                                    <input name="budget_max" type="number" min="0" step="0.01" value="{{ $summaryBudgetMax }}" class="ui-input mt-2">
                                </label>
                            </div>

                            <label class="text-sm">
                                <span class="ui-muted">Room Type</span>
                                <select class="ui-input mt-2" name="room_type">
                                    <option>Solo Room</option>
                                    <option>Shared Room</option>
                                    <option>Bedspace</option>
                                </select>
                            </label>

                            <label class="text-sm">
                                <span class="ui-muted">Gender Preference</span>
                                <select name="gender_preference" class="ui-input mt-2">
                                    @foreach ($options['gender_preference'] ?? ['female' => 'Female Only'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('gender_preference', $profile->gender_preference ?? 'female') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="text-sm">
                                <span class="ui-muted">Preferred Distance from School</span>
                                <select class="ui-input mt-2" name="preferred_distance">
                                    <option>Within 2 km</option>
                                    <option>Within 5 km</option>
                                    <option>Any distance</option>
                                </select>
                            </label>

                            <label class="text-sm">
                                <span class="ui-muted">Preferred Move-in Date</span>
                                <input type="date" class="ui-input mt-2" name="preferred_move_in_date" value="2026-06-01">
                            </label>

                            <label class="text-sm md:col-span-2">
                                <span class="ui-muted">Additional Notes (Optional)</span>
                                <textarea name="additional_notes" rows="4" class="ui-input mt-2" placeholder="Prefer a quiet place for studying. Near public transport is a plus.">{{ old('additional_notes', $profile->additional_notes ?? 'Prefer a quiet place for studying. Near public transport is a plus.') }}</textarea>
                            </label>
                        </div>
                    </section>

                    <section class="border-t ui-border p-6">
                        <h2 class="text-lg font-semibold">Amenities <span class="text-sm font-normal ui-muted">(Select all that apply)</span></h2>
                        <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                            @foreach ($visibleAmenities as $index => $amenity)
                                @php($checked = in_array((int) $amenity->id, $selectedAmenityIds, true) || (($amenities ?? collect())->isEmpty() && $index < 9))
                                <label class="flex items-center gap-3 rounded-lg border ui-border px-4 py-3 text-sm {{ $checked ? 'bg-violet-50 text-indigo-700 dark:bg-violet-950/20' : '' }}">
                                    @if (! ($amenity->disabled ?? false))
                                        <input type="checkbox" name="preferred_amenity_ids[]" value="{{ $amenity->id }}" @checked($checked) class="rounded border-slate-300 text-indigo-600">
                                    @else
                                        <input type="checkbox" @checked($checked) disabled class="rounded border-slate-300 text-indigo-600">
                                    @endif
                                    <span>{{ $amenity->name }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div id="lifestyle-fields" class="mt-6 grid gap-4 md:grid-cols-2">
                            <label class="text-sm">
                                <span class="ui-muted">Sleeping Schedule</span>
                                <select name="sleep_schedule" class="ui-input mt-2">
                                    @foreach ($options['sleep_schedule'] ?? ['balanced' => 'Balanced routine'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('sleep_schedule', $profile->sleep_schedule ?? 'balanced') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="text-sm">
                                <span class="ui-muted">Study Habits</span>
                                <select name="study_habits" class="ui-input mt-2">
                                    @foreach ($options['study_habits'] ?? ['quiet_focus' => 'Quiet focus'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('study_habits', $profile->study_habits ?? 'quiet_focus') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="text-sm">
                                <span class="ui-muted">Cleanliness Level</span>
                                <select name="cleanliness_level" class="ui-input mt-2">
                                    @for ($level = 1; $level <= 5; $level++)
                                        <option value="{{ $level }}" @selected((int) old('cleanliness_level', $profile->cleanliness_level ?? 4) === $level)>{{ $level }} / 5</option>
                                    @endfor
                                </select>
                            </label>
                            <label class="text-sm">
                                <span class="ui-muted">Noise Tolerance</span>
                                <select name="noise_tolerance" class="ui-input mt-2">
                                    @for ($level = 1; $level <= 5; $level++)
                                        <option value="{{ $level }}" @selected((int) old('noise_tolerance', $profile->noise_tolerance ?? 2) === $level)>{{ $level }} / 5</option>
                                    @endfor
                                </select>
                            </label>
                        </div>

                        <input type="hidden" name="smoking_preference" value="{{ old('smoking_preference', $profile->smoking_preference ?? 'non_smoker_only') }}">
                        <input type="hidden" name="drinking_preference" value="{{ old('drinking_preference', $profile->drinking_preference ?? 'occasional_ok') }}">
                        <input type="hidden" name="pets_preference" value="{{ old('pets_preference', $profile->pets_preference ?? 'no_pets') }}">
                        <input type="hidden" name="internet_usage" value="{{ old('internet_usage', $profile->internet_usage ?? 'heavy') }}">
                        @foreach ((array) $selectedHobbies as $hobby)
                            <input type="hidden" name="hobbies[]" value="{{ $hobby }}">
                        @endforeach

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
                            <a href="{{ route('user.profile') }}" class="rounded-lg border ui-border px-6 py-3 text-center text-sm font-semibold">Reset</a>
                            <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-700">Save Changes</button>
                        </div>
                    </section>
                </div>
            </fieldset>

            <aside class="space-y-5">
                <section class="ui-card p-5">
                    <h2 class="font-semibold text-indigo-700 dark:text-indigo-200">Preference Summary</h2>
                    <p class="mt-1 text-sm ui-muted">Here is a summary of your preferences.</p>
                    <dl class="mt-5 divide-y ui-border text-sm">
                        <div class="flex justify-between gap-4 py-3"><dt class="ui-muted">Location</dt><dd class="font-semibold text-right">Cagayan de Oro City</dd></div>
                        <div class="flex justify-between gap-4 py-3"><dt class="ui-muted">Budget Range</dt><dd class="font-semibold text-right">&#8369;{{ number_format((float) $summaryBudgetMin) }} - &#8369;{{ number_format((float) $summaryBudgetMax) }}</dd></div>
                        <div class="flex justify-between gap-4 py-3"><dt class="ui-muted">Room Type</dt><dd class="font-semibold text-right">Solo Room</dd></div>
                        <div class="flex justify-between gap-4 py-3"><dt class="ui-muted">Gender Preference</dt><dd class="font-semibold text-right">Female Only</dd></div>
                        <div class="flex justify-between gap-4 py-3"><dt class="ui-muted">Distance from School</dt><dd class="font-semibold text-right">Within 2 km</dd></div>
                        <div class="flex justify-between gap-4 py-3"><dt class="ui-muted">Move-in Date</dt><dd class="font-semibold text-right">Jun 1, 2026</dd></div>
                    </dl>
                </section>

                <section class="ui-card p-5 bg-emerald-50/60 dark:bg-emerald-950/20">
                    <h2 class="font-semibold text-emerald-700 dark:text-emerald-200">Selected Amenities ({{ $selectedAmenityNames->count() }})</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($selectedAmenityNames as $amenityName)
                            <span class="rounded-lg bg-white px-3 py-2 text-xs font-medium text-emerald-700 dark:bg-emerald-950/30">{{ $amenityName }}</span>
                        @endforeach
                    </div>
                </section>

                <section class="ui-card p-5 bg-amber-50/70 dark:bg-amber-950/20">
                    <h2 class="font-semibold text-amber-700 dark:text-amber-200">Tip</h2>
                    <p class="mt-3 text-sm ui-muted">The more specific your preferences, the better we can match you with the perfect boarding house.</p>
                </section>
            </aside>
        </form>
    </div>
</x-user.shell>
</x-layouts.dashboard>
