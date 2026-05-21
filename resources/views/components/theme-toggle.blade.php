@props([
    'showLabel' => false,
    'prefix' => null,
])

<button
    type="button"
    data-theme-toggle
    aria-label="Toggle color theme"
    aria-pressed="false"
    {{ $attributes->merge(['class' => 'theme-toggle-control']) }}
>
    <span class="theme-toggle-icon" aria-hidden="true">
        <span data-theme-icon="light">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="4" stroke-width="1.8" />
                <path stroke-linecap="round" stroke-width="1.8" d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
            </svg>
        </span>
        <span data-theme-icon="dark" class="hidden">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 14.4A7.5 7.5 0 0 1 9.6 3 9 9 0 1 0 21 14.4Z" />
            </svg>
        </span>
    </span>

    @if ($showLabel)
        <span class="theme-toggle-copy">
            @if ($prefix)
                <span>{{ $prefix }}</span>
            @endif
            <span data-theme-label>Light</span>
        </span>
    @endif
</button>
