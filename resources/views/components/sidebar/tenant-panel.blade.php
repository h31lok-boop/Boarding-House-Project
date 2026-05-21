@props([
    'messageCount' => 0,
    'notificationCount' => 0,
])

@php
    $r = fn ($name, $params = [], $fallback = null) => \Illuminate\Support\Facades\Route::has($name)
        ? route($name, $params)
        : ($fallback ?? url()->current());

    $user = auth()->user();
    $displayName = filled($user?->name) ? $user->name : 'Juan Student';
    $parts = preg_split('/\s+/', trim($displayName));
    $initials = strtoupper(substr($parts[0] ?? 'J', 0, 1).substr($parts[1] ?? 'S', 0, 1));
    $section = (string) request('section');
    $panel = (string) request('panel');
    $resolvedMessageCount = max((int) $messageCount, 2);
    $resolvedNotificationCount = max((int) $notificationCount, 3);

    $navBase = 'group flex items-center gap-3 rounded-xl border px-3 py-1.5 text-sm font-semibold transition-all duration-200';
    $navActive = $navBase.' border-emerald-400/30 bg-emerald-500 text-white shadow-lg shadow-emerald-950/20';
    $navInactive = $navBase.' border-transparent text-white/90 hover:border-white/10 hover:bg-white/10 hover:text-white';

    $links = [
        [
            'label' => 'Dashboard',
            'href' => $r('user.dashboard', [], $r('tenant.dashboard')),
            'active' => (request()->routeIs('user.dashboard') || request()->routeIs('tenant.dashboard')) && $panel === '' && ! in_array($section, ['bookings', 'messages'], true),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 11.5 12 4l8 7.5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M7 10v10h10V10"/></svg>',
        ],
        [
            'label' => 'Browse Listings',
            'href' => $r('user.browse-listings', [], $r('user.boarding-houses.index')),
            'active' => request()->routeIs('user.browse-listings') || request()->routeIs('tenant.boarding-houses') || request()->routeIs('user.boarding-houses.*'),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="6.5" stroke-width="1.7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m16 16 4.5 4.5"/></svg>',
        ],
        [
            'label' => 'My Applications',
            'href' => $r('user.applications', [], $r('tenant.applications', [], $r('user.dashboard', ['panel' => 'application-management']).'#application-management-panel')),
            'active' => request()->routeIs('user.applications') || request()->routeIs('tenant.applications') || ((request()->routeIs('user.dashboard') || request()->routeIs('tenant.dashboard')) && $panel === 'application-management'),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M7 4.5h8l3 3V19a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-13a1.5 1.5 0 0 1 1-1.5Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 4.5V8h3"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 12h6M9 16h4"/></svg>',
        ],
        [
            'label' => 'My Reservations',
            'href' => $r('user.reservations', [], $r('tenant.reservations', [], $r('user.dashboard', ['section' => 'bookings']).'#reservation-management-panel')),
            'active' => request()->routeIs('user.reservations') || request()->routeIs('user.bookings') || request()->routeIs('tenant.reservations') || ((request()->routeIs('user.dashboard') || request()->routeIs('tenant.dashboard')) && $panel === 'reservation-management') || $section === 'bookings',
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 3v4m8-4v4M7.5 11h9M8 15h4"/></svg>',
        ],
        [
            'label' => 'Saved Listings',
            'href' => $r('user.favorites.index', [], $r('tenant.saved-listings')),
            'active' => request()->routeIs('tenant.saved-listings') || request()->routeIs('user.favorites.*') || ((request()->routeIs('user.dashboard') || request()->routeIs('tenant.dashboard')) && $panel === 'saved-listings'),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m12 20-1.8-1.7C5.8 14.3 3 11.7 3 8.5A4.5 4.5 0 0 1 7.5 4c1.7 0 3.3.8 4.5 2.1A6 6 0 0 1 16.5 4 4.5 4.5 0 0 1 21 8.5c0 3.2-2.8 5.8-7.2 9.8L12 20Z"/></svg>',
        ],
        [
            'label' => 'Messages',
            'href' => $r('user.messages', [], $r('tenant.messages', [], $r('user.dashboard', ['panel' => 'messages-communication']).'#messages-communication-panel')),
            'active' => request()->routeIs('user.messages') || request()->routeIs('tenant.messages') || ((request()->routeIs('user.dashboard') || request()->routeIs('tenant.dashboard')) && $panel === 'messages-communication') || $section === 'messages',
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 6.5A2.5 2.5 0 0 1 7.5 4h9A2.5 2.5 0 0 1 19 6.5v6A2.5 2.5 0 0 1 16.5 15H9l-4 3V6.5Z"/></svg>',
            'badge' => $resolvedMessageCount,
        ],
        [
            'label' => 'Notifications',
            'href' => $r('user.notifications', [], $r('tenant.notifications', [], $r('user.dashboard', ['panel' => 'notifications']).'#notifications-panel')),
            'active' => request()->routeIs('user.notifications') || request()->routeIs('tenant.notifications') || ((request()->routeIs('user.dashboard') || request()->routeIs('tenant.dashboard')) && $panel === 'notifications'),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 17h5l-1.5-1.5A2 2 0 0 1 18 14.1V11a6 6 0 1 0-12 0v3.1a2 2 0 0 1-.5 1.4L4 17h5m6 0a3 3 0 1 1-6 0m6 0H9"/></svg>',
            'badge' => $resolvedNotificationCount,
        ],
        [
            'label' => 'Reviews',
            'href' => $r('user.reviews', [], $r('tenant.reviews', [], $r('user.dashboard', ['panel' => 'feedback-reviews']).'#feedback-reviews-panel')),
            'active' => request()->routeIs('user.reviews') || request()->routeIs('tenant.reviews') || ((request()->routeIs('user.dashboard') || request()->routeIs('tenant.dashboard')) && $panel === 'feedback-reviews'),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m12 17.3-5 2.6 1-5.6-4.1-4 5.7-.8L12 4.3l2.4 5.2 5.7.8-4.1 4 1 5.6-5-2.6Z"/></svg>',
        ],
        [
            'label' => 'Profile',
            'href' => $r('profile.edit'),
            'active' => request()->routeIs('profile.edit'),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="8" r="4" stroke-width="1.7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 20a7 7 0 0 1 14 0"/></svg>',
        ],
        [
            'label' => 'Settings',
            'href' => $r('user.settings', [], $r('tenant.settings', [], $r('user.dashboard'))),
            'active' => request()->routeIs('user.settings') || request()->routeIs('tenant.settings'),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10.4 4h3.2l.6 2.1a6.7 6.7 0 0 1 1.5.6l1.9-1 2.3 2.3-1 1.9c.3.5.5 1 .6 1.5l2.1.6v3.2l-2.1.6a6.7 6.7 0 0 1-.6 1.5l1 1.9-2.3 2.3-1.9-1a6.7 6.7 0 0 1-1.5.6l-.6 2.1h-3.2l-.6-2.1a6.7 6.7 0 0 1-1.5-.6l-1.9 1-2.3-2.3 1-1.9a6.7 6.7 0 0 1-.6-1.5l-2.1-.6V12l2.1-.6c.1-.5.3-1 .6-1.5l-1-1.9 2.3-2.3 1.9 1c.5-.3 1-.5 1.5-.6L10.4 4Z"/><circle cx="12" cy="13.6" r="2.8" stroke-width="1.7"/></svg>',
        ],
    ];
