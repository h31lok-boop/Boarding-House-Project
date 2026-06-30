<?php

use App\Models\User;

test('admin inquiries page renders the owner inquiries header', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.inquiries'))
        ->assertOk()
        ->assertSee('Inquiry Dashboard')
        ->assertSee('Property Inquiries')
        ->assertSee('Total Inquiries')
        ->assertSee('Average Response Time')
        ->assertSee('Current Filter')
        ->assertSee('Status Summary')
        ->assertSee('Inquiry List');
});
