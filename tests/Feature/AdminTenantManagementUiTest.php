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
        ->assertDontSee('Tenant Management')
        ->assertDontSee('Monitor tenant profiles, active stays, and housing history across your listings.')
        ->assertSee('Tenant Directory')
        ->assertSee('Add Tenant')
        ->assertDontSee('Search tenant name, email, phone, or profile')
        ->assertDontSee('All Status')
        ->assertDontSee('All Boarding Houses')
        ->assertDontSee('Apply')
        ->assertDontSee('Reset')
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
