<?php

test('machine learning insights are exposed only in the admin workspace', function () {
    $projectRoot = dirname(__DIR__, 2);
    $routes = file_get_contents($projectRoot.'/routes/web.php');
    $controller = file_get_contents($projectRoot.'/app/Http/Controllers/PredictiveInsightsController.php');
    $userSidebar = file_get_contents($projectRoot.'/resources/views/components/sidebar/user-panel.blade.php');
    $adminSidebar = file_get_contents($projectRoot.'/resources/views/components/sidebar/admin-panel.blade.php');

    expect(file_exists($projectRoot.'/resources/views/user/predictive-insights.blade.php'))->toBeFalse()
        ->and(substr_count($routes, "Route::get('/predictive-insights'"))->toBe(1)
        ->and($routes)->toContain("name('insights.index')")
        ->and($controller)
        ->toContain('abort_unless($user?->isSuperAdmin(), 403)')
        ->toContain("return view('admin.predictive-insights', \$data)")
        ->and($userSidebar)
        ->not->toContain("'label' => 'ML Insights'")
        ->not->toContain('user.insights.index')
        ->and($adminSidebar)
        ->toContain("'label' => 'ML Insights'")
        ->toContain("\$ownerHidden = ['transactions', 'insights', 'reports']");
});
