<?php

test('user accounts expose one canonical photo path with legacy compatibility', function () {
    $projectRoot = dirname(__DIR__, 2);
    $model = file_get_contents($projectRoot.'/app/Models/User.php');
    $migration = file_get_contents($projectRoot.'/database/migrations/2026_08_14_000001_add_photo_path_to_users_table.php');

    expect($migration)
        ->toContain("hasColumn('users', 'photo_path')")
        ->toContain("string('photo_path', 2048)")
        ->toContain("update(['photo_path' => \$legacyPath])");

    expect($model)
        ->toContain("'photo_path'")
        ->toContain('getEffectivePhotoPathAttribute')
        ->toContain('getPhotoUrlAttribute')
        ->toContain("Storage::disk('public')->url(\$path)");
});
test('profile photos are shown in account lists and profile dialogs', function () {
    $projectRoot = dirname(__DIR__, 2);
    $views = [
        'resources/views/admin/users.blade.php',
        'resources/views/admin/tenant-profiles.blade.php',
        'resources/views/admin/reservations.blade.php',
        'resources/views/admin/payments.blade.php',
        'resources/views/admin/transactions.blade.php',
        'resources/views/admin/inquiries.blade.php',
        'resources/views/admin/messages.blade.php',
        'resources/views/admin/reviews.blade.php',
        'resources/views/admin/payment-settings.blade.php',
    ];

    foreach ($views as $viewPath) {
        $view = file_get_contents($projectRoot.'/'.$viewPath);

        expect($view, $viewPath)
            ->toContain('photo_url')
            ->toContain('<img');
    }
});
