@props([
    'size' => 'md',
])

@php
    $sizeClasses = $size === 'lg' ? 'h-11 w-11' : 'h-9 w-9';
@endphp

<button
    type="button"
    data-theme-toggle
    class="theme-icon-toggle {{ $sizeClasses }} shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-amber-300 dark:hover:border-slate-600 dark:hover:bg-slate-800"
    aria-label="Switch to dark mode"
    aria-pressed="false"
    title="Switch to dark mode"
    {{ $attributes }}
>
    <svg data-theme-icon="moon" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
    </svg>
    <svg data-theme-icon="sun" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <circle cx="12" cy="12" r="4" stroke-width="1.8" />
        <path stroke-linecap="round" stroke-width="1.8" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
    </svg>
    <span class="sr-only" data-theme-label>Light</span>
</button>
