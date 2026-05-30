@props([
    'searchPlaceholder' => 'Search anything...',
])

@php
    $currentUser = auth()->user();
    $profileImage = $currentUser?->profile_image;
    $accountImageUrl = $profileImage
        ? (\Illuminate\Support\Str::startsWith($profileImage, ['http://', 'https://', '/'])
            ? $profileImage
            : \Illuminate\Support\Facades\Storage::url($profileImage))
        : asset('images/boardmatch-mark.svg');

    $pendingNotifications = 0;
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('notifications')
            && \Illuminate\Support\Facades\Schema::hasColumn('notifications', 'is_read')) {
            $pendingNotifications = \Illuminate\Support\Facades\DB::table('notifications')->where('is_read', false)->count();
        }
    } catch (\Throwable $e) {
        $pendingNotifications = 0;
    }
@endphp

<div x-data="{ logoutConfirm: false, profileOpen: false }"
     @keydown.escape.window="logoutConfirm = false; profileOpen = false"
     class="min-h-screen flex w-full bg-gray-50">

    {{-- Sidebar --}}
    <aside id="adminSidebar"
           class="sidebar w-[230px] shrink-0 h-screen sticky top-0 bg-white border-r border-gray-200 flex flex-col shadow-sm">

        {{-- Brand --}}
        <div class="px-5 py-5 border-b border-gray-100 flex items-center gap-3 shrink-0">
            <div class="h-9 w-9 rounded-xl bg-orange-500 flex items-center justify-center shadow-sm shrink-0">
                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
            </div>
            <div class="sidebar-text">
                <p class="font-bold text-gray-900 text-sm leading-tight">BoardMatch</p>
                <p class="text-xs text-orange-500 font-medium">Admin Panel</p>
            </div>
        </div>

        {{-- Navigation --}}
        <div id="sidebarNav" class="flex-1 overflow-y-auto min-h-0">
            <x-sidebar.admin-panel />
        </div>

        {{-- User Profile --}}
        <div class="border-t border-gray-100 px-4 py-3 shrink-0 relative">
            <button @click="profileOpen = !profileOpen"
                    class="flex items-center gap-3 w-full hover:bg-gray-50 rounded-lg px-2 py-2 transition-colors">
                <div class="h-8 w-8 overflow-hidden rounded-full border-2 border-orange-200 shadow-sm shrink-0">
                    <img src="{{ $accountImageUrl }}" alt="{{ $currentUser?->name ?? 'Account' }}"
                         class="h-full w-full object-cover">
                </div>
                <div class="flex-1 min-w-0 text-left sidebar-text">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $currentUser?->name ?? 'Admin' }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ ucfirst($currentUser?->role ?? 'Administrator') }}</p>
                </div>
                <svg class="h-4 w-4 text-gray-400 shrink-0 sidebar-text transition-transform duration-200"
                     :class="profileOpen ? 'rotate-180' : ''"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="profileOpen"
                 x-cloak
                 @click.outside="profileOpen = false"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="absolute bottom-full left-3 right-3 mb-1 bg-white rounded-xl shadow-lg border border-gray-200 z-50 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-800">{{ $currentUser?->name ?? 'Admin' }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $currentUser?->email ?? '' }}</p>
                </div>
                <div class="py-1 text-sm">
                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.settings') ? route('admin.settings') : '#' }}"
                       class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 text-gray-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z"/>
                        </svg>
                        Profile & Settings
                    </a>
                    <button @click="logoutConfirm = true; profileOpen = false"
                            class="flex items-center gap-2 w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4M14 16l4-4-4-4M18 12H9"/>
                        </svg>
                        Log out
                    </button>
                </div>
            </div>
        </div>

        {{-- Weather Widget --}}
        <div id="sidebarWeather" class="px-4 py-3 border-t border-gray-100 bg-gray-50 sidebar-text shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                    <svg id="weatherIcon" class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                    </svg>
                </div>
                <div>
                    <p id="sidebarWeatherTemp" class="text-xs font-bold text-gray-700">--°C</p>
                    <p id="sidebarWeatherDesc" class="text-[10px] text-gray-400 leading-tight">Loading...</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- Main Area --}}
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">

        {{-- Top Header Bar --}}
        <header class="sticky top-0 z-20 bg-white border-b border-gray-200 shadow-sm shrink-0">
            <div class="flex items-center gap-3 px-4 lg:px-6 py-3">

                @if (request()->routeIs('admin.dashboard'))
                {{-- Search (dashboard only) --}}
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text"
                               id="globalSearch"
                               placeholder="{{ $searchPlaceholder }}"
                               class="w-full pl-9 pr-16 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-orange-400 text-gray-700 placeholder-gray-400">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-gray-400 bg-white border border-gray-200 rounded px-1.5 py-0.5 font-medium hidden sm:block">Ctrl K</span>
                    </div>
                </div>
                @else
                <div class="flex-1"></div>
                @endif

                <div class="flex items-center gap-2">

                    {{-- Notifications Bell --}}
                    <a href="{{ route('admin.notifications') }}"
                       title="Notifications"
                       class="relative h-9 w-9 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center hover:bg-gray-100 transition-colors">
                        <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17H9m0 0-3-9a6 6 0 1 1 12 0l-3 9m-6 0v1a3 3 0 0 0 6 0v-1"/>
                        </svg>
                        @if ($pendingNotifications > 0)
                            <span class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-rose-500 text-white text-[9px] font-bold flex items-center justify-center leading-none">
                                {{ $pendingNotifications > 9 ? '9+' : $pendingNotifications }}
                            </span>
                        @endif
                    </a>

                    {{-- Logout --}}
                    <button @click="logoutConfirm = true"
                            class="flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4M14 16l4-4-4-4M18 12H9"/>
                        </svg>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 overflow-auto">
            <div class="px-4 lg:px-6 py-6 space-y-6">

                {{-- Flash Messages --}}
                @if (session('success') || session('error') || $errors->any())
                    <div class="space-y-2">
                        @if (session('success'))
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ session('success') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                {{ session('error') }}
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 flex items-start gap-2">
                                <svg class="h-4 w-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <div>
                                    @foreach ($errors->all() as $error)
                                        <p>{{ $error }}</p>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>

    {{-- Logout Confirm Modal (single, shared) --}}
    <div role="dialog"
         aria-modal="true"
         x-show="logoutConfirm"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         @click.self="logoutConfirm = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
            <div class="h-12 w-12 rounded-2xl bg-rose-50 flex items-center justify-center mx-auto mb-4">
                <svg class="h-6 w-6 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4M14 16l4-4-4-4M18 12H9"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-1 text-center">Confirm Logout</h3>
            <p class="text-sm text-gray-500 mb-5 text-center">Are you sure you want to log out of the admin panel?</p>
            <div class="flex justify-end gap-2">
                <button @click="logoutConfirm = false"
                        class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="px-4 py-2 rounded-lg bg-rose-500 text-white text-sm font-medium hover:bg-rose-600">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal overlay lock — keeps background non-interactive when any dialog is open --}}
