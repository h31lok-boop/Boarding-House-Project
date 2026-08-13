<x-layouts.dashboard>
<x-admin.shell :show-header="true">
    @php
        $properties = collect($properties ?? []);
        $propertyRows = collect($propertyRows ?? []);
        $attention = collect($needsAttention ?? [])->filter(fn ($item) => (int) ($item['count'] ?? 0) > 0)->take(3);
        $activities = collect($recentActivity ?? [])->take(5);
        $firstName = strtok(trim((string) ($ownerName ?? 'Owner')), ' ') ?: 'Owner';
        $money = fn ($amount) => 'PHP '.number_format((float) $amount, 0);
        $totalRooms = max((int) ($totalRooms ?? 0), 0);
        $availableRooms = max((int) ($availableRooms ?? 0), 0);
        $occupancyRate = min(max((int) ($occupancyRate ?? 0), 0), 100);
        $activeTenants = max((int) ($activeTenantCount ?? 0), 0);
        $pendingReservations = max((int) ($pendingReservationsCount ?? 0), 0);
        $monthlyRevenue = max((float) ($monthlyRevenue ?? 0), 0);

        $ownerMetrics = [
            ['label' => 'Monthly Revenue', 'value' => $money($monthlyRevenue), 'meta' => $selectedMonthLabel ?? now()->format('F Y'), 'href' => route('owner.payments'), 'tone' => 'emerald'],
            ['label' => 'Occupancy', 'value' => $occupancyRate.'%', 'meta' => number_format($availableRooms).' available of '.number_format($totalRooms), 'href' => route('owner.rooms'), 'tone' => 'blue'],
            ['label' => 'Active Tenants', 'value' => number_format($activeTenants), 'meta' => 'Across selected properties', 'href' => route('owner.tenants.index'), 'tone' => 'violet'],
            ['label' => 'Pending Reservations', 'value' => number_format($pendingReservations), 'meta' => $pendingReservations > 0 ? 'Waiting for review' : 'Queue is clear', 'href' => route('owner.reservations', ['status' => 'pending']), 'tone' => 'amber'],
        ];

        $toneClasses = [
            'emerald' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-300',
            'blue' => 'bg-blue-500/10 text-blue-600 dark:text-blue-300',
            'violet' => 'bg-violet-500/10 text-violet-600 dark:text-violet-300',
            'amber' => 'bg-amber-500/10 text-amber-600 dark:text-amber-300',
        ];
    @endphp

    <div class="space-y-5">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-300">Owner overview</p>
                    <h1 class="mt-1 text-xl font-black text-slate-950 dark:text-white">Welcome back, {{ $firstName }}</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Your property operations at a glance.</p>
                </div>
                <form method="GET" action="{{ route('owner.dashboard') }}" class="flex flex-col gap-2 sm:flex-row sm:items-end">
                    <label class="block">
                        <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Property</span>
                        <select name="property" class="h-10 w-full rounded-xl border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-white sm:w-52">
                            <option value="all">All properties</option>
                            @foreach ($properties as $property)
                                <option value="{{ $property->id }}" @selected(($selectedPropertyId ?? null) === (int) $property->id)>{{ $property->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Month</span>
                        <input type="month" name="month" value="{{ $selectedMonth ?? now()->format('Y-m') }}" max="{{ $maxMonth ?? now()->format('Y-m') }}" class="h-10 w-full rounded-xl border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-white sm:w-40">
                    </label>
                    <button class="h-10 rounded-xl bg-blue-600 px-4 text-xs font-bold text-white transition hover:bg-blue-700">Apply</button>
                </form>
            </div>
        </section>

        @if (! ($hasProperty ?? false))
            <section class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-lg font-black text-slate-950 dark:text-white">Add your first property</h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500 dark:text-slate-400">Create a boarding-house listing before managing rooms, reservations, tenants, and payments.</p>
                <a href="{{ route('owner.boarding-houses.create') }}" class="mt-5 inline-flex h-10 items-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white hover:bg-blue-700">Add property</a>
            </section>
        @else
            <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Owner dashboard summary">
                @foreach ($ownerMetrics as $metric)
                    <a href="{{ $metric['href'] }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:hover:border-slate-600">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ $metric['label'] }}</p>
                                <p class="mt-2 truncate text-2xl font-black text-slate-950 dark:text-white">{{ $metric['value'] }}</p>
                                <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $metric['meta'] }}</p>
                            </div>
                            <span class="mt-1 h-3 w-3 shrink-0 rounded-full {{ $toneClasses[$metric['tone']] }}"></span>
                        </div>
                    </a>
                @endforeach
            </section>

            <x-dashboard-chart-pair
                id-prefix="owner-dashboard"
                pie-title="Room Occupancy"
                pie-description="Room status for the selected properties."
                :pie-labels="['Occupied', 'Available', 'Reserved']"
                :pie-data="[(int) ($occupiedRooms ?? 0), $availableRooms, (int) ($reservedRooms ?? 0)]"
                line-title="Revenue Trend"
                line-description="Collected payments across the last six reporting months."
                :line-labels="$revenueChart['labels'] ?? []"
                :line-data="$revenueChart['data'] ?? []"
                line-dataset-label="Collected revenue"
                :currency="true"
            />

            <section class="grid gap-5 lg:grid-cols-[1.2fr_.8fr]">
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <header class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-700">
                        <div>
                            <h2 class="text-base font-black text-slate-950 dark:text-white">My properties</h2>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Showing up to four selected properties.</p>
                        </div>
                        <a href="{{ route('owner.my-boarding-house') }}" class="text-xs font-bold text-blue-600 dark:text-blue-300">Manage all</a>
                    </header>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($propertyRows->take(4) as $row)
                            <a href="{{ route('owner.dashboard', ['property' => $row['id'], 'month' => $selectedMonth ?? now()->format('Y-m')]) }}" class="flex items-center gap-3 px-5 py-3.5 transition hover:bg-slate-50 dark:hover:bg-slate-800/70">
                                <img src="{{ $row['image'] }}" alt="" class="h-12 w-14 shrink-0 rounded-xl object-cover ring-1 ring-slate-200 dark:ring-slate-700" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}'">
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-bold text-slate-900 dark:text-white">{{ $row['name'] }}</span>
                                    <span class="mt-1 block truncate text-xs text-slate-500 dark:text-slate-400">{{ $row['location'] }}</span>
                                </span>
                                <span class="shrink-0 text-right">
                                    <span class="block text-sm font-black text-slate-950 dark:text-white">{{ $row['occupancyRate'] }}%</span>
                                    <span class="text-[10px] text-slate-400">occupancy</span>
                                </span>
                            </a>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No property in this view.</div>
                        @endforelse
                    </div>
                </article>

                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <header class="border-b border-slate-100 px-5 py-4 dark:border-slate-700">
                        <h2 class="text-base font-black text-slate-950 dark:text-white">Needs attention</h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Outstanding work only.</p>
                    </header>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($attention as $item)
                            <a href="{{ $item['href'] }}" class="flex items-center gap-3 px-5 py-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/70">
                                <span class="flex h-9 min-w-9 items-center justify-center rounded-xl bg-amber-500/10 text-sm font-black text-amber-600 dark:text-amber-300">{{ $item['count'] }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-bold text-slate-900 dark:text-white">{{ $item['label'] }}</span>
                                    <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">{{ $item['description'] }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="px-5 py-10 text-center">
                                <p class="text-sm font-bold text-slate-900 dark:text-white">Everything is up to date</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">There are no urgent owner tasks.</p>
                            </div>
                        @endforelse
                    </div>
                </article>
            </section>

            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <header class="border-b border-slate-100 px-5 py-4 dark:border-slate-700">
                    <h2 class="text-base font-black text-slate-950 dark:text-white">Recent activity</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Latest reservation, payment, tenant, and room updates.</p>
                </header>
                <div class="grid divide-y divide-slate-100 dark:divide-slate-700 md:grid-cols-2 md:divide-x md:divide-y-0">
                    @forelse ($activities as $event)
                        @php
                            try {
                                $eventDate = \Illuminate\Support\Carbon::parse($event['at']);
                                $eventTime = $eventDate->diffForHumans();
                            } catch (\Throwable) {
                                $eventTime = 'Recently';
                            }
                        @endphp
                        <div class="flex items-start gap-3 px-5 py-4 md:border-b md:border-slate-100 md:dark:border-slate-700">
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-blue-500 ring-4 ring-blue-500/10"></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $event['title'] }}</p>
                                    <time class="shrink-0 text-[10px] font-semibold text-slate-400">{{ $eventTime }}</time>
                                </div>
                                <p class="mt-1 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">{{ $event['description'] }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No recent activity.</div>
                    @endforelse
                </div>
            </article>
        @endif
    </div>
</x-admin.shell>
</x-layouts.dashboard>
