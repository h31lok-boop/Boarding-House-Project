<?php

test('the supplied final logo is used across every shared system surface', function () {
    $projectRoot = dirname(__DIR__, 2);
    $logoPath = $projectRoot.'/public/images/boardmatch-final-logo.png';
    $logoReference = 'images/boardmatch-final-logo.png';
    $surfaces = [
        'resources/js/app.js',
        'resources/views/welcome.blade.php',
        'resources/views/auth/login.blade.php',
        'resources/views/auth/register.blade.php',
        'resources/views/auth/register-owner.blade.php',
        'resources/views/components/application-logo.blade.php',
        'resources/views/components/admin/shell.blade.php',
        'resources/views/components/sidebar/brand.blade.php',
        'resources/views/components/layouts/dashboard.blade.php',
        'resources/views/layouts/app.blade.php',
        'resources/views/layouts/guest.blade.php',
    ];

    expect(is_file($logoPath))->toBeTrue()
        ->and(filesize($logoPath))->toBeGreaterThan(0);

    foreach ($surfaces as $surface) {
        expect(file_get_contents($projectRoot.'/'.$surface))
            ->toContain($logoReference)
            ->not->toContain('images/boardmatch-mark.svg');
    }
});

test('the landing header uses the same solid navy color as the footer', function () {
    $projectRoot = dirname(__DIR__, 2);
    $landing = file_get_contents($projectRoot.'/resources/views/welcome.blade.php');

    expect($landing)
        ->toContain('.site-nav {')
        ->toContain('background: #101827;')
        ->toContain('.site-nav .brand-name')
        ->toContain('.site-nav .nav-links a.active')
        ->toContain('.site-nav .btn-primary')
        ->toContain('@media (max-width: 1400px) and (min-width: 1241px)')
        ->toContain('@media (max-width: 1240px)')
        ->toContain('grid-template-columns: minmax(0, 1.5fr) repeat(3, minmax(0, 1fr));')
        ->toContain('overflow-wrap: anywhere;');
});
