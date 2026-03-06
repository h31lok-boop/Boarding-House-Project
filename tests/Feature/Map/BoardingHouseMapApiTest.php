<?php

use App\Models\BoardingHouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin map api returns geotagged boarding houses', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);

    BoardingHouse::factory()->create([
        'name' => 'Map House One',
        'latitude' => 6.7440000,
        'longitude' => 125.3550000,
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    $response = $this->actingAs($admin)->getJson(route('map.admin.boarding-houses'));

    $response->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['count'],
        ]);

    expect($response->json('meta.count'))->toBeGreaterThan(0);
});

test('user map api excludes non-approved houses', function () {
    $tenant = User::factory()->create([
        'role' => 'tenant',
        'email_verified_at' => now(),
    ]);

    BoardingHouse::factory()->create([
        'name' => 'Approved House',
        'latitude' => 6.7440000,
        'longitude' => 125.3550000,
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    BoardingHouse::factory()->create([
        'name' => 'Pending House',
        'latitude' => 6.7445000,
        'longitude' => 125.3555000,
        'is_active' => true,
        'approval_status' => 'pending',
    ]);

    $response = $this->actingAs($tenant)->getJson(route('map.user.boarding-houses'));

    $response->assertOk();
    $names = collect($response->json('data'))->pluck('name');

    expect($names)->toContain('Approved House');
    expect($names)->not->toContain('Pending House');
});
