@php
    $navBase = 'flex items-center gap-2 px-3 py-2 rounded-lg transition-colors';
    $navActive = $navBase . ' ui-surface-2 text-[color:var(--text)] font-medium border ui-border';
    $navInactive = $navBase . ' text-[color:var(--muted)] hover:bg-[color:var(--surface-2)]';
    $logoutClass = $navBase . ' w-full text-left text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30';

    $menus = [
        [
            'label' => 'Dashboard',
            'route' => 'user.dashboard',
            'icon' => 'dashboard',
            'match' => ['user.dashboard'],
        ],
        [
            'label' => 'Find Boarding Houses',
            'route' => 'user.browse',
            'icon' => 'search',
            'match' => ['user.browse*'],
        ],
        [
            'label' => 'Matchmaking',
            'route' => 'user.recommendations',
            'icon' => 'matchmaking',
            'match' => ['user.recommendations*', 'user.match-requests*'],
        ],
        [
            'label' => 'My Preferences',
            'route' => 'user.profile',
            'icon' => 'preferences',
            'match' => ['user.profile*'],
        ],
        [
            'label' => 'Bookings & Reservations',
            'route' => 'user.reservations',
            'icon' => 'reservations',
            'match' => ['user.reservations*'],
        ],
        [
            'label' => 'Payments',
            'route' => 'user.payments',
            'icon' => 'payments',
            'match' => ['user.payments*'],
        ],
        [
            'label' => 'Feedback & Reviews',
            'route' => 'user.reviews',
            'icon' => 'reviews',
            'match' => ['user.reviews*'],
        ],
        [
            'label' => 'Messages',
            'route' => 'user.messages',
            'icon' => 'messages',
            'match' => ['user.messages*'],
        ],
        [
            'label' => 'Profile Settings',
            'route' => 'user.settings',
            'icon' => 'settings',
            'match' => ['user.settings*'],
        ],
    ];
@endphp

<nav class="flex-1 space-y-2 text-sm sidebar-nav" aria-label="Tenant navigation">
    <p class="text-xs uppercase ui-muted mb-2 sidebar-group">Main Navigation</p>

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

    <form method="POST" action="{{ route('logout') }}" class="pt-4">
        @csrf
        <button type="submit" class="{{ $logoutClass }}" title="Logout">
            <span class="sidebar-icon">
                @include('components.sidebar.partials.admin-icon', ['name' => 'logout'])
            </span>
            <span class="sidebar-text">
                <span class="block font-medium">Logout</span>
                <span class="block text-xs text-rose-500/80">Log out of your account</span>
            </span>
        </button>
    </form>
</nav>

<div class="sidebar-help mt-4 rounded-lg border ui-border bg-violet-50/70 p-4 text-sm dark:bg-violet-950/20">
    <div class="flex items-start gap-3">
        <span class="sidebar-icon text-violet-600">
            @include('components.sidebar.partials.admin-icon', ['name' => 'support'])
        </span>
        <div class="min-w-0">
            <p class="font-semibold text-violet-700 dark:text-violet-200">Need Help?</p>
            <p class="mt-1 text-xs ui-muted">Contact our support team</p>
            <a href="{{ route('user.messages') }}" class="mt-3 inline-flex rounded-lg bg-violet-600 px-3 py-2 text-xs font-semibold text-white hover:bg-violet-700">Go to Help Center</a>
        </div>
    </div>
</div>

<p class="text-xs ui-muted mt-4 sidebar-footer">&copy; 2026 BoardMatch. All rights reserved.</p>
