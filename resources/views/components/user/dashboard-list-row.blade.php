@props([
    'title',
    'detail' => null,
    'meta' => null,
    'href' => null,
    'tone' => 'blue',
])

@php
    $tones = [
        'blue' => 'bg-blue-50 text-blue-600 dark:bg-blue-400/10 dark:text-blue-300',
        'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300',
        'amber' => 'bg-amber-50 text-amber-600 dark:bg-amber-400/10 dark:text-amber-300',
        'rose' => 'bg-rose-50 text-rose-600 dark:bg-rose-400/10 dark:text-rose-300',
        'slate' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300',
    ];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge([
    'class' => 'flex items-center gap-3 rounded-xl p-2.5 transition hover:bg-slate-50 dark:hover:bg-slate-800/60'
]) }}>
    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] ?? $tones['blue'] }}">
        {{ $icon ?? '' }}
    </span>
    <span class="min-w-0 flex-1">
        <span class="block truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $title }}</span>
        @if($detail)
            <span class="mt-0.5 block truncate text-xs text-slate-500 dark:text-slate-400">{{ $detail }}</span>
        @endif
    </span>
    @if($meta)
        <span class="shrink-0 text-xs font-medium text-slate-400">{{ $meta }}</span>
    @endif
</{{ $tag }}>
