<?php

use App\Models\User;

test('admin inquiries page omits the owner inquiry toolbar', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.inquiries'))
        ->assertOk()
        ->assertDontSee('data-inquiries-toolbar', false)
        ->assertDontSee('Property Inquiries')
        ->assertDontSee('Manage tenant questions and requests.')
        ->assertSee('Total Inquiries')
        ->assertSee('Pending Follow-up')
        ->assertDontSee('All statuses')
        ->assertDontSee('Search by tenant, property, or message...')
        ->assertSee('Message Preview');
});
