@php
    $r = fn ($name, $params = [], $fallback = null) => \Illuminate\Support\Facades\Route::has($name)
        ? route($name, $params)
        : ($fallback ?? url()->current());

    $userName = 'Juan Dela Cruz';
    $userRole = 'Admin';
    $initials = 'JD';
    $profileImage = null;
    $profileHref = $r('admin.profile', [], $r('owner.profile'));
    $editProfileHref = $profileHref.'#personal-information';
    $settingsHref = $r('admin.settings', [], $r('owner.settings', [], $profileHref));
    $notificationSettingsHref = $settingsHref.'#notification-preferences';
    $helpSupportHref = $settingsHref.'#help-support';

    $navBase = 'group flex items-center gap-3 rounded-xl border px-3.5 py-3 text-sm transition-all duration-200';
    $navActive = $navBase.' border-transparent bg-[#22c55e] text-white shadow-lg shadow-emerald-950/30';
    $navInactive = $navBase.' border-transparent text-white/90 hover:bg-white/10 hover:text-white';

    $links = [
        [
            'label' => 'Dashboard',
            'href' => $r('admin.dashboard', [], $r('owner.dashboard')),
            'active' => request()->routeIs('admin.dashboard') || request()->routeIs('owner.dashboard'),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 11.5 12 4l8 7.5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M7 10v10h10V10"/></svg>',
        ],
        [
            'label' => 'Listings',
            'href' => $r('admin.listings', [], $r('owner.boarding-houses')),
            'active' => request()->routeIs('admin.listings*') || request()->routeIs('owner.boarding-houses*'),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 19V8.5L12 4l8 4.5V19"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 19v-4h6v4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 10h.01M12 10h.01M16 10h.01"/></svg>',
        ],
        [
            'label' => 'Rooms',
            'href' => $r('admin.rooms', [], $r('owner.rooms')),
            'active' => request()->routeIs('admin.rooms*') || request()->routeIs('owner.rooms*'),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 11h16v9H4z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 11V7a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 20v-3h4v3"/></svg>',
        ],
        [
            'label' => 'Inquiries',
            'href' => $r('admin.inquiries.index', [], $r('owner.inquiries.index')),
            'active' => request()->routeIs('admin.inquiries.*') || request()->routeIs('owner.inquiries.*'),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6 8h12M6 12h8M4 5h16a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H9l-5 4V6a1 1 0 0 1 1-1Z"/></svg>',
        ],
        [
            'label' => 'Messages',
            'href' => $r('admin.messages', [], $r('owner.messages', [], $r('admin.inquiries.index'))),
            'active' => request()->routeIs('admin.messages*') || request()->routeIs('owner.messages*'),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 5h16a1.5 1.5 0 0 1 1.5 1.5v9A1.5 1.5 0 0 1 20 17H8l-4 4V6.5A1.5 1.5 0 0 1 5.5 5Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m7 8 5 4 5-4"/></svg>',
        ],
        [
            'label' => 'Compliance',
            'href' => $r('admin.compliance.index', [], $r('owner.compliance.index')),
            'active' => request()->routeIs('admin.compliance.*') || request()->routeIs('owner.compliance.*'),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 3l7 4v5c0 4.5-2.7 7.9-7 9-4.3-1.1-7-4.5-7-9V7l7-4Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m9 12 2 2 4-4"/></svg>',
        ],
        [
            'label' => 'Reviews',
            'href' => $r('admin.reviews', [], $r('owner.feedback.index')),
            'active' => request()->routeIs('admin.reviews*') || request()->routeIs('owner.feedback.*'),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 3 14.8 8.6l6.2.9-4.5 4.4 1.1 6.1L12 17l-5.6 3 1.1-6.1L3 9.5l6.2-.9L12 3Z"/></svg>',
        ],
        [
            'label' => 'Reports',
            'href' => $r('admin.reports', [], $r('owner.reports', [], $r('admin.dashboard'))),
            'active' => request()->routeIs('admin.reports*') || request()->routeIs('owner.reports*'),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 19V5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v14"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 16v-5m4 5V8m4 8v-3M3 20h18"/></svg>',
        ],
        [
            'label' => 'Settings',
            'href' => $r('admin.settings', [], $r('owner.settings', [], $r('admin.profile'))),
            'active' => request()->routeIs('admin.settings*') || request()->routeIs('admin.profile*') || request()->routeIs('owner.settings*') || request()->routeIs('owner.profile*') || request()->routeIs('profile.*'),
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19.4 15a1.8 1.8 0 0 0 .36 2l.05.05a2.1 2.1 0 0 1-2.97 2.97l-.05-.05a1.8 1.8 0 0 0-2-.36 1.8 1.8 0 0 0-1.09 1.65V21a2.1 2.1 0 0 1-4.2 0v-.08a1.8 1.8 0 0 0-1.09-1.65 1.8 1.8 0 0 0-2 .36l-.05.05a2.1 2.1 0 0 1-2.97-2.97l.05-.05a1.8 1.8 0 0 0 .36-2A1.8 1.8 0 0 0 2.15 13H2a2.1 2.1 0 0 1 0-4.2h.08a1.8 1.8 0 0 0 1.65-1.09 1.8 1.8 0 0 0-.36-2l-.05-.05a2.1 2.1 0 0 1 2.97-2.97l.05.05a1.8 1.8 0 0 0 2 .36A1.8 1.8 0 0 0 9.43 1.45V1.4a2.1 2.1 0 0 1 4.2 0v.08a1.8 1.8 0 0 0 1.09 1.65 1.8 1.8 0 0 0 2-.36l.05-.05a2.1 2.1 0 0 1 2.97 2.97l-.05.05a1.8 1.8 0 0 0-.36 2A1.8 1.8 0 0 0 20.85 8.8H21a2.1 2.1 0 0 1 0 4.2h-.08A1.8 1.8 0 0 0 19.4 15Z"/></svg>',
        ],
    ];
