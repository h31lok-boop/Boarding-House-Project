<?php

use App\Models\User;

test('admin messages page renders the modern communication center layout', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.messages'))
        ->assertOk()
        ->assertSee('Owner Messages Dashboard')
        ->assertSee('Inbox Snapshot')
        ->assertSee('Search and Filters')
        ->assertSee('Conversation List')
        ->assertSee('Active Chat Panel')
        ->assertSee('Conversation Details')
        ->assertSee('Active')
        ->assertSee('Archived')
        ->assertSee('Awaiting Reply')
        ->assertSee('Resolved Conversations')
        ->assertSee('Apply')
        ->assertDontSee('Portfolio HQ')
        ->assertDontSee('BoardMatch Owner Portal')
        ->assertDontSee('Track portfolio performance, tenant activity, earnings, and action queues from one owner workspace.');
});
