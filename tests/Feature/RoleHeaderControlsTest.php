<?php

use App\Models\User;
use App\Models\UserNotification;

function headerNotification(User $recipient, array $attributes = []): UserNotification
{
    return UserNotification::query()->create(array_merge([
        'user_id' => $recipient->id,
        'type' => 'system',
        'title' => 'Header notification',
        'message' => 'A role-scoped header notification.',
        'is_read' => false,
        'read_at' => null,
    ], $attributes));
}

test('admin header has persistent theme and role-scoped notification controls', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $otherAdmin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    headerNotification($admin);
    headerNotification($admin, ['title' => 'Already read', 'is_read' => true, 'read_at' => now()]);
    headerNotification($otherAdmin, ['title' => 'Foreign admin notification']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('data-theme-mode="dashboard"', false)
        ->assertSee('data-theme-toggle', false)
        ->assertSee('data-theme-icon="moon"', false)
        ->assertSee('data-theme-icon="sun"', false)
        ->assertSee('data-sidebar-reopen', false)
        ->assertSee('aria-controls="adminSidebar"', false)
        ->assertSee('data-notification-modal-trigger', false)
        ->assertSee('data-notification-modal', false)
        ->assertSee('x-teleport="body"', false)
        ->assertSee('Header notification')
        ->assertDontSee('Foreign admin notification')
        ->assertSee('href="'.route('admin.notifications.index').'"', false)
        ->assertSee('Open notifications, 1 unread', false)
        ->assertSee('sticky top-2.5', false);
});

test('admin workspace header is identical and present once across admin pages', function (string $routeName) {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->get(route($routeName))
        ->assertOk()
        ->assertSee('data-admin-workspace-header', false)
        ->assertSee('data-workspace="admin"', false)
        ->assertSee('BoardMatch Admin')
        ->assertSee('Manage platform operations, users, properties, payments, and activity in one place.');

    expect(substr_count($response->getContent(), 'data-admin-workspace-header'))->toBe(1);
})->with([
    'dashboard' => ['admin.dashboard'],
    'messages' => ['admin.messages'],
    'payments' => ['admin.payments'],
    'settings' => ['admin.settings'],
]);

test('owner header has persistent theme and owner notification controls beside the profile', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $otherOwner = User::factory()->verifiedOwner()->create();

    headerNotification($owner);
    headerNotification($otherOwner, ['title' => 'Foreign owner notification']);

    $response = $this->actingAs($owner)
        ->get(route('owner.dashboard'))
        ->assertOk()
        ->assertSee('data-theme-toggle', false)
        ->assertSee('data-sidebar-reopen', false)
        ->assertSee('aria-controls="adminSidebar"', false)
        ->assertSee('data-notification-modal-trigger', false)
        ->assertSee('data-notification-modal', false)
        ->assertSee('Header notification')
        ->assertDontSee('Foreign owner notification')
        ->assertSee('href="'.route('owner.notifications.index').'"', false)
        ->assertSee('Open notifications, 1 unread', false)
        ->assertSee('sticky top-2.5', false);

    expect(strpos($response->getContent(), 'data-theme-toggle'))
        ->toBeLessThan(strpos($response->getContent(), $owner->name));

    $this->actingAs($owner)
        ->get(route('owner.boarding-houses'))
        ->assertOk()
        ->assertSee('dark:bg-[radial-gradient(circle_at_top_left', false);
});

test('tenant header has persistent theme and tenant notification controls beside the profile', function () {
    $tenant = User::factory()->create(['role' => 'user', 'is_active' => true]);
    $otherTenant = User::factory()->create(['role' => 'user', 'is_active' => true]);

    headerNotification($tenant);
    headerNotification($tenant, ['title' => 'Read tenant alert', 'is_read' => true, 'read_at' => now()]);
    headerNotification($otherTenant, ['title' => 'Foreign tenant notification']);

    $this->actingAs($tenant)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertSee('data-theme-toggle', false)
        ->assertSee('data-sidebar-reopen', false)
        ->assertSee('aria-controls="userSidebar"', false)
        ->assertSee('data-notification-modal-trigger', false)
        ->assertSee('data-notification-modal', false)
        ->assertSee('Header notification')
        ->assertDontSee('Foreign tenant notification')
        ->assertSee('href="'.route('user.notifications.index').'"', false)
        ->assertSee('Open notifications, 1 unread', false)
        ->assertSee('sticky top-[4.25rem]', false);
});

test('each role can open only its own notification workspace', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $owner = User::factory()->verifiedOwner()->create();
    $tenant = User::factory()->create(['role' => 'user', 'is_active' => true]);

    $this->actingAs($admin)->get(route('admin.notifications.index'))->assertOk();
    $this->actingAs($owner)->get(route('owner.notifications.index'))->assertOk();
    $this->actingAs($tenant)->get(route('user.notifications.index'))->assertOk();

    $this->actingAs($owner)->get(route('admin.notifications.index'))->assertForbidden();
    $this->actingAs($tenant)->get(route('owner.notifications.index'))->assertForbidden();
    $this->actingAs($admin)->get(route('user.notifications.index'))->assertForbidden();
});
