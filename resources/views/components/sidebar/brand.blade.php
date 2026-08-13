@php
    $dashRoute = 'dashboard';

    if (auth()->check()) {
        $user = auth()->user();
        if (method_exists($user, 'dashboardRouteName')) {
            $dashRoute = $user->dashboardRouteName();
        }
    }
@endphp

<a href="{{ route($dashRoute) }}" class="user-sidebar-brand flex min-w-0 flex-1 items-center gap-2.5">
    <div class="sidebar-brand-icon h-9 w-9 shrink-0 overflow-hidden rounded-xl shadow-[0_10px_22px_rgba(37,99,235,0.3)] ring-1 ring-white/15">
        <img src="{{ asset('images/boardmatch-final-logo.png') }}" alt="" class="h-full w-full object-cover" aria-hidden="true">
    </div>
    <div class="sidebar-brand-text min-w-0 leading-tight">
        <p class="truncate text-lg font-bold tracking-tight text-white">BoardMatch</p>
        <p class="truncate text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">Student housing</p>
    </div>
</a>
