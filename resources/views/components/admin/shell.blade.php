@php
    $r = fn($name, $params = []) => \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : url()->current();
    $currentUser = auth()->user();
    $profileImage = $currentUser?->profile_photo ?: $currentUser?->profile_image;
    $accountImageUrl = $profileImage
        ? (\Illuminate\Support\Str::startsWith($profileImage, ['http://', 'https://', '/'])
            ? $profileImage
            : \Illuminate\Support\Facades\Storage::url($profileImage))
        : null;
    $adminName = $currentUser?->name ?: 'Admin';
    $adminInitial = strtoupper(substr($adminName, 0, 1));
    $adminRole = ucfirst($currentUser?->role ?: 'Admin');
    $unreadNotificationsCount = 0;

    if ($currentUser && \Illuminate\Support\Facades\Schema::hasTable('notifications')) {
        $notificationsQuery = \Illuminate\Support\Facades\DB::table('notifications')->where('user_id', $currentUser->id);

        if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'is_read')) {
            $notificationsQuery->where('is_read', false);
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'read_at')) {
            $notificationsQuery->whereNull('read_at');
        }

        $unreadNotificationsCount = (int) $notificationsQuery->count();
    }

    $pageLabel = match (true) {
        request()->routeIs('admin.dashboard') => 'Overview',
        request()->is('admin/boarding-houses*', 'admin/listings*', 'admin/my-boarding-house*') => 'Boarding Houses',
        request()->is('admin/reservations*') => 'Reservations',
        request()->is('admin/tenants*', 'admin/tenant-profiles*') => 'Tenants',
        request()->is('admin/inquiries*') => 'Inquiries',
        request()->is('admin/transactions*', 'admin/payments*') => 'Transactions',
        request()->is('admin/messages*') => 'Messages',
        request()->is('admin/notifications*') => 'Notifications',
        request()->is('admin/reports*') => 'Reports',
        request()->is('admin/settings*') => 'Settings',
        request()->is('admin/search*') => 'Search',
        default => 'Admin',
    };

@endphp

<div
    x-data="{ logoutConfirm: false, adminProfileOpen: false }"
    @keydown.escape.window="logoutConfirm = false; adminProfileOpen = false"
    class="admin-shell w-full bg-[#f7f8fb]"
