<?php

test('admin reports keeps complete data in a simplified layout', function () {
    $projectRoot = dirname(__DIR__, 2);
    $view = file_get_contents($projectRoot.'/resources/views/admin/reports.blade.php');
    $controller = file_get_contents($projectRoot.'/app/Http/Controllers/AdminOwnerController.php');

    expect($view)
        ->toContain('data-simple-reports-dashboard')
        ->toContain('$kpiCards as $card')
        ->toContain("\$card['label']")
        ->toContain('Revenue trend')
        ->toContain('Booking status')
        ->toContain('Property performance')
        ->toContain('Export CSV')
        ->not->toContain('Operational Insights')
        ->not->toContain('Top-Performing Boarding Houses')
        ->not->toContain('Recent Activities')
        ->and($controller)
        ->toContain("'label' => 'Total Revenue'")
        ->toContain("'label' => 'Total Bookings'")
        ->toContain("'label' => 'Active Tenants'")
        ->toContain("'label' => 'Occupancy Rate'");
});
