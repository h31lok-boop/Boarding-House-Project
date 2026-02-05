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
        <a href="{{ route('tenant.bh-policies') }}" class="{{ request()->routeIs('tenant.bh-policies') ? $navActive : $navInactive }}">
            <span class="sidebar-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M6 3h9l5 5v13H6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 3v5h5"/></svg></span>
            <span class="sidebar-text">Policies</span>
        </a>
    </div>
</nav>

<p class="text-xs ui-muted mt-4 sidebar-footer">© 2026 Boarding House</p>
