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
        ->assertSee('Search boarding houses, tenants, reservations, payments...')
        ->assertSee('Owner portal')
        ->assertSee('Welcome back')
        ->assertSee('Total Properties')
        ->assertSee('Active Reservations')
        ->assertSee('Active Tenants')
        ->assertSee('Total Revenue')
        ->assertSee('Reservations Overview')
        ->assertSee('Revenue Overview')
        ->assertSee('Recent Activity')
        ->assertSee('Top Performing Properties')
        ->assertSee('Occupancy Overview')
        ->assertSee('Add Boarding House')
        ->assertSee('View all reservations')
        ->assertDontSee('Portfolio HQ')
        ->assertDontSee('Track portfolio performance, tenant activity, earnings, and action queues from one owner workspace.');
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
