@php
    $navBase = 'flex items-center gap-2 px-3 py-2 rounded-lg transition-colors';
    $navActive = $navBase . ' ui-surface-2 text-[color:var(--text)] font-medium border ui-border';
    $navInactive = $navBase . ' text-[color:var(--muted)] hover:bg-[color:var(--surface-2)]';

    $menus = [
        [
            'label' => 'Dashboard',
            'route' => 'admin.dashboard',
            'icon' => 'dashboard',
            'match' => ['admin.dashboard'],
        ],
        [
            'label' => 'Management',
            'route' => 'admin.users',
            'icon' => 'management',
            'match' => ['admin.users*', 'admin.boarding-houses*', 'admin.listings*', 'admin.rooms*', 'admin.tenant-profiles*'],
        ],
        [
            'label' => 'Matchmaking',
            'route' => 'admin.compatibility-scores',
            'icon' => 'matchmaking',
            'match' => ['admin.compatibility-scores*', 'admin.recommendations*', 'admin.match-requests*'],
        ],
        [
            'label' => 'Transactions',
            'route' => 'admin.inquiries',
            'icon' => 'transactions',
            'match' => ['admin.inquiries*', 'admin.messages*', 'admin.reservations*', 'admin.payments*'],
        ],
        [
            'label' => 'Feedback & Reports',
            'route' => 'admin.reviews',
            'icon' => 'reports',
            'match' => ['admin.reviews*', 'admin.reports*', 'admin.notifications*'],
        ],
        [
            'label' => 'Settings',
            'route' => 'admin.settings',
            'icon' => 'settings',
            'match' => ['admin.settings*'],
        ],
    ];
@endphp

<nav class="flex-1 space-y-2 text-sm sidebar-nav" aria-label="Admin owner navigation">
    <p class="text-xs uppercase ui-muted mb-2 sidebar-group">Admin / Owner</p>

    @foreach ($menus as $menu)
        @php
            $isActive = request()->routeIs(...$menu['match']);
        @endphp

        <a href="{{ route($menu['route']) }}" class="{{ $isActive ? $navActive : $navInactive }}" title="{{ $menu['label'] }}">
            <span class="sidebar-icon">
                @include('components.sidebar.partials.admin-icon', ['name' => $menu['icon']])
            </span>
            <span class="sidebar-text">{{ $menu['label'] }}</span>
        </a>
    @endforeach
</nav>

<p class="text-xs ui-muted mt-4 sidebar-footer">&copy; 2026 BoardMatch</p>
