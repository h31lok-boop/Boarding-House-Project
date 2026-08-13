<?php

test('admin dashboard statistics use one dark surface with accent colors', function () {
    $projectRoot = dirname(__DIR__, 2);
    $dashboard = file_get_contents($projectRoot.'/resources/views/admin/dashboard.blade.php');

    expect(substr_count($dashboard, 'data-admin-stat-card'))->toBe(7)
        ->and(substr_count($dashboard, 'dark:bg-slate-900'))->toBeGreaterThanOrEqual(7)
        ->and($dashboard)
        ->toContain('dark:border-amber-400/35')
        ->toContain('dark:border-rose-400/35')
        ->toContain('dark:border-blue-400/35')
        ->toContain('dark:text-amber-300')
        ->toContain('dark:text-rose-300')
        ->toContain('dark:text-blue-300');
});

test('admin dashboard and workspace header have deliberate mobile layouts', function () {
    $projectRoot = dirname(__DIR__, 2);
    $dashboard = file_get_contents($projectRoot.'/resources/views/admin/dashboard.blade.php');
    $shell = file_get_contents($projectRoot.'/resources/views/components/admin/shell.blade.php');
    $css = file_get_contents($projectRoot.'/resources/css/app.css');

    expect($dashboard)
        ->toContain('data-admin-stats-grid')
        ->toContain('grid-cols-1')
        ->toContain('min-[420px]:grid-cols-2')
        ->toContain('min-[420px]:col-span-2')
        ->and($shell)
        ->toContain('admin-workspace-header')
        ->toContain('admin-header-actions')
        ->toContain('aria-label="Open navigation menu"')
        ->toContain('aria-label="Open search"')
        ->and($css)
        ->toContain('@media (max-width: 479px)')
        ->toContain('.admin-shell .admin-header-ai');
});
