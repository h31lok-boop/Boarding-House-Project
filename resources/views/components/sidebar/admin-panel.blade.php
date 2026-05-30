@php
    $navBase     = 'flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors';
    $navActive   = $navBase . ' ui-surface-2 text-[color:var(--text)] font-medium border ui-border';
    $navInactive = $navBase . ' text-[color:var(--muted)] hover:bg-[color:var(--surface-2)]';

    $currentTab = request('tab', '');

    $menus = [
        [
            'label' => 'Dashboard',
            'icon'  => 'dashboard',
            'route' => 'admin.dashboard',
            'match' => ['admin.dashboard'],
        ],
        [
            'label' => 'Boarding Houses',
            'icon'  => 'boarding-house',
            'route' => 'admin.boarding-houses',
            'match' => ['admin.boarding-houses*', 'admin.listings*'],
        ],
        [
            'label' => 'Reservations',
            'icon'  => 'reservations',
            'route' => 'admin.reservations',
            'match' => ['admin.reservations*'],
        ],
        [
            'label' => 'Tenants',
            'icon'  => 'tenants',
            'route' => 'admin.tenant-profiles',
            'match' => ['admin.tenant-profiles*'],
        ],
        [
            'label' => 'Inquiries',
            'icon'  => 'inquiries',
            'route' => 'admin.inquiries',
            'match' => ['admin.inquiries*'],
        ],
        [
            'label'  => 'Transactions',
            'icon'   => 'transactions',
            'route'  => 'admin.payments',
            'params' => ['tab' => 'transactions'],
            'match'  => ['admin.payments*'],
        ],
        [
            'label' => 'Messages',
            'icon'  => 'messages',
            'route' => 'admin.messages',
            'match' => ['admin.messages*'],
        ],
        [
            'label' => 'Notifications',
            'icon'  => 'notifications',
            'route' => 'admin.notifications',
            'match' => ['admin.notifications*'],
        ],
        [
            'label' => 'Reports',
            'icon'  => 'reports',
            'route' => 'admin.reports',
            'match' => ['admin.reports*'],
        ],
        [
            'label' => 'Profile Settings',
            'icon'  => 'settings',
            'route' => 'admin.settings',
            'match' => ['admin.settings*'],
        ],
    ];
@endphp

<nav class="flex-1 space-y-1 py-3 px-2 text-sm sidebar-nav" aria-label="Admin navigation">
    <p class="text-xs uppercase ui-muted mb-2 px-1 sidebar-group">Main Navigation</p>

    @foreach ($menus as $menu)
        @php
            $isActive = request()->routeIs(...$menu['match']);
            $href = \Illuminate\Support\Facades\Route::has($menu['route'])
                ? route($menu['route'], $menu['params'] ?? [])
                : '#';
        @endphp
        <a href="{{ $href }}"
           class="{{ $isActive ? $navActive : $navInactive }}"
           title="{{ $menu['label'] }}">
            <span class="sidebar-icon h-4 w-4 shrink-0">
                @include('components.sidebar.partials.admin-icon', ['name' => $menu['icon']])
            </span>
            <span class="sidebar-text">{{ $menu['label'] }}</span>
        </a>
    @endforeach
</nav>

{{-- Help box — matches tenant sidebar style --}}
<div class="sidebar-help mx-2 mb-3 rounded-xl border ui-border bg-orange-50/70 p-4 text-sm shrink-0 dark:bg-orange-950/20">
    <div class="flex items-start gap-3">
        <span class="sidebar-icon h-4 w-4 shrink-0 text-orange-500 mt-0.5">
            @include('components.sidebar.partials.admin-icon', ['name' => 'support'])
        </span>
        <div class="min-w-0 sidebar-text">
            <p class="font-semibold text-orange-700 dark:text-orange-200">Need Help?</p>
            <p class="mt-1 text-xs ui-muted">Contact system support</p>
            <a href="{{ \Illuminate\Support\Facades\Route::has('admin.messages') ? route('admin.messages') : '#' }}"
               class="mt-3 inline-flex rounded-lg bg-orange-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-orange-600 transition-colors">
                Go to Messages
            </a>
        </div>
    </div>
</div>

<p class="text-xs ui-muted text-center pb-3 sidebar-footer">&copy; {{ date('Y') }} BoardMatch</p>
