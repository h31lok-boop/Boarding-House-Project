@php
    $r = fn($name, $params = []) => route($name, $params);
    $navBase = 'group/sidebar-item relative flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-[13px] font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400/70';
    $navActive = $navBase . ' bg-[#2563EB] text-white shadow-[0_10px_22px_rgba(37,99,235,0.3)]';
    $navInactive = $navBase . ' text-[#CBD5E1] hover:bg-white/10 hover:text-white';
    $isSectionRoute = fn($route, $section) => request()->routeIs($route) && request('section') === $section;
    $isUserPath = fn(string $path) => request()->is($path) || request()->is($path.'/*');
    $notificationBadge = null;

    if (auth()->check()
        && \Illuminate\Support\Facades\Schema::hasTable('notifications')
        && \Illuminate\Support\Facades\Schema::hasColumn('notifications', 'user_id')) {
        $notificationQuery = \Illuminate\Support\Facades\DB::table('notifications')
            ->where('user_id', auth()->id());

        if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'read_at')) {
            $notificationQuery->whereNull('read_at');
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'is_read')) {
            $notificationQuery->where('is_read', false);
        }

        $notificationCount = $notificationQuery->count();
        $notificationBadge = $notificationCount > 0
            ? ($notificationCount > 99 ? '99+' : (string) $notificationCount)
            : null;
    }

    $sections = [
        [
            'label' => 'MAIN',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'href' => $r('user.dashboard'),
                    'icon' => 'dashboard',
                    'active' => request()->routeIs('user.dashboard') || request()->is('user/dashboard'),
                ],
                [
                    'label' => 'Find Boarding Houses',
                    'href' => $r('user.boarding-houses.index', ['tab' => 'recommended']),
                    'icon' => 'search',
                    'active' => ($isUserPath('user/boarding-houses') || $isUserPath('user/browse-listings') || request()->routeIs('user.browse*'))
                        && request()->query('tab', 'recommended') !== 'matchmaking',
                ],
                [
                    'label' => 'Matchmaking',
                    'href' => $r('user.boarding-houses.index', ['tab' => 'matchmaking']),
                    'icon' => 'matchmaking',
                    'active' => $isUserPath('user/matchmaking')
                        || $isUserPath('user/recommendations')
                        || request()->query('tab') === 'matchmaking'
                        || request()->routeIs('user.recommendations*', 'user.matchmaking*', 'user.match-requests*'),
                ],
                [
                    'label' => 'My Preferences',
                    'href' => $r('user.preferences.index'),
                    'icon' => 'preferences',
                    'active' => $isUserPath('user/preferences') || request()->routeIs('user.profile*'),
                ],
            ],
        ],
        [
            'label' => 'BOOKING',
            'items' => [
                [
                    'label' => 'Reservations',
                    'href' => $r('user.reservations.index'),
                    'icon' => 'reservations',
                    'active' => $isUserPath('user/reservations') || request()->routeIs('user.reservations*'),
                ],
                [
                    'label' => 'Payments',
                    'href' => $r('user.payments.index'),
                    'icon' => 'payments',
                    'active' => ($isUserPath('user/payments') || request()->routeIs('user.payments*')) && ! $isUserPath('user/transactions') && request('section') !== 'transactions',
                ],
                [
                    'label' => 'Transactions',
                    'href' => $r('user.transactions.index'),
                    'icon' => 'transactions',
                    'active' => $isUserPath('user/transactions') || request()->routeIs('user.transactions*') || $isSectionRoute('user.payments*', 'transactions'),
                ],
                [
                    'label' => 'Messages',
                    'href' => $r('user.messages.index'),
                    'icon' => 'messages',
                    'active' => $isUserPath('user/messages') || request()->routeIs('user.messages*'),
                    'badge' => '2',
                    'badgeColor' => 'blue',
                ],
            ],
        ],
        [
            'label' => 'ACCOUNT',
            'items' => [
                [
                    'label' => 'Notifications',
                    'href' => $r('user.notifications.index'),
                    'icon' => 'notifications',
                    'active' => $isUserPath('user/notifications') || request()->routeIs('user.notifications*'),
                    'badge' => $notificationBadge,
                    'badgeColor' => 'red',
                ],
                [
                    'label' => 'Profile Settings',
                    'href' => $r('user.settings.index'),
                    'icon' => 'settings',
                    'active' => $isUserPath('user/settings') || request()->routeIs('user.settings*'),
                ],
                [
                    'label' => 'Help Center',
                    'href' => $r('user.help-center.index'),
                    'icon' => 'support',
                    'active' => $isUserPath('user/help-center') || request()->routeIs('user.help-center*', 'user.help*'),
                ],
            ],
        ],
    ];
@endphp

<nav class="sidebar-nav user-sidebar-nav flex-1 space-y-4 pr-1 text-sm" aria-label="Tenant navigation">
    @foreach ($sections as $section)
        <section class="space-y-1.5">
            <p class="sidebar-group px-2.5 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">{{ $section['label'] }}</p>
            <div class="space-y-1">
                @foreach ($section['items'] as $menu)
                    @php
                        $isActive = (bool) ($menu['active'] ?? false);

                        $badgeBase = 'sidebar-badge ml-auto inline-flex min-w-[1.25rem] items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none text-white shadow-sm';
                        $badgeTone = ($menu['badgeColor'] ?? 'blue') === 'red'
                            ? 'bg-red-500 shadow-red-500/20'
                            : 'bg-blue-500 shadow-blue-500/20';
                    @endphp

                    <a href="{{ $menu['href'] }}" class="{{ $isActive ? $navActive : $navInactive }}" title="{{ $menu['label'] }}" @if($isActive) aria-current="page" @endif>
                        <span class="sidebar-icon flex h-5 w-5 shrink-0 items-center justify-center">
                            @include('components.sidebar.partials.admin-icon', ['name' => $menu['icon']])
                        </span>
                        <span class="sidebar-text min-w-0 flex-1 truncate">{{ $menu['label'] }}</span>
                        @if (!empty($menu['badge']))
                            <span class="{{ $badgeBase }} {{ $isActive ? 'bg-white/20 text-white shadow-none' : $badgeTone }}">{{ $menu['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach
</nav>

<div class="sidebar-help mt-2 overflow-hidden rounded-xl border border-white/10 bg-white/[0.06] p-2.5 shadow-lg shadow-slate-950/20">
    <div class="flex items-center gap-2">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-500/20 text-blue-200 ring-1 ring-blue-400/20">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 8a3 3 0 1 0-2.83-4H8.83A3 3 0 1 0 6 8c.35 0 .69-.06 1-.17v8.34A3 3 0 1 0 8.83 20h6.34A3 3 0 1 0 18 16c-.35 0-.69.06-1 .17V7.83c.31.11.65.17 1 .17Z" />
            </svg>
        </span>
        <div class="sidebar-text min-w-0">
            <p class="text-[11px] font-bold text-white">Refer a Friend</p>
            <p class="mt-0.5 text-[10px] leading-3 text-slate-400">Earn rewards when friends book.</p>
        </div>
    </div>
    <a href="{{ $r('user.help-center.index') }}" class="sidebar-text mt-2 inline-flex h-7 w-full items-center justify-center rounded-lg bg-blue-600 px-3 text-[11px] font-bold text-white shadow-sm transition hover:bg-blue-500">
        Learn More
    </a>
</div>

<p class="sidebar-footer mt-3 text-center text-[11px] leading-4 text-slate-500">&copy; 2026 BoardMatch<br>All rights reserved.</p>
