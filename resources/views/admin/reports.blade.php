<x-layouts.dashboard>
<x-admin.shell :show-header="false">
@php
    $tab = $tab ?? 'overview';
    $range = $range ?? 'this_month';
    $rangeLabel = $rangeLabel ?? 'This Month';
    $rangeOptions = $rangeOptions ?? ['this_month' => 'This Month'];
    $distributionTotal = (int) ($bookingDistribution['total'] ?? 0);
    $distributionLabels = $bookingDistribution['labels'] ?? ['New', 'Confirmed', 'Currently Staying', 'Cancelled'];
    $distributionData = $bookingDistribution['data'] ?? [0, 0, 0, 0];
    $occupancyTotal = (int) ($occupancyChart['total'] ?? 0);
    $occupancyData = $occupancyChart['data'] ?? [0, 0];
    $summary = $reportSummary ?? [];
    $topPerformers = collect($topPerformingHouses ?? []);
    $activityFeed = collect($recentActivities ?? []);
    $insightCards = collect($aiInsights ?? []);

    $toneClasses = fn (string $tone): string => match ($tone) {
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'violet' => 'bg-violet-50 text-violet-700 ring-violet-100',
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-100',
        'slate' => 'bg-slate-100 text-slate-600 ring-slate-200',
        default => 'bg-blue-50 text-blue-700 ring-blue-100',
    };

    $cardPalette = [
        'bg-emerald-50 text-emerald-600' => ['tone' => 'emerald', 'spark' => '#10B981'],
        'bg-blue-50 text-blue-600' => ['tone' => 'blue', 'spark' => '#2563EB'],
        'bg-violet-50 text-violet-600' => ['tone' => 'violet', 'spark' => '#8B5CF6'],
        'bg-amber-50 text-amber-600' => ['tone' => 'amber', 'spark' => '#F59E0B'],
    ];
    $miniTrends = [
        [16, 19, 18, 22, 24, 23, 27, 29],
        [12, 15, 17, 16, 18, 20, 19, 22],
        [10, 12, 13, 15, 16, 18, 17, 19],
        [48, 52, 56, 58, 62, 64, 68, 71],
    ];
    $distributionColors = ['#2563EB', '#10B981', '#8B5CF6', '#F59E0B'];
    $occupancyColors = ['#2563EB', '#DBEAFE'];
@endphp

