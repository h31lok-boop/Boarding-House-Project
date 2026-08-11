<?php

use App\Models\User;

test('admin boarding houses page keeps review controls and the professional property editor', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.boarding-houses'))
        ->assertOk()
        ->assertSee('Boarding Houses')
        ->assertSee('Add Boarding House')
        ->assertSee('Listings')
        ->assertSee('Apply')
        ->assertSee('Boarding House Details')
        ->assertSee('data-property-photo-workspace', false)
        ->assertSee('data-property-photo-carousel', false)
        ->assertSee('data-property-location-map', false)
        ->assertSee('Add property photos')
        ->assertDontSee('data-owner-direct-editor', false)
        ->assertDontSee('Registered properties in your portfolio')
        ->assertDontSee('Owner portal')
        ->assertDontSee('Portfolio Listings')
        ->assertDontSee('Portfolio HQ')
        ->assertDontSee('BoardMatch Owner Portal')
        ->assertDontSee('Track portfolio performance, tenant activity, earnings, and action queues from one owner workspace.');
});
