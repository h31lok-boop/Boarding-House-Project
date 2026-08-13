<?php

test('dashboard shells use document scrolling on mobile', function () {
    $projectRoot = dirname(__DIR__, 2);
    $css = file_get_contents($projectRoot.'/resources/css/app.css');

    expect($css)
        ->toContain('.user-shell {')
        ->toContain('.admin-shell {')
        ->toContain('overflow-y: visible;')
        ->toContain('-webkit-overflow-scrolling: touch;')
        ->toContain('padding-bottom: env(safe-area-inset-bottom, 0px);');
});

test('modal isolation releases stale scroll locks and uses one modal scroll owner', function () {
    $projectRoot = dirname(__DIR__, 2);
    $css = file_get_contents($projectRoot.'/resources/css/app.css');
    $javascript = file_get_contents($projectRoot.'/resources/js/app.js');

    expect($css)
        ->toContain(".bm-modal-overlay[data-modal-active='true']")
        ->toContain('overflow: hidden !important;')
        ->and($javascript)
        ->toContain("el.getAttribute('aria-hidden') === 'true'")
        ->toContain("applyMobileSidebar('closed')")
        ->toContain("window.addEventListener('pageshow', queueModalState)")
        ->toContain("window.addEventListener('pagehide', restoreLockedElements)");
});
