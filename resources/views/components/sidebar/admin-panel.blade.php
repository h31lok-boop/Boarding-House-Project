@php
    $sidebarUser = Auth::user();
    $isOwnerWorkspace = request()->routeIs('owner.*') && $sidebarUser?->isStrictOwner();
    $workspace = $isOwnerWorkspace ? 'owner' : 'admin';
    $workspacePath = $isOwnerWorkspace ? 'owner' : 'admin';
    $routeName = fn (string $suffix) => $workspace.'.'.$suffix;

    $r = function ($name, $params = [], $fallback = null) use ($isOwnerWorkspace) {
        if ($isOwnerWorkspace && str_starts_with($name, 'admin.')) {
            $ownerName = 'owner.'.substr($name, 6);
            if (\Illuminate\Support\Facades\Route::has($ownerName)) {
                return route($ownerName, $params);
            }
        }

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

    $isSuperAdmin = $sidebarUser?->isSuperAdmin() ?? false;
    $sidebarUserIsOwner = $sidebarUser?->isStrictOwner() ?? false;

    $navBase = 'group/sidebar-item relative flex items-center gap-2 rounded-lg px-2 py-1.5 text-[12px] font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400/70';
    $navActive = $navBase . ' bg-[#2563EB] text-white shadow-[0_10px_22px_rgba(37,99,235,0.3)]';
    $navInactive = $navBase . ' text-[#CBD5E1] hover:bg-white/10 hover:text-white';

    $sections = [
        [
            'label' => 'MAIN',
            'items' => [
                [
                    'key' => 'dashboard',
                    'label' => 'Dashboard',
                    'href' => $r($routeName('dashboard')),
                    'icon' => 'dashboard',
                    'active' => request()->routeIs($routeName('dashboard')),
                ],
                [
                    'key' => 'boarding-houses',
                    'label' => $isSuperAdmin ? 'Boarding Houses' : 'My Properties',
                    'href' => $isSuperAdmin
                        ? $r('admin.boarding-houses')
                        : $r('owner.boarding-houses', [], 'owner.my-boarding-house'),
                    'icon' => 'boarding-house',
                    'active' => $isPath($workspacePath.'/boarding-houses*') || $isPath($workspacePath.'/my-boarding-house*'),
                ],
                [
                    'key' => 'reservations',
                    'label' => 'Reservations',
                    'href' => $r('admin.reservations.index', [], 'admin.reservations'),
                    'icon' => 'reservations',
                    'active' => $isPath($workspacePath.'/reservations*'),
                ],
                [
                    'key' => 'tenants',
                    'label' => 'Tenants',
                    'href' => $r('admin.tenants.index', [], 'admin.tenant-profiles'),
                    'icon' => 'tenants',
                    'active' => $isPath($workspacePath.'/tenants*', $workspacePath.'/tenant-profiles*'),
                ],
                [
                    'key' => 'inquiries',
                    'label' => 'Inquiries',
                    'href' => $r('admin.inquiries.index', [], 'admin.inquiries'),
                    'icon' => 'inquiries',
                    'active' => $isPath($workspacePath.'/inquiries*'),
                ],
                [
                    'key' => 'payments',
                    'label' => 'Payments',
                    'href' => $r('admin.payments'),
                    'icon' => 'payments',
                    'active' => $isPath($workspacePath.'/payments*', $workspacePath.'/payment-verification*'),
                ],
                [
                    'key' => 'services',
                    'label' => 'Services',
                    'href' => $r($workspace.'.services.index'),
                    'icon' => 'sparkles',
                    'active' => $isPath($workspacePath.'/services*'),
                ],
                [
                    'key' => 'transactions',
                    'label' => 'Transactions',
                    'href' => $r('admin.transactions.index'),
                    'icon' => 'transactions',
                    'active' => $isPath($workspacePath.'/transactions*'),
                ],
                [
                    'key' => 'messages',
                    'label' => 'Messages',
                    'href' => $r('admin.messages', [], 'admin.messages.index'),
                    'icon' => 'messages',
                    'active' => $isPath($workspacePath.'/messages*'),
                ],
                [
                    'key' => 'reviews',
                    'label' => 'Feedback & Reviews',
                    'href' => $r($routeName('reviews')),
                    'icon' => 'reviews',
                    'active' => $isPath($workspacePath.'/reviews*'),
                ],
            ],
        ],
        [
            'label' => 'ACCOUNT',
            'items' => [
                [
                    'key' => 'notifications',
                    'label' => 'Notifications',
                    'href' => $r($routeName('notifications.index')),
                    'icon' => 'notifications',
                    'active' => $isPath($workspacePath.'/notifications*'),
                ],
                [
                    'key' => 'insights',
                    'label' => 'ML Insights',
                    'href' => $r($routeName('insights.index')),
                    'icon' => 'analytics',
                    'active' => $isPath($workspacePath.'/predictive-insights*'),
                ],
                [
                    'key' => 'reports',
                    'label' => 'Reports',
                    'href' => $r('admin.reports.index', [], 'admin.reports'),
                    'icon' => 'reports',
                    'active' => $isPath($workspacePath.'/reports*'),
                ],
                [
                    'key' => 'settings',
                    'label' => 'Settings',
                    'href' => $r('admin.settings.index', [], 'admin.settings'),
                    'icon' => 'settings',
                    'active' => $isPath($workspacePath.'/settings*'),
                ],
                [
                    'key' => 'payment-settings',
                    'label' => 'PayMongo Settings',
                    'href' => $r($workspace.'.payment-settings'),
                    'icon' => 'payments',
                    'active' => $isPath($workspacePath.'/payment-settings*'),
                ],
            ],
        ],
    ];

    // Owners manage a single property — hide system-wide admin-only sections.
    if ($sidebarUserIsOwner) {
        $ownerHidden = ['transactions', 'insights', 'reports'];
        $sections = collect($sections)
            ->map(function ($section) use ($ownerHidden) {
                $section['items'] = array_values(array_filter(
                    $section['items'],
                    fn ($item) => ! in_array($item['key'], $ownerHidden, true)
                ));

                return $section;
            })
            ->filter(fn ($section) => count($section['items']) > 0)
            ->values()
            ->all();
    }
@endphp

<nav class="sidebar-nav admin-sidebar-nav flex-1 space-y-3 pr-1 text-sm" aria-label="{{ $isOwnerWorkspace ? 'Owner navigation' : 'Admin navigation' }}">
    @foreach ($sections as $section)
        <section class="space-y-1">
            <p class="sidebar-group px-2 text-[9px] font-bold uppercase tracking-[0.16em] text-slate-500">{{ $section['label'] }}</p>
            <div class="space-y-0.5">
                @foreach ($section['items'] as $menu)
                    @php($isActive = (bool) ($menu['active'] ?? false))
                    <a
                        href="{{ $menu['href'] }}"
                        class="{{ $isActive ? $navActive : $navInactive }}"
                        data-sidebar-key="{{ $menu['key'] }}"
                        title="{{ $menu['label'] }}"
                        @if($isActive) aria-current="page" @endif
                    >
                        <span class="sidebar-icon flex h-4 w-4 shrink-0 items-center justify-center">
                            @include('components.sidebar.partials.admin-icon', ['name' => $menu['icon']])
                        </span>
                        <span class="sidebar-text min-w-0 flex-1 truncate">{{ $menu['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach
</nav>
