@props([
    'label',
    'value',
    'meta' => null,
    'icon' => 'credit-card',
    'loading' => false,
])

<article {{ $attributes->merge(['class' => 'bm-stat-card p-4 dark:border-slate-800 dark:bg-slate-900']) }}>
    @if ($loading)
        <div class="flex animate-pulse items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
            <div class="min-w-0 flex-1 space-y-2">
                <div class="h-2.5 w-24 rounded bg-slate-200 dark:bg-slate-800"></div>
                <div class="h-5 w-32 rounded bg-slate-200 dark:bg-slate-800"></div>
                <div class="h-2.5 w-20 rounded bg-slate-200 dark:bg-slate-800"></div>
            </div>
        </div>
    @else
    <div class="flex items-center gap-3">
        <div class="bm-icon-soft flex h-10 w-10 shrink-0 dark:bg-blue-400/10 dark:text-blue-300">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                @switch($icon)
                    @case('check-circle')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        @break

                    @case('clock')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        @break

                    @case('calendar')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V6.75Z" />
                        @break

                    @default
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                @endswitch
            </svg>
        </div>

        <div class="min-w-0">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
            <p class="mt-1 truncate text-lg font-bold tracking-normal text-slate-950 dark:text-white">{{ $value }}</p>
            @if ($meta)
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $meta }}</p>
            @endif
        </div>
    </div>
    @endif
</article>
