@props([
    'searchPlaceholder' => 'Search boarding houses, rooms, or locations...',
    'messageCount' => 0,
    'notificationCount' => 0,
    'showHeader' => null,
])

@php
    $r = fn ($name, $params = [], $fallback = null) => \Illuminate\Support\Facades\Route::has($name)
        ? route($name, $params)
        : ($fallback ?? url()->current());

    $user = auth()->user();
    $displayName = filled($user?->name) ? $user->name : 'Juan Student';
    $roleName = 'User';
    $parts = preg_split('/\s+/', trim($displayName));
    $initials = strtoupper(substr($parts[0] ?? 'J', 0, 1).substr($parts[1] ?? 'S', 0, 1));
    $resolvedMessageCount = max((int) $messageCount, 2);
    $browseUrl = $r('user.browse-listings', [], $r('user.boarding-houses.index', [], $r('user.dashboard')));
    $messagesUrl = $r('user.messages', [], $r('user.dashboard', ['panel' => 'messages-communication'])).'#messages-communication-panel';
    $notificationsUrl = $r('user.notifications', [], $r('user.dashboard', ['panel' => 'notifications'])).'#notifications-panel';
    $settingsUrl = $r('user.settings', [], $r('user.dashboard'));
    $showHeader = is_null($showHeader) ? (request()->routeIs('user.dashboard') || request()->routeIs('tenant.dashboard')) : (bool) $showHeader;
@endphp

<div
    x-data="{
        sidebarOpen: false,
        profileOpen: false,
        notificationsOpen: false,
        notifications: [
            { id: 1, title: 'Your application for MetroNest Boarding Hub is pending review.', type: 'Application', time: '1 hour ago', unread: true },
            { id: 2, title: 'Casa Digos Boarding Stay approved your application.', type: 'Reservation', time: '1 day ago', unread: true },
            { id: 3, title: 'Complete your user profile to improve recommendations.', type: 'Profile', time: '2 days ago', unread: true }
        ],
        get unreadNotifications() {
            return this.notifications.filter((notification) => notification.unread).length;
        },
        markNotificationRead(id) {
            const notification = this.notifications.find((item) => item.id === id);
            if (notification) {
                notification.unread = false;
            }
        },
        markAllNotificationsRead() {
            this.notifications = this.notifications.map((notification) => ({ ...notification, unread: false }));
        }
    }"
    @keydown.escape.window="sidebarOpen = false; profileOpen = false; notificationsOpen = false"
    class="tenant-workspace flex h-screen w-full min-w-0 items-stretch overflow-hidden"
