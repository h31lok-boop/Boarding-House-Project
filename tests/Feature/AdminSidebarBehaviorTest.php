<?php

use App\Models\User;

test('admin sidebar highlights exactly one item on each admin sidebar page', function (string $path, string $activeKey) {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get($path);

    $response->assertOk();

    preg_match_all('/data-sidebar-key="([^"]+)"[^>]*aria-current="page"/s', $response->getContent(), $matches);

    expect($matches[1])->toBe([$activeKey]);
    expect($response->getContent())->not->toMatch('/data-sidebar-key="[^"]+"[^>]*href="#"/s');
})->with([
    'dashboard' => ['/admin/dashboard', 'dashboard'],
    'boarding houses' => ['/admin/boarding-houses', 'boarding-houses'],
    'reservations' => ['/admin/reservations', 'reservations'],
    'tenants' => ['/admin/tenants', 'tenants'],
    'legacy tenant profiles' => ['/admin/tenant-profiles', 'tenants'],
    'inquiries' => ['/admin/inquiries', 'inquiries'],
    'transactions' => ['/admin/transactions', 'transactions'],
    'messages' => ['/admin/messages', 'messages'],
    'notifications' => ['/admin/notifications', 'notifications'],
    'reports' => ['/admin/reports', 'reports'],
    'profile settings' => ['/admin/settings', 'settings'],
]);

test('notifications does not activate reports or transactions in the admin sidebar', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $html = $this->actingAs($admin)
        ->get('/admin/notifications')
        ->assertOk()
        ->getContent();

    expect($html)->toMatch('/data-sidebar-key="notifications"[^>]*aria-current="page"/s');
    expect($html)->not->toMatch('/data-sidebar-key="reports"[^>]*aria-current="page"/s');
    expect($html)->not->toMatch('/data-sidebar-key="transactions"[^>]*aria-current="page"/s');
});
