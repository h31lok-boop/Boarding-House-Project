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
