@props([
    'searchPlaceholder' => 'Search listings, reservations, messages...',
])

@php
    $title = $title ?? 'User Dashboard';
    $r = fn($name, $params = []) => \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : url()->current();
    $navBase = 'block px-3 py-2 rounded-lg';
    $navActive = $navBase . ' ui-surface-2 text-[color:var(--text)] font-medium border ui-border';
    $navInactive = $navBase . ' text-[color:var(--muted)] hover:bg-[color:var(--surface-2)]';
    $currentUser = auth()->user();
    $profileImage = $currentUser?->profile_image;
    $accountImageUrl = $profileImage
        ? (\Illuminate\Support\Str::startsWith($profileImage, ['http://', 'https://', '/'])
            ? $profileImage
            : \Illuminate\Support\Facades\Storage::url($profileImage))
        : asset('images/boardmatch-mark.svg');
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
        <x-sidebar.user-panel />
    </aside>

    <main class="flex-1 ui-bg">
        <div class="max-w-7xl mx-auto px-6 py-6 space-y-6">
            {{-- Header --}}
            @if (request()->routeIs('user.dashboard'))
                <div class="ui-card p-4 flex items-center gap-4">
                    <form method="GET" action="{{ $r('user.browse') }}" class="flex flex-1 gap-3">
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
                                    <a href="{{ $r('user.settings') }}" class="block px-4 py-2 hover:bg-[color:var(--surface-2)]">Profile Settings</a>
                                    <a href="{{ $r('user.profile') }}" class="block px-4 py-2 hover:bg-[color:var(--surface-2)]">Match Preferences</a>
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

            @if (session('success') || session('error') || session('status') || $errors->any())
                <div class="space-y-2">
                    @if (session('success'))
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if (session('status'))
                        <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-medium text-sky-700">
                            {{ str_replace('-', ' ', ucfirst(session('status'))) }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {{ $errors->first() }}
                        </div>
                    @endif
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>
</div>
