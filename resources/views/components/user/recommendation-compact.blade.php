@props([
    'house',
])

@php
    $fallbackPhoto = 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=900&q=80';
@endphp

<article {{ $attributes->merge(['class' => 'group overflow-hidden rounded-xl border border-slate-200 bg-white p-2 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg hover:shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-400/40 dark:hover:shadow-slate-950/30']) }}>
    <div class="flex gap-2">
        <div class="h-14 w-[4.5rem] shrink-0 overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-800">
            <img
                src="{{ $house['image'] ?? $fallbackPhoto }}"
                alt="{{ $house['name'] }}"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                loading="lazy"
                onerror="this.onerror=null;this.src='{{ $fallbackPhoto }}';"
            >
        </div>
        <div class="min-w-0 flex-1 py-0.5">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <h3 class="truncate text-[11px] font-semibold text-slate-950 dark:text-white">{{ $house['name'] }}</h3>
                    <p class="mt-0.5 truncate text-[10px] text-slate-500 dark:text-slate-400">{{ $house['location'] }}</p>
                </div>
                <span class="shrink-0 rounded-full bg-blue-50 px-1.5 py-0.5 text-[10px] font-bold text-blue-700 ring-1 ring-blue-100 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/20">{{ $house['match'] }}</span>
            </div>
            <div class="mt-1.5 flex items-center justify-between gap-3">
                <p class="text-[11px] font-bold text-slate-950 dark:text-white">{!! $house['price'] !!}</p>
                <a href="{{ $house['url'] }}" class="text-[11px] font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-200">View</a>
            </div>
        </div>
    </div>
</article>
