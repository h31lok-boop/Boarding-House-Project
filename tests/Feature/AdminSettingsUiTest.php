<?php

use App\Models\User;

test('admin settings page renders the compact account center layout', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.settings'))
        ->assertOk()
        ->assertSee('Profile Settings')
        ->assertSee('Manage your profile, security, and account preferences.')
        ->assertSee('Profile Information')
        ->assertSee('Security')
        ->assertSee('Additional Security')
        ->assertSee('Change Photo')
        ->assertSee('Save Changes')
        ->assertDontSee('Portfolio HQ')
        ->assertDontSee('BoardMatch Owner Portal')
        ->assertDontSee('Track portfolio performance, tenant activity, earnings, and action queues from one owner workspace.');
});

test('owner settings page renders the owner account center', function () {
    $owner = User::factory()->create([
        'role' => 'owner',
        'name' => 'Hazel Owner',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('owner.settings'))
        ->assertOk()
        ->assertSee('data-owner-account', false)
        ->assertSee('Owner account')
        ->assertSee('Hazel Owner')
        ->assertSee('Email verified')
        ->assertSee('Account settings sections')
        ->assertSee('Profile Information')
        ->assertSee('Additional Security')
        ->assertSee(route('owner.settings.profile.update'), false)
        ->assertDontSee(route('admin.settings.profile.update'), false);
});
