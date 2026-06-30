<?php

use App\Models\User;

test('admin tenant management page renders the compact dashboard layout', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.tenants.index'))
        ->assertOk()
        ->assertSee('Tenant Management')
        ->assertSee('Monitor tenant profiles, active stays, and housing history across your listings.')
        ->assertSee('Tenant Directory')
        ->assertSee('Add Tenant')
        ->assertSee('Apply')
        ->assertSee('Reset')
        ->assertSee('Messages')
        ->assertSee('Reservations')
        ->assertDontSee('Owner Tenant Management')
        ->assertDontSee('Directory Snapshot')
        ->assertDontSee('Tenant Insights')
        ->assertDontSee('Search and Filter')
        ->assertDontSee('Recent Activity')
        ->assertDontSee('Quick Actions')
        ->assertDontSee('Portfolio HQ')
        ->assertDontSee('BoardMatch Owner Portal')
        ->assertDontSee('Track portfolio performance, tenant activity, earnings, and action queues from one owner workspace.');
});
