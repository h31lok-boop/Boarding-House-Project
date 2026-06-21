@props([
    'href' => '#',
    'active' => false,
])

<a href="{{ $href }}" {{ $attributes->merge([
    'class' => 'inline-flex h-8 shrink-0 items-center justify-center rounded-full px-3 text-xs font-bold transition ring-1 '
        . ($active
            ? 'bg-blue-600 text-white ring-blue-600 shadow-sm shadow-blue-600/20'
            : 'bg-white text-slate-700 ring-slate-200 hover:bg-blue-50 hover:text-blue-700 hover:ring-blue-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-blue-400/10 dark:hover:text-blue-200 dark:hover:ring-blue-400/30')
]) }}>
    {{ $slot }}
</a>
