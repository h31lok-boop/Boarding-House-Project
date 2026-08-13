<?php

use App\Models\User;

test('admin messages page renders the messenger-style workspace inside the admin shell', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.messages'))
        ->assertOk()
        ->assertSee('BoardMatch Admin')
        ->assertSee('Chats')
        ->assertSee('Platform conversations')
        ->assertSee('Search messages')
        ->assertSee('Your messages')
        ->assertSee('Active')
        ->assertSee('Archived')
        ->assertSee('Dashboard')
        ->assertSee('data-messaging-interaction', false)
        ->assertDontSee('Portfolio HQ')
        ->assertDontSee('BoardMatch Owner Portal')
        ->assertDontSee('Track portfolio performance, tenant activity, earnings, and action queues from one owner workspace.');
});

test('owner messages page uses the owner workspace identity', function () {
    $owner = User::factory()->verifiedOwner()->create();

    $this->actingAs($owner)
        ->get(route('owner.messages'))
        ->assertOk()
        ->assertSee('BoardMatch Workspace')
        ->assertSee('Tenant conversations')
        ->assertSee('Chats')
        ->assertDontSee('Platform conversations');
});
