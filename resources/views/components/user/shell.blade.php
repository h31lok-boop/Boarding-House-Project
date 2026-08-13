@props([
    'searchPlaceholder' => 'Search listings, reservations, messages...',
    'topBar' => false,
])

@php
    $title = $title ?? 'User Dashboard';
    $r = fn($name, $params = []) => \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : url()->current();
    $currentUser = auth()->user();
    $accountImageUrl = $currentUser?->photo_url ?: asset('images/boardmatch-final-logo.png');
    $notificationsCount = 0;
    $messageCount = 0;

    if ($currentUser
        && \Illuminate\Support\Facades\Schema::hasTable('notifications')
        && \Illuminate\Support\Facades\Schema::hasColumn('notifications', 'user_id')) {
        $notificationQuery = \Illuminate\Support\Facades\DB::table('notifications')
            ->where('user_id', $currentUser->id);

        if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'read_at')) {
            $notificationQuery->whereNull('read_at');
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'is_read')) {
            $notificationQuery->where('is_read', false);
        }

        $notificationsCount = (int) $notificationQuery->count();
    }

    // Tenant message badges are based only on unread owner replies that belong
    // to this tenant's own inquiries. Never use a platform-wide inquiry count.
    if ($currentUser
        && \Illuminate\Support\Facades\Schema::hasTable('inquiries')
        && \Illuminate\Support\Facades\Schema::hasTable('notifications')
        && \Illuminate\Support\Facades\Schema::hasColumn('inquiries', 'user_id')
        && \Illuminate\Support\Facades\Schema::hasColumn('notifications', 'user_id')
        && \Illuminate\Support\Facades\Schema::hasColumn('notifications', 'type')
        && \Illuminate\Support\Facades\Schema::hasColumn('notifications', 'reference_id')) {
        $tenantInquiryReferences = \Illuminate\Support\Facades\DB::table('inquiries')
            ->where('user_id', $currentUser->id)
            ->pluck('id')
            ->map(fn ($id) => 'inquiry:'.$id);

        if ($tenantInquiryReferences->isNotEmpty()) {
            $messageQuery = \Illuminate\Support\Facades\DB::table('notifications')
                ->where('user_id', $currentUser->id)
                ->where('type', 'inquiry')
                ->whereIn('reference_id', $tenantInquiryReferences);

            if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'read_at')) {
                $messageQuery->whereNull('read_at');
            } elseif (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'is_read')) {
                $messageQuery->where('is_read', false);
            }

            $messageCount = (int) $messageQuery->count();
        }
    }
@endphp

