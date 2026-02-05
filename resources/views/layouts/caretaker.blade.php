<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $routeName = request()->route()?->getName();
        $routeTitle = null;

        if ($routeName) {
            if (str_starts_with($routeName, 'admin.')) {
                $routeTitle = 'Admin Dashboard';
            } elseif (str_starts_with($routeName, 'owner.')) {
                $routeTitle = 'Owner Dashboard';
            } elseif (str_starts_with($routeName, 'caretaker.')) {
                $routeTitle = 'Caretaker Dashboard';
            } elseif (str_starts_with($routeName, 'osas.')) {
                $routeTitle = 'OSAS Dashboard';
            } elseif (str_starts_with($routeName, 'tenant.')) {
                $routeTitle = 'Tenant Dashboard';
            } elseif ($routeName === 'profile.edit') {
                $routeTitle = 'Profile';
            }
        }

        $user = auth()->user();
        $legacyRole = $user?->role ? strtolower($user->role) : null;
        $roleTitle = null;

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            $roleTitle = 'Admin Dashboard';
        } elseif ($legacyRole === 'owner') {
            $roleTitle = 'Owner Dashboard';
        } elseif ($legacyRole === 'admin') {
            $roleTitle = 'Admin Dashboard';
        } elseif ($legacyRole === 'caretaker') {
            $roleTitle = 'Caretaker Dashboard';
        } elseif ($legacyRole === 'osas') {
            $roleTitle = 'OSAS Dashboard';
        } elseif ($legacyRole === 'tenant') {
            $roleTitle = 'Tenant Dashboard';
        } elseif ($user && method_exists($user, 'hasRole')) {
            if ($user->hasRole('caretaker')) {
                $roleTitle = 'Caretaker Dashboard';
            } elseif ($user->hasRole('osas')) {
                $roleTitle = 'OSAS Dashboard';
            } elseif ($user->hasRole('tenant')) {
                $roleTitle = 'Tenant Dashboard';
            }
        }

        $pageTitle = $title ?? $routeTitle ?? $roleTitle ?? config('app.name', 'Dashboard');
    @endphp
    <title>{{ $pageTitle }}</title>
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
<body class="min-h-screen ui-bg overflow-x-hidden">
    {{ $slot }}
</body>
</html>
