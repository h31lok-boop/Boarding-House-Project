<?php

test('admin notification dialogs use the browser top layer with a compact panel', function () {
    $projectRoot = dirname(__DIR__, 2);
    $view = file_get_contents($projectRoot.'/resources/views/admin/notifications.blade.php');
    $css = file_get_contents($projectRoot.'/resources/css/app.css');

    expect($view)
        ->toContain('data-admin-notification-compose-modal')
        ->toContain('data-admin-notification-detail-modal')
        ->toContain('<dialog')
        ->toContain('data-native-modal')
        ->toContain('$el.showModal()')
        ->toContain('class="bm-native-dialog"')
        ->toContain('bm-modal--notification')
        ->toContain('bm-notification-form-grid')
        ->toContain('@click.self="sendOpen = false"')
        ->toContain('@click.self="detailOpen = false"')
        ->toContain('aria-modal="true"');

    expect(substr_count($view, '<dialog'))->toBe(2)
        ->and($css)
        ->toContain('.bm-native-dialog::backdrop')
        ->toContain('.bm-native-dialog:not([open])')
        ->toContain('background: rgba(2, 6, 23, 0.84)')
        ->toContain('.bm-modal--notification')
        ->toContain('width: min(100%, 44rem)');
});
