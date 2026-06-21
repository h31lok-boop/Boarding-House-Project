@props(['name' => 'sparkles'])

<svg {{ $attributes->merge(['class' => 'h-5 w-5', 'fill' => 'none', 'viewBox' => '0 0 24 24', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'aria-hidden' => 'true']) }}>
    @switch($name)
        @case('academic-cap')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.15 12 5.25l7.74 4.9L12 15.05l-7.74-4.9Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 11.7v3.58c0 .7.4 1.35 1.04 1.64A10.08 10.08 0 0 0 12 17.8c1.48 0 2.9-.32 4.21-.88.64-.29 1.04-.94 1.04-1.64V11.7M19.5 10.5v5.25" />
            @break

        @case('arrow-path')
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.02 9.35h4.23V5.13" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 9.35A8.25 8.25 0 1 0 12 20.25a8.2 8.2 0 0 0 6.1-2.7" />
            @break

        @case('arrows-pointing-out')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9V3.75H9M3.75 3.75 10.5 10.5M20.25 15v5.25H15M20.25 20.25 13.5 13.5M15 3.75h5.25V9M20.25 3.75 13.5 10.5M9 20.25H3.75V15M3.75 20.25 10.5 13.5" />
            @break

        @case('bolt')
            <path stroke-linecap="round" stroke-linejoin="round" d="m13.5 2.25-8.25 11.25h6L10.5 21.75l8.25-11.25h-6l.75-8.25Z" />
            @break

        @case('book-open')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c-1.7-1-3.64-1.5-5.63-1.5A2.63 2.63 0 0 0 3.75 7.88v10.87c0 .41.34.75.75.75h.33A11.2 11.2 0 0 1 12 21m0-14.25c1.7-1 3.64-1.5 5.63-1.5a2.63 2.63 0 0 1 2.62 2.63v10.87c0 .41-.34.75-.75.75h-.33A11.2 11.2 0 0 0 12 21m0-14.25V21" />
            @break

        @case('chart-bar')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5V13.5M10.5 19.5V4.5M16.5 19.5v-10.5M3 19.5h18" />
            @break

        @case('check-circle')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            @break

        @case('clipboard-check')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5.25A2.25 2.25 0 0 1 11.25 3h1.5A2.25 2.25 0 0 1 15 5.25m-6 0h6m-6 0A2.25 2.25 0 0 0 6.75 7.5v10.5A2.25 2.25 0 0 0 9 20.25h6A2.25 2.25 0 0 0 17.25 18V7.5A2.25 2.25 0 0 0 15 5.25" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 13.5 1.5 1.5 3-3.75" />
            @break

        @case('currency-dollar')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M16.5 7.5c-.9-.9-2.25-1.5-4.05-1.5-2.1 0-3.7 1.05-3.7 2.7 0 1.8 1.65 2.4 3.75 2.85 2.25.45 4.05 1.13 4.05 3.15 0 1.8-1.8 3.3-4.35 3.3-1.95 0-3.53-.6-4.7-1.65" />
            @break

        @case('heart')
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.07-1.68-3.75-3.75-3.75-1.32 0-2.49.68-3.16 1.72L12 9.38 9.91 6.22A3.74 3.74 0 0 0 6.75 4.5 3.75 3.75 0 0 0 3 8.25c0 5.25 9 11.25 9 11.25s9-6 9-11.25Z" />
            @break

        @case('home')
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 11.25 8.25-7.5a1.13 1.13 0 0 1 1.5 0l8.25 7.5M5.25 10.5v8.25c0 .83.67 1.5 1.5 1.5h3.75v-5.25h3v5.25h3.75c.83 0 1.5-.67 1.5-1.5V10.5" />
            @break

        @case('information-circle')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75h.01M11.25 12h.75v4.5h.75" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            @break

        @case('light-bulb')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18h6M10.5 21h3M8.25 14.25a6 6 0 1 1 7.5 0c-.87.67-1.25 1.48-1.25 2.25h-5c0-.77-.38-1.58-1.25-2.25Z" />
            @break

        @case('map-pin')
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.5-7.5 10.5-7.5 10.5S4.5 18 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            @break

        @case('moon')
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.25A8.25 8.25 0 0 1 10.75 3a7.5 7.5 0 1 0 10.25 10.25Z" />
            @break

        @case('shield-check')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75 5.25 6v5.25c0 4.14 2.66 7.9 6.75 9 4.09-1.1 6.75-4.86 6.75-9V6L12 3.75Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 12.75 1.5 1.5 3.75-4.5" />
            @break

        @case('sun')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25M12 18.75V21M4.64 4.64l1.6 1.6M17.76 17.76l1.6 1.6M3 12h2.25M18.75 12H21M4.64 19.36l1.6-1.6M17.76 6.24l1.6-1.6" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            @break

        @case('truck')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75h11.25v9H3v-9ZM14.25 9h3l3.75 3.75v3H14.25V9Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 18.75a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM17.25 18.75a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" />
            @break

        @case('users')
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9.75a2.25 2.25 0 1 0 0-4.5M21.75 20.25a5.25 5.25 0 0 0-4.5-5.18M6 9.75a2.25 2.25 0 1 1 0-4.5M2.25 20.25a5.25 5.25 0 0 1 4.5-5.18" />
            @break

        @case('wifi')
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25a15 15 0 0 1 19.5 0M5.63 11.63a10.5 10.5 0 0 1 12.74 0M9.01 15a6 6 0 0 1 5.98 0M12 18.75h.01" />
            @break

        @case('x-mark')
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            @break

        @default
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.81 15.9 9 18.75l-.81-2.85A4.5 4.5 0 0 0 5.1 12.81L2.25 12l2.85-.81a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.81 2.85a4.5 4.5 0 0 0 3.09 3.09l2.85.81-2.85.81a4.5 4.5 0 0 0-3.09 3.09ZM18.26 8.72 18 9.75l-.26-1.03a3.38 3.38 0 0 0-2.46-2.46L14.25 6l1.03-.26a3.38 3.38 0 0 0 2.46-2.46L18 2.25l.26 1.03a3.38 3.38 0 0 0 2.46 2.46L21.75 6l-1.03.26a3.38 3.38 0 0 0-2.46 2.46Z" />
    @endswitch
</svg>
