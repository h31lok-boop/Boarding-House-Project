<x-layouts.dashboard>
<x-admin.shell :show-header="true">
    @php
        $propertiesCount = max((int) ($totalBoardingHouses ?? $totalProperties ?? 0), 0);
        $roomsCount = max((int) ($totalRooms ?? 0), 0);
        $availableCount = max((int) ($availableRooms ?? 0), 0);
        $tenantCount = max((int) ($activeTenants ?? $activeTenantCount ?? 0), 0);
        $occupancy = min(max((int) ($occupancyRate ?? 0), 0), 100);
        $revenue = max((float) ($totalRevenue ?? $monthlyIncome ?? 0), 0);
        $attentionItems = collect($pendingActions ?? [])
            ->filter(fn ($item) => (int) ($item['count'] ?? 0) > 0)
            ->take(4);
        $activities = collect($recentActivities ?? [])->take(5);

        $metricCards = [
            [
                'label' => 'Boarding Houses',
                'value' => number_format($propertiesCount),
                'meta' => number_format($roomsCount).' total rooms',
                'href' => route('admin.boarding-houses'),
                'tone' => 'blue',
            ],
            [
                'label' => 'Occupancy',
                'value' => $occupancy.'%',
                'meta' => number_format($availableCount).' rooms available',
                'href' => route('admin.boarding-houses'),
                'tone' => 'emerald',
            ],
            [
                'label' => 'Active Tenants',
                'value' => number_format($tenantCount),
                'meta' => 'Currently housed',
                'href' => route('admin.tenants.index'),
                'tone' => 'violet',
            ],
            [
                'label' => 'Collected Revenue',
                'value' => 'PHP '.number_format($revenue, 0),
                'meta' => 'Verified payments',
                'href' => route('admin.payments'),
                'tone' => 'amber',
            ],
        ];

        $metricTones = [
            'blue' => 'bg-blue-500/10 text-blue-600 dark:text-blue-300',
            'emerald' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-300',
            'violet' => 'bg-violet-500/10 text-violet-600 dark:text-violet-300',
            'amber' => 'bg-amber-500/10 text-amber-600 dark:text-amber-300',
        ];
    @endphp

    <div class="space-y-5">
        <section class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-300">Platform overview</p>
                <h1 class="mt-1 text-xl font-black text-slate-950 dark:text-white">Admin Dashboard</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">The key numbers and tasks that need attention today.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.users') }}" class="inline-flex h-9 items-center rounded-xl border border-slate-200 px-3 text-xs font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Manage users</a>
                <a href="{{ route('admin.reports.index') }}" class="inline-flex h-9 items-center rounded-xl bg-blue-600 px-3 text-xs font-bold text-white transition hover:bg-blue-700">View reports</a>
            </div>
        </section>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Admin dashboard summary">
            @foreach ($metricCards as $index => $metric)
                <a href="{{ $metric['href'] }}" class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:hover:border-slate-600">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ $metric['label'] }}</p>
                            <p class="mt-2 truncate text-2xl font-black text-slate-950 dark:text-white">{{ $metric['value'] }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $metric['meta'] }}</p>
                        </div>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $metricTones[$metric['tone']] }}">
                            @if ($index === 0)
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V8l7-4 7 4v13M9 21v-6h6v6"/></svg>
                            @elseif ($index === 1)
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19V9m5 10V5m5 14v-7m5 7V3"/></svg>
                            @elseif ($index === 2)
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87"/></svg>
                            @else
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m3-9.5c-.7-.9-1.7-1.5-3-1.5-1.7 0-3 1-3 2.5s1.3 2.2 3 2.5 3 1 3 2.5-1.3 2.5-3 2.5c-1.3 0-2.5-.6-3.2-1.6"/></svg>
                            @endif
                        </span>
                    </div>
                </a>
            @endforeach
        </section>

        <section class="grid gap-5 lg:grid-cols-[0.85fr_1.15fr]">
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <header class="border-b border-slate-100 px-5 py-4 dark:border-slate-700">
                    <h2 class="text-base font-black text-slate-950 dark:text-white">Needs attention</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Only outstanding operational tasks are shown.</p>
                </header>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($attentionItems as $item)
                        <a href="{{ $item['href'] ?? route('admin.dashboard') }}" class="flex items-center gap-3 px-5 py-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/70">
                            <span class="flex h-9 min-w-9 items-center justify-center rounded-xl bg-amber-500/10 text-sm font-black text-amber-600 dark:text-amber-300">{{ (int) ($item['count'] ?? 0) }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-bold text-slate-900 dark:text-white">{{ $item['label'] ?? 'Pending task' }}</span>
                                <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Open and review this queue</span>
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9 18 6-6-6-6"/></svg>
                        </a>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <p class="text-sm font-bold text-slate-900 dark:text-white">No urgent tasks</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">All operational queues are clear.</p>
                        </div>
                    @endforelse
                </div>
            </article>

            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <header class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-700">
                    <div>
                        <h2 class="text-base font-black text-slate-950 dark:text-white">Recent activity</h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Latest verified platform changes.</p>
                    </div>
                    <a href="{{ route('admin.notifications.index') }}" class="text-xs font-bold text-blue-600 dark:text-blue-300">Notifications</a>
                </header>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($activities as $activity)
                        @php
                            try {
                                $activityTime = $activity['time'] instanceof \DateTimeInterface
                                    ? \Illuminate\Support\Carbon::instance($activity['time'])->diffForHumans()
                                    : \Illuminate\Support\Carbon::parse($activity['time'])->diffForHumans();
                            } catch (\Throwable) {
                                $activityTime = 'Recently';
                            }
                        @endphp
                        <div class="flex items-start gap-3 px-5 py-3.5">
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-blue-500 ring-4 ring-blue-500/10"></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $activity['title'] ?? 'Activity updated' }}</p>
                                    <time class="shrink-0 text-[10px] font-semibold text-slate-400">{{ $activityTime }}</time>
                                </div>
                                <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $activity['description'] ?? '' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No recent activity.</div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