<script>
(function () {
    var scrollY = 0;

    function lockBody() {
        scrollY = window.scrollY || 0;
        document.body.style.overflow   = 'hidden';
        document.body.style.position   = 'fixed';
        document.body.style.top        = '-' + scrollY + 'px';
        document.body.style.width      = '100%';
        document.body.classList.add('modal-open');
    }

    function unlockBody() {
        document.body.style.overflow  = '';
        document.body.style.position  = '';
        document.body.style.top       = '';
        document.body.style.width     = '';
        document.body.classList.remove('modal-open');
        window.scrollTo(0, scrollY);
    }

    function anyModalOpen() {
        var dialogs = document.querySelectorAll('[role="dialog"]');
        for (var i = 0; i < dialogs.length; i++) {
            var d = dialogs[i];
            if (d.hasAttribute('x-cloak'))          continue; // pre-Alpine init
            if (d.style.display === 'none')          continue; // Alpine x-show hidden
            if (d.classList.contains('hidden'))      continue; // Tailwind hidden class
            if (getComputedStyle(d).display === 'none') continue; // any other hidden
            return true;
        }
        return false;
    }

    function syncLock() {
        if (anyModalOpen()) {
            lockBody();
        } else {
            unlockBody();
        }
    }

    // Watch style/attribute changes on all descendants so we catch Alpine x-show toggling
    var observer = new MutationObserver(function (mutations) {
        for (var i = 0; i < mutations.length; i++) {
            var m = mutations[i];
            if (m.type === 'attributes' &&
                (m.attributeName === 'style' || m.attributeName === 'x-cloak' || m.attributeName === 'class')) {
                var el = m.target;
                if (el.getAttribute('role') === 'dialog') {
                    syncLock();
                    return;
                }
            }
            // Catch new dialogs added to DOM
            if (m.type === 'childList' && m.addedNodes.length) {
                syncLock();
                return;
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        observer.observe(document.body, {
            subtree: true,
            attributes: true,
            attributeFilter: ['style', 'x-cloak', 'class'],
            childList: true,
        });
        // Also close on Escape globally
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                setTimeout(syncLock, 50);
            }
        });
    });
})();
</script>

