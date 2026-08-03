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
                            <tr
                                class="cursor-pointer transition hover:bg-slate-50/80 focus-within:bg-blue-50/40"
                                role="button"
                                tabindex="0"
                                @click="selected = {{ \Illuminate\Support\Js::from($score) }}; detailOpen = true"
                                @keydown.enter="selected = {{ \Illuminate\Support\Js::from($score) }}; detailOpen = true"
                                @keydown.space.prevent="selected = {{ \Illuminate\Support\Js::from($score) }}; detailOpen = true"
                            >
                                <td class="px-5 py-4 font-semibold">#{{ $loop->iteration }}</td>
                                <td class="px-5 py-4">{{ $score['tenant']->name }}</td>
                                <td class="px-5 py-4">{{ $score['candidate']->name }}</td>
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
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Match Details</h2><button type="button" @click="detailOpen = false" class="text-xl ui-muted">x</button></div>
                <div class="mt-5">
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
                <div class="bm-modal__footer"><button type="button" @click="detailOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button></div>
            </div>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
