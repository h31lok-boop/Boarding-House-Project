@php($name = $name ?? 'dashboard')

@switch($name)
    @case('dashboard')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 11.5 12 5l8 6.5"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6.5 10.5V19h11v-8.5"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10 19v-5h4v5"/>
        </svg>
        @break

    @case('boarding-house')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 21h18M3 10h18M3 7l9-4 9 4"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 21v-7h6v7"/>
        </svg>
        @break

    @case('search')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.7"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m16 16 4 4"/>
        </svg>
        @break

    @case('rooms')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <rect x="3" y="4" width="18" height="16" rx="2" stroke-width="1.7"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 9h18"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 9v11M8 14h2M14 14h2"/>
        </svg>
        @break

    @case('reservations')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.7"/>
            <path stroke-linecap="round" stroke-width="1.7" d="M8 3v4M16 3v4M4 10h16"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m8 15 2 2 5-5"/>
        </svg>
        @break

    @case('tenants')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="9" cy="7" r="3.5" stroke-width="1.7"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 20a6 6 0 0 1 12 0"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16 7a3 3 0 1 1 0 6M21 20a5 5 0 0 0-5-5"/>
        </svg>
        @break

    @case('inquiries')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 6h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H9l-5 4v-4H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/>
            <path stroke-linecap="round" stroke-width="1.7" d="M12 10v.5M12 14h.01"/>
        </svg>
        @break

    @case('matchmaking')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM16 19a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10.5 9.5 13.5 14.5M13.5 9.5l-3 5"/>
        </svg>
        @break

    @case('preferences')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-width="1.7" d="M4 7h4M14 7h6M4 12h9M17 12h3M4 17h6M14 17h6"/>
            <circle cx="11" cy="7" r="2.2" stroke-width="1.7"/>
            <circle cx="15" cy="12" r="2.2" stroke-width="1.7"/>
            <circle cx="12" cy="17" r="2.2" stroke-width="1.7"/>
        </svg>
        @break

    @case('payments')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <rect x="3" y="6" width="18" height="14" rx="2" stroke-width="1.7"/>
            <path stroke-linecap="round" stroke-width="1.7" d="M3 10h18M7 15h4"/>
        </svg>
        @break

    @case('transactions')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 7h14v10H5z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 10h14M8 15h3"/>
        </svg>
        @break

    @case('messages')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 6h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H9l-5 4v-4H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/>
            <path stroke-linecap="round" stroke-width="1.7" d="M8 10h8M8 14h5"/>
        </svg>
        @break

    @case('announcements')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M18 8.5c1.7.7 3 2.4 3 4.5s-1.3 3.8-3 4.5"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 15h2l6 4V5L6 9H4a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M14.5 8.5a4 4 0 0 1 0 7"/>
        </svg>
        @break

    @case('notifications')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 17H9m0 0-3-9a6 6 0 1 1 12 0l-3 9m-6 0v1a3 3 0 0 0 6 0v-1"/>
        </svg>
        @break

    @case('support')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 12a7 7 0 0 1 14 0v4a3 3 0 0 1-3 3h-2"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 12v3a2 2 0 0 0 2 2h1v-7H7a2 2 0 0 0-2 2ZM19 12v3a2 2 0 0 1-2 2h-1v-7h1a2 2 0 0 1 2 2Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M11 19h3"/>
        </svg>
        @break

    @case('reports')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M7 4h10v16H7z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10 8h4M10 12h4M10 16h2"/>
        </svg>
        @break

    @case('reviews')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9L12 3Z"/>
        </svg>
        @break

    @case('analytics')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 20V14M8 20V10M12 20V6M16 20V12M20 20V8"/>
        </svg>
        @break

    @case('occupancy')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 18h16"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M7 18V9m5 9V6m5 12v-4"/>
        </svg>
        @break

    @case('users')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="9" cy="7" r="3.5" stroke-width="1.7"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 20a6 6 0 0 1 12 0"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M18 8a3 3 0 0 1 0 6M21 20a5 5 0 0 0-5-5"/>
        </svg>
        @break

    @case('settings')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 12a7 7 0 0 0-.1-1.2l2-1.5-2-3.5-2.4 1a7 7 0 0 0-2-1.2L14.2 3h-4.4l-.3 2.6a7 7 0 0 0-2 1.2l-2.4-1-2 3.5 2 1.5A7 7 0 0 0 5 12c0 .4 0 .8.1 1.2l-2 1.5 2 3.5 2.4-1a7 7 0 0 0 2 1.2l.3 2.6h4.4l.3-2.6a7 7 0 0 0 2-1.2l2.4 1 2-3.5-2-1.5c.1-.4.1-.8.1-1.2Z"/>
        </svg>
        @break

    @case('audit-logs')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 22s-8-4-8-10V5l8-3 8 3v7c0 6-8 10-8 10Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 12l2 2 4-4"/>
        </svg>
        @break

    @case('management')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 20V8l8-4 8 4v12"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 20v-6h8v6M8 10h.01M12 10h.01M16 10h.01"/>
        </svg>
        @break

    @case('logout')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M14 16l4-4-4-4M18 12H9"/>
        </svg>
        @break

    @default
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="12" cy="12" r="9" stroke-width="1.7"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 12h8"/>
        </svg>
@endswitch
