@props([
    'showHeader' => true,
])

@php
    $currentUser = auth()->user();
    $isOwnerWorkspace = request()->routeIs('owner.*') && $currentUser?->isStrictOwner();
    $workspace = $isOwnerWorkspace ? 'owner' : 'admin';
    $workspacePath = $isOwnerWorkspace ? 'owner' : 'admin';
    $workspaceRoute = fn (string $suffix) => $workspace.'.'.$suffix;

    // The workspace header is intentionally persistent on every admin/owner
    // route. Page-level headings remain inside the slot below this app bar.
    $showHeader = true;
    $workspaceHeaderTitle = $isOwnerWorkspace ? 'BoardMatch Workspace' : 'BoardMatch Admin';
    $workspaceHeaderDescription = $isOwnerWorkspace
        ? 'Manage your properties, tenants, reservations, and revenue in one place.'
        : 'Manage platform operations, users, properties, payments, and activity in one place.';

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

        if ($fallback && \Illuminate\Support\Facades\Route::has($fallback)) {
            return route($fallback, $params);
        }

        return url()->current();
    };
    $profileImage = $currentUser?->profile_photo ?: $currentUser?->profile_image;
    $accountImageUrl = $profileImage
        ? (\Illuminate\Support\Str::startsWith($profileImage, ['http://', 'https://', '/'])
            ? $profileImage
            : \Illuminate\Support\Facades\Storage::url($profileImage))
        : null;

    $ownerName = $currentUser?->name ?: 'Property Owner';
    $ownerInitial = strtoupper(substr($ownerName, 0, 1));
    $ownerRole = match (strtolower((string) $currentUser?->role)) {
        'owner' => 'Property Owner',
        'admin' => 'System Administrator',
        default => 'Owner Workspace',
    };

    $notificationsCount = 0;
    $messageCount = 0;
    $pendingReservationCount = 0;

    if (\Illuminate\Support\Facades\Schema::hasTable('notifications') && $currentUser) {
        $notificationQuery = \Illuminate\Support\Facades\DB::table('notifications')->where('user_id', $currentUser->id);

        if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'read_at')) {
            $notificationQuery->whereNull('read_at');
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'is_read')) {
            $notificationQuery->where('is_read', false);
        }

        $notificationsCount = (int) $notificationQuery->count();
    }

    if (\Illuminate\Support\Facades\Schema::hasTable('inquiries') && $currentUser) {
        $messageQuery = \Illuminate\Support\Facades\DB::table('inquiries')
            ->where(function ($query) {
                $query->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['new', 'pending', 'open'])
                    ->orWhereNull('status')
                    ->orWhere('status', '');
            });

        if ($isOwnerWorkspace) {
            $ownerHouseIds = \App\Models\BoardingHouse::query()
                ->where('owner_id', $currentUser->id)
                ->pluck('id');

            $messageQuery->whereIn('boarding_house_id', $ownerHouseIds);
        }

        $messageCount = (int) $messageQuery->count();
    } elseif (\Illuminate\Support\Facades\Schema::hasTable('messages')) {
        $messageQuery = \Illuminate\Support\Facades\DB::table('messages');

        if ($isOwnerWorkspace && $currentUser) {
            $ownerHouseIds ??= \App\Models\BoardingHouse::query()
                ->where('owner_id', $currentUser->id)
                ->pluck('id');

            if (\Illuminate\Support\Facades\Schema::hasColumn('messages', 'boarding_house_id')) {
                $messageQuery->whereIn('boarding_house_id', $ownerHouseIds);
            } elseif (\Illuminate\Support\Facades\Schema::hasColumn('messages', 'receiver_id')) {
                $messageQuery->where('receiver_id', $currentUser->id);
            } elseif (\Illuminate\Support\Facades\Schema::hasColumn('messages', 'user_id')) {
                $messageQuery->where('user_id', $currentUser->id);
            } else {
                $messageQuery->whereRaw('1 = 0');
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('messages', 'is_read')) {
            $messageQuery->where('is_read', false);
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('messages', 'read_at')) {
            $messageQuery->whereNull('read_at');
        }

        $messageCount = (int) $messageQuery->count();
    }

    if (\Illuminate\Support\Facades\Schema::hasTable('reservations') && \Illuminate\Support\Facades\Schema::hasColumn('reservations', 'status')) {
        $reservationQuery = \Illuminate\Support\Facades\DB::table('reservations')
            ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['pending', 'approved', 'reserved']);

        if ($isOwnerWorkspace) {
            $ownerHouseIds ??= \App\Models\BoardingHouse::query()
                ->where('owner_id', $currentUser->id)
                ->pluck('id');
            $reservationQuery->whereIn('boarding_house_id', $ownerHouseIds);
        }

        $pendingReservationCount = (int) $reservationQuery->count();
    }

    $pageContext = match (true) {
        request()->routeIs($workspaceRoute('dashboard')) => [
            'label' => $isOwnerWorkspace ? 'Owner Dashboard' : 'Admin Dashboard',
            'title' => 'Hello, ' . ($currentUser?->name ?: 'Jani'),
            'description' => $isOwnerWorkspace
                ? 'Your property dashboard at a glance.'
                : 'System operations, account activity, and platform performance at a glance.',
        ],
        request()->is($workspacePath.'/my-boarding-house*') => [
            'label' => 'Property Management',
            'title' => 'My Properties',
            'description' => 'Manage your boarding houses, rooms, tenants, and property details.',
        ],
        request()->is($workspacePath.'/boarding-houses*', $workspacePath.'/listings*') => [
            'label' => 'Property Management',
            'title' => 'Portfolio Listings',
            'description' => 'Manage property details, listing visibility, amenities, and availability across all boarding houses.',
        ],
        request()->is($workspacePath.'/rooms*') => [
            'label' => 'Room Management',
            'title' => 'Room Inventory',
            'description' => 'Track room types, occupancy, rates, and room-level availability for every property.',
        ],
        request()->is($workspacePath.'/reservations*') => [
            'label' => 'Reservations',
            'title' => 'Reservation Queue',
            'description' => 'Review incoming requests, approvals, move-in schedules, and follow-up actions.',
        ],
        request()->is($workspacePath.'/tenants*', $workspacePath.'/tenant-profiles*') => [
            'label' => 'Tenant Management',
            'title' => 'Tenant Directory',
            'description' => 'Monitor tenant profiles, active stays, and housing history across your listings.',
        ],
        request()->is($workspacePath.'/inquiries*') => [
            'label' => 'Inquiry Inbox',
            'title' => 'Property Inquiries',
            'description' => 'Respond to questions from students and keep inquiries moving toward reservation decisions.',
        ],
        request()->is($workspacePath.'/messages*') => [
            'label' => 'Messages',
            'title' => 'Conversation Hub',
            'description' => 'Keep tenant conversations organized, timely, and easy to resolve.',
        ],
        request()->is($workspacePath.'/payments*', $workspacePath.'/transactions*') => [
            'label' => 'Payments & Earnings',
            'title' => 'Revenue Tracking',
            'description' => 'Record payments, monitor outstanding balances, and review collection status.',
        ],
        request()->is($workspacePath.'/payment-verification*') => [
            'label' => 'Receipt Review',
            'title' => 'Payment Receipt Verification',
            'description' => 'Approve or reject submitted receipts and keep payment records accurate.',
        ],
        request()->is($workspacePath.'/notifications*') => [
            'label' => 'Notifications',
            'title' => 'Owner Alerts',
            'description' => 'Stay on top of system updates, tenant actions, and account reminders.',
        ],
        request()->is($workspacePath.'/reports*') => [
            'label' => 'Reports & Analytics',
            'title' => 'Performance Reports',
            'description' => 'Review occupancy, revenue, inquiry, and operational performance trends.',
        ],
        request()->is($workspacePath.'/match-requests*', $workspacePath.'/recommendations*', $workspacePath.'/compatibility-scores*') => [
            'label' => 'Matching Insights',
            'title' => 'Demand & Match Insights',
            'description' => 'Review compatibility trends and student demand signals tied to your listings.',
        ],
        request()->is($workspacePath.'/settings*') => [
            'label' => 'Account Settings',
            'title' => 'Owner Account',
            'description' => 'Manage your profile, security settings, and workspace preferences.',
        ],
        request()->is($workspacePath.'/help-center*', $workspacePath.'/help*') => [
            'label' => 'Help Center',
            'title' => 'Owner Support Center',
            'description' => 'Find fast answers, support resources, and operational guidance for your owner workspace.',
        ],
        request()->is($workspacePath.'/search*') => [
            'label' => 'Workspace Search',
            'title' => 'Owner Search',
            'description' => 'Search properties, tenants, reservations, payments, and inquiries across your workspace.',
        ],
        default => [
            'label' => 'Owner Workspace',
            'title' => 'BoardMatch Workspace',
            'description' => 'Manage your properties, tenants, and revenue in one place.',
        ],
    };

    $dashboardSearchPlaceholder = 'Search boarding houses, tenants, reservations, payments...';
