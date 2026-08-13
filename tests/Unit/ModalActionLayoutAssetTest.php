<?php

test('shared modal panels keep stable dimensions with one scrollable body', function () {
    $projectRoot = dirname(__DIR__, 2);
    $css = file_get_contents($projectRoot.'/resources/css/app.css');

    expect($css)
        ->toContain('--bm-modal-block-size: 38rem;')
        ->toContain('--bm-modal-viewport-gap: 2rem;')
        ->toContain('height: min(var(--bm-modal-block-size), calc(100dvh - var(--bm-modal-viewport-gap, 2rem)));')
        ->toContain('.bm-modal--sm {')
        ->toContain('--bm-modal-block-size: 28rem;')
        ->toContain(".bm-modal--lg {\n    --bm-modal-block-size: 46rem;")
        ->toContain(".bm-modal--notification-detail {\n    --bm-modal-block-size: 34rem;")
        ->toContain(".bm-modal--xl {\n    --bm-modal-block-size: 50rem;")
        ->toContain('scrollbar-gutter: stable;')
        ->toContain(".bm-modal__body {\n    flex: 1 1 auto;")
        ->toContain('min-height: 0;')
        ->toContain('overflow-y: auto;')
        ->toContain(".bm-modal__footer {\n    border-top:")
        ->toContain('flex-shrink: 0;')
        ->toContain('position: relative;');
});

test('shared and data driven dialogs use the complete fixed panel structure', function () {
    $projectRoot = dirname(__DIR__, 2);
    $views = [
        'resources/views/admin/users.blade.php',
        'resources/views/admin/compatibility-scores.blade.php',
        'resources/views/admin/reviews.blade.php',
        'resources/views/components/ai-assistant.blade.php',
        'resources/views/components/header-notification-link.blade.php',
        'resources/views/user/notifications.blade.php',
    ];

    foreach ($views as $view) {
        expect(file_get_contents($projectRoot.'/'.$view))
            ->toContain('bm-modal__header')
            ->toContain('bm-modal__body')
            ->toContain('bm-modal__footer');
    }

    expect(file_get_contents($projectRoot.'/resources/views/admin/users.blade.php'))
        ->toContain('class="bm-modal bm-modal--lg"')
        ->toContain("viewTab = 'permit'")
        ->toContain("viewTab = 'photos'")
        ->toContain("viewTab = 'permissions'");
});

test('boarding house details uses the shared modal footer for all actions', function () {
    $projectRoot = dirname(__DIR__, 2);
    $view = file_get_contents($projectRoot.'/resources/views/admin/boarding-houses.blade.php');

    expect($view)
        ->toContain('Boarding House Details')
        ->toContain('data-admin-property-photo-carousel')
        ->toContain('Previous property photo')
        ->toContain('Next property photo')
        ->toContain('A simplified view of the verified property record.')
        ->not->toContain('<h3 class="text-sm font-semibold">Rooms</h3>')
        ->toContain('class="bm-modal__footer items-center justify-between"')
        ->toContain('class="bm-modal__button bm-modal__button--danger"')
        ->toContain('>Delete</button>')
        ->toContain('>Close</button>');
});

test('shared modal delete actions use the same button dimensions as neighboring actions', function () {
    $projectRoot = dirname(__DIR__, 2);
    $views = [
        'resources/views/admin/boarding-houses.blade.php',
        'resources/views/admin/tenant-profiles.blade.php',
        'resources/views/admin/rooms.blade.php',
        'resources/views/owner/property.blade.php',
        'resources/views/admin/services-content.blade.php',
        'resources/views/admin/users.blade.php',
        'resources/views/admin/notifications.blade.php',
        'resources/views/user/reviews.blade.php',
    ];

    foreach ($views as $view) {
        expect(file_get_contents($projectRoot.'/'.$view))
            ->toContain('bm-modal__button--danger');
    }
});

test('legacy long dialogs scroll as a whole instead of pinning actions over content', function () {
    $projectRoot = dirname(__DIR__, 2);
    $views = [
        'resources/views/admin/listings.blade.php',
        'resources/views/admin/my-boarding-house.blade.php',
        'resources/views/admin/reservations.blade.php',
        'resources/views/user/notifications.blade.php',
        'resources/views/user/messages.blade.php',
    ];

    foreach ($views as $view) {
        $contents = file_get_contents($projectRoot.'/'.$view);

        expect($contents)->not->toContain('class="min-h-0 flex-1 overflow-y-auto px-7 py-5"');
    }

    expect(file_get_contents($projectRoot.'/resources/views/admin/listings.blade.php'))
        ->toContain('flex flex-col overflow-x-hidden overflow-y-auto');
    expect(file_get_contents($projectRoot.'/resources/views/admin/my-boarding-house.blade.php'))
        ->toContain('flex flex-col overflow-x-hidden overflow-y-auto');
    expect(file_get_contents($projectRoot.'/resources/views/admin/reservations.blade.php'))
        ->toContain('max-h-[92vh] flex-col overflow-x-hidden overflow-y-auto');
    expect(file_get_contents($projectRoot.'/resources/views/user/messages.blade.php'))
        ->toContain('class="bm-modal"')
        ->toContain('bm-modal__body')
        ->toContain('bm-modal__footer');
});