@endphp

<div class="flex min-h-0 flex-1 flex-col pt-5">
    <nav class="min-h-0 flex-1 overflow-y-auto pr-1 pb-3 sidebar-nav">
        <div>
            <div class="space-y-1.5">
                @foreach ($links as $link)
                    <a href="{{ $link['href'] }}" @click="sidebarOpen = false" class="{{ $link['active'] ? $navActive : $navInactive }}">
                        <span class="sidebar-icon flex h-5 w-5 items-center justify-center text-current">{!! $link['icon'] !!}</span>
                        <span class="sidebar-text flex-1 truncate font-medium">{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </nav>

    <div class="sidebar-footer relative mt-3 shrink-0" x-data="{ profileOpen: false }" @click.outside="profileOpen = false">
        <x-theme-toggle class="mb-3 flex w-full items-center rounded-2xl border border-white/10 bg-white/10 p-3 text-left text-sm font-semibold text-white transition hover:bg-white/15" show-label prefix="Theme:" />

        <button type="button" @click="profileOpen = ! profileOpen" class="flex w-full items-center gap-3 rounded-2xl border border-white/10 bg-white/10 p-3 text-left text-white transition hover:bg-white/15" aria-haspopup="menu" :aria-expanded="profileOpen.toString()">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-emerald-500 text-xs font-bold text-white">
                @if ($profileImage)
                    <img src="{{ $profileImage }}" alt="{{ $userName }}" class="h-full w-full object-cover">
                @else
                    {{ $initials }}
                @endif
            </span>
            <span class="sidebar-text min-w-0 flex-1">
                <span class="block truncate text-sm font-semibold">{{ $userName }}</span>
                <span class="block truncate text-xs font-medium text-white/60">{{ $userRole }}</span>
            </span>
            <span class="sidebar-text flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white/10 text-white/70 transition" :class="profileOpen ? 'rotate-180' : ''">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
                </svg>
            </span>
        </button>

        <div x-show="profileOpen" style="display: none;" class="absolute bottom-full left-0 z-40 mb-3 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white py-2 text-sm text-slate-700 shadow-2xl shadow-slate-950/20">
            <a href="{{ $profileHref }}" class="flex items-center gap-3 px-3 py-2.5 font-semibold hover:bg-slate-50">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-700">{!! '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>' !!}</span>
                View Profile
            </a>
            <a href="{{ $editProfileHref }}" class="flex items-center gap-3 px-3 py-2.5 font-semibold hover:bg-slate-50">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700">{!! '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m4 20 4.2-1 10-10a2 2 0 0 0-3-3l-10 10L4 20Z"/><path d="m13.5 6.5 4 4"/></svg>' !!}</span>
                Edit Profile
            </a>
            <a href="{{ $settingsHref }}" class="flex items-center gap-3 px-3 py-2.5 font-semibold hover:bg-slate-50">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700">{!! '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.8 1.8 0 0 0 .36 2l.05.05a2.1 2.1 0 0 1-2.97 2.97l-.05-.05a1.8 1.8 0 0 0-2-.36 1.8 1.8 0 0 0-1.09 1.65V21a2.1 2.1 0 0 1-4.2 0v-.08a1.8 1.8 0 0 0-1.09-1.65 1.8 1.8 0 0 0-2 .36l-.05.05a2.1 2.1 0 0 1-2.97-2.97l.05-.05a1.8 1.8 0 0 0 .36-2A1.8 1.8 0 0 0 2.15 13H2a2.1 2.1 0 0 1 0-4.2h.08a1.8 1.8 0 0 0 1.65-1.09 1.8 1.8 0 0 0-.36-2l-.05-.05a2.1 2.1 0 0 1 2.97-2.97l.05.05a1.8 1.8 0 0 0 2 .36A1.8 1.8 0 0 0 9.43 1.45V1.4a2.1 2.1 0 0 1 4.2 0v.08a1.8 1.8 0 0 0 1.09 1.65 1.8 1.8 0 0 0 2-.36l.05-.05a2.1 2.1 0 0 1 2.97 2.97l-.05.05a1.8 1.8 0 0 0-.36 2A1.8 1.8 0 0 0 20.85 8.8H21a2.1 2.1 0 0 1 0 4.2h-.08A1.8 1.8 0 0 0 19.4 15Z"/></svg>' !!}</span>
                Account Settings
            </a>
            <a href="{{ $notificationSettingsHref }}" class="flex items-center gap-3 px-3 py-2.5 font-semibold hover:bg-slate-50">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700">{!! '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 17H9m9-5a6 6 0 1 0-12 0c0 3-1.5 4-2 5h16c-.5-1-2-2-2-5Z"/><path d="M10 20a2 2 0 0 0 4 0"/></svg>' !!}</span>
                Notification Settings
            </a>
            <a href="{{ $helpSupportHref }}" class="flex items-center gap-3 px-3 py-2.5 font-semibold hover:bg-slate-50">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700">{!! '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 4 2c-1 .7-1.5 1.2-1.5 2.5"/><path d="M12 17h.01"/></svg>' !!}</span>
                Help &amp; Support
            </a>
            <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 pt-1">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 px-3 py-2.5 text-left font-semibold text-rose-700 hover:bg-rose-50">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-700">{!! '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>' !!}</span>
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>
