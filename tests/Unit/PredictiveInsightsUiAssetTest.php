<?php

test('predictive insights clearly separates real records from optional AI analysis', function () {
    $projectRoot = dirname(__DIR__, 2);
    $view = file_get_contents($projectRoot.'/resources/views/shared/predictive-insights-content.blade.php');
    $controller = file_get_contents($projectRoot.'/app/Http/Controllers/PredictiveInsightsController.php');

    expect($view)
        ->toContain('Real Data + AI')
        ->toContain('Live database records')
        ->toContain('Monthly records')
        ->toContain('AI connected:')
        ->toContain("\$ai['reason']")
        ->toContain('AI explains verified results only')
        ->and($controller)
        ->toContain("\$data['aiInsights']")
        ->toContain('analyzePredictiveInsights')
        ->toContain('Cache::put')
        ->toContain('now()->addMinutes(15)');
});
