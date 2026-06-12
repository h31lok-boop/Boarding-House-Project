<x-layouts.dashboard>
<x-user.shell>
    <div class="space-y-6">
        <div class="ui-card p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] ui-muted">{{ $context }}</p>
                    <h2 class="mt-1 text-2xl font-semibold">{{ $candidate->name }}</h2>
                    <p class="text-sm ui-muted">{{ $candidate->boardingHouse?->name ?? 'Boarding house not assigned' }}</p>
                    @if ($requestState['status'] !== 'none')
                        <p class="mt-2 text-sm font-medium capitalize text-slate-600">{{ $requestState['direction'] }} {{ $requestState['status'] }}</p>
                    @endif
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('user.matchmaking.index') }}" class="rounded-lg border ui-border px-4 py-2 text-sm">Back to Recommendations</a>
                    <a href="{{ route('user.matchmaking.explain', $candidate) }}" class="rounded-lg border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-medium text-sky-700 hover:bg-sky-100">Explain with DeepSeek</a>
                    <a href="{{ route('user.preferences.index') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Update Profile</a>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[0.72fr,1.28fr]">
            <aside class="space-y-6">
                <div class="ui-card p-6">
                    <p class="text-xs uppercase tracking-[0.18em] ui-muted">Compatibility</p>
                    <p class="mt-3 text-5xl font-bold text-indigo-700">{{ $compatibility['compatibility_percent'] }}%</p>
                    <p class="mt-3 text-sm ui-muted">This is the current weighted score from the matchmaking config and the saved lifestyle profile fields.</p>
                </div>

                <div class="ui-card p-6">
                    <p class="text-xs uppercase tracking-[0.18em] ui-muted">DeepSeek Status</p>
                    <p class="mt-3 text-lg font-semibold {{ $deepSeekConfigured ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $deepSeekConfigured ? 'Configured' : 'Not Configured' }}
                    </p>
                    <p class="mt-3 text-sm ui-muted">
                        {{ $deepSeekConfigured ? 'Use DeepSeek to generate a short explanation for this recommendation.' : 'Set DEEPSEEK_API_KEY in your environment to enable AI match explanations.' }}
                    </p>
                </div>

                <div class="ui-card p-6">
                    <h3 class="text-lg font-semibold">Match Request</h3>
                    @if ($requestState['status'] === 'none')
                        <form method="POST" action="{{ route('user.matchmaking.requests.store', $candidate) }}" class="mt-4 space-y-3">
                            @csrf
                            <div>
                                <label for="message" class="block text-sm font-medium">Optional note</label>
                                <textarea id="message" name="message" rows="4" class="ui-input mt-2 w-full" placeholder="Share why you think this could be a good roommate match.">{{ old('message') }}</textarea>
                                @error('message')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Send Match Request</button>
                        </form>
                    @elseif ($requestState['status'] === 'pending' && $requestState['direction'] === 'incoming')
                        <div class="mt-4 space-y-3">
                            @if ($requestState['request']?->message)
                                <p class="rounded-lg border ui-border p-3 text-sm ui-muted">{{ $requestState['request']->message }}</p>
                            @endif
                            <div class="flex gap-3">
                                <form method="POST" action="{{ route('user.match-requests.accept', $requestState['request']) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Accept</button>
                                </form>
                                <form method="POST" action="{{ route('user.match-requests.decline', $requestState['request']) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-rose-200 px-4 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50">Decline</button>
                                </form>
                            </div>
                        </div>
                    @elseif ($requestState['status'] === 'pending' && $requestState['direction'] === 'outgoing')
                        <div class="mt-4 space-y-3">
                            <p class="text-sm ui-muted">Your request is waiting for a response.</p>
                            @if ($requestState['request']?->message)
                                <p class="rounded-lg border ui-border p-3 text-sm ui-muted">{{ $requestState['request']->message }}</p>
                            @endif
                            <form method="POST" action="{{ route('user.match-requests.cancel', $requestState['request']) }}">
                                @csrf
                                <button type="submit" class="rounded-lg border ui-border px-4 py-2 text-sm font-medium">Cancel Request</button>
                            </form>
                        </div>
                    @else
                        <div class="mt-4 space-y-3">
                            <p class="text-sm ui-muted">Latest request status: <span class="font-medium capitalize">{{ $requestState['status'] }}</span>.</p>
                            @if ($requestState['request']?->message)
                                <p class="rounded-lg border ui-border p-3 text-sm ui-muted">{{ $requestState['request']->message }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="ui-card p-6">
                    <h3 class="text-lg font-semibold">Candidate Summary</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="ui-muted">Sleep schedule</dt>
                            <dd>{{ str_replace('_', ' ', $candidate->tenantMatchProfile->sleep_schedule ?? 'n/a') }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="ui-muted">Study habits</dt>
                            <dd>{{ str_replace('_', ' ', $candidate->tenantMatchProfile->study_habits ?? 'n/a') }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="ui-muted">Internet usage</dt>
                            <dd>{{ str_replace('_', ' ', $candidate->tenantMatchProfile->internet_usage ?? 'n/a') }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="ui-muted">Cleanliness</dt>
                            <dd>{{ $candidate->tenantMatchProfile->cleanliness_level ?? 'n/a' }}/5</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="ui-muted">Noise tolerance</dt>
                            <dd>{{ $candidate->tenantMatchProfile->noise_tolerance ?? 'n/a' }}/5</dd>
                        </div>
                    </dl>
                </div>
            </aside>

            <div class="space-y-6">
                @if ($aiExplanation)
                    <div class="ui-card p-6">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] ui-muted">AI Explanation</p>
                                <h3 class="mt-1 text-lg font-semibold">DeepSeek Match Explanation</h3>
                            </div>
                            <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-medium text-sky-700">{{ $aiExplanation['model'] ?? 'DeepSeek' }}</span>
                        </div>

                        @if ($aiExplanation['success'])
                            <div class="prose prose-sm mt-4 max-w-none text-slate-700">
                                {!! nl2br(e($aiExplanation['content'])) !!}
                            </div>
                        @else
                            <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                                {{ $aiExplanation['reason'] }}
                            </p>
                        @endif
                    </div>
                @endif

                <div class="ui-card p-6">
                    <h3 class="text-lg font-semibold">Compatibility Breakdown</h3>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        @foreach ($compatibility['breakdown'] as $criterion)
                            <div class="rounded-xl border ui-border p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-medium">{{ $criterion['label'] }}</p>
                                    <span class="text-sm font-semibold">{{ (int) round($criterion['score'] * 100) }}%</span>
                                </div>
                                <div class="mt-3 h-2 rounded-full bg-slate-100">
                                    <div class="h-2 rounded-full bg-indigo-600" style="width: {{ max(4, (int) round($criterion['score'] * 100)) }}%"></div>
                                </div>
                                <p class="mt-2 text-xs ui-muted">Weight {{ number_format($criterion['weight'] * 100, 0) }}%</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="ui-card p-6">
                        <h3 class="text-lg font-semibold">Shared Strengths</h3>
                        <ul class="mt-4 space-y-3 text-sm ui-muted">
                            @forelse ($compatibility['highlights'] as $highlight)
                                <li>{{ $highlight }}</li>
                            @empty
                                <li>No strong overlap has surfaced yet.</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="ui-card p-6">
                        <h3 class="text-lg font-semibold">Potential Mismatches</h3>
                        <ul class="mt-4 space-y-3 text-sm ui-muted">
                            @forelse ($compatibility['conflicts'] as $conflict)
                                <li>{{ $conflict }}</li>
                            @empty
                                <li>No major incompatibilities detected in this pass.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-user.shell>
</x-layouts.dashboard>
