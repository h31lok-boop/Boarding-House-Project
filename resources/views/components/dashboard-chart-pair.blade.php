@props([
    'idPrefix',
    'pieTitle',
    'pieDescription',
    'pieLabels' => [],
    'pieData' => [],
    'lineTitle',
    'lineDescription',
    'lineLabels' => [],
    'lineData' => [],
    'lineDatasetLabel' => 'Value',
    'currency' => false,
])

@php
    $pieCanvasId = $idPrefix.'-pie-chart';
    $lineCanvasId = $idPrefix.'-line-chart';
    $pieValues = collect($pieData)->map(fn ($value) => max((float) $value, 0))->values()->all();
    $lineValues = collect($lineData)->map(fn ($value) => max((float) $value, 0))->values()->all();
    $hasPieData = collect($pieValues)->contains(fn ($value) => $value > 0);
    $hasLineData = collect($lineValues)->contains(fn ($value) => $value > 0);
    $displayPieLabels = $hasPieData ? array_values($pieLabels) : ['No data yet'];
    $displayPieValues = $hasPieData ? $pieValues : [1];
    $displayPieColors = $hasPieData
        ? ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#f43f5e']
        : ['#cbd5e1'];
@endphp

<section class="grid gap-5 lg:grid-cols-2" aria-label="{{ $pieTitle }} and {{ $lineTitle }}">
    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <header class="border-b border-slate-100 px-5 py-4 dark:border-slate-700">
            <h2 class="text-base font-black text-slate-950 dark:text-white">{{ $pieTitle }}</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $pieDescription }}</p>
        </header>
        <div class="relative h-64 p-5">
            <canvas id="{{ $pieCanvasId }}" role="img" aria-label="{{ $pieTitle }} pie graph"></canvas>
            @unless ($hasPieData)
                <span class="pointer-events-none absolute left-1/2 top-[45%] -translate-x-1/2 -translate-y-1/2 rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-bold text-slate-500 shadow-sm dark:bg-slate-900/90 dark:text-slate-400">No records yet</span>
            @endunless
        </div>
    </article>

    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <header class="border-b border-slate-100 px-5 py-4 dark:border-slate-700">
            <h2 class="text-base font-black text-slate-950 dark:text-white">{{ $lineTitle }}</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $lineDescription }}</p>
        </header>
        <div class="relative h-64 p-5">
            <canvas id="{{ $lineCanvasId }}" role="img" aria-label="{{ $lineTitle }} line graph"></canvas>
            @unless ($hasLineData)
                <span class="pointer-events-none absolute right-5 top-5 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400">No activity yet</span>
            @endunless
        </div>
    </article>
</section>

@once
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endonce

<script>
    (() => {
        const initializeCharts = () => {
            if (typeof Chart === 'undefined') {
                window.setTimeout(initializeCharts, 100);
                return;
            }

            const darkMode = document.documentElement.dataset.theme === 'dark'
                || document.documentElement.classList.contains('dark');
            const labelColor = darkMode ? '#cbd5e1' : '#64748b';
            const gridColor = darkMode ? 'rgba(148, 163, 184, 0.16)' : 'rgba(148, 163, 184, 0.20)';
            const tooltip = {
                backgroundColor: darkMode ? '#020617' : '#0f172a',
                padding: 10,
                cornerRadius: 10,
                titleColor: '#ffffff',
                bodyColor: '#e2e8f0',
            };
            const formatValue = (value) => @js((bool) $currency)
                ? new Intl.NumberFormat('en-PH', {
                    style: 'currency',
                    currency: 'PHP',
                    maximumFractionDigits: 0,
                }).format(value)
                : new Intl.NumberFormat('en-PH').format(value);

            const pieCanvas = document.getElementById(@js($pieCanvasId));
            if (pieCanvas && !Chart.getChart(pieCanvas)) {
                new Chart(pieCanvas, {
                    type: 'pie',
                    data: {
                        labels: @json($displayPieLabels),
                        datasets: [{
                            data: @json($displayPieValues),
                            backgroundColor: @json($displayPieColors),
                            borderColor: darkMode ? '#0f172a' : '#ffffff',
                            borderWidth: 3,
                            hoverOffset: 6,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: labelColor, boxWidth: 10, boxHeight: 10, padding: 16, usePointStyle: true },
                            },
                            tooltip: {
                                ...tooltip,
                                callbacks: { label: (context) => ` ${context.label}: ${formatValue(context.raw)}` },
                            },
                        },
                    },
                });
            }

            const lineCanvas = document.getElementById(@js($lineCanvasId));
            if (lineCanvas && !Chart.getChart(lineCanvas)) {
                const context = lineCanvas.getContext('2d');
                const fill = context.createLinearGradient(0, 0, 0, 220);
                fill.addColorStop(0, 'rgba(37, 99, 235, 0.28)');
                fill.addColorStop(1, 'rgba(37, 99, 235, 0.02)');

                new Chart(lineCanvas, {
                    type: 'line',
                    data: {
                        labels: @json(array_values($lineLabels)),
                        datasets: [{
                            label: @js($lineDatasetLabel),
                            data: @json($lineValues),
                            borderColor: '#2563eb',
                            backgroundColor: fill,
                            fill: true,
                            borderWidth: 2.5,
                            tension: 0.35,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#2563eb',
                            pointBorderColor: darkMode ? '#0f172a' : '#ffffff',
                            pointBorderWidth: 2,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                ...tooltip,
                                callbacks: { label: (context) => ` ${@js($lineDatasetLabel)}: ${formatValue(context.raw)}` },
                            },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: { color: labelColor, font: { size: 10, weight: '600' } },
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                border: { display: false },
                                ticks: {
                                    color: labelColor,
                                    font: { size: 10 },
                                    callback: (value) => formatValue(value),
                                },
                            },
                        },
                    },
                });
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeCharts, { once: true });
        } else {
            initializeCharts();
        }
    })();
</script>
