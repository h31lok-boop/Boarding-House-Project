@props([
    'listing',
    'showMatch' => true,
])

@php
    $availabilityTone = ($listing['availability_tone'] ?? 'green') === 'orange'
        ? 'bg-amber-50 text-amber-700 ring-amber-100 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20'
        : 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20';
@endphp

<article {{ $attributes->merge(['class' => 'group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm shadow-slate-200/70 transition duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-200/80 dark:border-slate-800 dark:bg-slate-900 dark:shadow-slate-950/30']) }}>
    <div class="relative aspect-[16/9] overflow-hidden bg-slate-100 dark:bg-slate-800">
        <img
            src="{{ $listing['image'] }}"
            alt="{{ $listing['name'] }}"
            loading="lazy"
            class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
            onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}';"
        >

        @if($showMatch)
            <span class="absolute left-2.5 top-2.5 inline-flex h-6 items-center rounded-full bg-white/95 px-2.5 text-[11px] font-bold text-blue-700 shadow-sm ring-1 ring-blue-100 dark:bg-slate-950/90 dark:text-blue-200 dark:ring-blue-400/20">
                {{ $listing['match_score'] }}% Match
            </span>
        @endif

        <button
            type="button"
            class="absolute right-2.5 top-2.5 flex h-8 w-8 items-center justify-center rounded-full bg-white/95 text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:text-rose-500 dark:bg-slate-950/90 dark:text-slate-200 dark:ring-slate-700"
            :class="{ 'text-rose-500': isSaved({{ $listing['id'] }}) }"
            @click="toggleSaved({{ $listing['id'] }})"
            aria-label="Save {{ $listing['name'] }}"
        >
            <svg class="h-4 w-4" :fill="isSaved({{ $listing['id'] }}) ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
        </button>
    </div>

    <div class="space-y-2.5 p-3">
        <div class="min-w-0">
            <div class="flex items-start justify-between gap-2">
                <h2 class="min-w-0 truncate text-sm font-bold text-slate-950 dark:text-white">{{ $listing['name'] }}</h2>
                <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 {{ $availabilityTone }}">{{ $listing['availability_label'] }}</span>
            </div>

            <p class="mt-1 flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <svg class="h-3.5 w-3.5 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                <span class="truncate">{{ $listing['distance_label'] }}</span>
            </p>

            <p class="mt-1 flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12v6.75m0-6.75 8.955-8.955a1.125 1.125 0 0 1 1.59 0L21.75 12M2.25 12h19.5m0 0v6.75M6 18.75V15a1.5 1.5 0 0 1 1.5-1.5h9A1.5 1.5 0 0 1 18 15v3.75" /></svg>
                <span class="truncate">{{ $listing['room'] }}</span>
            </p>
        </div>

        <div class="flex items-end justify-between gap-2">
            <p class="text-base font-bold text-slate-950 dark:text-white">
                {!! $listing['price_label'] !!}
                @if($listing['has_price'])
                    <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">/month</span>
                @endif
            </p>
            <p class="flex shrink-0 items-center gap-1 text-xs font-bold text-slate-700 dark:text-slate-200">
                <svg class="h-4 w-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006Z" clip-rule="evenodd" /></svg>
                {{ $listing['rating'] }}
                <span class="font-semibold text-slate-400">({{ $listing['reviews'] }})</span>
            </p>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <a href="{{ $listing['url'] }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-blue-200 bg-white px-3 text-xs font-bold text-blue-600 transition hover:bg-blue-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100 dark:border-blue-400/30 dark:bg-slate-950 dark:text-blue-300 dark:hover:bg-blue-400/10">
                View Details
            </a>
            <a href="{{ $listing['url'] }}#reservation-panel" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-3 text-xs font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/25 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200">
                Reserve
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>
    </div>
</article>