{{-- Sidebar scroll persistence --}}
<script>
(function () {
    var nav = document.getElementById('sidebarNav');
    if (!nav) return;

    var key = 'bm_sidebar_scroll';
    var saved = sessionStorage.getItem(key);
    if (saved !== null) {
        nav.scrollTop = parseInt(saved, 10) || 0;
    }

    window.addEventListener('beforeunload', function () {
        sessionStorage.setItem(key, nav.scrollTop);
    });

    // Also save on every click of sidebar links so it's captured even on fast navigations
    nav.addEventListener('click', function (e) {
        var link = e.target.closest('a[href]');
        if (link) {
            sessionStorage.setItem(key, nav.scrollTop);
        }
    });
})();
</script>

{{-- Weather Widget --}}
<script>
(function () {
    var tempEl = document.getElementById('sidebarWeatherTemp');
    var descEl = document.getElementById('sidebarWeatherDesc');
    var iconEl = document.getElementById('weatherIcon');
    if (!tempEl || !descEl) return;

    var weatherDescs = {
        0: 'Clear sky', 1: 'Mainly clear', 2: 'Partly cloudy', 3: 'Overcast',
        45: 'Foggy', 48: 'Icy fog',
        51: 'Light drizzle', 53: 'Drizzle', 55: 'Heavy drizzle',
        61: 'Slight rain', 63: 'Rain', 65: 'Heavy rain',
        71: 'Slight snow', 73: 'Snow', 75: 'Heavy snow',
        80: 'Rain showers', 81: 'Showers', 82: 'Heavy showers',
        95: 'Thunderstorm', 96: 'Thunderstorm + hail', 99: 'Thunderstorm + hail'
    };

    var sunnyIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 100 10A5 5 0 0012 7z"/>';
    var cloudIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>';
    var rainIcon  = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15zM8 19l-.5 2M12 19l-.5 2M16 19l-.5 2"/>';
    var stormIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>';

    function getIcon(code) {
        if (code === 0 || code === 1) return { svg: sunnyIcon, color: 'text-yellow-400', bg: 'bg-yellow-50' };
        if (code >= 95) return { svg: stormIcon, color: 'text-purple-500', bg: 'bg-purple-50' };
        if (code >= 51 && code <= 82) return { svg: rainIcon, color: 'text-blue-400', bg: 'bg-blue-50' };
        return { svg: cloudIcon, color: 'text-blue-400', bg: 'bg-blue-50' };
    }

    function updateIcon(iconData) {
        if (!iconEl) return;
        var parent = iconEl.parentElement;
        if (parent) parent.className = 'h-8 w-8 rounded-lg flex items-center justify-center shrink-0 ' + iconData.bg;
        iconEl.className = 'h-4 w-4 ' + iconData.color;
        iconEl.innerHTML = iconData.svg;
    }

    function loadWeather(lat, lon) {
        fetch('https://api.open-meteo.com/v1/forecast?latitude=' + lat + '&longitude=' + lon + '&current=temperature_2m,weathercode&timezone=Asia%2FManila')
            .then(function (r) { return r.json(); })
            .then(function (d) {
                var temp = Math.round(d.current.temperature_2m);
                var code = d.current.weathercode;
                tempEl.textContent = temp + '°C';
                descEl.textContent = weatherDescs[code] || 'Partly cloudy';
                updateIcon(getIcon(code));
            })
            .catch(function () {
                var widget = document.getElementById('sidebarWeather');
                if (widget) widget.classList.add('hidden');
            });
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (pos) { loadWeather(pos.coords.latitude.toFixed(4), pos.coords.longitude.toFixed(4)); },
            function () { loadWeather(8.4542, 124.6319); }
        );
    } else {
        loadWeather(8.4542, 124.6319);
    }
})();
</script>
