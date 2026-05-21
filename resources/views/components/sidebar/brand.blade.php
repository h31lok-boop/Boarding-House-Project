@props([
    'title' => 'StaySafe Finder',
    'subtitle' => null,
])

@php
    $routeName = request()->route()?->getName() ?? '';
    $dashboardUrl = url('/dashboard');

    if (auth()->check()) {
        $user = auth()->user();
        $candidateRoute = method_exists($user, 'dashboardRouteName') ? $user->dashboardRouteName() : null;

        if (filled($candidateRoute) && \Illuminate\Support\Facades\Route::has($candidateRoute)) {
            $dashboardUrl = route($candidateRoute);
        }
    }

    $roleLabel = $subtitle ?? match (true) {
        str_starts_with($routeName, 'tenant.') => 'User Workspace',
        str_starts_with($routeName, 'user.') => 'User Workspace',
        str_starts_with($routeName, 'owner.') => 'Admin Workspace',
        str_starts_with($routeName, 'admin.') => 'Admin Workspace',
        str_starts_with($routeName, 'superduperadmin.') => 'Admin Workspace',
        default => 'Workspace',
    };
@endphp

<a
    href="{{ $dashboardUrl }}"
    class="sidebar-brand-expanded flex min-w-0 items-center gap-3 rounded-2xl border border-white/10 bg-slate-950 px-3.5 py-3 text-white shadow-sm transition-colors duration-200 hover:border-white/25 hover:bg-slate-900"
    title="{{ $title }}"
>
    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/15 bg-white/10 text-white">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 11.5 12 5l8 6.5V19a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-7.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m9.5 13 1.7 1.7 3.3-3.7" />
        </svg>
    </span>

    <span class="sidebar-brand-text min-w-0 leading-tight">
        <span class="block truncate text-[0.95rem] font-semibold tracking-tight text-white">{{ $title }}</span>
        <span class="block truncate pt-0.5 text-xs font-medium uppercase text-white/60">{{ $roleLabel }}</span>
    </span>
</a>

<a
    href="{{ $dashboardUrl }}"
    class="collapsed-only hidden items-center justify-center rounded-2xl border border-white/10 bg-slate-950 p-3 text-white shadow-sm"
    title="{{ $title }} | {{ $roleLabel }}"
>
    <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/15 bg-white/10">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 11.5 12 5l8 6.5V19a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-7.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m9.5 13 1.7 1.7 3.3-3.7" />
        </svg>
    </span>
</a>

<style>
    [data-sidebar='collapsed'] .sidebar .sidebar-brand-expanded {
        display: none;
    }

    [data-sidebar='collapsed'] .sidebar .collapsed-only {
        display: inline-flex !important;
    }
</style>
