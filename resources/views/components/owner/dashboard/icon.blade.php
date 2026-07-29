@props(['name'])

<svg {{ $attributes->merge(['fill' => 'none', 'viewBox' => '0 0 24 24', 'stroke' => 'currentColor', 'aria-hidden' => 'true']) }}>
    @switch($name)
        @case('revenue')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-3-2.8.9.7c1.2.9 3.1.9 4.2 0 1.2-.9 1.2-2.3 0-3.2-.6-.5-1.3-.7-2.1-.7s-1.5-.2-2-.7c-1.1-.9-1.1-2.3 0-3.2s2.9-.9 4 0l.4.4M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            @break
        @case('occupancy')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19V10m5 9V5m5 14v-7m5 7V8"/>
            @break
        @case('tenants')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19.1a9.4 9.4 0 0 0 2.6.4 9.3 9.3 0 0 0 4.1-1 4.1 4.1 0 0 0-7.5-2.4M15 19.1A6.4 6.4 0 0 0 2.3 19l-.1.1A12.3 12.3 0 0 0 8.6 21c2.3 0 4.5-.6 6.4-1.8ZM12 6.4a3.4 3.4 0 1 1-6.8 0 3.4 3.4 0 0 1 6.8 0Zm8.3 2.2a2.6 2.6 0 1 1-5.3 0 2.6 2.6 0 0 1 5.3 0Z"/>
            @break
        @case('rooms')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m2.5 12 8.7-8.7a1.1 1.1 0 0 1 1.6 0l8.7 8.7M4.5 10v11h15V10M9 21v-6h6v6"/>
            @break
        @case('reservation')
        @case('reservations')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3v3m10-3v3M4 9h16M5.5 5h13A1.5 1.5 0 0 1 20 6.5v13A1.5 1.5 0 0 1 18.5 21h-13A1.5 1.5 0 0 1 4 19.5v-13A1.5 1.5 0 0 1 5.5 5Z"/>
            @break
        @case('payment')
        @case('payments')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7.5h18m-16.5 0V18A1.5 1.5 0 0 0 6 19.5h12a1.5 1.5 0 0 0 1.5-1.5V7.5M8 14h4"/>
            @break
        @case('notification')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17H9m0 0-3-9a6 6 0 1 1 12 0l-3 9m-6 0v1a3 3 0 0 0 6 0v-1"/>
            @break
        @case('check-in')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12 4 4L19 6M4 21h16"/>
            @break
        @case('room')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 20V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v15M2 20h20M14 12h.01"/>
            @break
        @default
            <circle cx="12" cy="12" r="8" stroke-width="1.8"/>
    @endswitch
</svg>