@endphp

<nav class="sidebar-nav min-h-0 flex-1 overflow-y-auto py-1">
    <p class="mb-2 px-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-white/50 sidebar-group">User Workspace</p>
    <div class="space-y-0.5">
        @foreach ($links as $link)
            <a href="{{ $link['href'] }}" @click="sidebarOpen = false" class="{{ $link['active'] ? $navActive : $navInactive }}">
                <span class="sidebar-icon flex h-5 w-5 shrink-0 items-center justify-center text-current">{!! $link['icon'] !!}</span>
                <span class="sidebar-text min-w-0 flex-1 truncate">{{ $link['label'] }}</span>
                @if (! empty($link['badge']))
                    <span class="sidebar-text inline-flex min-w-[1.75rem] shrink-0 items-center justify-center rounded-full bg-red-600 px-2 py-0.5 text-[10px] font-bold text-white">
                        {{ $link['badge'] > 99 ? '99+' : $link['badge'] }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>
</nav>

<div class="sidebar-footer mt-2 shrink-0 pt-2" x-data="{ tenantProfileOpen: false }">
    <div class="relative">
        <button
            type="button"
            class="flex w-full items-center gap-2.5 rounded-2xl border border-white/10 bg-white/10 p-2 text-left text-white shadow-sm transition hover:bg-white/15"
            @click="tenantProfileOpen = ! tenantProfileOpen"
        >
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-xs font-extrabold text-white">{{ $initials ?: 'JS' }}</span>
            <span class="sidebar-text min-w-0 flex-1">
                <span class="block truncate text-sm font-bold">{{ $displayName }}</span>
                <span class="block text-xs font-medium text-white/65">User</span>
            </span>
            <svg class="sidebar-text h-4 w-4 shrink-0 text-white/65" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
            </svg>
        </button>

        <div
            x-show="tenantProfileOpen"
            x-transition
            @click.outside="tenantProfileOpen = false"
            class="absolute bottom-full left-0 z-50 mb-3 w-full overflow-hidden rounded-2xl border border-white/10 bg-slate-950/95 py-2 text-sm text-white shadow-xl"
            style="display: none;"
        >
            <a href="{{ $r('profile.edit') }}" class="block px-4 py-2.5 font-medium hover:bg-white/10">View Profile</a>
            <a href="{{ $r('user.settings', [], $r('tenant.settings', [], $r('user.dashboard'))) }}" class="block px-4 py-2.5 font-medium hover:bg-white/10">Settings</a>
            <form method="POST" action="{{ route('logout') }}" class="border-t border-white/10 pt-1">
                @csrf
                <button type="submit" class="block w-full px-4 py-2.5 text-left font-medium text-rose-200 hover:bg-rose-500/10">Logout</button>
            </form>
        </div>
    </div>
</div>