<div class="user-shell w-full bg-[#f7f8fb] dark:bg-[#020617]">
    {{-- Sidebar --}}
    <div class="sidebar-overlay" data-sidebar-overlay aria-hidden="true"></div>

    <aside id="userSidebar" class="sidebar user-sidebar fixed inset-y-0 left-0 z-50 h-screen w-[240px] shrink-0 overflow-hidden border-r border-white/10 bg-[linear-gradient(180deg,#0F172A_0%,#111827_48%,#0B1224_100%)] px-3 py-4 shadow-2xl shadow-slate-950/30 flex flex-col" aria-label="Tenant sidebar">
        <div class="sidebar-header">
            <x-sidebar.brand />
            <button type="button" class="h-9 w-9 rounded-lg border border-white/10 bg-white/5 text-slate-200 shadow-sm transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-400/70 flex items-center justify-center" data-sidebar-toggle aria-controls="userSidebar" aria-expanded="true" aria-label="Toggle sidebar">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
        <x-sidebar.user-panel />
    </aside>

    <button
        type="button"
        class="sidebar-reopen-button"
        data-sidebar-toggle
        data-sidebar-reopen
        aria-controls="userSidebar"
        aria-expanded="false"
        aria-label="Open sidebar"
        title="Open sidebar"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <span class="sr-only">Open sidebar</span>
    </button>

    <main class="user-dashboard-main min-w-0 bg-[#f7f8fb] dark:bg-[#020617]">
        <div class="mx-auto max-w-[1540px] space-y-5 px-4 py-5 sm:px-6 2xl:px-8">
            {{-- Persistent tenant header. All counters and links are scoped to the signed-in tenant. --}}
            <header data-tenant-workspace-header class="sticky top-2.5 z-[60] rounded-[1.1rem] border border-white/80 bg-white px-3 py-2.5 shadow-[0_14px_30px_rgba(15,23,42,0.08)] dark:border-slate-800/90 dark:bg-slate-950 sm:px-3.5">
                <div class="flex min-w-0 items-center justify-between gap-2.5">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-400/70 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 md:hidden" data-sidebar-toggle aria-controls="userSidebar" aria-expanded="false" aria-label="Open navigation menu">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <div class="min-w-0">
                            <h1 class="truncate text-[1.05rem] font-black tracking-tight text-slate-950 dark:text-white sm:text-[1.2rem]">BoardMatch Student</h1>
                            <p class="mt-0.5 hidden truncate text-[11px] text-slate-500 dark:text-slate-400 sm:block">Your matches, reservations, payments, and conversations in one place.</p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                        <a href="{{ $r('user.messages.index') }}" data-tenant-message-link class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-800 dark:hover:text-blue-300" aria-label="Messages, {{ $messageCount }} unread" title="Messages">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 6h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H9l-5 4v-4H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/><path stroke-linecap="round" stroke-width="1.8" d="M8 10h8M8 14h5"/></svg>
                            @if ($messageCount > 0)
                                <span class="absolute -right-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full bg-blue-600 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">{{ $messageCount > 99 ? '99+' : $messageCount }}</span>
                            @endif
                        </a>
                        <x-ai-assistant />
                        <x-header-notification-link :href="$r('user.notifications.index')" :count="$notificationsCount" />
                        <x-theme-icon-toggle />

                        <div class="relative z-[70]" x-data="{ open: false, confirm: false }">
                            <button type="button" @click="open = !open" class="flex h-9 items-center gap-2 rounded-xl border border-slate-200 bg-white p-1 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/60 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800 sm:h-11 sm:min-w-[10.5rem] sm:rounded-2xl sm:px-2" aria-haspopup="menu" :aria-expanded="open">
                                <span class="h-7 w-7 shrink-0 overflow-hidden rounded-full border border-slate-200 bg-blue-50 shadow-sm dark:border-slate-700 sm:h-9 sm:w-9">
                                    <img src="{{ $accountImageUrl }}" alt="{{ $currentUser?->name ?? 'Tenant account' }}" class="h-full w-full object-cover">
                                </span>
                                <span class="hidden min-w-0 flex-1 text-left leading-tight sm:block">
                                    <span class="block max-w-28 truncate text-[13px] font-bold text-slate-900 dark:text-white">{{ $currentUser?->name ?? 'Tenant' }}</span>
                                    <span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400">Student / Tenant</span>
                                </span>
                                <svg class="hidden h-4 w-4 shrink-0 text-slate-400 transition sm:block" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-cloak x-show="open" @click.outside="open = false" x-transition class="absolute right-0 top-full z-[80] mt-2 w-[min(17rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900" role="menu">
                                <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                                    <p class="truncate text-sm font-bold text-slate-950 dark:text-white">{{ $currentUser?->name ?? 'Tenant' }}</p>
                                    <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">{{ $currentUser?->email ?? '' }}</p>
                                </div>
                                <div class="p-2 text-sm">
                                    <a href="{{ $r('user.settings.index') }}" class="flex rounded-xl px-3 py-2.5 font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700 dark:text-slate-200 dark:hover:bg-blue-500/10 dark:hover:text-blue-300" role="menuitem">Profile</a>
                                    <button type="button" @click="confirm = true; open = false" class="flex w-full rounded-xl px-3 py-2.5 text-left font-semibold text-rose-600 transition hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10" role="menuitem">Log out</button>
                                </div>
                            </div>
                            <template x-teleport="body">
                                <div data-modal-root role="dialog" aria-modal="true" x-show="confirm" x-cloak x-transition @click.self="confirm = false" class="bm-modal-overlay">
                                    <section class="bm-modal w-full max-w-sm" @click.stop>
                                        <header class="bm-modal__header"><div><h2 class="text-base font-black text-slate-950 dark:text-white">Confirm logout</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">End your current BoardMatch session?</p></div></header>
                                        <footer class="bm-modal__footer"><button type="button" @click="confirm = false" class="btn-secondary">Cancel</button><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn-danger">Log out</button></form></footer>
                                    </section>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                @if ($topBar)
                    <form method="GET" action="{{ $r('user.boarding-houses.index') }}" class="mt-2.5 flex w-full min-w-0 gap-2 border-t border-slate-100 pt-2.5 dark:border-slate-800">
                        <input name="q" type="text" placeholder="{{ $searchPlaceholder }}" class="min-w-0 flex-1 ui-input text-sm">
                        <button class="shrink-0 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Search</button>
                    </form>
                @endif
            </header>

            @isset($header)
                <div class="ui-card p-3.5">
                    {{ $header }}
                </div>
            @endisset

            <x-toast />

            {{ $slot }}
        </div>
    </main>
</div>
