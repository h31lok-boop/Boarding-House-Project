<x-layouts.dashboard>
<x-admin.shell>
    <div x-data="{ detailOpen: false, selected: {} }" class="space-y-6">
        <div class="ui-card p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Matchmaking</p>
            <h1 class="mt-2 text-2xl font-bold">Recommendations</h1>
            <p class="mt-2 text-sm ui-muted">Review ranked boarding house recommendations for selected tenants.</p>
        </div>

        <form method="GET" class="ui-card p-4 grid gap-3 md:grid-cols-[1fr_auto]">
            <select name="tenant_id" class="ui-input text-sm">
                @forelse ($tenants as $option)
                    <option value="{{ $option->id }}" @selected($tenant?->id === $option->id)>{{ $option->name }} · {{ $option->email }}</option>
                @empty
                    <option value="">No tenants available</option>
                @endforelse
            </select>
            <button class="btn-secondary">Show Recommendations</button>
        </form>

        @unless ($hasProfiles)
            <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                Match profiles are not available in this database, so rankings use listing status, price, and availability as fallback signals.
            </div>
        @endunless

        <div class="grid gap-4 md:grid-cols-3">
            <div class="ui-card p-5"><p class="text-sm ui-muted">Selected Tenant</p><p class="mt-2 text-xl font-bold">{{ $tenant?->name ?? 'None' }}</p></div>
            <div class="ui-card p-5"><p class="text-sm ui-muted">Recommendations</p><p class="mt-2 text-2xl font-bold">{{ $recommendations->count() }}</p></div>
            <div class="ui-card p-5"><p class="text-sm ui-muted">Top Score</p><p class="mt-2 text-2xl font-bold">{{ $recommendations->max('percent') ?? 0 }}%</p></div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse ($recommendations as $item)
                @php
                    $house = $item['house'];
                    $payload = [
                        'name' => $house->name,
                        'address' => $house->address ?: $house->full_address,
                        'percent' => $item['percent'],
                        'price' => $house->effective_price ? 'PHP '.number_format((float) $house->effective_price, 2) : 'Not set',
                        'reasons' => $item['reasons'],
                        'warnings' => $item['warnings'],
                    ];
                @endphp
                <article class="ui-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] ui-muted">Rank #{{ $loop->iteration }}</p>
                            <h2 class="mt-2 text-lg font-semibold">{{ $house->name }}</h2>
                            <p class="mt-1 text-sm ui-muted">{{ $house->address ?: $house->full_address ?: 'No address set' }}</p>
                        </div>
                        <span class="rounded-full bg-[color:var(--surface-2)] px-3 py-1 text-sm font-bold text-[color:var(--brand-600)]">{{ $item['percent'] }}%</span>
                    </div>
                    <div class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                        <div><p class="ui-muted">Fee</p><p class="font-semibold">{{ $house->effective_price ? 'PHP '.number_format((float) $house->effective_price, 2) : 'Not set' }}</p></div>
                        <div><p class="ui-muted">Rooms</p><p class="font-semibold">{{ $house->rooms->count() }}</p></div>
                        <div><p class="ui-muted">Availability</p><p class="font-semibold">{{ $house->available_rooms ?? $house->rooms->where('status', 'Available')->count() }}</p></div>
                    </div>
                    <button type="button" class="btn-secondary mt-5 w-full" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; detailOpen = true">View Match Details</button>
                </article>
            @empty
                <div class="ui-card p-6 text-sm ui-muted">No recommendations available.</div>
            @endforelse
        </div>

        <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="ui-card w-full max-w-xl p-6">
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold" x-text="selected.name"></h2><button type="button" @click="detailOpen = false" class="text-xl ui-muted">x</button></div>
                <p class="mt-2 text-sm ui-muted" x-text="selected.address"></p>
                <p class="mt-5 text-4xl font-bold text-[color:var(--brand-600)]" x-text="`${selected.percent || 0}%`"></p>
                <p class="mt-1 text-sm ui-muted">Recommendation score</p>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div>
                        <h3 class="font-semibold">Reasons</h3>
                        <template x-for="item in selected.reasons || []"><p class="mt-2 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-700" x-text="item"></p></template>
                    </div>
                    <div>
                        <h3 class="font-semibold">Warnings</h3>
                        <template x-for="item in selected.warnings || []"><p class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-700" x-text="item"></p></template>
                    </div>
                </div>
                <div class="mt-6 flex justify-end"><button type="button" @click="detailOpen = false" class="btn-secondary">Close</button></div>
            </div>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
