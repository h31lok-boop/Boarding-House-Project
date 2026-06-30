@props([
    'label',
    'state' => 'upcoming',
    'last' => false,
])

@php
    $isDone = $state === 'done';
    $isCurrent = $state === 'current';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex min-w-0 flex-1 flex-col items-center px-3 text-center lg:px-4']) }}>
    @unless($last)
        <span class="absolute left-1/2 top-4 hidden h-px w-full -translate-y-1/2 bg-slate-200 lg:block dark:bg-slate-800"></span>
    @endunless
    <span @class([
        'relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[10px] font-bold ring-4 transition duration-300',
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
    <span class="mt-3 block min-w-0 max-w-full">
        <span @class([
            'mx-auto block max-w-[8.5rem] break-words text-[11px] font-semibold leading-4',
            'text-slate-950 dark:text-white' => $isDone || $isCurrent,
            'text-slate-500 dark:text-slate-400' => ! $isDone && ! $isCurrent,
        ])>{{ $label }}</span>
        <span class="mt-1 block text-[9px] font-medium uppercase tracking-[0.08em] text-slate-400 dark:text-slate-500">
            {{ $isDone ? 'Completed' : ($isCurrent ? 'Current' : 'Waiting') }}
        </span>
    </span>
</div>
