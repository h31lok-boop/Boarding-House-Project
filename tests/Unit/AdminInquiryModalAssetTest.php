<?php

test('admin inquiry dialogs use the shared compact modal foundation', function () {
    $projectRoot = dirname(__DIR__, 2);
    $view = file_get_contents($projectRoot.'/resources/views/admin/inquiries.blade.php');

    expect($view)
        ->toContain('aria-labelledby="inquiry-details-title"')
        ->toContain('aria-labelledby="inquiry-reply-title"')
        ->toContain('id="inquiry-details-title" class="bm-modal__title"')
        ->toContain('id="inquiry-reply-title" class="bm-modal__title"')
        ->toContain('class="bm-modal bm-modal--notification-detail"')
        ->toContain('class="bm-modal__body bm-modal__body--compact"')
        ->toContain('class="bm-modal__footer"')
        ->toContain('@click.self="viewOpen = false"')
        ->toContain('@click.self="replyOpen = false"');

    expect(substr_count($view, 'data-modal-root'))->toBe(2);
    expect(substr_count($view, 'class="bm-modal__header"'))->toBe(2);
    expect(substr_count($view, 'class="bm-modal__footer"'))->toBe(2);
});
