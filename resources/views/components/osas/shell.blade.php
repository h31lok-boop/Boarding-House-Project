@props([
    'searchPlaceholder' => 'Search validations, houses, validators...',
])

@php
    $title = $title ?? 'OSAS Dashboard';
    $r = fn($name, $params = []) => \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : url()->current();
    $navBase = 'flex items-center gap-2 px-3 py-2 rounded-lg';
    $navActive = $navBase . ' ui-surface-2 text-[color:var(--text)] font-medium border ui-border';
    $navInactive = $navBase . ' text-[color:var(--muted)] hover:bg-[color:var(--surface-2)]';
@endphp

<div class="min-h-screen flex w-full">
    <aside class="sidebar w-[260px] shrink-0 h-screen sticky top-0 ui-surface border-r ui-border px-4 py-6 flex flex-col">
        <div class="sidebar-header">
            <x-sidebar.brand />
            <button class="h-9 w-9 rounded-full ui-surface border ui-border flex items-center justify-center shadow" data-sidebar-toggle>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <nav class="flex-1 space-y-4 text-sm sidebar-nav">
            <div>
                <p class="text-xs uppercase ui-muted mb-2 sidebar-group">Validation</p>
                <a href="{{ $r('osas.dashboard') }}" class="{{ request()->routeIs('osas.dashboard') ? $navActive : $navInactive }}">
                    <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 10l9-7 9 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M5 10v10h14V10"/></svg></span>
                    <span class="sidebar-text">Dashboard</span>
                </a>
                <a href="{{ $r('osas.validators.index') }}" class="{{ request()->routeIs('osas.validators.*') ? $navActive : $navInactive }}">
                    <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 20a7 7 0 0 1 14 0"/></svg></span>
                    <span class="sidebar-text">Validator Accounts</span>
                </a>
                <a href="{{ $r('osas.workbench') }}" class="{{ request()->routeIs('osas.workbench') ? $navActive : $navInactive }}">
                    <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M6 3h9l5 5v13H6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 3v5h5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 13h6M9 17h6"/></svg></span>
                    <span class="sidebar-text">Validation Workbench</span>
                </a>
                <a href="{{ $r('osas.accreditation') }}" class="{{ request()->routeIs('osas.accreditation') ? $navActive : $navInactive }}">
                    <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="m5 12 4 4 10-10"/></svg></span>
                    <span class="sidebar-text">Accreditation Control</span>
                </a>
                <a href="{{ $r('osas.reports') }}" class="{{ request()->routeIs('osas.reports') ? $navActive : $navInactive }}">
                    <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 20V6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 20V10"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 20V4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M16 20V12"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M20 20V8"/></svg></span>
                    <span class="sidebar-text">Reports / Monitoring</span>
                </a>
            </div>
            <div>
                <p class="text-xs uppercase ui-muted mb-2 sidebar-group">System</p>
                <a href="{{ $r('osas.settings') }}" class="{{ request()->routeIs('osas.settings') ? $navActive : $navInactive }}">
                    <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="m4.9 6.3 1.6-1 1.2 1.3a7.5 7.5 0 0 1 2.2-.9l.4-1.7h2l.4 1.7c.8.2 1.5.5 2.2.9l1.2-1.3 1.6 1-0.6 1.7c.6.6 1 1.3 1.3 2.1l1.7.4v2l-1.7.4c-.2.8-.6 1.5-1.3 2.1l.6 1.7-1.6 1-1.2-1.3a7.5 7.5 0 0 1-2.2.9l-.4 1.7h-2l-.4-1.7a7.5 7.5 0 0 1-2.2-.9l-1.2 1.3-1.6-1 .6-1.7a7.2 7.2 0 0 1-1.3-2.1L3 13v-2l1.7-.4c.2-.8.6-1.5 1.3-2.1l-.6-1.7Z"/></svg></span>
                    <span class="sidebar-text">Settings</span>
                </a>
            </div>
        </nav>
        <p class="text-xs ui-muted mt-4 sidebar-footer">© 2026 Boarding House</p>
    </aside>

    <main class="flex-1 ui-bg">
        <div class="max-w-7xl mx-auto px-6 py-6 space-y-6">
            <div class="ui-card p-4 flex items-center gap-4">
                <input type="text" placeholder="{{ $searchPlaceholder }}" class="flex-1 ui-input text-sm">
                <div class="flex items-center gap-2">
                    <button class="h-9 w-9 rounded-full ui-surface border ui-border flex items-center justify-center shadow">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </button>
                    <button type="button" class="theme-toggle" data-theme-toggle><span>Theme:</span> <span data-theme-label>Light</span></button>
                    <div class="relative" x-data="{open:false,confirm:false}">
                        <button @click="open=!open" class="flex items-center gap-2 px-2 py-1 rounded-full hover:bg-[color:var(--surface-2)]">
                            <img src="https://i.pravatar.cc/40?img=22" class="h-9 w-9 rounded-full object-cover" alt="OSAS">
                            <svg class="h-4 w-4 ui-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.outside="open=false" x-transition class="absolute right-0 mt-2 w-52 ui-surface rounded-xl shadow-lg border ui-border z-50">
                            <div class="px-4 py-3 border-b ui-border text-sm">
                                <p class="font-semibold">{{ auth()->user()->name ?? 'OSAS Validator' }}</p>
                                <p class="text-xs ui-muted">{{ auth()->user()->email ?? '' }}</p>
                            </div>
                            <div class="py-2 text-sm">
                                <a href="{{ $r('osas.settings') }}" class="block px-4 py-2 hover:bg-[color:var(--surface-2)]">Settings</a>
                                <button @click="confirm=true;open=false" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50">Log out</button>
                            </div>
                        </div>
                        <div x-show="confirm" x-transition class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                            <div @click.outside="confirm=false" class="ui-card p-6 w-[320px] shadow-xl">
                                <h3 class="text-lg font-semibold mb-2">Confirm Logout</h3>
                                <p class="text-sm ui-muted mb-4">Are you sure you want to log out?</p>
                                <div class="flex justify-end gap-2">
                                    <button @click="confirm=false" class="btn-secondary">Cancel</button>
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
