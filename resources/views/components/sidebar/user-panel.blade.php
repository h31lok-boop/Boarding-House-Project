@php
    $navBase = 'flex items-center gap-2 px-3 py-2 rounded-lg';
    $navActive = $navBase . ' ui-surface-2 text-[color:var(--text)] font-medium border ui-border';
    $navInactive = $navBase . ' text-[color:var(--muted)] hover:bg-[color:var(--surface-2)]';
    $links = [
        ['route' => 'user.dashboard', 'label' => 'Dashboard'],
        ['route' => 'user.browse', 'label' => 'Browse Listings'],
        ['route' => 'user.recommendations', 'label' => 'Recommended Boarding Houses'],
        ['route' => 'user.reservations', 'label' => 'Reservations'],
        ['route' => 'user.payments', 'label' => 'Payments'],
        ['route' => 'user.messages', 'label' => 'Messages'],
        ['route' => 'user.reviews', 'label' => 'Reviews'],
        ['route' => 'user.profile', 'label' => 'Profile'],
        ['route' => 'user.settings', 'label' => 'Settings'],
    ];
@endphp

<nav class="flex-1 space-y-4 text-sm sidebar-nav">
    <div>
        <p class="text-xs uppercase ui-muted mb-2 sidebar-group">User</p>
        @foreach ($links as $link)
            <a href="{{ route($link['route']) }}" class="{{ request()->routeIs($link['route'].'*') ? $navActive : $navInactive }}">
                <span class="sidebar-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="1.6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 12h8"/></svg>
                </span>
                <span class="sidebar-text">{{ $link['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>

<p class="text-xs ui-muted mt-4 sidebar-footer">&copy; 2026 Boarding House</p>
