@props([
    'searchPlaceholder' => 'Search listings, reservations, messages...',
    'topBar' => true,
])

@php
    $title = $title ?? 'User Dashboard';
    $r = fn($name, $params = []) => \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : url()->current();
    $currentUser = auth()->user();
    $profileImage = $currentUser?->profile_photo ?: $currentUser?->profile_image;
    $accountImageUrl = $profileImage
        ? (\Illuminate\Support\Str::startsWith($profileImage, ['http://', 'https://', '/'])
            ? $profileImage
            : \Illuminate\Support\Facades\Storage::url($profileImage))
        : asset('images/boardmatch-mark.svg');
@endphp

<div class="user-shell w-full bg-[#f7f8fb]">
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

    <main class="user-dashboard-main min-w-0 bg-[#f7f8fb]">
        <div class="sticky top-0 z-30 mb-4 border-b border-slate-200 bg-[#f7f8fb]/95 px-4 py-3 backdrop-blur md:hidden">
            <button type="button" class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-400/70" data-sidebar-toggle aria-controls="userSidebar" aria-expanded="false" aria-label="Open sidebar">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <span>Menu</span>
            </button>
        </div>

        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 2xl:px-8 py-6 space-y-6">
            {{-- Header --}}
            @if ($topBar && request()->routeIs('user.dashboard') && !request()->is('admin/*'))
                <div class="ui-card p-4 flex items-center gap-4">
                    <form method="GET" action="{{ $r('user.boarding-houses.index') }}" class="flex flex-1 gap-3">
                        <input name="q" type="text" placeholder="{{ $searchPlaceholder }}" class="flex-1 ui-input text-sm">
                        <button class="rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white hover:bg-indigo-700">Search</button>
                    </form>
                    <div class="flex items-center gap-2">
                        <button class="h-9 w-9 rounded-full ui-surface border ui-border flex items-center justify-center shadow">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </button>
                        <button type="button" class="theme-toggle" data-theme-toggle><span>Theme:</span> <span data-theme-label>Light</span></button>
                        <div class="relative" x-data="{ open: false, confirm: false }">
                            <button @click="open = !open" class="flex items-center gap-2 px-2 py-1 rounded-full hover:bg-[color:var(--surface-2)]">
                                <div class="h-9 w-9 overflow-hidden rounded-full border ui-border shadow">
                                    <img src="{{ $accountImageUrl }}" alt="{{ $currentUser?->name ?? 'Account' }}" class="h-full w-full object-cover">
                                </div>
                                <svg class="h-4 w-4 ui-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-52 ui-surface rounded-xl shadow-lg border ui-border z-50">
                                <div class="px-4 py-3 border-b ui-border text-sm">
                                    <p class="font-semibold">{{ auth()->user()->name ?? 'User' }}</p>
                                    <p class="text-xs ui-muted">{{ auth()->user()->email ?? '' }}</p>
                                </div>
                                <div class="py-2 text-sm">
                                    <a href="{{ $r('user.settings.index') }}" class="block px-4 py-2 hover:bg-[color:var(--surface-2)]">Settings</a>
                                    <a href="{{ $r('user.preferences.index') }}" class="block px-4 py-2 hover:bg-[color:var(--surface-2)]">Match Preferences</a>
                                    <button @click="confirm = true; open = false" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50">Log out</button>
                                </div>
                            </div>
                            <div data-modal-root role="dialog" aria-modal="true" x-show="confirm" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
                                <div class="ui-card p-6 w-[320px] shadow-xl">
                                    <h3 class="text-lg font-semibold mb-2">Confirm Logout</h3>
                                    <p class="text-sm ui-muted mb-4">Are you sure you want to log out?</p>
                                    <div class="flex justify-end gap-2">
                                        <button @click="confirm = false" class="btn-secondary">Cancel</button>
                                        <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn-danger">Log out</button></form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @isset($header)
                <div class="ui-card p-4">
                    {{ $header }}
                </div>
            @endisset

            <x-toast />

            {{ $slot }}
        </div>
    </main>
</div>
