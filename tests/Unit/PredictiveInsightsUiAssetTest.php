<?php

test('predictive insights presents simple complete records and optional AI analysis', function () {
    $projectRoot = dirname(__DIR__, 2);
    $view = file_get_contents($projectRoot.'/resources/views/shared/predictive-insights-content.blade.php');
    $controller = file_get_contents($projectRoot.'/app/Http/Controllers/PredictiveInsightsController.php');

    expect($view)
        ->toContain('data-simple-predictive-insights')
        ->toContain('ML Insights')
        ->toContain('Live database records')
        ->toContain('Historical trend')
        ->toContain('Monthly records')
        ->toContain('Highest demand')
        ->toContain('AI connected:')
        ->toContain("\$ai['reason']")
        ->toContain('AI explains the calculated results')
        ->not->toContain('System actions')
        ->not->toContain('How it works:')
        ->and($controller)
        ->toContain("\$data['aiInsights']")
        ->toContain('analyzePredictiveInsights')
        ->toContain('Cache::put')
        ->toContain('now()->addMinutes(15)');
});
