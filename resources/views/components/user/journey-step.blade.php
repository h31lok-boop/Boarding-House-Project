@props([
    'label',
    'state' => 'upcoming',
    'last' => false,
])

@php
    $isDone = $state === 'done';
    $isCurrent = $state === 'current';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex min-w-[112px] flex-1 items-center gap-2.5']) }}>
    @unless($last)
        <span class="absolute left-7 right-0 top-3.5 hidden h-px translate-x-3 bg-slate-200 dark:bg-slate-800 xl:block"></span>
    @endunless
    <span @class([
        'relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[11px] font-bold ring-4 transition duration-500',
        'bg-emerald-500 text-white ring-emerald-100 dark:ring-emerald-400/15' => $isDone,
        'animate-pulse bg-blue-600 text-white ring-blue-100 dark:ring-blue-400/20' => $isCurrent,
        'bg-slate-100 text-slate-400 ring-slate-50 dark:bg-slate-800 dark:text-slate-500 dark:ring-slate-800/60' => ! $isDone && ! $isCurrent,
    ])>
        @if($isDone)
            <span aria-hidden="true">&check;</span>
        @elseif($isCurrent)
            <span aria-hidden="true">...</span>
        @endif
    </span>
    <span>
        <span @class([
            'block text-xs font-semibold',
            'text-slate-950 dark:text-white' => $isDone || $isCurrent,
            'text-slate-500 dark:text-slate-400' => ! $isDone && ! $isCurrent,
        ])>{{ $label }}</span>
        <span class="mt-0.5 block text-[10px] font-medium text-slate-400">
            {{ $isDone ? 'Completed' : ($isCurrent ? 'Current stage' : 'Waiting') }}
        </span>
    </span>
</div>
