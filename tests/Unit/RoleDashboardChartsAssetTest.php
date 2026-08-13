<?php

test('each role dashboard includes a designated pie and line graph', function () {
    $projectRoot = dirname(__DIR__, 2);
    $admin = file_get_contents($projectRoot.'/resources/views/admin/dashboard.blade.php');
    $owner = file_get_contents($projectRoot.'/resources/views/owner/dashboard.blade.php');
    $tenant = file_get_contents($projectRoot.'/resources/views/user/dashboard.blade.php');
    $component = file_get_contents($projectRoot.'/resources/views/components/dashboard-chart-pair.blade.php');

    expect($admin)
        ->toContain('id-prefix="admin-dashboard"')
        ->toContain('pie-title="Room Occupancy"')
        ->toContain('line-title="Weekly Revenue"')
        ->and($owner)
        ->toContain('id-prefix="owner-dashboard"')
        ->toContain('pie-title="Room Occupancy"')
        ->toContain('line-title="Revenue Trend"')
        ->and($tenant)
        ->toContain('id-prefix="tenant-dashboard"')
        ->toContain('pie-title="Payment Overview"')
        ->toContain('line-title="Payment History"')
        ->and($component)
        ->toContain("type: 'pie'")
        ->toContain("type: 'line'")
        ->toContain('h-64')
        ->toContain('No records yet')
        ->toContain('No activity yet');
});
