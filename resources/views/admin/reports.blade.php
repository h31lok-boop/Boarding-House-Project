<x-layouts.dashboard>
<x-admin.shell :show-header="false">
@php
    $range = $range ?? 'this_month';
    $rangeLabel = $rangeLabel ?? 'This Month';
    $rangeOptions = $rangeOptions ?? ['this_month' => 'This Month'];
    $summary = $reportSummary ?? [];
    $distributionTotal = (int) ($bookingDistribution['total'] ?? 0);
    $distributionLabels = $bookingDistribution['labels'] ?? ['New', 'Confirmed', 'Currently Staying', 'Cancelled'];
    $distributionData = $bookingDistribution['data'] ?? [0, 0, 0, 0];
    $occupancyData = $occupancyChart['data'] ?? [0, 0];
    $occupancyTotal = max((int) ($occupancyChart['total'] ?? 0), 0);
@endphp

<div class="space-y-3 text-slate-950 dark:text-slate-100" data-simple-reports-dashboard>
    <header class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-xl font-black tracking-tight sm:text-2xl">Reports</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Revenue, bookings, occupancy, tenants, and property performance.</p>
            </div>

            <div class="flex flex-wrap items-end gap-2">
                <form method="GET" action="{{ route('admin.reports.index') }}">
                    <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400">
                        Reporting period
                        <select
                            name="range"
                            onchange="this.form.submit()"
                            class="mt-1 block h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-800 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        >
                            @foreach ($rangeOptions as $value => $label)
                                <option value="{{ $value }}" @selected($range === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </form>
                <a
                    href="{{ route('admin.reports.export', ['range' => $range]) }}"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v10m0 0 4-4m-4 4-4-4M5 19h14"/>
                    </svg>
                    Export CSV
                </a>
            </div>
        </div>
    </header>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Report summary">
        @foreach ($kpiCards as $card)
            @php
                $accent = match ($card['icon']) {
                    'revenue' => 'text-emerald-600 dark:text-emerald-300',
                    'bookings' => 'text-blue-600 dark:text-blue-300',
                    'tenants' => 'text-violet-600 dark:text-violet-300',
                    default => 'text-amber-600 dark:text-amber-300',
                };
            @endphp
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <p class="text-[11px] font-black uppercase tracking-[0.1em] text-slate-500 dark:text-slate-400">{{ $card['label'] }}</p>
                <p class="mt-2 text-2xl font-black {{ $accent }}">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $card['trend'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid gap-3 xl:grid-cols-[minmax(0,1.4fr)_minmax(300px,0.6fr)]">
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h2 class="text-base font-black">Revenue trend</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Paid transactions during {{ strtolower($rangeLabel) }}.</p>
                </div>
                <span class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-[11px] font-bold text-slate-600 dark:bg-slate-900 dark:text-slate-300">
                    Avg PHP {{ number_format((float) ($summary['averageRevenuePerHouse'] ?? 0), 0) }} / property
                </span>
            </div>
            <div class="mt-3 h-64">
                <canvas id="simpleRevenueChart" aria-label="Revenue trend chart"></canvas>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <h2 class="text-base font-black">Current overview</h2>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-900">
                    <p class="text-[10px] font-black uppercase text-slate-400">Properties</p>
                    <p class="mt-1 text-xl font-black">{{ number_format((int) ($summary['totalProperties'] ?? 0)) }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-900">
                    <p class="text-[10px] font-black uppercase text-slate-400">Total rooms</p>
                    <p class="mt-1 text-xl font-black">{{ number_format((int) ($summary['totalRooms'] ?? 0)) }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-900">
                    <p class="text-[10px] font-black uppercase text-slate-400">Available rooms</p>
                    <p class="mt-1 text-xl font-black">{{ number_format((int) ($summary['vacantRooms'] ?? 0)) }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-900">
                    <p class="text-[10px] font-black uppercase text-slate-400">Reviews</p>
                    <p class="mt-1 text-xl font-black">{{ number_format((int) ($summary['totalReviews'] ?? 0)) }}</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ number_format((float) ($summary['averageRating'] ?? 0), 1) }} average rating</p>
                </div>
            </div>

            <div class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-800">
                <div class="flex items-center justify-between gap-3 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Occupied rooms</span>
                    <strong>{{ number_format((int) ($occupancyData[0] ?? 0)) }} / {{ number_format($occupancyTotal) }}</strong>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-full rounded-full bg-blue-600" style="width: {{ $occupancyTotal > 0 ? min(100, round(((int) ($occupancyData[0] ?? 0) / $occupancyTotal) * 100)) : 0 }}%"></div>
                </div>
            </div>

            <div class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-800">
                <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Booking status</p>
                <div class="mt-2 space-y-2">
                    @foreach ($distributionLabels as $index => $label)
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="text-slate-600 dark:text-slate-300">{{ $label }}</span>
                            <strong>{{ number_format((int) ($distributionData[$index] ?? 0)) }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </article>
    </section>

    <section id="property-report" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 px-4 py-3 dark:border-slate-800">
            <div>
                <h2 class="text-base font-black">Property performance</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Complete results for {{ strtolower($rangeLabel) }}.</p>
            </div>
            <span class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-[11px] font-bold text-slate-600 dark:bg-slate-900 dark:text-slate-300">
                {{ method_exists($reportRows, 'total') ? number_format($reportRows->total()) : number_format(count($reportRows)) }} properties
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-xs">
                <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Boarding house</th>
                        <th class="px-4 py-3">Revenue</th>
                        <th class="px-4 py-3">Bookings</th>
                        <th class="px-4 py-3">Occupancy</th>
                        <th class="px-4 py-3">Tenants</th>
                        <th class="px-4 py-3">Reviews</th>
                        <th class="px-4 py-3">Rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($reportRows as $row)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-bold text-slate-900 dark:text-white">{{ $row['boarding_house'] }}</p>
                                <p class="mt-0.5 max-w-xs truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $row['location'] ?? 'Location not set' }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-bold text-emerald-600 dark:text-emerald-300">PHP {{ number_format((float) ($row['revenue'] ?? 0), 2) }}</td>
                            <td class="px-4 py-3">{{ number_format((int) ($row['bookings'] ?? 0)) }}</td>
                            <td class="px-4 py-3">{{ number_format((int) ($row['occupancy_rate'] ?? 0)) }}%</td>
                            <td class="px-4 py-3">{{ number_format((int) ($row['tenants'] ?? 0)) }}</td>
                            <td class="px-4 py-3">{{ number_format((int) ($row['reviews'] ?? 0)) }}</td>
                            <td class="px-4 py-3">{{ number_format((float) ($row['average_rating'] ?? 0), 1) }} / 5</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No property report data is available for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($reportRows, 'hasPages') && $reportRows->hasPages())
            <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                {{ $reportRows->links() }}
            </div>
        @endif
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('simpleRevenueChart');
        if (!canvas || typeof Chart === 'undefined') return;

        const chart = @json($revenueTrendChart);
        const context = canvas.getContext('2d');
        const gradient = context.createLinearGradient(0, 0, 0, canvas.height);
        gradient.addColorStop(0, 'rgba(37, 99, 235, .24)');
        gradient.addColorStop(1, 'rgba(37, 99, 235, .02)');
        const dark = document.documentElement.classList.contains('dark');
        const textColor = dark ? '#94A3B8' : '#64748B';
        const gridColor = dark ? 'rgba(71, 85, 105, .35)' : 'rgba(148, 163, 184, .2)';

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: chart.labels,
                datasets: [{
                    data: chart.data,
                    borderColor: '#2563EB',
                    backgroundColor: gradient,
                    borderWidth: 2.25,
                    pointRadius: 2.5,
                    pointHoverRadius: 4,
                    tension: .35,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: textColor, font: { size: 10 } }, grid: { display: false }, border: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: { color: textColor, font: { size: 10 }, callback: value => 'PHP ' + Number(value).toLocaleString() },
                        grid: { color: gridColor },
                        border: { display: false }
                    }
                }
            }
        });
    });
</script>
</x-admin.shell>
</x-layouts.dashboard>
