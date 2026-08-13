<?php

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function notificationUser(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ], $attributes));
}

function makeNotification(User $user, array $attributes = []): UserNotification
{
    return UserNotification::query()->create(array_merge([
        'user_id' => $user->id,
        'type' => 'reservation',
        'title' => 'Reservation status updated',
        'message' => 'Your reservation request is now pending owner confirmation.',
        'data' => [],
        'is_read' => false,
        'read_at' => null,
    ], $attributes));
}

function notificationListContent($response): string
{
    preg_match('/<section data-notification-list.*?<\/section>/s', $response->getContent(), $matches);

    return $matches[0] ?? '';
}

test('notifications page renders notification content instead of profile settings', function () {
    $user = notificationUser();

    makeNotification($user, [
        'title' => 'Payment confirmed',
        'type' => 'Payment Confirmation',
        'message' => 'Your payment has been confirmed.',
    ]);

    $this->actingAs($user)
        ->get(route('user.notifications.index'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Notifications')
        ->assertSee('Updates on your reservations, payments, messages, and system alerts.')
        ->assertSee('Search notifications')
        ->assertSee('Newest First')
        ->assertSee('Payment confirmed')
        ->assertDontSee('Manage your account information, security, notifications, and privacy preferences.');
});

test('notifications page shows empty state', function () {
    $user = notificationUser();

    $this->actingAs($user)
        ->get(route('user.notifications.index'))
        ->assertOk()
        ->assertSee('No notifications found')
        ->assertSee('Updates about reservations, payments, messages, and system alerts will appear here.');
});

test('notifications can be filtered by type', function () {
    $user = notificationUser();
    makeNotification($user, [
        'type' => 'payment',
        'title' => 'Payment receipt ready',
    ]);
    makeNotification($user, [
        'type' => 'message',
        'title' => 'Owner replied',
    ]);

    $response = $this->actingAs($user)
        ->get(route('user.notifications.index', ['filter' => 'payments']))
        ->assertOk();

    expect(notificationListContent($response))
        ->toContain('Payment receipt ready')
        ->not->toContain('Owner replied');
});

test('notifications can be searched', function () {
    $user = notificationUser();
    makeNotification($user, [
        'type' => 'inquiry',
        'title' => 'Viewing schedule confirmed',
        'message' => 'Your inquiry has a confirmed viewing date.',
    ]);
    makeNotification($user, [
        'type' => 'system',
        'title' => 'Password reminder',
        'message' => 'Update your password soon.',
    ]);

    $response = $this->actingAs($user)
        ->get(route('user.notifications.index', ['q' => 'viewing']))
        ->assertOk();

    expect(notificationListContent($response))
        ->toContain('Viewing schedule confirmed')
        ->not->toContain('Password reminder');
});

test('notifications can be sorted unread first', function () {
    $user = notificationUser();
    makeNotification($user, [
        'title' => 'Read first chronologically',
        'is_read' => true,
        'read_at' => now(),
        'created_at' => now()->addMinute(),
    ]);
    makeNotification($user, [
        'title' => 'Unread should appear first',
        'created_at' => now()->subMinute(),
    ]);

    $response = $this->actingAs($user)
        ->get(route('user.notifications.index', ['sort' => 'unread']))
        ->assertOk();

    $list = notificationListContent($response);

    expect($list)->toContain('Unread should appear first')
        ->and(strpos($list, 'Unread should appear first'))
        ->toBeLessThan(strpos($list, 'Read first chronologically'));
});

test('duplicate database notifications are rendered and counted once', function () {
    $user = notificationUser();

    makeNotification($user, [
        'type' => 'system',
        'title' => 'Duplicated notice',
        'message' => 'This notice was inserted more than once.',
        'created_at' => now()->subMinute(),
    ]);
    makeNotification($user, [
        'type' => 'system',
        'title' => 'Duplicated notice',
        'message' => 'This notice was inserted more than once.',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->get(route('user.notifications.index'))
        ->assertOk();

    $content = $response->getContent();

    expect($content)->toMatch('/data-summary-card="all"\s+data-summary-count="1"/')
        ->and(substr_count($content, 'data-notification-card'))->toBe(1)
        ->and(substr_count($content, 'Duplicated notice'))->toBeGreaterThan(0);
});

test('deleting a visible duplicate removes its duplicate group', function () {
    $user = notificationUser();

    $older = makeNotification($user, [
        'type' => 'system',
        'title' => 'Duplicate to delete',
        'message' => 'Delete this duplicate group.',
        'created_at' => now()->subMinute(),
    ]);
    $newer = makeNotification($user, [
        'type' => 'system',
        'title' => 'Duplicate to delete',
        'message' => 'Delete this duplicate group.',
        'created_at' => now(),
    ]);

    $this->actingAs($user)
        ->delete(route('user.notifications.destroy', $newer->id))
        ->assertRedirect()
        ->assertSessionHas('success', 'Notification deleted successfully.');

    $this->assertDatabaseMissing('notifications', ['id' => $older->id]);
    $this->assertDatabaseMissing('notifications', ['id' => $newer->id]);
});

test('user can mark a notification as read', function () {
    $user = notificationUser();
    $notification = makeNotification($user);

    $this->actingAs($user)
        ->post(route('user.notifications.read', $notification->id))
        ->assertRedirect()
        ->assertSessionHas('success', 'Notification marked as read.');

    $notification->refresh();

    expect($notification->read_at)->not->toBeNull()
        ->and($notification->is_read)->toBeTrue();
});

test('user can mark all notifications as read', function () {
    $user = notificationUser();
    makeNotification($user);
    makeNotification($user, ['type' => 'message']);

    $this->actingAs($user)
        ->post(route('user.notifications.readAll'))
        ->assertRedirect()
        ->assertSessionHas('success', 'All notifications marked as read.');

    expect(UserNotification::query()->where('user_id', $user->id)->unread()->count())->toBe(0);
});

test('user can delete own notification', function () {
    $user = notificationUser();
    $notification = makeNotification($user);

    $this->actingAs($user)
        ->delete(route('user.notifications.destroy', $notification->id))
        ->assertRedirect()
        ->assertSessionHas('success', 'Notification deleted successfully.');

    $this->assertDatabaseMissing('notifications', [
        'id' => $notification->id,
    ]);
});

test('user can clear all own notifications', function () {
    $user = notificationUser();
    makeNotification($user);
    makeNotification($user, ['type' => 'system']);

    $this->actingAs($user)
        ->delete(route('user.notifications.clearAll'))
        ->assertRedirect(route('user.notifications.index'))
        ->assertSessionHas('success', 'All notifications cleared.');

    expect(UserNotification::query()->where('user_id', $user->id)->count())->toBe(0);
});

test('user cannot manage another users notification', function () {
    $user = notificationUser();
    $other = notificationUser();
    $notification = makeNotification($other);

    $this->actingAs($user)
        ->post(route('user.notifications.read', $notification->id))
        ->assertNotFound();

    expect($notification->fresh()->read_at)->toBeNull();
});

test('unread notification badge only shows unread count', function () {
    $user = notificationUser();
    makeNotification($user, ['title' => 'Unread one']);
    makeNotification($user, ['title' => 'Unread two', 'type' => 'system']);
    makeNotification($user, [
        'title' => 'Read one',
        'is_read' => true,
        'read_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('user.notifications.index'))
        ->assertOk()
        ->assertSee('Unread one')
        ->assertSee('Unread two')
        ->assertSee('>2<', false);
});
