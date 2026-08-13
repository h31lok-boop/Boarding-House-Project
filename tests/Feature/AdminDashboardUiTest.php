<?php

use App\Models\User;

test('admin dashboard renders the modern dashboard sections', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('BoardMatch Admin')
        ->assertSee('Boarding Houses')
        ->assertSee('Occupancy')
        ->assertSee('Active Tenants')
        ->assertSee('Collected Revenue')
        ->assertSee('Room Occupancy')
        ->assertSee('Weekly Revenue')
        ->assertSee('Needs attention')
        ->assertSee('Recent activity');
});

test('admin dashboard action routes are reachable', function (string $routeName, array $params = []) {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route($routeName, $params))
        ->assertOk();
})->with([
    'search' => ['admin.search', ['query' => 'tenant']],
    'boarding house create' => ['admin.boarding-houses.create', []],
    'notifications' => ['admin.notifications.index', []],
    'reports' => ['admin.reports.index', []],
]);

test('admin dashboard export downloads a csv report', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard.export'));

    $response->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('boardmatch-dashboard-report');
});
