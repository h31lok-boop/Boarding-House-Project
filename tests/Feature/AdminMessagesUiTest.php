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
        ->assertSee('Admin Messages Dashboard')
        ->assertSee('Communication Center')
        ->assertSee('All conversations')
        ->assertSee('Conversation List')
        ->assertSee('Active')
        ->assertSee('Archived')
        ->assertSee('Awaiting Reply')
        ->assertSee('Apply')
        ->assertDontSee('Portfolio HQ')
        ->assertDontSee('BoardMatch Owner Portal')
        ->assertDontSee('Track portfolio performance, tenant activity, earnings, and action queues from one owner workspace.');
});

test('owner messages page uses the owner workspace identity', function () {
    $owner = User::factory()->create([
        'role' => 'owner',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('owner.messages'))
        ->assertOk()
        ->assertSee('Owner Messages Dashboard')
        ->assertDontSee('Admin Messages Dashboard');
});
