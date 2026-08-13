<?php

use App\Models\BoardingHouse;
use App\Models\BoardingHouseMatch;
use App\Models\Inquiry;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserPreference;
use App\Services\BoardMatchAiContextService;

test('tenant AI context contains only that tenant records and approved public listings', function () {
    $tenant = User::factory()->create(['role' => 'user', 'is_active' => true]);
    $otherTenant = User::factory()->create(['role' => 'user', 'is_active' => true]);
    $publicHouse = BoardingHouse::factory()->create([
        'name' => 'Approved Public House',
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    UserPreference::query()->create([
        'user_id' => $tenant->id,
        'preferred_rental_budget' => 4321,
        'preferred_locations' => ['TENANT-OWN-LOCATION'],
        'room_type' => 'private',
    ]);
    UserPreference::query()->create([
        'user_id' => $otherTenant->id,
        'preferred_rental_budget' => 9876,
        'preferred_locations' => ['FOREIGN-TENANT-LOCATION'],
        'room_type' => 'shared',
    ]);

    Inquiry::query()->create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $publicHouse->id,
        'message' => 'OWN-TENANT-INQUIRY',
        'status' => 'pending',
    ]);
    Inquiry::query()->create([
        'user_id' => $otherTenant->id,
        'boarding_house_id' => $publicHouse->id,
        'message' => 'FOREIGN-TENANT-INQUIRY',
        'status' => 'pending',
    ]);

    BoardingHouseMatch::query()->create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $publicHouse->id,
        'match_score' => 88,
        'match_reasons' => ['OWN-MATCH-REASON'],
    ]);
    BoardingHouseMatch::query()->create([
        'user_id' => $otherTenant->id,
        'boarding_house_id' => $publicHouse->id,
        'match_score' => 11,
        'match_reasons' => ['FOREIGN-MATCH-REASON'],
    ]);

    UserNotification::query()->create([
        'user_id' => $tenant->id,
        'type' => 'system',
        'title' => 'OWN-TENANT-NOTIFICATION',
        'message' => 'Visible only to this tenant.',
    ]);
    UserNotification::query()->create([
        'user_id' => $otherTenant->id,
        'type' => 'system',
        'title' => 'FOREIGN-TENANT-NOTIFICATION',
        'message' => 'Must not be in the other tenant context.',
    ]);

    $context = app(BoardMatchAiContextService::class)->build($tenant);

    expect($context)
        ->toContain('Only the current tenant records plus approved public boarding-house listings.')
        ->toContain('Approved Public House')
        ->toContain('TENANT-OWN-LOCATION')
        ->toContain('OWN-MATCH-REASON')
        ->toContain('OWN-TENANT-NOTIFICATION')
        ->not->toContain('FOREIGN-TENANT-LOCATION')
        ->not->toContain('FOREIGN-MATCH-REASON')
        ->not->toContain('FOREIGN-TENANT-NOTIFICATION')
        ->not->toContain('FOREIGN-TENANT-INQUIRY');
});

test('AI endpoint rejects an authenticated account with no supported role scope', function () {
    $unsupported = User::factory()->create([
        'role' => 'auditor',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($unsupported)
        ->postJson(route('assistant.ask'), ['question' => 'Show me tenant records'])
        ->assertForbidden();
});
