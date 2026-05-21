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
            } elseif ($routeName === 'owner.profile') {
                $routeTitle = 'Admin Profile';
            } elseif (str_starts_with($routeName, 'owner.')) {
                $routeTitle = 'Admin Dashboard';
            } elseif (str_starts_with($routeName, 'tenant.')) {
                $routeTitle = 'User Dashboard';
            } elseif (str_starts_with($routeName, 'user.')) {
                $routeTitle = 'User Dashboard';
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
            $roleTitle = 'Admin Dashboard';
        } elseif ($legacyRole === 'admin') {
            $roleTitle = 'Admin Dashboard';
        } elseif ($legacyRole === 'tenant') {
            $roleTitle = 'User Dashboard';
        } elseif ($legacyRole === 'user') {
            $roleTitle = 'User Dashboard';
        } elseif ($user && method_exists($user, 'hasRole')) {
            if ($user->hasRole('tenant') || $user->hasRole('user')) {
                $roleTitle = 'User Dashboard';
            }
        }

        $pageTitle = $title ?? $routeTitle ?? $roleTitle ?? config('app.name', 'Dashboard');
    @endphp
    <title>{{ $pageTitle }}</title>
    <x-theme-init />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen ui-bg overflow-x-hidden">
    {{ $slot }}
</body>
</html>
