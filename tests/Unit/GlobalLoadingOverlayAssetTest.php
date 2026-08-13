<?php

test('global loading overlay uses BoardMatch branding and excludes messaging routes', function () {
    $projectRoot = dirname(__DIR__, 2);
    $javascript = file_get_contents($projectRoot.'/resources/js/app.js');
    $stylesheet = file_get_contents($projectRoot.'/resources/css/app.css');
    $landing = file_get_contents($projectRoot.'/resources/views/welcome.blade.php');

    expect($javascript)
        ->toContain('setupGlobalLoadingOverlay')
        ->toContain('/images/boardmatch-final-logo.png')
        ->toContain('const minimumDuration = 1000')
        ->toContain('boardmatch-loading-visible-until')
        ->toContain("dataset.loadingOverlayReady = 'true'")
        ->toContain('[data-loading-overlay="true"]')
        ->not->toContain('boardmatch-loading-ring')
        ->not->toContain('boardmatch-loading-core')
        ->toContain('messagePathPattern')
        ->toContain('data-no-loading-overlay')
        ->toContain('data-messaging-interaction')
        ->and($stylesheet)
        ->toContain('.boardmatch-loading-overlay')
        ->toContain("[data-theme='dark'] .boardmatch-loading-overlay")
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->and($landing)
        ->toContain("@vite(['resources/css/app.css', 'resources/js/app.js'])");
});
