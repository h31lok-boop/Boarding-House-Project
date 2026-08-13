<?php

test('admin dashboard uses four focused summary cards with dark mode support', function () {
    $projectRoot = dirname(__DIR__, 2);
    $dashboard = file_get_contents($projectRoot.'/resources/views/admin/dashboard.blade.php');

    expect($dashboard)
        ->toContain("'Boarding Houses'")
        ->toContain("'Occupancy'")
        ->toContain("'Active Tenants'")
        ->toContain("'Collected Revenue'")
        ->toContain('xl:grid-cols-4')
        ->and(substr_count($dashboard, 'dark:bg-slate-900'))->toBeGreaterThanOrEqual(4)
        ->and($dashboard)
        ->toContain('dark:text-amber-300')
        ->toContain('dark:text-emerald-300')
        ->toContain('dark:text-violet-300')
        ->toContain('dark:text-blue-300');
});

test('admin dashboard and workspace header have deliberate mobile layouts', function () {
    $projectRoot = dirname(__DIR__, 2);
    $dashboard = file_get_contents($projectRoot.'/resources/views/admin/dashboard.blade.php');
    $shell = file_get_contents($projectRoot.'/resources/views/components/admin/shell.blade.php');
    $css = file_get_contents($projectRoot.'/resources/css/app.css');

    expect($dashboard)
        ->toContain('sm:grid-cols-2')
        ->toContain('xl:grid-cols-4')
        ->toContain('flex flex-col')
        ->and($shell)
        ->toContain('admin-workspace-header')
        ->toContain('admin-header-actions')
        ->toContain('aria-label="Open navigation menu"')
        ->not->toContain('aria-label="Open search"')
        ->and($css)
        ->toContain('@media (max-width: 479px)')
        ->toContain('.admin-shell .admin-header-ai');
});
