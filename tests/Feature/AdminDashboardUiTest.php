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
        ->assertSee('Welcome back')
        ->assertSee('Total Boarding Houses')
        ->assertSee('Active Reservations')
        ->assertSee('Active Tenants')
        ->assertSee('Total Revenue')
        ->assertSee('Reservations Overview')
        ->assertSee('Revenue Overview')
        ->assertSee('Recent Activity')
        ->assertSee('Latest Reservations')
        ->assertDontSee('Pending Admin Actions')
        ->assertDontSee('Revenue Summary')
        ->assertDontSee('Occupancy Rate')
        ->assertDontSee('Inquiry Status')
        ->assertDontSee('Match Requests');
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
