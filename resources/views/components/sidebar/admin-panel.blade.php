@php
    $navBase = 'flex items-center gap-2 px-3 py-2 rounded-lg transition-colors';
    $navActive = $navBase . ' ui-surface-2 text-[color:var(--text)] font-medium border ui-border';
    $navInactive = $navBase . ' text-[color:var(--muted)] hover:bg-[color:var(--surface-2)]';
    $groupBase = 'sidebar-menu-button flex items-center justify-between gap-2 px-3 py-2 rounded-lg transition-colors';
    $groupActive = $groupBase . ' ui-surface-2 text-[color:var(--text)] font-medium border ui-border';
    $groupInactive = $groupBase . ' text-[color:var(--muted)] hover:bg-[color:var(--surface-2)]';
    $subBase = 'sidebar-subitem block rounded-lg px-3 py-2 text-[13px] transition-colors';
    $subActive = $subBase . ' bg-[color:var(--surface-2)] text-[color:var(--text)] font-medium';
    $subInactive = $subBase . ' text-[color:var(--muted)] hover:bg-[color:var(--surface-2)] hover:text-[color:var(--text)]';

    $menus = [
        [
            'label' => 'Dashboard',
            'route' => 'admin.dashboard',
            'icon' => 'dashboard',
            'match' => ['admin.dashboard'],
        ],
        [
            'label' => 'Management',
            'icon' => 'management',
            'match' => ['admin.users*', 'admin.boarding-houses*', 'admin.listings*', 'admin.rooms*', 'admin.tenant-profiles*'],
            'items' => [
                ['route' => 'admin.users', 'label' => 'Users'],
                ['route' => 'admin.boarding-houses', 'label' => 'Boarding Houses'],
                ['route' => 'admin.rooms', 'label' => 'Rooms'],
                ['route' => 'admin.tenant-profiles', 'label' => 'Tenant Profiles'],
            ],
        ],
        [
            'label' => 'Matchmaking',
            'icon' => 'matchmaking',
            'match' => ['admin.compatibility-scores*', 'admin.recommendations*', 'admin.match-requests*'],
            'items' => [
                ['route' => 'admin.compatibility-scores', 'label' => 'Compatibility Scores'],
                ['route' => 'admin.recommendations', 'label' => 'Recommendations'],
                ['route' => 'admin.match-requests', 'label' => 'Match Requests'],
            ],
        ],
        [
            'label' => 'Transactions',
            'icon' => 'transactions',
            'match' => ['admin.inquiries*', 'admin.messages*', 'admin.reservations*', 'admin.payments*'],
            'items' => [
                ['route' => 'admin.inquiries', 'label' => 'Inquiries'],
                ['route' => 'admin.messages', 'label' => 'Messages'],
                ['route' => 'admin.reservations', 'label' => 'Reservations'],
                ['route' => 'admin.payments', 'label' => 'Payments'],
            ],
        ],
        [
            'label' => 'Feedback & Reports',
            'icon' => 'reports',
            'match' => ['admin.reviews*', 'admin.reports*', 'admin.notifications*'],
            'items' => [
                ['route' => 'admin.reviews', 'label' => 'Reviews'],
                ['route' => 'admin.reports', 'label' => 'Reports'],
                ['route' => 'admin.notifications', 'label' => 'Notifications'],
            ],
        ],
        [
            'label' => 'Settings',
            'icon' => 'settings',
            'match' => ['admin.settings*'],
            'items' => [
                ['route' => 'admin.settings', 'label' => 'Profile', 'hash' => 'profile'],
                ['route' => 'admin.settings', 'label' => 'Security', 'hash' => 'security'],
                ['route' => 'admin.settings', 'label' => 'Privacy', 'hash' => 'privacy'],
                ['route' => 'admin.settings', 'label' => 'Backup', 'hash' => 'backup'],
                ['route' => 'admin.settings', 'label' => 'Restore', 'hash' => 'restore'],
            ],
        ],
    ];
@endphp

<nav class="flex-1 space-y-2 text-sm sidebar-nav" aria-label="Admin owner navigation">
    <p class="text-xs uppercase ui-muted mb-2 sidebar-group">Admin / Owner</p>

    @foreach ($menus as $menu)
        @php
            $isActive = request()->routeIs(...$menu['match']);
        @endphp

        @if (isset($menu['route']))
            <a href="{{ route($menu['route']) }}" class="{{ $isActive ? $navActive : $navInactive }}" title="{{ $menu['label'] }}">
                <span class="sidebar-icon">
                    @include('components.sidebar.partials.admin-icon', ['name' => $menu['icon']])
                </span>
                <span class="sidebar-text">{{ $menu['label'] }}</span>
            </a>
        @else
            <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }" class="sidebar-menu">
                <button type="button" class="{{ $isActive ? $groupActive : $groupInactive }}" title="{{ $menu['label'] }}" @click="open = !open" :aria-expanded="open.toString()">
                    <span class="sidebar-menu-main">
                        <span class="sidebar-icon">
                            @include('components.sidebar.partials.admin-icon', ['name' => $menu['icon']])
                        </span>
                        <span class="sidebar-text sidebar-menu-label">{{ $menu['label'] }}</span>
                    </span>
                    <svg class="sidebar-chevron h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6"/>
                    </svg>
                </button>

                <div x-show="open" x-transition x-cloak class="sidebar-submenu">
                    @foreach ($menu['items'] as $item)
                        @php
                            $href = route($item['route']) . (isset($item['hash']) ? '#'.$item['hash'] : '');
                            $itemActive = request()->routeIs($item['route'].'*') && ! isset($item['hash']);
                        @endphp
                        <a href="{{ $href }}" class="{{ $itemActive ? $subActive : $subInactive }}">
                            <span class="sidebar-text">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</nav>

<p class="text-xs ui-muted mt-4 sidebar-footer">&copy; 2026 BoardMatch</p>