>
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-slate-950/55 lg:hidden"
        style="display: none;"
        @click="sidebarOpen = false"
    ></div>

    <aside
        class="tenant-sidebar fixed inset-y-0 left-0 z-50 flex h-screen w-[256px] max-w-[88vw] shrink-0 flex-col overflow-hidden border-r border-white/10 px-4 py-3 pb-3 text-white shadow-2xl transition-transform duration-200 lg:sticky lg:top-0 lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        <div class="sidebar-header">
            <x-sidebar.brand title="DSSC BOARDING" subtitle="HOUSE SYSTEM" />
            <button
                type="button"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/15 bg-white/10 text-white transition hover:bg-white/15 lg:hidden"
                @click="sidebarOpen = false"
                aria-label="Close sidebar"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                </svg>
            </button>
        </div>

        <x-sidebar.tenant-panel
            :message-count="$resolvedMessageCount"
            :notification-count="$notificationCount"
        />
    </aside>

    <div class="flex h-screen min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
        @if ($showHeader)
            <header class="shrink-0 px-4 py-4 sm:px-5 sm:py-5 lg:px-8 lg:py-6 lg:pb-0">
                <div class="tenant-card p-3 sm:p-4">
                    @isset($header)
                        {{ $header }}
                    @else
                        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <button
                                    type="button"
                                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50 lg:hidden"
                                    @click="sidebarOpen = true"
                                    aria-label="Open sidebar"
                                >
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                                    </svg>
                                </button>

                                <form method="GET" action="{{ $browseUrl }}" class="tenant-search flex min-w-0 flex-1 items-center gap-3">
                                    <span class="shrink-0 text-slate-400">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <circle cx="11" cy="11" r="7" stroke-width="1.8" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m20 20-3.5-3.5" />
                                        </svg>
                                    </span>
                                    <input
                                        type="search"
                                        name="q"
                                        value="{{ request('q') }}"
                                        placeholder="{{ $searchPlaceholder }}"
                                        class="min-w-0 flex-1 border-0 bg-transparent text-sm font-medium text-slate-800 outline-none placeholder:text-slate-400 focus:ring-0"
                                    >
                                </form>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                <x-theme-toggle class="dashboard-theme-button" />

                                <button
                                    type="button"
                                    class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50"
                                    @click="notificationsOpen = true"
                                    aria-label="Notifications"
                                >
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17a3 3 0 0 0 6 0" />
                                    </svg>
                                    <span
                                        x-show="unreadNotifications > 0"
                                        x-text="unreadNotifications"
                                        class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white"
                                    >3</span>
                                </button>

                                <a
                                    href="{{ $messagesUrl }}"
                                    class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50"
                                    aria-label="Messages"
                                >
                                    <svg class="h-[19px] w-[19px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M20 14.5A3.5 3.5 0 0 1 16.5 18H8l-4 3V7.5A3.5 3.5 0 0 1 7.5 4h9A3.5 3.5 0 0 1 20 7.5v7Z" />
                                        <path stroke-linecap="round" stroke-width="1.9" d="M8 9h8M8 13h5" />
                                    </svg>
                                    <span class="absolute -right-1.5 -top-1.5 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold leading-none text-white ring-2 ring-white">{{ $resolvedMessageCount }}</span>
                                </a>

                                <div class="relative">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-left shadow-sm transition hover:bg-slate-50"
                                        @click="profileOpen = ! profileOpen"
                                        aria-label="User profile menu"
                                    >
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">{{ $initials ?: 'JS' }}</span>
                                        <span class="hidden min-w-0 leading-tight sm:block">
                                            <span class="block max-w-[150px] truncate text-sm font-semibold text-slate-950">{{ $displayName }}</span>
                                            <span class="block text-xs font-medium text-slate-500">{{ $roleName }}</span>
                                        </span>
                                        <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
                                        </svg>
                                    </button>

                                    <div
                                        x-show="profileOpen"
                                        x-transition
                                        @click.outside="profileOpen = false"
                                        class="absolute right-0 z-50 mt-3 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white py-2 text-sm shadow-xl"
                                        style="display: none;"
                                    >
                                        <a href="{{ $r('profile.edit') }}" class="block px-4 py-2.5 font-medium text-slate-700 hover:bg-slate-50">View Profile</a>
                                        <a href="{{ $settingsUrl }}" class="block px-4 py-2.5 font-medium text-slate-700 hover:bg-slate-50">Settings</a>
                                        <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 pt-1">
                                            @csrf
                                            <button type="submit" class="block w-full px-4 py-2.5 text-left font-medium text-rose-600 hover:bg-rose-50">Logout</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endisset
                </div>
            </header>
        @else
            <div class="shrink-0 px-4 pt-4 sm:px-5 sm:pt-5 lg:hidden">
                <button
                    type="button"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50"
                    @click="sidebarOpen = true"
                    aria-label="Open sidebar"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>
            </div>
        @endif

        <main class="tenant-scroll-area min-h-0 min-w-0 flex-1 overflow-y-auto overflow-x-hidden px-4 pb-4 {{ $showHeader ? 'sm:px-5 sm:pb-5 lg:px-8 lg:py-6' : 'pt-4 sm:px-5 sm:py-5 lg:px-8 lg:py-6' }}">
            <div class="w-full min-w-0 space-y-6">
            {{ $slot }}
            </div>
        </main>
    </div>

    <div
        x-show="notificationsOpen"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4"
        style="display: none;"
        role="dialog"
        aria-modal="true"
        aria-labelledby="tenant-notifications-title"
    >
        <section
            x-transition.scale
            @click.outside="notificationsOpen = false"
            class="tenant-card max-h-[90vh] w-full max-w-lg overflow-hidden"
        >
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 id="tenant-notifications-title" class="text-lg font-bold text-slate-950">Notifications</h2>
                    <p class="tenant-muted text-sm">Unread updates from applications, reservations, and support.</p>
                </div>
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100" @click="notificationsOpen = false" aria-label="Close notifications">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <div class="max-h-[56vh] overflow-y-auto p-4">
                <template x-for="notification in notifications" :key="notification.id">
                    <button
                        type="button"
                        class="mb-3 flex w-full gap-3 rounded-2xl border border-slate-200 p-4 text-left transition hover:border-blue-200 hover:bg-blue-50/60"
                        :class="notification.unread ? 'bg-white' : 'bg-slate-50'"
                        @click="markNotificationRead(notification.id)"
                    >
                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full" :class="notification.unread ? 'bg-red-600' : 'bg-slate-300'"></span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold text-slate-900" x-text="notification.title"></span>
                            <span class="mt-2 flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500">
                                <span x-text="notification.type"></span>
                                <span aria-hidden="true">/</span>
                                <span x-text="notification.time"></span>
                                <span x-show="! notification.unread" class="rounded-full bg-slate-200 px-2 py-0.5 text-slate-600">Read</span>
                            </span>
                        </span>
                    </button>
                </template>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-5 py-4">
                <a href="{{ $notificationsUrl }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">View all notifications</a>
                <button type="button" class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800" @click="markAllNotificationsRead()">Mark all as read</button>
            </div>
        </section>
    </div>
</div>
