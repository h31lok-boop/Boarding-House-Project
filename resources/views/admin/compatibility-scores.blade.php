<x-layouts.dashboard>
<x-admin.shell>
    <div x-data="{ detailOpen: false, selected: {} }" class="space-y-6">
        <div class="ui-card p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Matchmaking</p>
            <h1 class="mt-2 text-2xl font-bold">Compatibility Scores</h1>
            <p class="mt-2 text-sm ui-muted">Rank tenant-to-tenant compatibility using weighted profile factors when match profiles are available.</p>
        </div>

        @unless ($hasProfiles)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Tenant match profile storage is not available in this database yet. The page is ready and will show real scores once the migration is applied.
            </div>
        @endunless

        <form method="GET" class="ui-card p-4 grid gap-3 md:grid-cols-[1fr_180px_auto]">
            <input class="ui-input text-sm" value="Weighted Compatibility Algorithm" disabled>
            <select name="min_score" class="ui-input text-sm">
                <option value="">All scores</option>
                <option value="90" @selected(request('min_score') === '90')>90% and above</option>
                <option value="75" @selected(request('min_score') === '75')>75% and above</option>
                <option value="50" @selected(request('min_score') === '50')>50% and above</option>
            </select>
            <button class="btn-secondary">Filter</button>
        </form>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="ui-card p-5"><p class="text-sm ui-muted">Tenants</p><p class="mt-2 text-2xl font-bold">{{ $tenants->count() }}</p></div>
            <div class="ui-card p-5"><p class="text-sm ui-muted">Score Pairs</p><p class="mt-2 text-2xl font-bold">{{ $scores->count() }}</p></div>
            <div class="ui-card p-5"><p class="text-sm ui-muted">Best Match</p><p class="mt-2 text-2xl font-bold">{{ $scores->max('percent') ?? 0 }}%</p></div>
        </div>

        <div class="ui-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[color:var(--surface-2)] text-xs uppercase ui-muted">
                        <tr>
                            <th class="px-5 py-3 text-left">Rank</th>
                            <th class="px-5 py-3 text-left">Tenant</th>
                            <th class="px-5 py-3 text-left">Candidate</th>
                            <th class="px-5 py-3 text-left">Score</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y ui-border">
                        @forelse ($scores as $score)
                            @php
                                $scorePayload = [
                                    'percent' => $score['percent'],
                                    'highlights' => $score['highlights'],
                                    'conflicts' => $score['conflicts'],
                                    'tenant_name' => $score['tenant']->name,
                                    'tenant_photo_url' => $score['tenant']->photo_url,
                                    'candidate_name' => $score['candidate']->name,
                                    'candidate_photo_url' => $score['candidate']->photo_url,
                                ];
                            @endphp
                            <tr
                                class="cursor-pointer transition hover:bg-slate-50/80 focus-within:bg-blue-50/40"
                                role="button"
                                tabindex="0"
                                @click="selected = {{ \Illuminate\Support\Js::from($scorePayload) }}; detailOpen = true"
                                @keydown.enter="selected = {{ \Illuminate\Support\Js::from($scorePayload) }}; detailOpen = true"
                                @keydown.space.prevent="selected = {{ \Illuminate\Support\Js::from($scorePayload) }}; detailOpen = true"
                            >
                                <td class="px-5 py-4 font-semibold">#{{ $loop->iteration }}</td>
                                <td class="px-5 py-4"><span class="flex items-center gap-2"><span class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-100 text-[10px] font-black text-blue-700">@if ($score['tenant']->photo_url)<img src="{{ $score['tenant']->photo_url }}" alt="{{ $score['tenant']->name }}" class="h-full w-full object-cover">@else{{ strtoupper(substr($score['tenant']->name, 0, 2)) }}@endif</span>{{ $score['tenant']->name }}</span></td>
                                <td class="px-5 py-4"><span class="flex items-center gap-2"><span class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-violet-100 text-[10px] font-black text-violet-700">@if ($score['candidate']->photo_url)<img src="{{ $score['candidate']->photo_url }}" alt="{{ $score['candidate']->name }}" class="h-full w-full object-cover">@else{{ strtoupper(substr($score['candidate']->name, 0, 2)) }}@endif</span>{{ $score['candidate']->name }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-2 w-32 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-[color:var(--brand-500)]" style="width: {{ $score['percent'] }}%"></div></div>
                                        <span class="font-semibold">{{ $score['percent'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center ui-muted">No compatibility scores available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="detailOpen" x-cloak @keydown.escape.window="detailOpen = false" class="bm-modal-overlay">
            <div class="bm-modal bm-modal--lg">
                <div class="bm-modal__header"><div><h2 class="bm-modal__title">Match Details</h2><div class="mt-2 flex flex-wrap items-center gap-4 text-sm font-semibold"><span class="flex items-center gap-2"><span class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-blue-100 text-xs text-blue-700"><template x-if="selected.tenant_photo_url"><img :src="selected.tenant_photo_url" :alt="selected.tenant_name" class="h-full w-full object-cover"></template><span x-show="!selected.tenant_photo_url" x-text="(selected.tenant_name || 'T').slice(0, 2).toUpperCase()"></span></span><span x-text="selected.tenant_name"></span></span><span class="ui-muted">and</span><span class="flex items-center gap-2"><span class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-violet-100 text-xs text-violet-700"><template x-if="selected.candidate_photo_url"><img :src="selected.candidate_photo_url" :alt="selected.candidate_name" class="h-full w-full object-cover"></template><span x-show="!selected.candidate_photo_url" x-text="(selected.candidate_name || 'C').slice(0, 2).toUpperCase()"></span></span><span x-text="selected.candidate_name"></span></span></div></div><button type="button" @click="detailOpen = false" class="bm-modal__close" aria-label="Close match details">&times;</button></div>
                <div class="bm-modal__body">
                    <div>
                        <p class="text-4xl font-bold text-[color:var(--brand-600)]" x-text="`${selected.percent || 0}%`"></p>
                        <p class="mt-2 text-sm ui-muted">Weighted compatibility score based on budget, lifestyle, habits, preferences, and interests.</p>
                    </div>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div>
                            <h3 class="font-semibold">Highlights</h3>
                            <template x-for="item in selected.highlights || []"><p class="mt-2 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-700" x-text="item"></p></template>
                        </div>
                        <div>
                            <h3 class="font-semibold">Conflicts</h3>
                            <template x-for="item in selected.conflicts || []"><p class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-700" x-text="item"></p></template>
                        </div>
                    </div>
                </div>
                <div class="bm-modal__footer"><button type="button" @click="detailOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button></div>
            </div>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
