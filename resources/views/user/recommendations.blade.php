<x-layouts.dashboard>
<x-user.shell>
    <div class="space-y-6">
        <div class="ui-card p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold">Recommended Roommates</h2>
                    <p class="text-sm ui-muted">Ranked candidates based on your current match profile and the first-pass weighted compatibility engine.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('user.profile') }}" class="rounded-lg border ui-border px-4 py-2 text-sm">Edit Profile</a>
                    <span class="rounded-full bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700">{{ $matchCount }} of {{ $totalMatchCount ?? $matchCount }} candidates</span>
                </div>
            </div>
        </div>

        @php
            $filters = $filters ?? [];
            $filterOptions = $filterOptions ?? [];
        @endphp

        <form method="GET" action="{{ route('user.recommendations') }}" class="ui-card p-4 grid gap-3 md:grid-cols-2 xl:grid-cols-6">
            <input type="number" min="0" max="100" name="min_score" value="{{ $filters['min_score'] ?? '' }}" placeholder="Minimum score" class="ui-input text-sm">
            <input type="number" step="0.01" min="0" name="budget_min" value="{{ $filters['budget_min'] ?? '' }}" placeholder="Budget min" class="ui-input text-sm">
            <input type="number" step="0.01" min="0" name="budget_max" value="{{ $filters['budget_max'] ?? '' }}" placeholder="Budget max" class="ui-input text-sm">

            <select name="boarding_house_id" class="ui-input text-sm">
                <option value="">Any location</option>
                @foreach(($filterOptions['boardingHouses'] ?? collect()) as $house)
                    <option value="{{ $house->id }}" @selected(($filters['boarding_house_id'] ?? null) === $house->id)>{{ $house->name }}</option>
                @endforeach
            </select>

            <select name="gender_preference" class="ui-input text-sm">
                <option value="">Any gender preference</option>
                @foreach(($filterOptions['gender_preference'] ?? []) as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['gender_preference'] ?? null) === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="sleep_schedule" class="ui-input text-sm">
                <option value="">Any sleep schedule</option>
                @foreach(($filterOptions['sleep_schedule'] ?? []) as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['sleep_schedule'] ?? null) === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="study_habits" class="ui-input text-sm xl:col-span-2">
                <option value="">Any study habit</option>
                @foreach(($filterOptions['study_habits'] ?? []) as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['study_habits'] ?? null) === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="md:col-span-2 xl:col-span-4 flex items-center justify-end gap-2">
                <a href="{{ route('user.recommendations') }}" class="rounded-lg border ui-border px-4 py-2 text-sm">Reset</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Apply Filters</button>
            </div>
        </form>

        @if ($matches->isEmpty())
            <div class="ui-card p-8 text-center">
                <h3 class="text-lg font-semibold">No ranked matches yet</h3>
                <p class="mt-2 text-sm ui-muted">Once other users complete their match profiles, recommendations will show up here.</p>
            </div>
        @else
            <div class="grid gap-6 xl:grid-cols-2">
                @foreach ($matches as $match)
                    @php
                        $candidate = $match['candidate'];
                        $compatibility = $match['compatibility'];
                        $profile = $candidate->tenantMatchProfile;
                        $requestState = $match['requestState'];
                    @endphp
                    <article class="ui-card p-6 space-y-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] ui-muted">{{ $match['context'] }}</p>
                                <h3 class="mt-1 text-xl font-semibold">{{ $candidate->name }}</h3>
                                <p class="text-sm ui-muted">{{ $candidate->boardingHouse?->name ?? 'Boarding house not assigned' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl font-bold text-indigo-700">{{ $compatibility['compatibility_percent'] }}%</p>
                                <p class="text-xs ui-muted">compatibility</p>
                                @if ($requestState['status'] !== 'none')
                                    <p class="mt-2 text-xs font-medium capitalize text-slate-600">{{ $requestState['direction'] }} {{ $requestState['status'] }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border ui-border p-4">
                                <p class="text-xs uppercase tracking-[0.16em] ui-muted">Lifestyle</p>
                                <p class="mt-2 text-sm">{{ str_replace('_', ' ', $profile->sleep_schedule ?? 'n/a') }} sleeper</p>
                                <p class="text-sm">{{ str_replace('_', ' ', $profile->study_habits ?? 'n/a') }} study style</p>
                            </div>
                            <div class="rounded-xl border ui-border p-4">
                                <p class="text-xs uppercase tracking-[0.16em] ui-muted">Budget</p>
                                <p class="mt-2 text-sm">
                                    @if ($profile->budget_min !== null || $profile->budget_max !== null)
                                        PHP {{ number_format((float) ($profile->budget_min ?? 0), 0) }} - {{ number_format((float) ($profile->budget_max ?? 0), 0) }}
                                    @else
                                        Not specified
                                    @endif
                                </p>
                                <p class="text-sm">Internet: {{ str_replace('_', ' ', $profile->internet_usage ?? 'n/a') }}</p>
                            </div>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div>
                                <h4 class="text-sm font-semibold">Strong matches</h4>
                                <ul class="mt-2 space-y-2 text-sm ui-muted">
                                    @forelse ($compatibility['highlights'] as $highlight)
                                        <li>{{ $highlight }}</li>
                                    @empty
                                        <li>No dominant overlap yet.</li>
                                    @endforelse
                                </ul>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold">Potential friction</h4>
                                <ul class="mt-2 space-y-2 text-sm ui-muted">
                                    @forelse ($compatibility['conflicts'] as $conflict)
                                        <li>{{ $conflict }}</li>
                                    @empty
                                        <li>No major conflicts detected.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold">Score breakdown</h4>
                            <div class="mt-3 space-y-3">
                                @foreach ($compatibility['breakdown'] as $criterion)
                                    <div>
                                        <div class="mb-1 flex items-center justify-between text-xs">
                                            <span class="ui-muted">{{ $criterion['label'] }}</span>
                                            <span>{{ (int) round($criterion['score'] * 100) }}%</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-slate-100">
                                            <div class="h-2 rounded-full bg-indigo-600" style="width: {{ max(4, (int) round($criterion['score'] * 100)) }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            @if ($requestState['status'] === 'pending' && $requestState['direction'] === 'incoming')
                                <form method="POST" action="{{ route('user.match-requests.accept', $requestState['request']) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Accept</button>
                                </form>
                            @endif

                            <a href="{{ route('user.recommendations.show', $candidate) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">View Full Match</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-user.shell>
</x-layouts.dashboard>
