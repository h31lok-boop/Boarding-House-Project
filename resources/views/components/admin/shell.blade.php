@props([
    'searchPlaceholder' => 'Search users, houses, bookings...',
])

@php
    $title = $title ?? 'Admin Dashboard';
@endphp

@php
    $r = fn($name, $params = []) => \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : url()->current();
    $navBase = 'block px-3 py-2 rounded-lg';
    $navActive = $navBase . ' ui-surface-2 text-[color:var(--text)] font-medium border ui-border';
    $navInactive = $navBase . ' text-[color:var(--muted)] hover:bg-[color:var(--surface-2)]';
@endphp

<div class="min-h-screen flex w-full">
    {{-- Sidebar --}}
    <aside class="sidebar w-[260px] shrink-0 h-screen sticky top-0 ui-surface border-r ui-border px-4 py-6 flex flex-col">
        <div class="sidebar-header">
            <x-sidebar.brand />
            <button class="h-9 w-9 rounded-full ui-surface border ui-border flex items-center justify-center shadow" data-sidebar-toggle>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
        <x-sidebar.admin-panel />
    </aside>

    <main class="flex-1 ui-bg">
        <div class="max-w-7xl mx-auto px-6 py-6 space-y-6">
            {{-- Header --}}
            <div class="ui-card p-4 flex items-center gap-4">
                <input type="text" placeholder="{{ $searchPlaceholder }}" class="flex-1 ui-input text-sm">
                <div class="flex items-center gap-2">
                    <button class="h-9 w-9 rounded-full ui-surface border ui-border flex items-center justify-center shadow">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </button>
                    <button type="button" class="theme-toggle" data-theme-toggle><span>Theme:</span> <span data-theme-label>Light</span></button>
                    <div class="relative" x-data="{ open: false, confirm: false }">
                        <button @click="open = !open" class="flex items-center gap-2 px-2 py-1 rounded-full hover:bg-[color:var(--surface-2)]">
                            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-orange-500 via-rose-500 to-amber-400 text-white flex items-center justify-center text-xs font-semibold">
                                {{ Str::substr(auth()->user()->name ?? 'U', 0, 2) }}
                            </div>
                            <svg class="h-4 w-4 ui-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-52 ui-surface rounded-xl shadow-lg border ui-border z-50">
                            <div class="px-4 py-3 border-b ui-border text-sm">
                                <p class="font-semibold">{{ auth()->user()->name ?? 'Admin' }}</p>
                                <p class="text-xs ui-muted">{{ auth()->user()->email ?? '' }}</p>
                            </div>
                            <div class="py-2 text-sm">
                                <a href="{{ $r('profile.edit') }}" class="block px-4 py-2 hover:bg-[color:var(--surface-2)]">Profile</a>
                                <button @click="confirm = true; open = false" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50">Log out</button>
                            </div>
                        </div>
                        <div x-show="confirm" x-transition class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                            <div @click.outside="confirm = false" class="ui-card p-6 w-[320px] shadow-xl">
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

            {{ $slot }}
        </div>
    </main>
</div>