@endphp

<div
    x-data="{ adminProfileOpen: false, adminProfileMenuReady: false }"
    @keydown.escape.window="adminProfileOpen = false; adminProfileMenuReady = false"
    class="admin-shell relative w-full overflow-x-hidden bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.08),_transparent_28%),radial-gradient(circle_at_top_right,_rgba(37,99,235,0.08),_transparent_24%),#f4f7fb] dark:bg-[#020617]"
>
    <div class="sidebar-overlay" data-sidebar-overlay aria-hidden="true"></div>

    <aside
        id="adminSidebar"
        class="sidebar admin-sidebar fixed inset-y-0 left-0 z-50 h-screen w-[200px] shrink-0 overflow-hidden border-r border-white/10 bg-[linear-gradient(180deg,#0F172A_0%,#111827_48%,#0B1224_100%)] px-2.5 py-3 shadow-2xl shadow-slate-950/30 flex flex-col"
        aria-label="{{ $isOwnerWorkspace ? 'Owner sidebar' : 'Admin sidebar' }}"
    >
        <div class="sidebar-header flex items-center gap-1.5">
            <a href="{{ $r($workspaceRoute('dashboard')) }}" class="admin-sidebar-brand flex min-w-0 flex-1 items-center gap-2">
                <div class="sidebar-brand-icon flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-[0_10px_22px_rgba(37,99,235,0.3)] ring-1 ring-white/15">
                    @include('components.sidebar.partials.admin-icon', ['name' => 'boarding-house'])
                </div>
                <div class="sidebar-brand-text min-w-0 leading-tight">
                    <p class="truncate text-base font-bold tracking-tight text-white">BoardMatch</p>
                    <p class="truncate text-[9px] font-semibold uppercase tracking-[0.16em] text-slate-400">{{ $isOwnerWorkspace ? 'Owner portal' : 'Admin portal' }}</p>
                </div>
            </a>
            <button
                type="button"
                class="h-7 w-7 rounded-lg border border-white/10 bg-white/5 text-slate-200 shadow-sm transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-400/70 flex items-center justify-center"
                data-sidebar-toggle
                aria-controls="adminSidebar"
                aria-expanded="true"
                aria-label="Toggle sidebar"
            >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <div id="sidebarNav" class="flex min-h-0 flex-1 overflow-y-auto">
            <x-sidebar.admin-panel />
        </div>

        <p class="sidebar-footer mt-3 border-t border-white/10 pt-2 text-center text-[10px] leading-4 text-slate-500">&copy; {{ date('Y') }} BoardMatch<br>All rights reserved.</p>
    </aside>

    <button
        type="button"
        class="sidebar-reopen-button"
        data-sidebar-toggle
        data-sidebar-reopen
        aria-controls="adminSidebar"
        aria-expanded="false"
        aria-label="Open sidebar"
        title="Open sidebar"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <span class="sr-only">Open sidebar</span>
    </button>

    <main class="admin-dashboard-main min-w-0 bg-transparent">
        <div class="relative mx-auto flex max-w-[1640px] flex-col gap-2.5 px-2.5 py-2.5 sm:px-3 sm:py-3 xl:px-4">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-40 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.7),_transparent_62%)] dark:bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.08),_transparent_62%)]"></div>

            <div class="relative md:hidden">
                <button
                    type="button"
                    class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 bg-white/90 px-3 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-400/70"
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

            @if ($showHeader)
                <header data-admin-workspace-header data-workspace="{{ $workspace }}" class="sticky top-2.5 z-[60] overflow-visible rounded-[1.1rem] border border-white/80 bg-white/95 px-3 py-2.5 shadow-[0_14px_30px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800/90 dark:bg-slate-950/95 xl:px-3.5">
                    <div class="absolute inset-y-0 right-0 hidden w-32 bg-[radial-gradient(circle_at_center,_rgba(16,185,129,0.12),_transparent_68%)] lg:block"></div>
                    <div class="relative flex flex-col gap-2.5 xl:flex-row xl:items-center xl:justify-between">
                        <div class="min-w-0">
                            <h1 class="text-[1.2rem] font-black tracking-tight text-slate-950 dark:text-white">{{ $workspaceHeaderTitle }}</h1>
                            <p class="mt-1 max-w-3xl text-[11px] leading-5 text-slate-500 dark:text-slate-400">{{ $workspaceHeaderDescription }}</p>
                        </div>

                        <div class="flex w-full flex-col gap-2 xl:w-auto xl:min-w-[32rem] xl:items-end">
                            <div class="flex min-w-0 flex-wrap items-center gap-2 xl:flex-nowrap xl:justify-end">
                                @if (! $isOwnerWorkspace)
                                    <form method="GET" action="{{ $r('admin.search') }}" class="min-w-0 flex-1 sm:flex-none">
                                        <label class="relative block">
                                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                                                </svg>
                                            </span>
                                            <input
                                                name="query"
                                                value="{{ request('query') }}"
                                                class="h-9 w-full min-w-[120px] max-w-[180px] rounded-xl border border-slate-200 bg-slate-50/90 pl-9 pr-3 text-[12px] text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:focus:border-emerald-400 dark:focus:bg-slate-800 dark:focus:ring-emerald-900"
                                                placeholder="Search..."
                                            >
                                        </label>
                                    </form>
                                @endif
                                <a href="{{ $r('admin.reservations') }}" class="inline-flex h-9 shrink-0 items-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 text-[11px] font-bold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:hover:bg-emerald-400/15">
                                    <span>Reservations</span>
                                    <span class="rounded-full bg-white px-2 py-0.5 text-[11px] leading-none text-emerald-700 dark:bg-slate-900 dark:text-emerald-300">{{ $pendingReservationCount }}</span>
                                </a>
                                <a href="{{ $r('admin.messages') }}" class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-emerald-400/30 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300" aria-label="Messages, {{ $messageCount }} awaiting reply">
                                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 6h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H9l-5 4v-4H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/>
                                        <path stroke-linecap="round" stroke-width="1.8" d="M8 10h8M8 14h5"/>
                                    </svg>
                                    @if ($messageCount > 0)
                                        <span class="absolute -right-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full bg-emerald-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">{{ $messageCount > 99 ? '99+' : $messageCount }}</span>
                                    @endif
                                </a>
                                <x-header-notification-link :href="$r($workspaceRoute('notifications.index'))" :count="$notificationsCount" />
                                <x-theme-icon-toggle />

                                <div class="relative z-[70] min-w-0 flex-1 sm:flex-none">
                                    <button
                                        type="button"
                                        @click.stop="adminProfileOpen = ! adminProfileOpen; adminProfileMenuReady = false; if (adminProfileOpen) setTimeout(() => adminProfileMenuReady = true, 120)"
                                        class="flex h-11 w-full min-w-0 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-2.5 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/60 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-slate-600 dark:hover:bg-slate-800 sm:w-auto sm:min-w-[12rem] sm:max-w-[15rem]"
                                        aria-haspopup="menu"
                                        :aria-expanded="adminProfileOpen"
                                    >
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-emerald-500 to-blue-600 text-sm font-bold text-white">
                                            @if ($accountImageUrl)
                                                <img src="{{ $accountImageUrl }}" alt="{{ $ownerName }}" class="h-full w-full object-cover">
                                            @else
                                                {{ $ownerInitial }}
                                            @endif
                                        </span>
                                        <span class="min-w-0 flex-1 text-left leading-tight">
                                            <span class="block truncate text-[13px] font-bold text-slate-900 dark:text-white">{{ $ownerName }}</span>
                                            <span class="block truncate text-[11px] font-semibold text-slate-500 dark:text-slate-400">{{ $ownerRole }}</span>
                                        </span>
                                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition" :class="adminProfileOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <div
                                        x-cloak
                                        x-show="adminProfileOpen"
                                        x-transition
                                        @click.outside="adminProfileOpen = false; adminProfileMenuReady = false"
                                        @click.stop
                                        class="absolute right-0 top-full z-[80] mt-2 w-[min(17.5rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/12 dark:border-slate-700 dark:bg-slate-900"
                                        :class="adminProfileMenuReady ? 'pointer-events-auto' : 'pointer-events-none'"
                                        role="menu"
                                    >
                                        <div class="border-b border-slate-100 px-4 py-4 dark:border-slate-800">
                                            <p class="truncate text-base font-bold text-slate-950 dark:text-white">{{ $ownerName }}</p>
                                            <p class="mt-0.5 truncate text-sm text-slate-500 dark:text-slate-400">{{ $currentUser?->email }}</p>
                                        </div>
                                        <div class="p-2 text-sm">
                                            <a href="{{ $r('admin.settings.index') }}" class="flex items-center rounded-xl px-3 py-3 text-base font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700 dark:text-slate-200 dark:hover:bg-blue-400/10 dark:hover:text-blue-300" role="menuitem">Profile Management</a>
                                            <a href="{{ $r('admin.settings.index', ['tab' => 'security']) }}" class="flex items-center rounded-xl px-3 py-3 text-base font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700 dark:text-slate-200 dark:hover:bg-blue-400/10 dark:hover:text-blue-300" role="menuitem">Security Settings</a>
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button class="flex w-full items-center rounded-xl px-3 py-3 text-left text-base font-semibold text-rose-600 transition hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-400/10" role="menuitem">Logout</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
            @endif

            <x-toast />

            <div class="relative z-0">
                {{ $slot }}
            </div>
        </div>
    </main>
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
