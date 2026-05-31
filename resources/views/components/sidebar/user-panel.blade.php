@php
    $navBase = 'flex items-center gap-4 px-3 py-3 rounded-lg transition-colors';
    $navActive = $navBase . ' bg-[#fff3e8] text-[#111827] font-semibold border border-[#ffd9b8]';
    $navInactive = $navBase . ' text-[#1f2937] hover:bg-[#f8fafc]';

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

<nav class="flex-1 space-y-2 text-[15px] sidebar-nav" aria-label="Tenant navigation">
    <p class="text-[13px] uppercase text-[#344154] font-semibold mb-6 sidebar-group">Main Navigation</p>

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

<div class="sidebar-help mt-4 rounded-lg bg-gradient-to-br from-[#f7f4ff] to-[#fffafb] p-5 text-sm">
    <div class="flex items-start gap-3">
        <span class="sidebar-icon mt-0.5 text-[#6c4cff]">
            @include('components.sidebar.partials.admin-icon', ['name' => 'support'])
        </span>
        <div class="min-w-0">
            <p class="font-semibold text-[#8a73ff]">Need Help?</p>
            <p class="mt-4 text-xs text-[#64748b]">Contact our support team</p>
            <a href="{{ route('user.messages') }}" class="mt-4 inline-flex rounded-lg bg-[#5f35e8] px-4 py-3 text-xs font-semibold text-white hover:bg-[#4e29c8]">Go to Help Center</a>
        </div>
    </div>
</div>

<p class="text-xs leading-6 text-[#697386] mt-5 sidebar-footer">&copy; 2026 BoardMatch.<br>All rights reserved.</p>
