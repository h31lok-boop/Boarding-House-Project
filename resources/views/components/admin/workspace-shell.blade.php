@props([
    'workspace' => null,
    'title' => 'Workspace',
    'subtitle' => null,
    'active' => 'overview',
    'profileRoleLabel' => null,
])

@php
    $workspace = $workspace
        ?? (request()->routeIs('owner.*')
            ? 'owner'
            : (auth()->user()?->isSuperDuperAdmin() ? 'superduperadmin' : 'admin'));
    $workspace = match ($workspace) {
        'superduperadmin', 'owner' => $workspace,
        default => 'admin',
    };

    $r = function (string $name, array $params = [], ?string $fallback = null) {
        if (\Illuminate\Support\Facades\Route::has($name)) {
            return route($name, $params, false);
        }

        return $fallback ?? url()->current();
    };

    $roleLabel = function (?string $role): string {
        return match (strtolower((string) $role)) {
            'superduperadmin', 'owner' => 'Owner',
            'admin', 'caretaker', 'manager' => 'Caretaker',
            'tenant', 'user', 'student' => 'Tenant/Student',
            'validator', 'osas' => 'OSAS',
            default => ucfirst((string) $role ?: 'User'),
        };
    };

    $user = auth()->user();
    $userName = $user?->name ?? (in_array($workspace, ['superduperadmin', 'owner'], true) ? 'Owner' : 'Caretaker');
    $userInitials = collect(preg_split('/\s+/', trim($userName)))
        ->filter()
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->take(2)
        ->implode('');
    $userRole = filled($profileRoleLabel)
        ? $profileRoleLabel
        : match ($workspace) {
            'superduperadmin', 'owner' => 'Owner',
            'admin' => 'Admin / Caretaker',
            default => $roleLabel($user?->role),
        };
    $ownerProfileHref = match ($workspace) {
        'superduperadmin' => \Illuminate\Support\Facades\Route::has('superduperadmin.profile')
            ? route('superduperadmin.profile')
            : null,
        'owner' => \Illuminate\Support\Facades\Route::has('owner.profile')
            ? route('owner.profile')
            : null,
        default => null,
    };
    $isOwnerWorkspace = in_array($workspace, ['superduperadmin', 'owner'], true);
    $ownerDashboardHref = $workspace === 'superduperadmin' ? $r('superduperadmin.dashboard') : $r('owner.dashboard');
    $ownerListingsHref = $workspace === 'superduperadmin' ? $r('superduperadmin.boarding-houses.index') : $r('owner.boarding-houses');
    $ownerRoomsHref = $workspace === 'superduperadmin' ? $r('superduperadmin.rooms', [], $ownerListingsHref) : $r('owner.rooms');
    $ownerInquiriesHref = $workspace === 'superduperadmin' ? $r('superduperadmin.inquiries', [], $ownerDashboardHref) : $r('owner.inquiries.index');
    $ownerMessagesHref = $workspace === 'superduperadmin' ? $r('superduperadmin.messages', [], $ownerDashboardHref) : $r('owner.messages');
    $ownerComplianceHref = $workspace === 'superduperadmin' ? $r('superduperadmin.compliance', [], $ownerListingsHref) : $r('owner.compliance.index');
    $ownerReviewsHref = $workspace === 'superduperadmin' ? $r('superduperadmin.reviews', [], $ownerDashboardHref) : $r('owner.feedback.index');
    $ownerReportsHref = $workspace === 'superduperadmin' ? $r('superduperadmin.reports', [], $ownerDashboardHref) : $r('owner.reports', [], $ownerDashboardHref);
    $ownerSettingsHref = $workspace === 'superduperadmin' ? $r('superduperadmin.settings', [], $ownerProfileHref ?? $ownerDashboardHref) : $r('owner.settings', [], $ownerProfileHref ?? $ownerDashboardHref);
    $ownerUserCardHref = $ownerProfileHref ?? $ownerSettingsHref;
    $ownerEditProfileHref = $ownerUserCardHref ? $ownerUserCardHref.'#personal-information' : $ownerSettingsHref;
    $ownerNotificationSettingsHref = $ownerSettingsHref.'#notification-preferences';
    $ownerHelpSupportHref = $ownerSettingsHref.'#help-support';
    $ownerCardName = 'Juan Dela Cruz';
    $ownerCardInitials = 'JD';

    $navItems = $workspace === 'superduperadmin'
        ? [
            ['key' => 'overview', 'label' => 'Dashboard', 'href' => $ownerDashboardHref, 'icon' => 'home', 'active' => request()->routeIs('superduperadmin.dashboard')],
            ['key' => 'listings', 'label' => 'My Listings', 'href' => $ownerListingsHref, 'icon' => 'building', 'active' => request()->routeIs('superduperadmin.boarding-houses.*')],
            ['key' => 'rooms', 'label' => 'Rooms', 'href' => $ownerRoomsHref, 'icon' => 'door', 'active' => request()->routeIs('superduperadmin.rooms')],
            ['key' => 'inquiries', 'label' => 'Inquiries', 'href' => $ownerInquiriesHref, 'icon' => 'chat', 'active' => request()->routeIs('superduperadmin.inquiries')],
            ['key' => 'messages', 'label' => 'Messages', 'href' => $ownerMessagesHref, 'icon' => 'mail', 'active' => request()->routeIs('superduperadmin.messages')],
            ['key' => 'compliance', 'label' => 'OSAS Compliance', 'href' => $ownerComplianceHref, 'icon' => 'shield', 'active' => request()->routeIs('superduperadmin.compliance')],
            ['key' => 'reviews', 'label' => 'Reviews', 'href' => $ownerReviewsHref, 'icon' => 'star', 'active' => request()->routeIs('superduperadmin.reviews')],
            ['key' => 'reports', 'label' => 'Reports', 'href' => $ownerReportsHref, 'icon' => 'chart', 'active' => request()->routeIs('superduperadmin.reports')],
            ['key' => 'settings', 'label' => 'Settings', 'href' => $ownerSettingsHref, 'icon' => 'settings', 'active' => request()->routeIs('superduperadmin.settings') || request()->routeIs('superduperadmin.profile')],
        ]
        : ($workspace === 'owner'
            ? [
                ['key' => 'overview', 'label' => 'Dashboard', 'href' => $ownerDashboardHref, 'icon' => 'home', 'active' => request()->routeIs('owner.dashboard')],
                ['key' => 'listings', 'label' => 'My Listings', 'href' => $ownerListingsHref, 'icon' => 'building', 'active' => request()->routeIs('owner.boarding-houses*')],
                ['key' => 'rooms', 'label' => 'Rooms', 'href' => $ownerRoomsHref, 'icon' => 'door', 'active' => request()->routeIs('owner.rooms*')],
                ['key' => 'inquiries', 'label' => 'Inquiries', 'href' => $ownerInquiriesHref, 'icon' => 'chat', 'active' => request()->routeIs('owner.inquiries.*')],
                ['key' => 'messages', 'label' => 'Messages', 'href' => $ownerMessagesHref, 'icon' => 'mail', 'active' => request()->routeIs('owner.messages*')],
                ['key' => 'compliance', 'label' => 'OSAS Compliance', 'href' => $ownerComplianceHref, 'icon' => 'shield', 'active' => request()->routeIs('owner.compliance.*')],
                ['key' => 'reviews', 'label' => 'Reviews', 'href' => $ownerReviewsHref, 'icon' => 'star', 'active' => request()->routeIs('owner.feedback.*')],
                ['key' => 'reports', 'label' => 'Reports', 'href' => $ownerReportsHref, 'icon' => 'chart', 'active' => request()->routeIs('owner.reports*')],
                ['key' => 'settings', 'label' => 'Settings', 'href' => $r('owner.settings', [], $ownerSettingsHref), 'icon' => 'settings', 'active' => request()->routeIs('owner.settings*') || request()->routeIs('owner.profile*') || request()->routeIs('profile.*')],
            ]
        : [
            ['key' => 'overview', 'label' => 'Dashboard', 'href' => $r('admin.dashboard'), 'icon' => 'grid'],
            ['key' => 'users', 'label' => 'Manage Users', 'href' => $r('admin.users'), 'icon' => 'users'],
            ['key' => 'history', 'label' => 'Tenant History', 'href' => $r('admin.tenant-history'), 'icon' => 'history'],
        ]);

    $icon = function (string $name): string {
        return match ($name) {
            'grid' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg>',
            'building' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 21V7.5L12 3l8 4.5V21"/><path d="M9 21v-4h6v4"/><path d="M8 10h.01M12 10h.01M16 10h.01"/></svg>',
            'door' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 21h12"/><path d="M8 21V5.5A1.5 1.5 0 0 1 9.2 4l6-1.5A1.5 1.5 0 0 1 17 3.95V21"/><path d="M12.5 12.5h.01"/></svg>',
            'chat' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H9l-5 4V6a1 1 0 0 1 1-1Z"/><path d="M7 9h10M7 12h6"/></svg>',
            'mail' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>',
            'shield' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 19 7v5c0 4.5-2.7 7.9-7 9-4.3-1.1-7-4.5-7-9V7l7-4Z"/><path d="m9 12 2 2 4-4"/></svg>',
            'star' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 2.8 5.6 6.2.9-4.5 4.4 1.1 6.1L12 17l-5.6 3 1.1-6.1L3 9.5l6.2-.9L12 3Z"/></svg>',
            'chart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v14"/><path d="M8 16v-5m4 5V8m4 8v-3M3 20h18"/></svg>',
            'settings' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.8 1.8 0 0 0 .36 2l.05.05a2.1 2.1 0 0 1-2.97 2.97l-.05-.05a1.8 1.8 0 0 0-2-.36 1.8 1.8 0 0 0-1.09 1.65V21a2.1 2.1 0 0 1-4.2 0v-.08a1.8 1.8 0 0 0-1.09-1.65 1.8 1.8 0 0 0-2 .36l-.05.05a2.1 2.1 0 0 1-2.97-2.97l.05-.05a1.8 1.8 0 0 0 .36-2A1.8 1.8 0 0 0 2.15 13H2a2.1 2.1 0 0 1 0-4.2h.08a1.8 1.8 0 0 0 1.65-1.09 1.8 1.8 0 0 0-.36-2l-.05-.05a2.1 2.1 0 0 1 2.97-2.97l.05.05a1.8 1.8 0 0 0 2 .36A1.8 1.8 0 0 0 9.43 1.45V1.4a2.1 2.1 0 0 1 4.2 0v.08a1.8 1.8 0 0 0 1.09 1.65 1.8 1.8 0 0 0 2-.36l.05-.05a2.1 2.1 0 0 1 2.97 2.97l-.05.05a1.8 1.8 0 0 0-.36 2A1.8 1.8 0 0 0 20.85 8.8H21a2.1 2.1 0 0 1 0 4.2h-.08A1.8 1.8 0 0 0 19.4 15Z"/></svg>',
            'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            'history' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 3v5h5"/><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"/><path d="M12 7v5l3 3"/></svg>',
            'menu' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16"/></svg>',
            'theme' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v2.5M12 18.5V21M4.93 4.93l1.77 1.77M17.3 17.3l1.77 1.77M3 12h2.5M18.5 12H21M4.93 19.07l1.77-1.77M17.3 6.7l1.77-1.77"/><circle cx="12" cy="12" r="4.5"/></svg>',
            'home' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 10.5 9-7 9 7"/><path d="M5 9.9V20a1 1 0 0 0 1 1h4.5v-6h3v6H18a1 1 0 0 0 1-1V9.9"/></svg>',
            'logout' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>',
            default => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/></svg>',
        };
    };
