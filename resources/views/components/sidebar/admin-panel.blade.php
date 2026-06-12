@php
    $r = function ($name, $params = [], $fallback = null) {
        if (\Illuminate\Support\Facades\Route::has($name)) {
            return route($name, $params);
        }

        return $fallback && \Illuminate\Support\Facades\Route::has($fallback)
            ? route($fallback, $params)
            : url()->current();
    };

    $isPath = fn (...$patterns) => collect($patterns)->contains(
        fn ($pattern) => request()->is($pattern)
    );

    $unreadNotificationsCount = 0;
    if (\Illuminate\Support\Facades\Schema::hasTable('notifications') && auth()->id()) {
        $notificationsQuery = \Illuminate\Support\Facades\DB::table('notifications')
            ->where('user_id', auth()->id());

        if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'is_read')) {
            $notificationsQuery->where('is_read', false);
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'read_at')) {
            $notificationsQuery->whereNull('read_at');
        }

        $unreadNotificationsCount = (int) $notificationsQuery->count();
    }

    $navBase = 'group/sidebar-item relative flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-[13px] font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400/70';
    $navActive = $navBase . ' bg-[#2563EB] text-white shadow-[0_10px_22px_rgba(37,99,235,0.3)]';
    $navInactive = $navBase . ' text-[#CBD5E1] hover:bg-white/10 hover:text-white';

    $sections = [
        [
            'label' => 'MAIN',
            'items' => [
                [
                    'key' => 'dashboard',
                    'label' => 'Dashboard',
                    'href' => $r('admin.dashboard'),
                    'icon' => 'dashboard',
                    'active' => request()->routeIs('admin.dashboard'),
                ],
                [
                    'key' => 'boarding-houses',
                    'label' => 'Boarding Houses',
                    'href' => $r('admin.boarding-houses.index', [], 'admin.boarding-houses'),
                    'icon' => 'boarding-house',
                    'active' => $isPath('admin/boarding-houses*'),
                ],
            ],
        ],
        [
            'label' => 'MANAGEMENT',
            'items' => [
                [
                    'key' => 'reservations',
                    'label' => 'Reservations',
                    'href' => $r('admin.reservations.index', [], 'admin.reservations'),
                    'icon' => 'reservations',
                    'active' => $isPath('admin/reservations*'),
                ],
                [
                    'key' => 'tenants',
                    'label' => 'Tenants',
                    'href' => $r('admin.tenants.index', [], 'admin.tenant-profiles'),
                    'icon' => 'tenants',
                    'active' => $isPath('admin/tenants*', 'admin/tenant-profiles*'),
                ],
                [
                    'key' => 'inquiries',
                    'label' => 'Inquiries',
                    'href' => $r('admin.inquiries', [], 'admin.inquiries.index'),
                    'icon' => 'inquiries',
                    'active' => $isPath('admin/inquiries*'),
                ],
                [
                    'key' => 'transactions',
                    'label' => 'Transactions',
                    'href' => $r('admin.transactions.index', [], 'admin.payments'),
                    'icon' => 'transactions',
                    'active' => $isPath('admin/transactions*'),
                ],
                [
                    'key' => 'messages',
                    'label' => 'Messages',
                    'href' => $r('admin.messages', [], 'admin.messages.index'),
                    'icon' => 'messages',
                    'active' => $isPath('admin/messages*'),
                ],
                [
                    'key' => 'notifications',
                    'label' => 'Notifications',
                    'href' => $r('admin.notifications.index', [], 'admin.notifications'),
                    'icon' => 'notifications',
                    'active' => $isPath('admin/notifications*'),
                    'badge' => $unreadNotificationsCount,
                ],
                [
                    'key' => 'reports',
                    'label' => 'Reports',
                    'href' => $r('admin.reports.index', [], 'admin.reports'),
                    'icon' => 'reports',
                    'active' => $isPath('admin/reports*'),
                ],
            ],
        ],
        [
            'label' => 'ACCOUNT',
            'items' => [
                [
                    'key' => 'settings',
                    'label' => 'Settings',
                    'href' => $r('admin.settings.index', [], 'admin.settings'),
                    'icon' => 'settings',
                    'active' => $isPath('admin/settings*'),
                ],
            ],
        ],
    ];
@endphp

<nav class="sidebar-nav admin-sidebar-nav flex-1 space-y-4 pr-1 text-sm" aria-label="Admin navigation">
    @foreach ($sections as $section)
        <section class="space-y-1.5">
            <p class="sidebar-group px-2.5 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">{{ $section['label'] }}</p>
            <div class="space-y-1">
                @foreach ($section['items'] as $menu)
                    @php($isActive = (bool) ($menu['active'] ?? false))
                    <a
                        href="{{ $menu['href'] }}"
                        class="{{ $isActive ? $navActive : $navInactive }}"
                        data-sidebar-key="{{ $menu['key'] }}"
                        title="{{ $menu['label'] }}"
                        @if($isActive) aria-current="page" @endif
                    >
                        <span class="sidebar-icon flex h-5 w-5 shrink-0 items-center justify-center">
                            @include('components.sidebar.partials.admin-icon', ['name' => $menu['icon']])
                        </span>
                        <span class="sidebar-text min-w-0 flex-1 truncate">{{ $menu['label'] }}</span>
                        @if ((int) ($menu['badge'] ?? 0) > 0)
                            <span class="sidebar-badge inline-flex min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">
                                {{ (int) $menu['badge'] > 99 ? '99+' : (int) $menu['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach
</nav>
