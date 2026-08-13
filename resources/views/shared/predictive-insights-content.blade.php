@php
    $toneClasses = [
        'blue' => 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-400/20 dark:bg-blue-400/10 dark:text-blue-300',
        'violet' => 'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-400/20 dark:bg-violet-400/10 dark:text-violet-300',
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300',
        'rose' => 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-300',
    ];
    $directionLabel = fn (string $direction) => match ($direction) {
        'up' => 'Rising',
        'down' => 'Declining',
        default => 'Stable',
    };
    $formatValue = fn ($value) => fmod((float) $value, 1.0) === 0.0
        ? number_format((int) $value)
        : number_format((float) $value, 1);
    $ai = $aiInsights ?? [];
    $analysis = $ai['analysis'] ?? null;
    $recentIndexes = collect(array_keys($labels ?? []))->slice(-6)->values();
@endphp

<div class="space-y-3 text-slate-950 dark:text-slate-100" data-simple-predictive-insights>
    <header class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl font-black tracking-tight sm:text-2xl">ML Insights</h1>
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">Live database records</span>
                    @if ($ai['success'] ?? false)
                        <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[10px] font-bold text-violet-700 dark:bg-violet-400/10 dark:text-violet-300">AI connected: {{ ucfirst($ai['provider']) }}</span>
                    @elseif ($ai['configured'] ?? false)
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700 dark:bg-amber-400/10 dark:text-amber-300">AI temporarily unavailable</span>
                    @else
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500 dark:bg-slate-900 dark:text-slate-400">AI not configured</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Demand, reservations, occupancy, and payment risk from verified system records.</p>
            </div>

            <form method="GET" class="flex items-end gap-2">
                <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400">
                    Period
                    <select name="months" class="mt-1 block h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-800 outline-none focus:border-violet-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        @foreach ([4, 6, 9, 12] as $window)
                            <option value="{{ $window }}" @selected($months === $window)>{{ $window }} months</option>
                        @endforeach
                    </select>
                </label>
                <button class="inline-flex h-10 items-center justify-center rounded-xl bg-violet-600 px-4 text-sm font-bold text-white transition hover:bg-violet-700">Refresh</button>
            </form>
        </div>
    </header>

    @unless ($hasHistoricalData)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200">
            No historical activity is available yet. Insights will update automatically when records are created.
        </div>
    @endunless

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Prediction summary">
        @foreach ($cards as $card)
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[11px] font-black uppercase tracking-[0.1em] text-slate-500 dark:text-slate-400">{{ $card['title'] }}</p>
                    <span class="rounded-full border px-2 py-1 text-[9px] font-black {{ $toneClasses[$card['tone']] ?? $toneClasses['blue'] }}">{{ $card['riskLabel'] ?: $directionLabel($card['direction']) }}</span>
                </div>
                <div class="mt-3 flex items-end justify-between gap-3">
                    <div>
                        <p class="text-2xl font-black">{{ $formatValue($card['prediction']) }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Forecast {{ $card['unit'] }}</p>
                    </div>
                    <p class="text-right text-xs text-slate-500 dark:text-slate-400">Current<br><strong class="text-slate-800 dark:text-slate-200">{{ $formatValue($card['current']) }}</strong></p>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid gap-3 xl:grid-cols-[minmax(0,1.45fr)_minmax(280px,0.55fr)]">
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <div>
                <h2 class="text-base font-black">Historical trend</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Monthly values for the selected period.</p>
            </div>
            <div class="mt-3 h-64">
                <canvas id="predictiveHistoryChart" aria-label="Predictive historical trend chart"></canvas>
            </div>
        </article>

        <article class="rounded-2xl border border-violet-200 bg-violet-50/50 p-4 dark:border-violet-400/20 dark:bg-violet-400/5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-base font-black">AI summary</h2>
                @if ($ai['success'] ?? false)
                    <span class="rounded-full bg-violet-100 px-2 py-1 text-[10px] font-bold text-violet-700 dark:bg-violet-400/10 dark:text-violet-300">{{ ucfirst($ai['provider']) }}</span>
                @endif
            </div>

            @if ($analysis)
                <p class="mt-3 text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $analysis['summary'] }}</p>
                @if (! empty($analysis['highlights']))
                    <ul class="mt-3 space-y-1.5">
                        @foreach (array_slice($analysis['highlights'], 0, 3) as $highlight)
                            <li class="flex gap-2 text-xs leading-5 text-slate-600 dark:text-slate-300"><span class="text-violet-600">•</span><span>{{ $highlight }}</span></li>
                        @endforeach
                    </ul>
                @endif
                <div class="mt-4 rounded-xl bg-white px-3 py-3 text-xs leading-5 dark:bg-slate-900">
                    <strong>Recommended action:</strong> {{ $analysis['action'] }}
                </div>
            @else
                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $ai['reason'] ?? 'AI analysis is currently unavailable. Verified records remain visible.' }}</p>
            @endif

            <div class="mt-4 border-t border-violet-200 pt-3 dark:border-violet-400/20">
                <p class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Highest demand</p>
                <div class="mt-2 space-y-2">
                    @forelse (collect($topDemand)->take(3) as $house)
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="truncate font-semibold">{{ $house['name'] }}</span>
                            <span class="shrink-0 font-black text-violet-700 dark:text-violet-300">{{ $house['score'] }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 dark:text-slate-400">No demand records yet.</p>
                    @endforelse
                </div>
            </div>
        </article>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800">
            <h2 class="text-sm font-black">Monthly records</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-xs">
                <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Month</th>
                        <th class="px-4 py-3">Demand</th>
                        <th class="px-4 py-3">Reservations</th>
                        <th class="px-4 py-3">Occupancy</th>
                        <th class="px-4 py-3">Payment risk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach ($recentIndexes as $index)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 font-bold">{{ $labels[$index] }}</td>
                            <td class="px-4 py-3">{{ $formatValue($series['demand'][$index] ?? 0) }}</td>
                            <td class="px-4 py-3">{{ $formatValue($series['reservations'][$index] ?? 0) }}</td>
                            <td class="px-4 py-3">{{ $formatValue($series['occupancy'][$index] ?? 0) }}%</td>
                            <td class="px-4 py-3">{{ $formatValue($series['payment_risk'][$index] ?? 0) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <p class="px-1 text-[10px] leading-5 text-slate-500 dark:text-slate-400">
        Forecasts use verified BoardMatch records. AI explains the calculated results and does not replace database values.
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('predictiveHistoryChart');
        if (!canvas || typeof Chart === 'undefined') return;

        const labels = @json($labels);
        const series = @json($series);
        const dark = document.documentElement.classList.contains('dark');
        const textColor = dark ? '#94A3B8' : '#64748B';
        const gridColor = dark ? 'rgba(71, 85, 105, .35)' : 'rgba(148, 163, 184, .2)';

        new Chart(canvas, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'Demand', data: series.demand, borderColor: '#2563EB', backgroundColor: '#2563EB', tension: .3, borderWidth: 2 },
                    { label: 'Reservations', data: series.reservations, borderColor: '#8B5CF6', backgroundColor: '#8B5CF6', tension: .3, borderWidth: 2 },
                    { label: 'Occupancy %', data: series.occupancy, borderColor: '#10B981', backgroundColor: '#10B981', tension: .3, borderWidth: 2 },
                    { label: 'Payment risk %', data: series.payment_risk, borderColor: '#F43F5E', backgroundColor: '#F43F5E', tension: .3, borderWidth: 2 },
                ].map(dataset => ({ ...dataset, pointRadius: 2, pointHoverRadius: 4, fill: false }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { color: textColor, usePointStyle: true, boxWidth: 7, padding: 14, font: { size: 10 } } }
                },
                scales: {
                    x: { ticks: { color: textColor, font: { size: 10 } }, grid: { display: false }, border: { display: false } },
                    y: { beginAtZero: true, ticks: { color: textColor, font: { size: 10 } }, grid: { color: gridColor }, border: { display: false } }
                }
            }
        });
    });
</script>
