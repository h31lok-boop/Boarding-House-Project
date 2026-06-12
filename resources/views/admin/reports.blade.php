<x-layouts.dashboard>
<x-admin.shell>
@php
    $tab = $tab ?? 'overview';
    $range = $range ?? 'this_month';
    $rangeLabel = $rangeLabel ?? 'This Month';
    $rangeOptions = $rangeOptions ?? ['this_month' => 'This Month'];
    $distributionTotal = (int) ($bookingDistribution['total'] ?? 0);
    $distributionLabels = $bookingDistribution['labels'] ?? ['New', 'Confirmed', 'Currently Staying', 'Cancelled'];
    $distributionData = $bookingDistribution['data'] ?? [0, 0, 0, 0];
@endphp

<div class="space-y-6">
    <section class="rounded-2xl border border-slate-200 bg-white px-6 py-6 shadow-sm shadow-slate-900/5">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase text-blue-700">Reports</p>
                <h1 class="mt-3 text-3xl font-bold text-slate-950">Reports &amp; Analytics</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">Track revenue, reservations, tenants, and occupancy at a glance.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route('admin.reports.index') }}">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <label class="relative block">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.8"/>
                                <path stroke-linecap="round" stroke-width="1.8" d="M8 3v4M16 3v4M4 10h16"/>
                            </svg>
                        </span>
                        <select
                            name="range"
                            onchange="this.form.submit()"
                            class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-12 pr-10 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 sm:w-52"
                        >
                            @foreach ($rangeOptions as $value => $label)
                                <option value="{{ $value }}" @selected($range === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </form>

                <a
                    href="{{ route('admin.reports.export', ['range' => $range]) }}"
                    class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-bold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v11m0 0 4-4m-4 4-4-4"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 20h14"/>
                    </svg>
                    Export
                </a>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white px-6 shadow-sm shadow-slate-900/5">
        <nav class="flex gap-8" aria-label="Report tabs">
            <a
                href="{{ route('admin.reports.index', ['tab' => 'overview', 'range' => $range]) }}"
                class="border-b-2 py-4 text-sm font-bold transition {{ $tab === 'overview' ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}"
            >
                Overview
            </a>
            <a
                href="{{ route('admin.reports.index', ['tab' => 'detailed', 'range' => $range]) }}"
                class="border-b-2 py-4 text-sm font-bold transition {{ $tab === 'detailed' ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}"
            >
                Detailed Reports
            </a>
        </nav>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($kpiCards as $card)
            <article class="flex min-h-[128px] items-center gap-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-900/5">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $card['tone'] }}">
                    @if ($card['icon'] === 'revenue')
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="12" cy="12" r="9" stroke-width="1.8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 7v10M9 9.5c0-1.1 1.3-2 3-2s3 .9 3 2-1.3 2-3 2-3 .9-3 2 1.3 2 3 2 3-.9 3-2"/>
                        </svg>
                    @elseif ($card['icon'] === 'bookings')
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.8"/>
                            <path stroke-linecap="round" stroke-width="1.8" d="M8 3v4M16 3v4M4 10h16"/>
                        </svg>
                    @elseif ($card['icon'] === 'tenants')
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="12" cy="8" r="3.5" stroke-width="1.8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 20a7 7 0 0 1 14 0"/>
                        </svg>
                    @else
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v9l7 4"/>
                            <circle cx="12" cy="12" r="9" stroke-width="1.8"/>
                        </svg>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase text-slate-500">{{ $card['label'] }}</p>
                    <p class="mt-2 text-2xl font-bold text-slate-950">{{ $card['value'] }}</p>
                    <p class="mt-2 text-sm font-semibold text-emerald-600">{{ $card['trend'] }}</p>
                </div>
            </article>
        @endforeach
    </section>

    @if ($tab === 'overview')
        <section class="grid gap-5 xl:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-900/5">
                <h2 class="text-lg font-bold text-slate-950">Revenue Trend</h2>
                <div class="mt-5 h-72">
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-900/5">
                <h2 class="text-lg font-bold text-slate-950">Booking Distribution</h2>
                <div class="mt-5 grid gap-5 md:grid-cols-[260px_1fr] md:items-center">
                    <div class="relative mx-auto h-64 w-64">
                        <canvas id="bookingDistributionChart"></canvas>
                        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                            <p class="text-2xl font-bold text-slate-950">{{ number_format($distributionTotal) }}</p>
                            <p class="text-xs font-medium text-slate-500">Total Bookings</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @foreach ($distributionLabels as $index => $label)
                            @php
                                $count = (int) ($distributionData[$index] ?? 0);
                                $percent = $distributionTotal > 0 ? round(($count / $distributionTotal) * 100) : 0;
                                $colors = ['bg-blue-600', 'bg-emerald-500', 'bg-violet-500', 'bg-amber-500'];
                            @endphp
                            <div class="flex items-center justify-between gap-4 text-sm">
                                <span class="inline-flex items-center gap-2 font-medium text-slate-700">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $colors[$index] ?? 'bg-slate-400' }}"></span>
                                    {{ $label }}
                                </span>
                                <span class="font-semibold text-slate-500">{{ number_format($count) }} ({{ $percent }}%)</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>
        </section>
    @endif

    <section id="detailed-reports" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg font-bold text-slate-950">Detailed Reports</h2>
            <a
                href="{{ route('admin.reports.index', ['tab' => 'detailed', 'range' => $range]) }}"
                class="inline-flex items-center gap-2 text-sm font-bold text-blue-700 transition hover:text-blue-800"
            >
                View All Reports
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/>
                </svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[960px] w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50/70">
                    <tr class="text-xs font-bold uppercase text-slate-500">
                        <th class="px-6 py-4">Boarding House</th>
                        <th class="px-6 py-4">Revenue (PHP)</th>
                        <th class="px-6 py-4">Bookings</th>
                        <th class="px-6 py-4">Occupancy Rate</th>
                        <th class="px-6 py-4">Tenants</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($reportRows as $row)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $row['boarding_house'] }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ number_format((float) $row['revenue'], 2) }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ number_format((int) $row['bookings']) }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $row['occupancy_rate'] }}%</td>
                            <td class="px-6 py-4 text-slate-700">{{ number_format((int) $row['tenants']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12">
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

@if ($tab === 'overview')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') return;

            const revenueCanvas = document.getElementById('revenueTrendChart');
            const bookingCanvas = document.getElementById('bookingDistributionChart');
            const revenueChart = @json($revenueTrendChart);
            const bookingChart = @json($bookingDistribution);

            if (revenueCanvas) {
                new Chart(revenueCanvas, {
                    type: 'line',
                    data: {
                        labels: revenueChart.labels,
                        datasets: [{
                            label: 'Revenue (PHP)',
                            data: revenueChart.data,
                            borderColor: '#2563EB',
                            backgroundColor: 'rgba(37, 99, 235, 0.12)',
                            pointBackgroundColor: '#FFFFFF',
                            pointBorderColor: '#2563EB',
                            pointBorderWidth: 3,
                            pointRadius: 4,
                            tension: 0.35,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: { boxWidth: 18, color: '#64748B', font: { size: 12, weight: '600' } }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#E2E8F0' },
                                ticks: {
                                    color: '#64748B',
                                    callback: value => Number(value).toLocaleString()
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#64748B' }
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
                        cutout: '62%',
                        plugins: { legend: { display: false } }
                    }
                });
            }
        });
    </script>
@endif
</x-admin.shell>
</x-layouts.dashboard>
