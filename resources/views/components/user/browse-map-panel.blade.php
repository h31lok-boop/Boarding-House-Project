@props([
    'dsscLandmark',
    'mapHouses',
    'mapUrl',
    'showMatchScores' => false,
])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm shadow-slate-200/70 dark:border-slate-800 dark:bg-slate-900 dark:shadow-slate-950/30']) }}>
    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-800">
        <div>
            <h2 class="text-sm font-bold text-slate-950 dark:text-white">DSSC Area Map</h2>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Campus and nearby boarding houses</p>
        </div>
        <a href="{{ $mapUrl }}" target="_blank" rel="noopener" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-bold text-blue-600 transition hover:bg-blue-50 dark:border-slate-700 dark:bg-slate-950 dark:text-blue-300 dark:hover:bg-blue-400/10">
            Open Map
        </a>
    </div>

    <div class="relative h-[420px] bg-slate-100 dark:bg-slate-800">
        <div class="h-full w-full" data-boardmatch-browse-map data-marker-class="bm-map-marker" aria-label="Interactive map of DSSC Main Campus and nearby boarding houses"></div>
        <script type="application/json" data-boardmatch-browse-map-config>
            {!! json_encode([
                'dssc' => $dsscLandmark,
                'houses' => $mapHouses,
                'showMatchScores' => $showMatchScores,
            ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
        </script>

        <div class="pointer-events-none absolute bottom-3 left-3 rounded-xl border border-white/80 bg-white/95 p-3 text-xs shadow-lg dark:border-slate-700 dark:bg-slate-950/90">
            <p class="mb-2 font-bold text-slate-900 dark:text-white">Legend</p>
            <div class="space-y-1.5 text-slate-600 dark:text-slate-300">
                <p class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Available</p>
                <p class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>Few rooms left</p>
                <p class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span>High match</p>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-2 px-4 py-3 text-xs font-semibold text-slate-600 dark:text-slate-300">
        <svg class="h-4 w-4 shrink-0 text-blue-600 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
        <span>{{ $dsscLandmark['name'] ?? 'DSSC Main Campus' }}, {{ $dsscLandmark['address'] ?? 'Matti, Digos City' }}</span>
    </div>
</section>
