@php
    $navBase = 'flex items-center gap-2 px-3 py-2 rounded-lg';
    $navActive = $navBase . ' ui-surface-2 text-[color:var(--text)] font-medium border ui-border';
    $navInactive = $navBase . ' text-[color:var(--muted)] hover:bg-[color:var(--surface-2)]';
@endphp

<nav class="flex-1 space-y-4 text-sm sidebar-nav">
    <div>
        <p class="text-xs uppercase ui-muted mb-2 sidebar-group">Overview</p>
        <a href="{{ route('tenant.dashboard') }}" class="{{ request()->routeIs('tenant.dashboard') ? $navActive : $navInactive }}">
            <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 10l9-7 9 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M5 10v10h14V10"/></svg></span>
            <span class="sidebar-text">Dashboard</span>
        </a>
    </div>
    <div>
        <p class="text-xs uppercase ui-muted mb-2 sidebar-group">Services</p>
        <a href="{{ route('tenant.boarding-houses') }}" class="{{ request()->routeIs('tenant.boarding-houses') ? $navActive : $navInactive }}">
            <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 10l9-7 9 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M5 10v10h14V10"/></svg></span>
            <span class="sidebar-text">Boarding Houses</span>
        </a>
        <a href="{{ route('user.favorites.index') }}" class="{{ request()->routeIs('user.favorites.*') ? $navActive : $navInactive }}">
            <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="m12 21-1.4-1.3C5.4 14.9 2 11.8 2 8a5 5 0 0 1 8.5-3.5L12 6l1.5-1.5A5 5 0 0 1 22 8c0 3.8-3.4 6.9-8.6 11.7L12 21Z"/></svg></span>
            <span class="sidebar-text">Favorites</span>
        </a>
        <a href="{{ route('tenant.bh-policies') }}" class="{{ request()->routeIs('tenant.bh-policies') ? $navActive : $navInactive }}">
            <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M6 3h9l5 5v13H6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 3v5h5"/></svg></span>
            <span class="sidebar-text">Policies</span>
        </a>
        <a href="{{ route('tenant.match-profile.edit') }}" class="{{ request()->routeIs('tenant.match-profile.*') ? $navActive : $navInactive }}">
            <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 3 4 7v6c0 5 3.4 7.8 8 8 4.6-.2 8-3 8-8V7l-8-4Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 12h6M12 9v6"/></svg></span>
            <span class="sidebar-text">Match Profile</span>
        </a>
        <a href="{{ route('tenant.matches.index') }}" class="{{ request()->routeIs('tenant.matches.*') ? $navActive : $navInactive }}">
            <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 12h8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 8v8"/><circle cx="12" cy="12" r="9" stroke-width="1.6"/></svg></span>
            <span class="sidebar-text">Matches</span>
        </a>
        <a href="{{ route('tenant.match-requests.index') }}" class="{{ request()->routeIs('tenant.match-requests.*') ? $navActive : $navInactive }}">
            <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 7h16M4 12h10M4 17h7"/></svg></span>
            <span class="sidebar-text">Match Requests</span>
        </a>
        <a href="{{ route('tenant.account') }}" class="{{ request()->routeIs('tenant.account*') ? $navActive : $navInactive }}">
            <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 20a8 8 0 0 1 16 0"/></svg></span>
            <span class="sidebar-text">Account</span>
        </a>
    </div>
</nav>

<p class="text-xs ui-muted mt-4 sidebar-footer">© 2026 Boarding House</p>
