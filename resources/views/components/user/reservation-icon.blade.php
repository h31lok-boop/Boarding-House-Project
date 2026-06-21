@props(['name' => 'calendar'])

<svg {{ $attributes->merge(['class' => 'h-5 w-5', 'fill' => 'none', 'viewBox' => '0 0 24 24', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'aria-hidden' => 'true']) }}>
    @switch($name)
        @case('arrow-right')
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            @break

        @case('banknotes')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A2.25 2.25 0 0 1 5.25 5.25h13.5A2.25 2.25 0 0 1 21 7.5v9A2.25 2.25 0 0 1 18.75 18.75H5.25A2.25 2.25 0 0 1 3 16.5v-9Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5ZM6.75 9.75h.01M17.25 14.25h.01" />
            @break

        @case('building')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 21V5.25A2.25 2.25 0 0 1 6.75 3h6A2.25 2.25 0 0 1 15 5.25V21M15 8.25h2.25A2.25 2.25 0 0 1 19.5 10.5V21M3 21h18" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5h3M8.25 11.25h3M8.25 15h3" />
            @break

        @case('calendar')
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A1.5 1.5 0 0 1 18.75 20.25H5.25A1.5 1.5 0 0 1 3.75 18.75v-12A1.5 1.5 0 0 1 5.25 5.25Z" />
            @break

        @case('chat')
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 10.5h.01M12 10.5h.01M16.5 10.5h.01M5.25 18.75l-1.5 3v-4.5A4.5 4.5 0 0 1 1.5 13.5v-6A4.5 4.5 0 0 1 6 3h12a4.5 4.5 0 0 1 4.5 4.5v6A4.5 4.5 0 0 1 18 18H7.5l-2.25.75Z" />
            @break

        @case('check')
            <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 12.75 4.5 4.5 9-10.5" />
            @break

        @case('clock')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5V12l3 3M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            @break

        @case('credit-card')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5A2.25 2.25 0 0 1 6 5.25h12A2.25 2.25 0 0 1 20.25 7.5v9A2.25 2.25 0 0 1 18 18.75H6A2.25 2.25 0 0 1 3.75 16.5v-9Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.75h16.5M7.5 15h2.25" />
            @break

        @case('document')
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3.75h6L19.5 9v10.5a.75.75 0 0 1-.75.75H5.25a.75.75 0 0 1-.75-.75v-15a.75.75 0 0 1 .75-.75h3Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 3.75V9h5.25M8.25 13.5h7.5M8.25 16.5h4.5" />
            @break

        @case('eye')
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            @break

        @case('home')
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 11.25 8.25-7.5a1.13 1.13 0 0 1 1.5 0l8.25 7.5M5.25 10.5v8.25c0 .83.67 1.5 1.5 1.5h3.75V15h3v5.25h3.75c.83 0 1.5-.67 1.5-1.5V10.5" />
            @break

        @case('map-pin')
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.5-7.5 10.5-7.5 10.5S4.5 18 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            @break

        @case('phone')
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.34c0-.93.78-1.71 1.71-1.71h2.42c.38 0 .71.27.8.64l.92 4.17c.07.29-.02.59-.23.77l-1.32 1.2c.43 1.85 1.68 3.4 3.45 4.56.23.14.52.12.72-.07l1.37-1.24c.22-.2.52-.28.77-.22l4.17.93c.37.08.64.41.64.79v2.42c0 .93-.78 1.71-1.71 1.71H6.34A4.09 4.09 0 0 1 2.25 16.9V6.34Z" />
            @break

        @case('shield')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75 5.25 6v5.25c0 4.14 2.66 7.9 6.75 9 4.09-1.1 6.75-4.86 6.75-9V6L12 3.75Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 12.75 1.5 1.5 3.75-4.5" />
            @break

        @case('user')
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
            @break

        @case('wifi')
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25a15 15 0 0 1 19.5 0M5.63 11.63a10.5 10.5 0 0 1 12.74 0M9.01 15a6 6 0 0 1 5.98 0M12 18.75h.01" />
            @break

        @case('x')
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            @break

        @default
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A1.5 1.5 0 0 1 18.75 20.25H5.25A1.5 1.5 0 0 1 3.75 18.75v-12A1.5 1.5 0 0 1 5.25 5.25Z" />
    @endswitch
</svg>
