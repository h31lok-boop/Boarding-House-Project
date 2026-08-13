<?php

use App\Models\User;

test('admin reports page renders the compact analytics center layout', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reports.index'))
        ->assertOk()
        ->assertSee('Administrator Reports Dashboard')
        ->assertSee('Analytics Center')
        ->assertSee('Report Controls')
        ->assertSee('Performance Highlights')
        ->assertSee('Revenue Trend')
        ->assertSee('Occupancy Snapshot')
        ->assertSee('Booking Distribution')
        ->assertSee('Portfolio Health')
        ->assertSee('Detailed Reports')
        ->assertSee('Reviews')
        ->assertSee('Rating')
        ->assertSee('Operational Insights')
        ->assertSee('Open ML Predictions')
        ->assertSee('Top-Performing Boarding Houses')
        ->assertSee('Recent Activities')
        ->assertSee('Export Report')
        ->assertDontSee('Portfolio HQ')
        ->assertDontSee('BoardMatch Owner Portal')
        ->assertDontSee('Track revenue, reservations, tenants, and occupancy at a glance.');
});
