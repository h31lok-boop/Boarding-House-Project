@props([
    'method',
    'loading' => false,
])

@php
    $type = strtolower((string) ($method['type'] ?? 'cash'));
    $badge = $method['badge'] ?? 'Verified';
@endphp

<article {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white p-2.5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900']) }}>
    @if ($loading)
        <div class="flex animate-pulse items-start gap-2.5">
            <div class="h-7 w-7 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
            <div class="min-w-0 flex-1 space-y-2">
                <div class="h-2.5 w-28 rounded bg-slate-200 dark:bg-slate-800"></div>
                <div class="h-2.5 w-36 rounded bg-slate-200 dark:bg-slate-800"></div>
                <div class="h-2.5 w-24 rounded bg-slate-200 dark:bg-slate-800"></div>
            </div>
        </div>
    @else
    <div class="flex items-start gap-2.5">
        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                @switch($type)
                    @case('gcash')
                    @case('maya')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                        @break

                    @case('bank')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 18.75h15M5.25 8.25h13.5M12 3 3.75 7.5h16.5L12 3Zm-4.5 5.25v10.5m3-10.5v10.5m3-10.5v10.5m3-10.5v10.5" />
                        @break

                    @default
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                @endswitch
            </svg>
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-[11px] font-semibold text-slate-950 dark:text-white">{{ $method['name'] }}</h3>
                <x-payments.status-badge :status="$badge" />
            </div>
            <p class="mt-1 text-[11px] leading-4 text-slate-600 dark:text-slate-300">{{ $method['detail'] }}</p>
            @if (! empty($method['subdetail']))
                <p class="mt-0.5 text-[11px] font-medium text-slate-900 dark:text-slate-100">{{ $method['subdetail'] }}</p>
            @endif
        </div>
    </div>
    @endif
</article>
