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
@endphp

<div class="space-y-4 text-slate-950 dark:text-slate-100">
    <section class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950 sm:p-6">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-violet-600 dark:text-violet-400">Real Data + AI</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">Predictive Insights</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">Verified system records produce every number below. A configured AI provider only explains those results.</p>
                <div class="mt-3 flex flex-wrap gap-2 text-[11px] font-bold">
                    <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">Live database records</span>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-600 dark:bg-slate-900 dark:text-slate-300">{{ $scope }}</span>
                    @if ($ai['success'] ?? false)
                        <span class="rounded-full bg-violet-50 px-3 py-1.5 text-violet-700 dark:bg-violet-400/10 dark:text-violet-300">AI connected: {{ ucfirst($ai['provider']) }} · {{ $ai['model'] }}</span>
                    @elseif ($ai['configured'] ?? false)
                        <span class="rounded-full bg-amber-50 px-3 py-1.5 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300">AI unavailable</span>
                    @else
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-500 dark:bg-slate-900 dark:text-slate-400">AI not configured</span>
                    @endif
                </div>
            </div>

            <form method="GET" class="flex items-end gap-2">
                <label class="text-xs font-bold text-slate-600 dark:text-slate-300">
                    History
                    <select name="months" class="mt-1 block h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-800 outline-none focus:border-violet-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        @foreach ([4, 6, 9, 12] as $window)
                            <option value="{{ $window }}" @selected($months === $window)>{{ $window }} months</option>
                        @endforeach
                    </select>
                </label>
                <button class="inline-flex h-10 items-center justify-center rounded-xl bg-violet-600 px-4 text-sm font-bold text-white transition hover:bg-violet-700">Refresh</button>
            </form>
        </div>
    </section>

    @unless ($hasHistoricalData)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200">
            No historical activity is available for this scope yet. The system will display insights automatically after real records are created.
        </div>
    @endunless

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">{{ $card['title'] }}</p>
                    <span class="rounded-full border px-2 py-1 text-[10px] font-black {{ $toneClasses[$card['tone']] ?? $toneClasses['blue'] }}">{{ $card['riskLabel'] ?: $directionLabel($card['direction']) }}</span>
                </div>
                <div class="mt-4 flex items-end justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase text-slate-400">Forecast</p>
                        <p class="mt-1 text-3xl font-black">{{ $formatValue($card['prediction']) }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $card['unit'] }}</p>
                    </div>
                    <div class="text-right text-xs text-slate-500 dark:text-slate-400">
                        <p>Current: <strong class="text-slate-800 dark:text-slate-200">{{ $formatValue($card['current']) }}</strong></p>
                        <p class="mt-1">Fit: {{ $card['confidence'] }}%</p>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid gap-4 xl:grid-cols-[1.15fr_0.85fr]">
        <article class="rounded-[1.4rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <div>
                <h2 class="text-base font-black">Monthly records</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Values read from reservations, inquiries, rooms, tenants, and payments.</p>
            </div>
            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="min-w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr>
                            <th class="px-3 py-3">Month</th>
                            <th class="px-3 py-3">Demand</th>
                            <th class="px-3 py-3">Reservations</th>
                            <th class="px-3 py-3">Occupancy</th>
                            <th class="px-3 py-3">Payment risk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach ($labels as $index => $label)
                            <tr>
                                <td class="whitespace-nowrap px-3 py-3 font-bold">{{ $label }}</td>
                                <td class="px-3 py-3">{{ $formatValue($series['demand'][$index] ?? 0) }}</td>
                                <td class="px-3 py-3">{{ $formatValue($series['reservations'][$index] ?? 0) }}</td>
                                <td class="px-3 py-3">{{ $formatValue($series['occupancy'][$index] ?? 0) }}%</td>
                                <td class="px-3 py-3">{{ $formatValue($series['payment_risk'][$index] ?? 0) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>

        <div class="space-y-4">
            <article class="rounded-[1.4rem] border border-violet-200 bg-violet-50/60 p-5 dark:border-violet-400/20 dark:bg-violet-400/5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-base font-black">AI summary</h2>
                    @if ($ai['success'] ?? false)
                        <span class="rounded-full bg-violet-100 px-2.5 py-1 text-[10px] font-bold text-violet-700 dark:bg-violet-400/10 dark:text-violet-300">{{ ucfirst($ai['provider']) }}</span>
                    @endif
                </div>

                @if ($analysis)
                    <p class="mt-3 text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $analysis['summary'] }}</p>
                    <ul class="mt-4 space-y-2">
                        @foreach ($analysis['highlights'] as $highlight)
                            <li class="flex gap-2 text-sm text-slate-600 dark:text-slate-300"><span class="text-violet-600">•</span><span>{{ $highlight }}</span></li>
                        @endforeach
                    </ul>
                    <div class="mt-4 rounded-xl bg-white px-3 py-3 text-sm dark:bg-slate-900">
                        <strong>Next step:</strong> {{ $analysis['action'] }}
                    </div>
                @else
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $ai['reason'] ?? 'AI analysis is currently unavailable. Real data remains visible.' }}</p>
                @endif
            </article>

            <article class="rounded-[1.4rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h2 class="text-base font-black">System actions</h2>
                <ol class="mt-3 space-y-2">
                    @foreach (array_slice($recommendations, 0, 3) as $recommendation)
                        <li class="flex gap-2 text-sm leading-6 text-slate-600 dark:text-slate-300"><span class="font-black text-violet-600">{{ $loop->iteration }}.</span><span>{{ $recommendation }}</span></li>
                    @endforeach
                </ol>
            </article>
        </div>
    </section>

    <section class="rounded-[1.4rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <h2 class="text-base font-black">Highest demand listings</h2>
        <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($topDemand as $house)
                <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-3 dark:bg-slate-900">
                    <p class="truncate text-xs font-bold">{{ $house['name'] }}</p>
                    <span class="shrink-0 rounded-full bg-blue-100 px-2 py-1 text-[10px] font-black text-blue-700 dark:bg-blue-400/10 dark:text-blue-300">{{ $house['score'] }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">No demand records yet.</p>
            @endforelse
        </div>
    </section>

    <p class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-[11px] leading-5 text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
        <strong class="text-slate-700 dark:text-slate-200">How it works:</strong> {{ $methodology }} AI explains verified results only and never replaces system records.
    </p>
</div>
