<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $routeName = request()->route()?->getName();
        $pageTitle = match (true) {
            $routeName && str_starts_with($routeName, 'superduperadmin.') => 'Admin Workspace',
            $routeName && str_starts_with($routeName, 'admin.') => 'Admin Workspace',
            $routeName && str_starts_with($routeName, 'owner.') => 'Admin Dashboard',
            $routeName && str_starts_with($routeName, 'tenant.') => 'User Dashboard',
            $routeName && str_starts_with($routeName, 'user.') => 'User Dashboard',
            default => 'Dashboard',
        };
    @endphp
    <title>{{ $title ?? $pageTitle }}</title>
    <x-theme-init />
    <script>
        (() => {
            try {
                const sidebar = localStorage.getItem('sidebar') || 'expanded';
                document.documentElement.setAttribute('data-sidebar', sidebar);
            } catch (error) {
                document.documentElement.setAttribute('data-sidebar', 'expanded');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
@php
    $routeName = request()->route()?->getName();
    $role = auth()->user()?->role ? strtolower(auth()->user()->role) : null;
    $usesTenantWorkspace = ($routeName && (str_starts_with($routeName, 'tenant.') || str_starts_with($routeName, 'user.')))
        || (in_array($role, ['tenant', 'user'], true) && $routeName && (
            str_starts_with($routeName, 'user.boarding-houses.')
            || str_starts_with($routeName, 'user.favorites.')
            || $routeName === 'profile.edit'
        ));
@endphp
<body class="{{ $usesTenantWorkspace ? 'tenant-workspace-page h-screen overflow-hidden' : 'min-h-screen overflow-x-hidden' }} ui-bg transition-colors">
    {{ $slot }}
</body>
</html>
