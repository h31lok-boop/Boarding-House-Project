<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            (function () {
                const stored = localStorage.getItem('theme');
                if (stored) {
                    document.documentElement.setAttribute('data-theme', stored);
                }
            })();
        </script>
    </head>
    <body class="font-sans antialiased ui-bg">
        <div class="min-h-screen flex">
            @include('layouts.navigation')

            <div class="flex-1">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 app-shell">
                    @isset($header)
                        <header class="ui-card mb-4">
                            <div class="py-4 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <main class="max-w-4xl mx-auto">
                        @yield('content')
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
