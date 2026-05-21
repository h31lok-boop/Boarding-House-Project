<?php

use App\Models\BoardingHouse;
use App\Models\Review;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('tenant', 'web');
});

test('tenant reviews page renders sample reviews and sidebar navigation', function () {
    $tenant = User::factory()->create([
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $tenant->syncRoles(['tenant']);

    $response = $this->actingAs($tenant)->get(route('tenant.reviews'));

    $response->assertOk()
        ->assertSee('Reviews')
        ->assertSee('View tenant feedback, ratings, and boarding house experiences.')
        ->assertSee('Write a Review')
        ->assertSee('Average Rating')
        ->assertSee('Total Reviews')
        ->assertSee('Approved Reviews')
        ->assertSee('Pending Reviews')
        ->assertSee('Hazel Sabando')
        ->assertSee('Casa Digos Boarding Stay')
        ->assertSee('No reviews found')
        ->assertSee('tenantReviewsPage')
        ->assertDontSee('Search boarding houses, rooms, or locations...');
});

test('tenant shell search header is dashboard only', function () {
    $tenant = User::factory()->create([
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $tenant->syncRoles(['tenant']);

    $this->actingAs($tenant)
        ->get(route('tenant.dashboard'))
        ->assertOk()
        ->assertSee('Tenant Dashboard')
        ->assertSee('Search boarding houses, rooms, or locations...');

    $this->actingAs($tenant)
        ->get(route('tenant.reviews'))
        ->assertOk()
        ->assertDontSee('Search boarding houses, rooms, or locations...');
});

test('tenant settings uses its own page without dashboard anchor jump', function () {
    $tenant = User::factory()->create([
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $tenant->syncRoles(['tenant']);

    $this->actingAs($tenant)
        ->get(route('tenant.settings'))
        ->assertOk()
        ->assertSee('Settings')
        ->assertSee('Manage your DSSC Boarding tenant preferences, account access, notifications, and support shortcuts.')
        ->assertSee('Privacy and Security')
        ->assertSee('Two-Factor Authentication')
        ->assertSee('Add a second verification step when signing in.')
        ->assertSee('Backend Setup Required')
        ->assertSee('Account Shortcuts')
        ->assertSee('Appearance')
        ->assertSee('Device Session')
        ->assertSee('/tenant/settings', false)
        ->assertDontSee('#account-settings-panel', false);
});

test('tenant can submit a pending review', function () {
    $tenant = User::factory()->create([
        'name' => 'Hazel Sabando',
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $tenant->syncRoles(['tenant']);

    BoardingHouse::factory()->create([
        'name' => 'MetroNest Boarding Hub',
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $response = $this->actingAs($tenant)->postJson(route('tenant.reviews.store'), [
        'boarding_house' => 'MetroNest Boarding Hub',
        'rating' => 5,
        'comment' => 'The place is clean and close to school.',
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Review submitted successfully.')
        ->assertJsonPath('review.tenantName', 'Hazel Sabando')
        ->assertJsonPath('review.tenantInitials', 'HS')
        ->assertJsonPath('review.boardingHouseName', 'MetroNest Boarding Hub')
        ->assertJsonPath('review.rating', 5)
        ->assertJsonPath('review.status', 'Pending');

    $review = Review::query()->where('user_id', $tenant->id)->firstOrFail();

    expect($review->comment)->toBe('The place is clean and close to school.')
        ->and((int) $review->rating)->toBe(5)
        ->and($review->status)->toBe('pending');
});

test('tenant review submission validates required fields', function () {
    $tenant = User::factory()->create([
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $tenant->syncRoles(['tenant']);

    $this->actingAs($tenant)
        ->postJson(route('tenant.reviews.store'), [
            'boarding_house' => '',
            'rating' => '',
            'comment' => '   ',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['boarding_house', 'rating', 'comment']);
});
