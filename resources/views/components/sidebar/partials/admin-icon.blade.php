@php($name = $name ?? 'dashboard')

@switch($name)
    @case('dashboard')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 11.5 12 5l8 6.5"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6.5 10.5V19h11v-8.5"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10 19v-5h4v5"/>
        </svg>
        @break

    @case('management')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 20V8l8-4 8 4v12"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 20v-6h8v6M8 10h.01M12 10h.01M16 10h.01"/>
        </svg>
        @break

    @case('matchmaking')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM16 19a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10.5 9.5 13.5 14.5M13.5 9.5l-3 5"/>
        </svg>
        @break

    @case('transactions')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 7h14v10H5z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 10h14M8 15h3"/>
        </svg>
        @break

    @case('reports')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M7 4h10v16H7z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10 8h4M10 12h4M10 16h2"/>
        </svg>
        @break

    @case('settings')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 12a7 7 0 0 0-.1-1.2l2-1.5-2-3.5-2.4 1a7 7 0 0 0-2-1.2L14.2 3h-4.4l-.3 2.6a7 7 0 0 0-2 1.2l-2.4-1-2 3.5 2 1.5A7 7 0 0 0 5 12c0 .4 0 .8.1 1.2l-2 1.5 2 3.5 2.4-1a7 7 0 0 0 2 1.2l.3 2.6h4.4l.3-2.6a7 7 0 0 0 2-1.2l2.4 1 2-3.5-2-1.5c.1-.4.1-.8.1-1.2Z"/>
        </svg>
        @break

    @default
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="12" cy="12" r="9" stroke-width="1.7"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 12h8"/>
        </svg>
@endswitch