<div class="space-y-3 text-slate-950">
    <header class="rounded-xl border border-slate-200 bg-white/95 p-3.5 shadow-sm shadow-slate-200/60 backdrop-blur">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-blue-600">Analytics Center</p>
                <h1 class="mt-1 text-xl font-bold tracking-tight text-slate-950">Owner Reports Dashboard</h1>
                <p class="mt-0.5 text-xs text-slate-500">Compact revenue, occupancy, property performance, and booking analytics in one workspace.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex h-9 items-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-600">
                    Reporting window: {{ $rangeLabel }}
                </span>
                <a
                    href="{{ route('admin.reports.export', ['range' => $range]) }}"
                    class="inline-flex h-9 items-center justify-center gap-2 rounded-xl bg-blue-600 px-3.5 text-xs font-semibold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v10m0 0 4-4m-4 4-4-4"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 19h14"/>
                    </svg>
                    Export Report
                </a>
            </div>
        </div>
    </header>

    <section class="rounded-[1.35rem] border border-slate-200 bg-white p-3.5 shadow-sm shadow-slate-200/70">
        <div class="flex flex-col gap-3">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">Report Controls</p>
                    <p class="mt-0.5 text-xs text-slate-500">Adjust the date range, switch report modes, and export current results without leaving the analytics center.</p>
                </div>
                <p class="text-[11px] font-medium text-slate-400">Updated {{ now()->format('M d, Y') }}</p>
            </div>

            <div class="grid gap-2.5 xl:grid-cols-[220px_auto_minmax(0,1fr)_auto] xl:items-center">
                <form method="GET" action="{{ route('admin.reports.index') }}" class="contents">
                    <input type="hidden" name="tab" value="{{ $tab }}">

                    <label class="relative block xl:col-start-1">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.8"/>
                                <path stroke-linecap="round" stroke-width="1.8" d="M8 3v4M16 3v4M4 10h16"/>
                            </svg>
                        </span>
                        <select
                            name="range"
                            onchange="this.form.submit()"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-10 text-sm font-semibold text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                        >
                            @foreach ($rangeOptions as $value => $label)
                                <option value="{{ $value }}" @selected($range === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </form>

                <nav class="flex flex-wrap gap-2 xl:col-start-2" aria-label="Report tabs">
                    <a
                        href="{{ route('admin.reports.index', ['tab' => 'overview', 'range' => $range]) }}"
                        class="inline-flex h-10 items-center rounded-xl border px-3 text-xs font-semibold transition {{ $tab === 'overview' ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700' }}"
                    >
                        Overview
                    </a>
                    <a
                        href="{{ route('admin.reports.index', ['tab' => 'detailed', 'range' => $range]) }}"
                        class="inline-flex h-10 items-center rounded-xl border px-3 text-xs font-semibold transition {{ $tab === 'detailed' ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700' }}"
                    >
                        Detailed Reports
                    </a>
                </nav>

                <div class="flex flex-wrap items-center gap-2 xl:col-start-4 xl:justify-end">
                    <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-600">
                        {{ number_format((int) ($summary['totalProperties'] ?? 0)) }} properties
                    </span>
                    <a
                        href="{{ route('admin.reports.index', ['tab' => 'detailed', 'range' => $range]) }}#detailed-reports"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                    >
                        Open Table
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[1.35rem] border border-slate-200 bg-white p-3.5 shadow-sm shadow-slate-200/70">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">Performance Highlights</p>
                <p class="mt-0.5 text-xs text-slate-500">Compact KPI cards surface revenue, bookings, tenants, and occupancy with less whitespace.</p>
            </div>
            <p class="text-[11px] font-medium text-slate-400">Trend indicators compare against the previous period</p>
        </div>

        <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($kpiCards as $index => $card)
                @php
                    $palette = $cardPalette[$card['tone']] ?? ['tone' => 'blue', 'spark' => '#2563EB'];
                    $series = $miniTrends[$index] ?? $miniTrends[0];
                    $min = min($series);
                    $max = max($series);
                    $spread = max($max - $min, 1);
                    $points = collect($series)->values()->map(function ($value, $pointIndex) use ($series, $min, $spread) {
                        $x = (84 / max(count($series) - 1, 1)) * $pointIndex + 2;
                        $y = 38 - (($value - $min) / $spread) * 28;
                        return round($x, 2).','.round($y, 2);
                    })->implode(' ');
                @endphp
                <article class="rounded-[1.2rem] border border-slate-200 bg-white p-3 shadow-sm shadow-slate-200/60">
                    <div class="flex items-start justify-between gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl ring-1 {{ $toneClasses($palette['tone']) }}">
                            @if ($card['icon'] === 'revenue')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <circle cx="12" cy="12" r="8.5" stroke-width="1.8"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 7v10M9 9.5c0-1.1 1.3-2 3-2s3 .9 3 2-1.3 2-3 2-3 .9-3 2 1.3 2 3 2 3-.9 3-2"/>
                                </svg>
                            @elseif ($card['icon'] === 'bookings')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.8"/>
                                    <path stroke-linecap="round" stroke-width="1.8" d="M8 3v4M16 3v4M4 10h16"/>
                                </svg>
                            @elseif ($card['icon'] === 'tenants')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <circle cx="12" cy="8" r="3.5" stroke-width="1.8"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 20a7 7 0 0 1 14 0"/>
                                </svg>
                            @else
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v9l7 4"/>
                                    <circle cx="12" cy="12" r="9" stroke-width="1.8"/>
                                </svg>
                            @endif
                        </span>

                        <svg class="h-10 w-16 shrink-0" viewBox="0 0 88 44" fill="none" aria-hidden="true">
                            <polyline points="{{ $points }}" fill="none" stroke="{{ $palette['spark'] }}" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <p class="mt-3 text-[12px] font-semibold uppercase tracking-wide text-slate-400">{{ $card['label'] }}</p>
                    <p class="mt-1 text-[1.55rem] font-black tracking-tight text-slate-950">{{ $card['value'] }}</p>
                    <p class="mt-1.5 text-[12px] font-semibold {{ str_contains(strtolower($card['trend']), '-') ? 'text-rose-600' : 'text-emerald-600' }}">{{ $card['trend'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="grid gap-3 xl:grid-cols-[minmax(0,1.35fr)_360px]">
        <div class="space-y-3">
            @if ($tab === 'overview')
                <section class="grid gap-3 lg:grid-cols-2">
                    <article class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-base font-bold text-slate-950">Revenue Trend</h2>
                                <p class="mt-0.5 text-xs text-slate-500">Revenue performance across {{ strtolower($rangeLabel) }} with compact month or date buckets.</p>
                            </div>
                            <span class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-[11px] font-semibold text-slate-600">
                                Avg PHP {{ number_format((float) ($summary['averageRevenuePerHouse'] ?? 0), 0) }}/house
                            </span>
                        </div>
                        <div class="mt-4 h-64">
                            <canvas id="revenueTrendChart"></canvas>
                        </div>
                    </article>

                    <article class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-base font-bold text-slate-950">Occupancy Snapshot</h2>
                                <p class="mt-0.5 text-xs text-slate-500">Occupied versus vacant rooms for the current reporting window.</p>
                            </div>
                            <span class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-[11px] font-semibold text-slate-600">
                                {{ number_format((int) ($summary['occupiedRooms'] ?? 0)) }} occupied
                            </span>
                        </div>
                        <div class="mt-4 grid gap-4 sm:grid-cols-[176px_minmax(0,1fr)] sm:items-center">
                            <div class="relative mx-auto h-44 w-44">
                                <canvas id="occupancyOverviewChart"></canvas>
                                <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-[1.7rem] font-black tracking-tight text-slate-950">{{ $kpiCards[3]['value'] ?? '0%' }}</span>
                                    <span class="text-[11px] font-medium text-slate-500">Occupancy Rate</span>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="rounded-2xl bg-slate-50 px-3 py-2.5">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Portfolio Health</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-700">{{ number_format((int) ($summary['vacantRooms'] ?? 0)) }} rooms are still available for new bookings.</p>
                                </div>
                                @foreach (['Occupied Rooms', 'Vacant Rooms'] as $index => $label)
                                    @php
                                        $value = (int) ($occupancyData[$index] ?? 0);
                                        $percent = $occupancyTotal > 0 ? round(($value / $occupancyTotal) * 100) : 0;
                                    @endphp
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span class="inline-flex items-center gap-2 font-medium text-slate-700">
                                            <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $occupancyColors[$index] ?? '#CBD5E1' }}"></span>
                                            {{ $label }}
                                        </span>
                                        <span class="font-semibold text-slate-500">{{ number_format($value) }} ({{ $percent }}%)</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </article>
                </section>

                <section class="grid gap-3 lg:grid-cols-2">
                    <article class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-base font-bold text-slate-950">Booking Distribution</h2>
                                <p class="mt-0.5 text-xs text-slate-500">See where the booking pipeline is concentrated right now.</p>
                            </div>
                            <span class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-[11px] font-semibold text-slate-600">
                                {{ number_format($distributionTotal) }} total bookings
                            </span>
                        </div>
                        <div class="mt-4 grid gap-4 sm:grid-cols-[176px_minmax(0,1fr)] sm:items-center">
                            <div class="relative mx-auto h-44 w-44">
                                <canvas id="bookingDistributionChart"></canvas>
                                <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-[1.7rem] font-black tracking-tight text-slate-950">{{ number_format($distributionTotal) }}</span>
                                    <span class="text-[11px] font-medium text-slate-500">Bookings</span>
                                </div>
                            </div>
                            <div class="space-y-3">
                                @foreach ($distributionLabels as $index => $label)
                                    @php
                                        $count = (int) ($distributionData[$index] ?? 0);
                                        $percent = $distributionTotal > 0 ? round(($count / $distributionTotal) * 100) : 0;
                                    @endphp
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span class="inline-flex items-center gap-2 font-medium text-slate-700">
                                            <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $distributionColors[$index] ?? '#94A3B8' }}"></span>
                                            {{ $label }}
                                        </span>
                                        <span class="font-semibold text-slate-500">{{ number_format($count) }} ({{ $percent }}%)</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </article>

                    <article class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-base font-bold text-slate-950">Portfolio Health</h2>
                                <p class="mt-0.5 text-xs text-slate-500">A denser snapshot of rooms, bookings, and portfolio averages.</p>
                            </div>
                            <span class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-[11px] font-semibold text-slate-600">
                                {{ $rangeLabel }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Total Rooms</p>
                                <p class="mt-1 text-xl font-black tracking-tight text-slate-950">{{ number_format((int) ($summary['totalRooms'] ?? 0)) }}</p>
                                <p class="mt-1 text-[12px] text-slate-500">{{ number_format((int) ($summary['occupiedRooms'] ?? 0)) }} occupied</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Avg Bookings / House</p>
                                <p class="mt-1 text-xl font-black tracking-tight text-slate-950">{{ number_format((float) ($summary['averageBookingsPerHouse'] ?? 0), 1) }}</p>
                                <p class="mt-1 text-[12px] text-slate-500">Across {{ number_format((int) ($summary['totalProperties'] ?? 0)) }} properties</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Vacant Capacity</p>
                                <p class="mt-1 text-xl font-black tracking-tight text-slate-950">{{ number_format((int) ($summary['vacantRooms'] ?? 0)) }}</p>
                                <p class="mt-1 text-[12px] text-slate-500">Rooms still open for new tenants</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Top Performer</p>
                                <p class="mt-1 truncate text-base font-bold text-slate-950">{{ $topPerformers->first()['boarding_house'] ?? 'No property yet' }}</p>
                                <p class="mt-1 text-[12px] text-slate-500">Highest weighted revenue and occupancy score</p>
                            </div>
                        </div>
                    </article>
                </section>
            @endif

            <section id="detailed-reports" class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-white shadow-sm shadow-slate-200/70">
                <div class="border-b border-slate-200 px-4 py-3.5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-950">Detailed Reports</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Property-by-property revenue, booking, occupancy, and tenant counts with the current filters applied.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-[11px] font-semibold text-slate-600">
                                {{ number_format(method_exists($reportRows, 'total') ? $reportRows->total() : count($reportRows)) }} records
                            </span>
                            @if ($tab !== 'detailed')
                                <a
                                    href="{{ route('admin.reports.index', ['tab' => 'detailed', 'range' => $range]) }}#detailed-reports"
                                    class="inline-flex h-8 items-center gap-1.5 text-[12px] font-bold text-blue-700 transition hover:text-blue-800"
                                >
                                    View All Reports
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[980px] w-full text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50/80">
                            <tr class="text-[11px] font-bold uppercase tracking-wide text-slate-500">
                                <th class="px-4 py-3">Boarding House</th>
                                <th class="px-4 py-3">Revenue</th>
                                <th class="px-4 py-3">Bookings</th>
                                <th class="px-4 py-3">Occupancy</th>
                                <th class="px-4 py-3">Tenants</th>
                                <th class="px-4 py-3">Rooms</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($reportRows as $row)
                                <tr class="transition hover:bg-slate-50/80">
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <img
                                                src="{{ $row['cover_image_url'] ?? asset('images/boarding-house-placeholder.svg') }}"
                                                alt="{{ $row['boarding_house'] }}"
                                                class="h-10 w-14 rounded-lg border border-slate-200 object-cover"
                                                onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}';"
                                            >
                                            <div class="min-w-0">
                                                <p class="truncate font-bold text-slate-900">{{ $row['boarding_house'] }}</p>
                                                <p class="truncate text-[12px] text-slate-500">{{ $row['location'] ?? 'Location not set' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 font-semibold text-slate-700">PHP {{ number_format((float) $row['revenue'], 2) }}</td>
                                    <td class="px-4 py-3.5 text-slate-700">{{ number_format((int) $row['bookings']) }}</td>
                                    <td class="px-4 py-3.5">
                                        <div class="min-w-[118px]">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="font-semibold text-slate-700">{{ $row['occupancy_rate'] }}%</span>
                                                <span class="text-[11px] text-slate-400">{{ number_format((int) ($row['occupied_rooms'] ?? 0)) }}/{{ number_format((int) ($row['rooms'] ?? 0)) }}</span>
                                            </div>
                                            <div class="mt-1.5 h-1.5 rounded-full bg-slate-100">
                                                <div class="h-1.5 rounded-full bg-blue-600" style="width: {{ min(max((int) $row['occupancy_rate'], 0), 100) }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-700">{{ number_format((int) $row['tenants']) }}</td>
                                    <td class="px-4 py-3.5 text-slate-700">{{ number_format((int) ($row['rooms'] ?? 0)) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12">
                                        <div class="mx-auto max-w-md text-center">
                                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 4h10v16H7z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 8h4M10 12h4M10 16h2"/>
                                                </svg>
                                            </div>
                                            <p class="mt-4 text-lg font-bold text-slate-950">No report data yet</p>
                                            <p class="mt-2 text-sm leading-6 text-slate-500">Report data will appear once boarding houses, reservations, and transactions are recorded.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (method_exists($reportRows, 'hasPages') && $reportRows->hasPages())
                    <div class="flex justify-center border-t border-slate-200 px-6 py-4">
                        <nav class="flex items-center gap-2" aria-label="Reports pagination">
                            @if ($reportRows->onFirstPage())
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
                                </span>
                            @else
                                <a href="{{ $reportRows->previousPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
                                </a>
                            @endif

                            @for ($page = 1; $page <= $reportRows->lastPage(); $page++)
                                @if ($page === $reportRows->currentPage())
                                    <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-blue-600 px-3 text-sm font-bold text-white shadow-sm shadow-blue-600/20">{{ $page }}</span>
                                @else
                                    <a href="{{ $reportRows->url($page) }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">{{ $page }}</a>
                                @endif
                            @endfor

                            @if ($reportRows->hasMorePages())
                                <a href="{{ $reportRows->nextPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
                                </a>
                            @else
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
                                </span>
                            @endif
                        </nav>
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-3">
            <section class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">AI Insights</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Fast readouts based on the live dashboard metrics for {{ strtolower($rangeLabel) }}.</p>
                    </div>
                    <span class="inline-flex h-8 items-center rounded-lg bg-blue-50 px-2.5 text-[11px] font-semibold text-blue-700">BoardMatch AI</span>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($insightCards as $insight)
                        <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-sm font-bold text-slate-950">{{ $insight['title'] }}</h3>
                                <span class="inline-flex h-7 items-center rounded-full px-2.5 text-[10px] font-bold ring-1 {{ $toneClasses($insight['tone'] ?? 'blue') }}">
                                    {{ ucfirst($insight['tone'] ?? 'blue') }}
                                </span>
                            </div>
                            <p class="mt-2 text-[13px] font-semibold text-slate-700">{{ $insight['summary'] }}</p>
                            <p class="mt-1 text-[12px] leading-5 text-slate-500">{{ $insight['detail'] }}</p>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4 text-center">
                            <p class="text-sm font-semibold text-slate-900">AI insights will appear here</p>
                            <p class="mt-1 text-xs text-slate-500">Once reports have enough data, the dashboard will surface trends and opportunities.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">Top-Performing Boarding Houses</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Best-performing properties ranked by revenue, occupancy, bookings, and tenants.</p>
                    </div>
                    <span class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-[11px] font-semibold text-slate-600">
                        Top {{ number_format($topPerformers->count()) }}
                    </span>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($topPerformers as $index => $property)
                        <article class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm shadow-slate-200/40">
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-xs font-bold text-blue-700">
                                {{ $index + 1 }}
                            </span>
                            <img
                                src="{{ $property['cover_image_url'] ?? asset('images/boarding-house-placeholder.svg') }}"
                                alt="{{ $property['boarding_house'] }}"
                                class="h-12 w-16 rounded-xl border border-slate-200 object-cover"
                                onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}';"
                            >
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-slate-950">{{ $property['boarding_house'] }}</p>
                                <p class="truncate text-[12px] text-slate-500">{{ $property['location'] ?? 'Location not set' }}</p>
                                <div class="mt-2 flex flex-wrap gap-2 text-[11px] font-semibold text-slate-500">
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5">{{ $property['occupancy_rate'] }}% occupied</span>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5">{{ number_format((int) $property['bookings']) }} bookings</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-[12px] font-semibold text-slate-400">Revenue</p>
                                <p class="mt-1 text-sm font-black text-emerald-600">PHP {{ number_format((float) $property['revenue'], 0) }}</p>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4 text-center">
                            <p class="text-sm font-semibold text-slate-900">No property performance data yet</p>
                            <p class="mt-1 text-xs text-slate-500">Rankings will show once bookings, rooms, and payments are recorded.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">Recent Activities</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Latest reservation, payment, inquiry, and listing updates from across the portfolio.</p>
                    </div>
                    <a href="{{ route('admin.notifications.index') }}" class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-white px-2.5 text-[11px] font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        View All
                    </a>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($activityFeed as $activity)
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700 ring-1 ring-blue-100">
                                @include('components.sidebar.partials.admin-icon', ['name' => $activity['icon']])
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-950">{{ $activity['title'] }}</p>
                                <p class="mt-0.5 text-[12px] leading-5 text-slate-500">{{ $activity['description'] }}</p>
                                <div class="mt-1.5 flex flex-wrap items-center gap-2 text-[11px]">
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 font-semibold text-slate-600">{{ $activity['badge'] ?? 'Updated' }}</span>
                                    <span class="text-slate-400">{{ $activity['time']?->diffForHumans() ?? 'Recently' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4 text-center">
                            <p class="text-sm font-semibold text-slate-900">No recent activities yet</p>
                            <p class="mt-1 text-xs text-slate-500">New reservations, payments, inquiries, and room updates will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </aside>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') return;

        const revenueCanvas = document.getElementById('revenueTrendChart');
        const bookingCanvas = document.getElementById('bookingDistributionChart');
        const occupancyCanvas = document.getElementById('occupancyOverviewChart');
        const revenueChart = @json($revenueTrendChart);
        const bookingChart = @json($bookingDistribution);
        const occupancyChart = @json($occupancyChart);
        const tickStyles = {
            color: '#64748B',
            font: { size: 10, family: 'Manrope, sans-serif' }
        };

        if (revenueCanvas) {
            const revenueContext = revenueCanvas.getContext('2d');
            const revenueGradient = revenueContext.createLinearGradient(0, 0, 0, revenueCanvas.height);
            revenueGradient.addColorStop(0, 'rgba(37, 99, 235, 0.22)');
            revenueGradient.addColorStop(1, 'rgba(37, 99, 235, 0.02)');

            new Chart(revenueCanvas, {
                type: 'line',
                data: {
                    labels: revenueChart.labels,
                    datasets: [{
                        label: 'Revenue (PHP)',
                        data: revenueChart.data,
                        borderColor: '#2563EB',
                        backgroundColor: revenueGradient,
                        pointBackgroundColor: '#FFFFFF',
                        pointBorderColor: '#2563EB',
                        pointBorderWidth: 2.2,
                        pointRadius: 3.2,
                        pointHoverRadius: 4.5,
                        tension: 0.35,
                        borderWidth: 2.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                ...tickStyles,
                                callback: value => 'PHP ' + Number(value).toLocaleString()
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.18)',
                                drawBorder: false
                            },
                            border: { display: false }
                        },
                        x: {
                            ticks: tickStyles,
                            grid: { display: false, drawBorder: false },
                            border: { display: false }
                        }
                    }
                }
            });
        }

        if (bookingCanvas) {
            new Chart(bookingCanvas, {
                type: 'doughnut',
                data: {
                    labels: bookingChart.labels,
                    datasets: [{
                        data: bookingChart.data,
                        backgroundColor: ['#2563EB', '#10B981', '#8B5CF6', '#F59E0B'],
                        borderColor: '#FFFFFF',
                        borderWidth: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: { legend: { display: false } }
                }
            });
        }

        if (occupancyCanvas) {
            new Chart(occupancyCanvas, {
                type: 'doughnut',
                data: {
                    labels: occupancyChart.labels,
                    datasets: [{
                        data: occupancyChart.data,
                        backgroundColor: ['#2563EB', '#DBEAFE'],
                        borderColor: '#FFFFFF',
                        borderWidth: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '74%',
                    plugins: { legend: { display: false } }
                }
            });
        }
    });
</script>
</x-admin.shell>
</x-layouts.dashboard>
