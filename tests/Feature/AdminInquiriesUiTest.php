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
        ->assertSee('Property Inquiries')
        ->assertSee('Manage tenant questions and requests.')
        ->assertSee('Total Inquiries')
        ->assertSee('Pending Follow-up')
        ->assertSee('All statuses')
        ->assertSee('Message Preview');
});
