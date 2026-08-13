<!DOCTYPE html>
<html lang="en" data-theme="light" data-theme-mode="dashboard">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Boarding House Match Making System') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/boardmatch-final-logo.png') }}">
    <script>
        (function () {
            try {
                const stored = localStorage.getItem('theme');
                const theme = stored === 'dark' || stored === 'light'
                    ? stored
                    : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

                document.documentElement.setAttribute('data-theme', theme);
            } catch (error) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    <style id="dashboard-critical-styles">
        :where(.admin-shell svg, .user-shell svg) {
            height: 1.25rem;
            width: 1.25rem;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 dark:bg-slate-900 overflow-x-hidden transition-colors">
    {{ $slot }}
</body>
</html>
