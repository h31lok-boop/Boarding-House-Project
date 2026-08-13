<?php

use App\Services\PredictiveAnalyticsService;
use Tests\TestCase;

uses(TestCase::class);

test('linear regression projects a rising historical series', function () {
    $forecast = app(PredictiveAnalyticsService::class)->forecast([2, 4, 6, 8, 10, 12], 0);

    expect($forecast['prediction'])->toBe(14.0)
        ->and($forecast['direction'])->toBe('up')
        ->and($forecast['confidence'])->toBeGreaterThanOrEqual(90);
});

test('forecast respects percentage boundaries', function () {
    $forecast = app(PredictiveAnalyticsService::class)->forecast([80, 90, 95, 100], 0, 100);

    expect($forecast['prediction'])->toBe(100.0)
        ->and($forecast['direction'])->toBe('up');
});

test('forecast safely handles an empty data set', function () {
    $forecast = app(PredictiveAnalyticsService::class)->forecast([], 0, 100);

    expect($forecast)->toMatchArray([
        'prediction' => 0.0,
        'slope' => 0.0,
        'direction' => 'stable',
        'confidence' => 0,
    ]);
});
