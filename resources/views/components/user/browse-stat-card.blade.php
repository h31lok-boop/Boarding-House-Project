@props([
    'title',
    'value',
    'description',
    'icon' => 'home',
    'tone' => 'blue',
])

@php
    $toneClasses = [
        'blue' => 'bg-blue-50 text-blue-600 ring-blue-100 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/20',
        'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20',
        'amber' => 'bg-amber-50 text-amber-600 ring-amber-100 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20',
        'sky' => 'bg-sky-50 text-sky-600 ring-sky-100 dark:bg-sky-400/10 dark:text-sky-300 dark:ring-sky-400/20',
    ][$tone] ?? 'bg-blue-50 text-blue-600 ring-blue-100 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/20';
@endphp

<article {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white p-3 shadow-sm shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 dark:shadow-slate-950/20']) }}>
    <div class="flex items-center gap-2.5">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ring-1 {{ $toneClasses }}">
            @switch($icon)
                @case('map')
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75 3.75 4.5v13.5L9 20.25m0-13.5 6-2.25m-6 2.25v13.5m6-15.75 5.25 2.25v13.5L15 18m0-13.5V18m0 0-6 2.25" /></svg>
                    @break
                @case('sparkles')
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.091-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.091 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.091ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.456-2.456L14.25 6l1.035-.259a3.375 3.375 0 0 0 2.456-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" /></svg>
                    @break
                @case('banknotes')
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75h19.5M3.75 8.25h16.5a1.5 1.5 0 0 1 1.5 1.5v6.75a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5V9.75a1.5 1.5 0 0 1 1.5-1.5Zm8.25 6a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" /></svg>
                    @break
                @default
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.592 0L21.75 12M4.5 9.75v9A1.5 1.5 0 0 0 6 20.25h3.75v-6h4.5v6H18a1.5 1.5 0 0 0 1.5-1.5v-9" /></svg>
            @endswitch
        </span>
        <div class="min-w-0">
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $title }}</p>
            <p class="mt-0.5 text-xl font-bold leading-none text-slate-950 dark:text-white">{{ $value }}</p>
            <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">{{ $description }}</p>
        </div>
    </div>
</article>
