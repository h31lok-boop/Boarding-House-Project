<?php

use App\Models\User;

test('admin reports page renders the simple complete reporting layout', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reports.index'))
        ->assertOk()
        ->assertSee('Reports')
        ->assertSee('Reporting period')
        ->assertSee('Total Revenue')
        ->assertSee('Total Bookings')
        ->assertSee('Active Tenants')
        ->assertSee('Occupancy Rate')
        ->assertSee('Revenue trend')
        ->assertSee('Current overview')
        ->assertSee('Booking status')
        ->assertSee('Property performance')
        ->assertSee('Reviews')
        ->assertSee('Rating')
        ->assertSee('Export CSV')
        ->assertDontSee('Operational Insights')
        ->assertDontSee('Top-Performing Boarding Houses')
        ->assertDontSee('Recent Activities');
});
