@props([
    'searchPlaceholder' => 'Search tenants, rooms, bookings...',
])

@php
    $r = fn($name, $params = []) => \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : url()->current();
    $navBase = 'block px-3 py-2 rounded-lg';
    $navActive = $navBase . ' ui-surface-2 text-[color:var(--text)] font-medium border ui-border';
    $navInactive = $navBase . ' text-[color:var(--muted)] hover:bg-[color:var(--surface-2)]';
@endphp

<div class="min-h-screen flex w-full">
    {{-- Sidebar --}}
    <aside class="w-[260px] shrink-0 h-screen sticky top-0 ui-surface border-r ui-border px-4 py-6 flex flex-col">
        <div class="mb-6 flex items-center gap-2">
            <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-orange-500 via-amber-400 to-rose-400 text-white flex items-center justify-center font-bold">CT</div>
            <div class="leading-tight">
                <p class="text-sm font-semibold">Caretaker</p>
                <p class="text-xs ui-muted">Operations</p>
            </div>
        </div>

        <nav class="flex-1 space-y-4 text-sm">
            <div>
                <p class="text-xs uppercase ui-muted mb-2">Operations</p>
                <a href="{{ $r('caretaker.dashboard') }}" class="{{ request()->routeIs('caretaker.dashboard') ? $navActive : $navInactive }}">Dashboard</a>
                <a href="{{ $r('caretaker.tenants.index') }}" class="{{ request()->routeIs('caretaker.tenants.*') ? $navActive : $navInactive }}">Tenants</a>
                <a href="{{ $r('caretaker.bookings.index') }}" class="{{ request()->routeIs('caretaker.bookings.*') ? $navActive : $navInactive }}">Bookings</a>
            </div>
            <div>
                <p class="text-xs uppercase ui-muted mb-2">Property</p>
                <a href="{{ $r('caretaker.rooms.index') }}" class="{{ request()->routeIs('caretaker.rooms.*') ? $navActive : $navInactive }}">Rooms & Listings</a>
                <a href="{{ $r('caretaker.maintenance.index') }}" class="{{ request()->routeIs('caretaker.maintenance.*') ? $navActive : $navInactive }}">Maintenance</a>
            </div>
            <div>
                <p class="text-xs uppercase ui-muted mb-2">Issues</p>
                <a href="{{ $r('caretaker.incidents.index') }}" class="{{ request()->routeIs('caretaker.incidents.*') ? $navActive : $navInactive }}">Incidents & Complaints</a>
            </div>
            <div>
                <p class="text-xs uppercase ui-muted mb-2">Communication</p>
                <a href="{{ $r('caretaker.notices.index') }}" class="{{ request()->routeIs('caretaker.notices.*') ? $navActive : $navInactive }}">Notices</a>
            </div>
            <div>
                <p class="text-xs uppercase ui-muted mb-2">Insights</p>
                <a href="{{ $r('caretaker.reports.index') }}" class="{{ request()->routeIs('caretaker.reports.*') ? $navActive : $navInactive }}">Reports / Analytics</a>
                <a href="{{ $r('caretaker.settings') }}" class="{{ request()->routeIs('caretaker.settings') ? $navActive : $navInactive }}">Settings</a>
            </div>
        </nav>
        <p class="text-xs ui-muted mt-4">© 2026 Boarding House</p>
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
                            <img src="https://i.pravatar.cc/40?img=10" class="h-9 w-9 rounded-full object-cover" alt="Caretaker">
                            <svg class="h-4 w-4 ui-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-52 ui-surface rounded-xl shadow-lg border ui-border z-50">
                            <div class="px-4 py-3 border-b ui-border text-sm">
                                <p class="font-semibold">{{ auth()->user()->name ?? 'Caretaker' }}</p>
                                <p class="text-xs ui-muted">{{ auth()->user()->email ?? '' }}</p>
                            </div>
                            <div class="py-2 text-sm">
                                <a href="{{ $r('caretaker.settings') }}" class="block px-4 py-2 hover:bg-[color:var(--surface-2)]">Settings</a>
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
