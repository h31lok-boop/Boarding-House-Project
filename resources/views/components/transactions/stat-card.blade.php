@props([
    'label',
    'value',
    'description' => null,
    'icon' => 'document',
])

<article {{ $attributes->merge(['class' => 'bm-stat-card p-3.5 dark:border-slate-800 dark:bg-slate-900']) }}>
    <div class="flex items-center gap-2.5">
        <div class="bm-icon-soft flex h-9 w-9 shrink-0 dark:bg-blue-400/10 dark:text-blue-300">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                @switch($icon)
                    @case('wallet')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.75V9.75A2.25 2.25 0 0 0 18.75 7.5H4.5A2.25 2.25 0 0 1 2.25 5.25m0 0A2.25 2.25 0 0 1 4.5 3h13.5A2.25 2.25 0 0 1 20.25 5.25V7.5m-18-2.25v13.5A2.25 2.25 0 0 0 4.5 21h14.25A2.25 2.25 0 0 0 21 18.75v-3m-4.5-3h4.5v3h-4.5a1.5 1.5 0 0 1 0-3Z" />
                        @break

                    @case('clock')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        @break

                    @case('calendar')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V6.75Z" />
                        @break

                    @default
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625A3.375 3.375 0 0 0 16.125 8.25h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25A2.25 2.25 0 0 0 6 4.5v15A2.25 2.25 0 0 0 8.25 21.75h7.5A2.25 2.25 0 0 0 18 19.5v-2.25" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 2.25V7.5A1.5 1.5 0 0 0 15 9h4.5" />
                @endswitch
            </svg>
        </div>
        <div class="min-w-0">
            <p class="truncate text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $label }}</p>
            <p class="mt-1 truncate text-lg font-bold text-slate-950 dark:text-white">{{ $value }}</p>
            @if ($description)
                <p class="mt-0.5 truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $description }}</p>
            @endif
        </div>
    </div>
</article>