@endphp

<x-layouts.caretaker :title="$title">
    @once
        <style>
            .dashboard-shell {
                min-height: 100vh;
                padding: 0.75rem;
                background: var(--bg);
            }

            .dashboard-shell__inner {
                min-height: calc(100vh - 1.5rem);
            }

            .dashboard-sidebar {
                width: 252px;
                border: 1px solid rgba(231, 224, 216, 0.9);
                background: var(--surface);
                box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            }

            .dashboard-avatar {
                background: #4b5563;
                box-shadow: none;
            }

            .dashboard-nav-link {
                display: flex;
                align-items: center;
                gap: 0.65rem;
                padding: 0.55rem 0.65rem;
                border: 1px solid transparent;
                border-radius: 0.75rem;
                color: var(--muted);
                transition: all 0.18s ease;
            }

            .dashboard-nav-link:hover {
                border-color: rgba(231, 224, 216, 0.75);
                background: rgba(15, 23, 42, 0.04);
                color: var(--text);
            }

            .dashboard-nav-link.is-active {
                border-color: rgba(148, 163, 184, 0.24);
                background: rgba(148, 163, 184, 0.08);
                color: var(--text);
                box-shadow: none;
            }

            .dashboard-nav-icon {
                width: 1.65rem;
                height: 1.65rem;
                flex-shrink: 0;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 0.55rem;
                background: rgba(148, 163, 184, 0.12);
                color: var(--muted);
            }

            .dashboard-nav-icon svg {
                width: 1.05rem;
                height: 1.05rem;
            }

            .dashboard-nav-link.is-active .dashboard-nav-icon {
                background: rgba(148, 163, 184, 0.16);
                color: var(--text);
            }

            .dashboard-shell--owner {
                padding: 0;
            }

            .dashboard-shell--owner .dashboard-shell__inner {
                min-height: 100vh;
            }

            .dashboard-shell--owner .dashboard-sidebar {
                width: 236px;
                height: 100vh;
                min-height: 100vh;
                border: 0;
                border-radius: 0;
                background: linear-gradient(180deg, #071d3a 0%, #082a48 48%, #06172d 100%);
                box-shadow: 16px 0 32px rgba(6, 23, 45, 0.18);
                color: #fff;
            }

            .dashboard-shell--owner .dashboard-owner-brand {
                color: #fff;
                text-decoration: none;
            }

            .dashboard-shell--owner .dashboard-owner-brand-mark {
                display: inline-flex;
                height: 2.35rem;
                width: 2.35rem;
                flex-shrink: 0;
                align-items: center;
                justify-content: center;
                border-radius: 0.85rem;
                border: 1px solid rgba(255, 255, 255, 0.14);
                background: rgba(255, 255, 255, 0.1);
            }

            .dashboard-shell--owner .dashboard-owner-brand-mark svg {
                height: 1.15rem;
                width: 1.15rem;
            }

            .dashboard-shell--owner .dashboard-nav-link {
                gap: 0.75rem;
                border-color: transparent;
                border-radius: 0.85rem;
                padding: 0.68rem 0.8rem;
                color: rgba(255, 255, 255, 0.9);
            }

            .dashboard-shell--owner .dashboard-nav-link:hover {
                border-color: transparent;
                background: rgba(255, 255, 255, 0.1);
                color: #fff;
            }

            .dashboard-shell--owner .dashboard-nav-link.is-active {
                border-color: transparent;
                background: #22c55e;
                color: #fff;
                box-shadow: 0 12px 22px rgba(7, 20, 38, 0.28);
            }

            .dashboard-shell--owner .dashboard-nav-icon,
            .dashboard-shell--owner .dashboard-nav-link.is-active .dashboard-nav-icon {
                height: 1.35rem;
                width: 1.35rem;
                border-radius: 0;
                background: transparent;
                color: currentColor;
            }

            .dashboard-shell--owner .dashboard-nav-icon svg {
                height: 1.08rem;
                width: 1.08rem;
            }

            .dashboard-shell--owner .dashboard-owner-user-card {
                border: 1px solid rgba(255, 255, 255, 0.1);
                background: rgba(255, 255, 255, 0.1);
                color: #fff;
                text-decoration: none;
                transition: background 0.16s ease;
            }

            .dashboard-shell--owner .dashboard-owner-user-card:hover {
                background: rgba(255, 255, 255, 0.15);
            }

            .dashboard-shell--owner .dashboard-owner-avatar {
                background: #22c55e;
            }

            .dashboard-header {
                border: 1px solid rgba(231, 224, 216, 0.88);
                background: var(--surface);
                box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
                overflow: hidden;
            }

            .dashboard-header__body {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                gap: 0.7rem;
                align-items: center;
                padding: 0.7rem 0.95rem;
            }

            .dashboard-header__lead {
                display: flex;
                min-width: 0;
                align-items: center;
                gap: 0.75rem;
            }

            .dashboard-header__copy {
                min-width: 0;
            }

            .dashboard-header__copy h1 {
                margin: 0;
                font-size: 1rem;
                font-weight: 600;
                letter-spacing: -0.02em;
                color: var(--text);
                line-height: 1.2;
            }

            .dashboard-header__copy p {
                margin: 0.2rem 0 0;
                max-width: 52rem;
                color: var(--muted);
                font-size: 0.76rem;
                line-height: 1.35;
            }

            .dashboard-header__controls {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.55rem;
            }

            .dashboard-header__profile {
                display: inline-flex;
                max-width: 100%;
                align-items: center;
                gap: 0.55rem;
                border: 1px solid rgba(231, 224, 216, 0.88);
                background: var(--surface);
                padding: 0.35rem 0.5rem 0.35rem 0.35rem;
                box-shadow: none;
            }

            .dashboard-header__profile-copy {
                min-width: 0;
                line-height: 1.15;
            }

            .dashboard-sidebar-footer {
                gap: 0.45rem;
                padding-top: 0.55rem;
            }

            .dashboard-sidebar-footer-card {
                border-radius: 0.8rem;
                padding: 0.45rem 0.55rem;
            }

            .dashboard-sidebar-footer-avatar {
                height: 1.9rem;
                width: 1.9rem;
                border-radius: 0.8rem;
                font-size: 0.66rem;
            }

            .dashboard-sidebar-footer-title {
                font-size: 0.76rem;
                line-height: 1.2;
            }

            .dashboard-sidebar-footer-subtitle {
                font-size: 0.64rem;
                line-height: 1.2;
            }

            .dashboard-sidebar-footer-action {
                display: flex;
                width: 100%;
                align-items: center;
                gap: 0.55rem;
                border-radius: 0.8rem;
                padding: 0.45rem 0.6rem;
                font-size: 0.76rem;
                line-height: 1.2;
            }

            .dashboard-sidebar-footer-icon {
                height: 1.5rem;
                width: 1.5rem;
                border-radius: 0.55rem;
            }

            .dashboard-sidebar-footer-icon svg {
                width: 0.9rem;
                height: 0.9rem;
            }

            [data-theme='dark'] .dashboard-sidebar,
            [data-theme='dark'] .dashboard-header {
                border-color: rgba(42, 34, 30, 0.92);
                background: rgba(23, 19, 17, 0.96);
                box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
            }

            [data-theme='dark'] .dashboard-nav-link:hover {
                border-color: rgba(42, 34, 30, 0.9);
                background: rgba(255, 255, 255, 0.03);
            }

            [data-theme='dark'] .dashboard-nav-link.is-active {
                border-color: rgba(148, 163, 184, 0.2);
                background: rgba(255, 255, 255, 0.04);
            }

            [data-theme='dark'] .dashboard-nav-icon {
                background: rgba(255, 255, 255, 0.04);
                color: rgba(226, 232, 240, 0.72);
            }

            [data-theme='dark'] .dashboard-nav-link.is-active .dashboard-nav-icon {
                background: rgba(255, 255, 255, 0.08);
                color: rgba(248, 250, 252, 0.94);
            }

            [data-theme='dark'] .dashboard-header__profile {
                border-color: rgba(42, 34, 30, 0.92);
                background: rgba(23, 19, 17, 0.96);
                box-shadow: none;
            }

            [data-theme='dark'] .dashboard-shell--owner .dashboard-sidebar {
                border: 0;
                background: linear-gradient(180deg, #071d3a 0%, #082a48 48%, #06172d 100%);
                box-shadow: 16px 0 32px rgba(0, 0, 0, 0.22);
            }

            [data-theme='dark'] .dashboard-shell--owner .dashboard-nav-link:hover {
                border-color: transparent;
                background: rgba(255, 255, 255, 0.1);
            }

            [data-theme='dark'] .dashboard-shell--owner .dashboard-nav-link.is-active {
                border-color: transparent;
                background: #22c55e;
                color: #fff;
            }

            [data-theme='dark'] .dashboard-shell--owner .dashboard-nav-icon,
            [data-theme='dark'] .dashboard-shell--owner .dashboard-nav-link.is-active .dashboard-nav-icon {
                background: transparent;
                color: currentColor;
            }

            @media (min-width: 1024px) {
                .dashboard-shell {
                    padding: 1rem;
                }

                .dashboard-shell__inner {
                    min-height: calc(100vh - 2rem);
                }

                .dashboard-header__body {
                    grid-template-columns: minmax(0, 1fr) auto;
                    gap: 0.9rem;
                    padding: 0.8rem 1.2rem;
                }

                .dashboard-header__controls {
                    justify-content: flex-end;
                }

                .dashboard-header__copy h1 {
                    font-size: 1.125rem;
                }
            }
        </style>
    @endonce

    <div class="dashboard-shell {{ $isOwnerWorkspace ? 'dashboard-shell--owner' : '' }}" x-data="{ sidebarOpen: false }">
        <div class="dashboard-shell__inner lg:flex {{ $isOwnerWorkspace ? '' : 'lg:gap-4' }}">
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/40 lg:hidden" @click="sidebarOpen = false"></div>

        <aside
            class="dashboard-sidebar fixed inset-y-0 left-0 z-50 flex w-[236px] flex-col px-3 py-4 transition-transform duration-200 lg:sticky lg:top-0 lg:translate-x-0 {{ $isOwnerWorkspace ? '' : 'rounded-r-2xl lg:h-[calc(100vh-2rem)] lg:rounded-2xl' }}"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex items-center justify-between gap-3 px-1">
                @if($isOwnerWorkspace)
                    <a href="{{ $ownerDashboardHref }}" class="dashboard-owner-brand flex min-w-0 items-center gap-2.5">
                        <span class="dashboard-owner-brand-mark">
                            {!! $icon('home') !!}
                        </span>
                        <span class="min-w-0 leading-tight">
                            <span class="block truncate text-[0.88rem] font-semibold tracking-tight">DSSC BOARDING</span>
                            <span class="block truncate pt-0.5 text-[0.65rem] font-medium uppercase text-white/60">HOUSE SYSTEM</span>
                        </span>
                    </a>
                @else
                    <a href="{{ $r('admin.dashboard') }}" class="flex min-w-0 items-center gap-2.5 text-[color:var(--text)] no-underline">
                        <x-application-logo class="h-7 w-7 shrink-0 text-[color:var(--muted)]" style="fill: currentColor;" />
                        <span class="min-w-0">
                            <span class="block truncate text-[15px] font-semibold">Boarding House</span>
                        </span>
                    </a>
                @endif

                <button type="button" class="lg:hidden flex h-10 w-10 items-center justify-center rounded-xl border {{ $isOwnerWorkspace ? 'border-white/15 bg-white/10 text-white hover:bg-white/15' : 'ui-border bg-[color:var(--surface-2)]' }}" @click="sidebarOpen = false">
                    {!! $icon('menu') !!}
                </button>
            </div>

            <div class="mt-5 space-y-1.5 overflow-y-auto">
                @unless($isOwnerWorkspace)
                    <p class="px-3 text-[10px] font-semibold uppercase tracking-[0.18em] ui-muted">Menu</p>
                @endunless
                @foreach($navItems as $item)
                    @php
                        $isActive = $item['active'] ?? ($active === $item['key']);
                    @endphp
                    <a
                        href="{{ $item['href'] }}"
                        class="dashboard-nav-link {{ $isActive ? 'is-active' : '' }}"
                        @click="sidebarOpen = false">
                        <span class="dashboard-nav-icon">
                            {!! $icon($item['icon']) !!}
                        </span>
                        <span class="text-[0.95rem] font-medium">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>

            <div class="dashboard-sidebar-footer mt-auto flex flex-col">
                @if($isOwnerWorkspace)
                    <div class="relative" x-data="{ profileOpen: false }" @click.outside="profileOpen = false">
                        <button type="button" @click="profileOpen = ! profileOpen" class="dashboard-owner-user-card flex w-full items-center gap-3 rounded-2xl p-3 text-left" title="Open Owner Profile" aria-haspopup="menu" :aria-expanded="profileOpen.toString()">
                            <span class="dashboard-owner-avatar flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white">
                                {{ $ownerCardInitials }}
                            </span>
                            <span class="min-w-0 flex-1 leading-tight">
                                <span class="block truncate text-sm font-semibold">{{ $ownerCardName }}</span>
                                <span class="block truncate pt-0.5 text-xs font-medium text-white/60">Owner</span>
                            </span>
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white/10 text-white/70 transition" :class="profileOpen ? 'rotate-180' : ''">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </span>
                        </button>

                        <div x-show="profileOpen" style="display: none;" class="absolute bottom-full left-0 z-40 mb-3 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white py-2 text-sm text-slate-700 shadow-2xl shadow-slate-950/20">
                            <a href="{{ $ownerUserCardHref }}" class="flex items-center gap-3 px-3 py-2.5 font-semibold hover:bg-slate-50">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-700 [&>svg]:h-4 [&>svg]:w-4">{!! $icon('users') !!}</span>
                                View Profile
                            </a>
                            <a href="{{ $ownerEditProfileHref }}" class="flex items-center gap-3 px-3 py-2.5 font-semibold hover:bg-slate-50">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 [&>svg]:h-4 [&>svg]:w-4">{!! $icon('settings') !!}</span>
                                Edit Profile
                            </a>
                            <a href="{{ $ownerSettingsHref }}" class="flex items-center gap-3 px-3 py-2.5 font-semibold hover:bg-slate-50">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 [&>svg]:h-4 [&>svg]:w-4">{!! $icon('settings') !!}</span>
                                Account Settings
                            </a>
                            <a href="{{ $ownerNotificationSettingsHref }}" class="flex items-center gap-3 px-3 py-2.5 font-semibold hover:bg-slate-50">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 [&>svg]:h-4 [&>svg]:w-4">{!! $icon('mail') !!}</span>
                                Notification Settings
                            </a>
                            <a href="{{ $ownerHelpSupportHref }}" class="flex items-center gap-3 px-3 py-2.5 font-semibold hover:bg-slate-50">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 [&>svg]:h-4 [&>svg]:w-4">{!! $icon('chat') !!}</span>
                                Help &amp; Support
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 pt-1">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 px-3 py-2.5 text-left font-semibold text-rose-700 hover:bg-rose-50">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-700 [&>svg]:h-4 [&>svg]:w-4">{!! $icon('logout') !!}</span>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="dashboard-sidebar-footer-card border ui-border bg-[color:var(--surface-2)]/75">
                        <div class="flex items-center gap-2.5">
                            <span class="dashboard-avatar dashboard-sidebar-footer-avatar flex items-center justify-center font-extrabold text-white">
                                {{ $userInitials }}
                            </span>
                            <div class="min-w-0">
                                <p class="dashboard-sidebar-footer-title truncate font-semibold text-[color:var(--text)]">{{ $userName }}</p>
                                <p class="dashboard-sidebar-footer-subtitle truncate ui-muted">{{ $userRole }}</p>
                            </div>
                        </div>
                    </div>

                    <x-theme-toggle class="dashboard-sidebar-footer-action justify-between border ui-border bg-[color:var(--surface)] font-medium text-[color:var(--text)] transition hover:bg-[color:var(--surface-2)]" show-label prefix="Theme" />

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dashboard-sidebar-footer-action border ui-border bg-[color:var(--surface)] font-medium text-[color:var(--text)] transition hover:bg-[color:var(--surface-2)]">
                            <span class="dashboard-sidebar-footer-icon flex items-center justify-center bg-[color:var(--surface-2)] ui-muted">{!! $icon('logout') !!}</span>
                            <span>Logout</span>
                        </button>
                    </form>
                @endif
            </div>
        </aside>

        <main class="min-w-0 flex-1 overflow-x-hidden">
            <header class="dashboard-header rounded-[1.5rem]">
                <div class="dashboard-header__body">
                    <div class="dashboard-header__lead">
                        <button type="button" class="flex h-9 w-9 items-center justify-center rounded-xl border ui-border bg-[color:var(--surface)] lg:hidden" @click="sidebarOpen = true">
                            {!! $icon('menu') !!}
                        </button>
                        <div class="dashboard-header__copy">
                            <h1 class="truncate">{{ $title }}</h1>
                            @if(filled($subtitle))
                                <p>{{ $subtitle }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="dashboard-header__controls">
                        <x-theme-toggle class="dashboard-theme-button" />

                        @isset($actions)
                            {{ $actions }}
                        @endisset

                        @unless($isOwnerWorkspace)
                            <div class="dashboard-header__profile rounded-[1rem]">
                                <span class="dashboard-avatar flex h-8 w-8 items-center justify-center rounded-full text-[10px] font-bold text-white">
                                    {{ $userInitials }}
                                </span>
                                <div class="dashboard-header__profile-copy pr-1">
                                    <p class="text-sm font-medium text-[color:var(--text)]">{{ $userName }}</p>
                                    <p class="text-xs ui-muted">{{ $userRole }}</p>
                                </div>
                            </div>
                        @endunless
                    </div>
                </div>
            </header>

            <div class="min-w-0 space-y-5 px-3 pb-4 pt-3 sm:px-5 lg:px-6">
                @if(session('success'))
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <strong>Validation errors:</strong>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
        </div>
    </div>
</x-layouts.caretaker>
