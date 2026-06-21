@props([
    'href' => null,
    'title' => '',
    'meta' => null,
    'icon' => null,
    'tone' => 'blue',
])

@php
    $tones = [
        'blue' => 'bg-blue-50 text-blue-600 ring-blue-100 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/20',
        'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20',
        'amber' => 'bg-amber-50 text-amber-600 ring-amber-100 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20',
        'purple' => 'bg-purple-50 text-purple-600 ring-purple-100 dark:bg-purple-400/10 dark:text-purple-300 dark:ring-purple-400/20',
        'rose' => 'bg-rose-50 text-rose-600 ring-rose-100 dark:bg-rose-400/10 dark:text-rose-300 dark:ring-rose-400/20',
        'slate' => 'bg-slate-100 text-slate-600 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700',
    ];

    $accents = [
        'blue' => 'before:bg-blue-500',
        'emerald' => 'before:bg-emerald-500',
        'amber' => 'before:bg-amber-500',
        'purple' => 'before:bg-purple-500',
        'rose' => 'before:bg-rose-500',
        'slate' => 'before:bg-slate-400',
    ];

    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge([
    'class' => 'group relative block overflow-hidden rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition duration-300 before:absolute before:inset-x-0 before:top-0 before:h-1 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg hover:shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-400/40 dark:hover:shadow-slate-950/30 '.($accents[$tone] ?? $accents['blue'])
]) }}>
    <div class="flex items-start justify-between gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ring-1 {{ $tones[$tone] ?? $tones['blue'] }}">
            {{ $icon }}
        </span>
        @if($meta)
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $meta }}</span>
        @endif
    </div>
    <div class="mt-3">
        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ $title }}</h3>
        {{ $slot }}
    </div>
</{{ $tag }}>