>
    <div class="sidebar-overlay" data-sidebar-overlay aria-hidden="true"></div>

    <aside
        id="adminSidebar"
        class="sidebar admin-sidebar fixed inset-y-0 left-0 z-50 h-screen w-[240px] shrink-0 overflow-hidden border-r border-white/10 bg-[linear-gradient(180deg,#0F172A_0%,#111827_48%,#0B1224_100%)] px-3 py-4 shadow-2xl shadow-slate-950/30 flex flex-col"
        aria-label="Admin sidebar"
    >
        <div class="sidebar-header">
            <a href="{{ $r('admin.dashboard') }}" class="admin-sidebar-brand flex min-w-0 flex-1 items-center gap-2.5">
                <div class="sidebar-brand-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-[0_10px_22px_rgba(37,99,235,0.3)] ring-1 ring-white/15">
                    @include('components.sidebar.partials.admin-icon', ['name' => 'boarding-house'])
                </div>
                <div class="sidebar-brand-text min-w-0 leading-tight">
                    <p class="truncate text-lg font-bold tracking-tight text-white">BoardMatch</p>
                    <p class="truncate text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">Admin panel</p>
                </div>
            </a>
            <button
                type="button"
                class="h-9 w-9 rounded-lg border border-white/10 bg-white/5 text-slate-200 shadow-sm transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-400/70 flex items-center justify-center"
                data-sidebar-toggle
                aria-controls="adminSidebar"
                aria-expanded="true"
                aria-label="Toggle sidebar"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <div id="sidebarNav" class="flex min-h-0 flex-1 overflow-y-auto">
            <x-sidebar.admin-panel />
        </div>

        <p class="sidebar-footer mt-4 border-t border-white/10 pt-3 text-center text-[11px] leading-4 text-slate-500">&copy; {{ date('Y') }} BoardMatch<br>All rights reserved.</p>
    </aside>

    <main class="admin-dashboard-main min-w-0 bg-[#f7f8fb]">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 2xl:px-8 py-6 space-y-6">
            <div class="md:hidden">
                <button
                    type="button"
                    class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-400/70"
                    data-sidebar-toggle
                    aria-controls="adminSidebar"
                    aria-expanded="false"
                    aria-label="Open sidebar"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span>Menu</span>
                </button>
            </div>

            @if (request()->routeIs('admin.dashboard'))
                <header class="ui-card rounded-2xl px-4 py-3 shadow-sm">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <nav class="flex items-center gap-2 text-sm font-semibold text-slate-500" aria-label="Breadcrumb">
                            <a href="{{ $r('admin.dashboard') }}" class="text-slate-700 transition hover:text-blue-700">Dashboard</a>
                            <svg class="h-4 w-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9 5 7 7-7 7"/>
                            </svg>
                            <span class="text-blue-700">{{ $pageLabel }}</span>
                        </nav>

                        <form method="GET" action="{{ $r('admin.search') }}" class="min-w-0 flex-1 xl:max-w-2xl">
                            <label class="relative block">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                                    </svg>
                                </span>
                                <input
                                    name="query"
                                    value="{{ request('query') }}"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                    placeholder="Search boarding houses, tenants, reservations, payments..."
                                >
                            </label>
                        </form>

                        <div class="flex items-center justify-between gap-3 xl:justify-end">
                            <a href="{{ $r('admin.notifications.index') }}" class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" aria-label="Notifications">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17H9m0 0-3-9a6 6 0 1 1 12 0l-3 9m-6 0v1a3 3 0 0 0 6 0v-1"/>
                                </svg>
                                @if ($unreadNotificationsCount > 0)
                                    <span class="absolute -right-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">
                                        {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                                    </span>
                                @endif
                            </a>

                            <div class="relative">
                                <button
                                    type="button"
                                    @click="adminProfileOpen = !adminProfileOpen"
                                    class="flex min-w-0 items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-left shadow-sm transition hover:border-blue-200 hover:bg-blue-50"
                                    aria-haspopup="menu"
                                    :aria-expanded="adminProfileOpen"
                                >
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-600 text-sm font-bold text-white">
                                        @if ($accountImageUrl)
                                            <img src="{{ $accountImageUrl }}" alt="{{ $adminName }}" class="h-full w-full object-cover">
                                        @else
                                            {{ $adminInitial }}
                                        @endif
                                    </span>
                                    <span class="hidden min-w-0 leading-tight sm:block">
                                        <span class="block truncate text-sm font-bold text-slate-900">{{ $adminName }}</span>
                                        <span class="block truncate text-xs font-semibold text-slate-500">{{ $adminRole }}</span>
                                    </span>
                                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition" :class="adminProfileOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div
                                    x-cloak
                                    x-show="adminProfileOpen"
                                    x-transition
                                    @click.outside="adminProfileOpen = false"
                                    class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/12"
                                    role="menu"
                                >
                                    <div class="border-b border-slate-100 px-4 py-3">
                                        <p class="truncate text-sm font-bold text-slate-900">{{ $adminName }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ $currentUser?->email }}</p>
                                    </div>
                                    <div class="p-1.5 text-sm">
                                        <a href="{{ $r('admin.settings.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700" role="menuitem">Profile Settings</a>
                                        <a href="{{ $r('admin.settings.index', ['tab' => 'security']) }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700" role="menuitem">Account Settings</a>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button class="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-left font-semibold text-rose-600 transition hover:bg-rose-50" role="menuitem">Logout</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
            @endif

            <x-toast />

            {{ $slot }}
        </div>
    </main>

    <div
        data-modal-root
        role="dialog"
        aria-modal="true"
        x-show="logoutConfirm"
        x-cloak
        x-transition
        @click.self="logoutConfirm = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
    >
        <div class="ui-card w-full max-w-sm p-6 text-center shadow-xl">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4M14 16l4-4-4-4M18 12H9"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900">Confirm Logout</h3>
            <p class="mt-2 text-sm leading-6 text-slate-500">Are you sure you want to log out of the admin panel?</p>
            <div class="mt-5 flex justify-center gap-2">
                <button type="button" @click="logoutConfirm = false" class="btn-secondary">Cancel</button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-danger">Log out</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var nav = document.getElementById('sidebarNav');
    if (!nav) return;

    var key = 'bm_admin_sidebar_scroll';
    var saved = sessionStorage.getItem(key);
    if (saved !== null) {
        nav.scrollTop = parseInt(saved, 10) || 0;
    }

    window.addEventListener('beforeunload', function () {
        sessionStorage.setItem(key, nav.scrollTop);
    });

    nav.addEventListener('click', function (e) {
        var link = e.target.closest('a[href]');
        if (link) {
            sessionStorage.setItem(key, nav.scrollTop);
        }
    });
})();
</script>
