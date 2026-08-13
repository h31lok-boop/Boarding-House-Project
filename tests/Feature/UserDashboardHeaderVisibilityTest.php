<?php

use App\Models\User;

test('dashboard search header appears on the user dashboard', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertSee('Search boarding houses, locations, reservations...');
});

test('dashboard search header is hidden on other user pages', function (string $routeName) {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertOk()
        ->assertDontSee('Search anything...')
        ->assertDontSee('Search boarding houses, locations, reservations...');
})->with([
    'boarding houses' => ['user.boarding-houses.index'],
    'matchmaking' => ['user.matchmaking.index'],
    'preferences' => ['user.preferences.index'],
    'reservations' => ['user.reservations.index'],
    'payments' => ['user.payments.index'],
    'transactions' => ['user.transactions.index'],
    'messages' => ['user.messages.index'],
    'notifications' => ['user.notifications.index'],
    'settings' => ['user.settings.index'],
    'help center' => ['user.help-center.index'],
]);

test('dashboard search header is hidden on admin pages', function (string $routeName) {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route($routeName))
        ->assertOk()
        ->assertDontSee('Search anything...')
        ->assertDontSee('Search boarding houses, locations, reservations...');
})->with([
    'dashboard' => ['admin.dashboard'],
    'users' => ['admin.users'],
    'boarding houses' => ['admin.boarding-houses'],
    'my boarding house' => ['admin.my-boarding-house'],
    'listings' => ['admin.listings'],
    'rooms' => ['admin.rooms'],
    'tenant profiles' => ['admin.tenant-profiles'],
    'tenants' => ['admin.tenants.index'],
    'compatibility scores' => ['admin.compatibility-scores'],
    'recommendations' => ['admin.recommendations'],
    'match requests' => ['admin.match-requests'],
    'inquiries' => ['admin.inquiries'],
    'messages' => ['admin.messages'],
    'reservations' => ['admin.reservations'],
    'payments' => ['admin.payments'],
    'transactions' => ['admin.transactions.index'],
    'reviews' => ['admin.reviews'],
    'reports' => ['admin.reports.index'],
    'ML insights' => ['admin.insights.index'],
    'notifications' => ['admin.notifications.index'],
    'settings' => ['admin.settings'],
]);
