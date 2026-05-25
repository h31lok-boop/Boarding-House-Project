<x-layouts.caretaker>
<x-tenant.shell>
    @php
        $options = $fieldOptions ?? [];
        $selectedHobbies = old('hobbies', $profile->hobbies ?? []);
    @endphp

    <div class="space-y-6">
        <div class="ui-card p-6">
            <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold">Match Profile</h2>
                    <p class="text-sm ui-muted">Set the lifestyle and budget preferences that will power roommate recommendations.</p>
                </div>
                @if ($profile->completed_at)
                    <p class="text-xs ui-muted">Last updated {{ $profile->completed_at->format('M d, Y h:i A') }}</p>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('tenant.match-profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 xl:grid-cols-[1.35fr,0.65fr]">
                <div class="ui-card p-6 space-y-6">
                    <section class="space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold">Budget and Setup</h3>
                            <p class="text-sm ui-muted">These values help narrow matching candidates and boarding house options.</p>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="budget_min" class="block text-sm font-medium">Minimum monthly budget</label>
                                <input id="budget_min" name="budget_min" type="number" step="0.01" min="0" value="{{ old('budget_min', $profile->budget_min) }}" class="ui-input mt-2 w-full">
                                @error('budget_min')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="budget_max" class="block text-sm font-medium">Maximum monthly budget</label>
                                <input id="budget_max" name="budget_max" type="number" step="0.01" min="0" value="{{ old('budget_max', $profile->budget_max) }}" class="ui-input mt-2 w-full">
                                @error('budget_max')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div>
                            <label for="gender_preference" class="block text-sm font-medium">Gender preference</label>
                            <select id="gender_preference" name="gender_preference" class="ui-input mt-2 w-full">
                                @foreach ($options['gender_preference'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('gender_preference', $profile->gender_preference) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('gender_preference')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </section>

                    <section class="space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold">Lifestyle Fit</h3>
                            <p class="text-sm ui-muted">These values will become the first version of the weighted compatibility score.</p>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="sleep_schedule" class="block text-sm font-medium">Sleeping schedule</label>
                                <select id="sleep_schedule" name="sleep_schedule" class="ui-input mt-2 w-full">
                                    @foreach ($options['sleep_schedule'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('sleep_schedule', $profile->sleep_schedule) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('sleep_schedule')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="study_habits" class="block text-sm font-medium">Study habits</label>
                                <select id="study_habits" name="study_habits" class="ui-input mt-2 w-full">
                                    @foreach ($options['study_habits'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('study_habits', $profile->study_habits) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('study_habits')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="cleanliness_level" class="block text-sm font-medium">Cleanliness level</label>
                                <select id="cleanliness_level" name="cleanliness_level" class="ui-input mt-2 w-full">
                                    @for ($level = 1; $level <= 5; $level++)
                                        <option value="{{ $level }}" @selected((int) old('cleanliness_level', $profile->cleanliness_level) === $level)>{{ $level }} / 5</option>
                                    @endfor
                                </select>
                                @error('cleanliness_level')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="noise_tolerance" class="block text-sm font-medium">Noise tolerance</label>
                                <select id="noise_tolerance" name="noise_tolerance" class="ui-input mt-2 w-full">
                                    @for ($level = 1; $level <= 5; $level++)
                                        <option value="{{ $level }}" @selected((int) old('noise_tolerance', $profile->noise_tolerance) === $level)>{{ $level }} / 5</option>
                                    @endfor
                                </select>
                                @error('noise_tolerance')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="smoking_preference" class="block text-sm font-medium">Smoking preference</label>
                                <select id="smoking_preference" name="smoking_preference" class="ui-input mt-2 w-full">
                                    @foreach ($options['smoking_preference'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('smoking_preference', $profile->smoking_preference) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('smoking_preference')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="drinking_preference" class="block text-sm font-medium">Drinking preference</label>
                                <select id="drinking_preference" name="drinking_preference" class="ui-input mt-2 w-full">
                                    @foreach ($options['drinking_preference'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('drinking_preference', $profile->drinking_preference) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('drinking_preference')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="pets_preference" class="block text-sm font-medium">Pets preference</label>
                                <select id="pets_preference" name="pets_preference" class="ui-input mt-2 w-full">
                                    @foreach ($options['pets_preference'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('pets_preference', $profile->pets_preference) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('pets_preference')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="internet_usage" class="block text-sm font-medium">Internet usage</label>
                                <select id="internet_usage" name="internet_usage" class="ui-input mt-2 w-full">
                                    @foreach ($options['internet_usage'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('internet_usage', $profile->internet_usage) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('internet_usage')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold">Hobbies and Notes</h3>
                            <p class="text-sm ui-muted">These help future match explanations feel more human than just a number.</p>
                        </div>
                        <div class="grid gap-3 md:grid-cols-2">
                            @foreach ($options['hobbies'] as $value => $label)
                                <label class="flex items-center gap-3 rounded-lg border ui-border px-3 py-2">
                                    <input type="checkbox" name="hobbies[]" value="{{ $value }}" @checked(in_array($value, $selectedHobbies, true)) class="rounded border-slate-300 text-indigo-600">
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('hobbies')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                        @error('hobbies.*')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror

                        <div>
                            <label for="additional_notes" class="block text-sm font-medium">Additional notes</label>
                            <textarea id="additional_notes" name="additional_notes" rows="4" class="ui-input mt-2 w-full" placeholder="Share anything that matters for roommate compatibility, such as prayer time, visitors, or study routines.">{{ old('additional_notes', $profile->additional_notes) }}</textarea>
                            @error('additional_notes')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </section>
                </div>

                <aside class="space-y-6">
                    <div class="ui-card p-6">
                        <h3 class="text-lg font-semibold">What this enables</h3>
                        <ul class="mt-4 space-y-3 text-sm ui-muted">
                            <li>Weighted roommate compatibility scoring</li>
                            <li>Clearer housing recommendations by budget and lifestyle fit</li>
                            <li>Future match explanations instead of opaque rankings</li>
                        </ul>
                    </div>
                    <div class="ui-card p-6">
                        <h3 class="text-lg font-semibold">Profile status</h3>
                        <p class="mt-3 text-sm ui-muted">
                            @if (session('status') === 'tenant-match-profile-updated')
                                Match profile updated successfully.
                            @elseif ($profile->completed_at)
                                Your profile is complete and ready for recommendation scoring.
                            @else
                                Complete this form to activate roommate recommendations.
                            @endif
                        </p>
                    </div>
                </aside>
            </div>

            <div class="flex items-center justify-between ui-card p-4">
                <p class="text-sm ui-muted">This is the first implementation pass. The actual compatibility engine will use these fields next.</p>
                <button type="submit" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                    Save Match Profile
                </button>
            </div>
        </form>
    </div>
</x-tenant.shell>
</x-layouts.caretaker>
