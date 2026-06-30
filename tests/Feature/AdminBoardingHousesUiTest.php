<?php

use App\Models\User;

test('admin boarding houses page renders the streamlined owner listings layout', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.boarding-houses'))
        ->assertOk()
        ->assertSee('Property Management')
        ->assertSee('Boarding Houses')
        ->assertSee('Review property details, room inventory, and listing status in a cleaner owner workspace.')
        ->assertSee('Add Boarding House')
        ->assertSee('Listings')
        ->assertSee('Apply')
        ->assertSee('Boarding Houses')
        ->assertDontSee('Total Rooms')
        ->assertDontSee('Occupied Rooms')
        ->assertDontSee('Occupancy Rate')
        ->assertDontSee('Registered properties in your portfolio')
        ->assertDontSee('Owner portal')
        ->assertDontSee('Portfolio Listings')
        ->assertDontSee('Portfolio HQ')
        ->assertDontSee('BoardMatch Owner Portal')
        ->assertDontSee('Track portfolio performance, tenant activity, earnings, and action queues from one owner workspace.');
});
