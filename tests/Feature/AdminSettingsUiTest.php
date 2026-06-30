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
        ->assertSee('Owner Settings Dashboard')
        ->assertSee('Account Center')
        ->assertSee('Account Overview')
        ->assertSee('Workspace Sections')
        ->assertSee('Profile Management')
        ->assertSee('Account Information')
        ->assertSee('Security Settings')
        ->assertSee('Quick Actions')
        ->assertSee('Recent Activity')
        ->assertSee('Account Status')
        ->assertSee('Notification Preferences')
        ->assertSee('Workspace Preferences')
        ->assertSee('Change Password')
        ->assertDontSee('Portfolio HQ')
        ->assertDontSee('BoardMatch Owner Portal')
        ->assertDontSee('Track portfolio performance, tenant activity, earnings, and action queues from one owner workspace.');
});
