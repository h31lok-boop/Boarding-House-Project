<?php

use App\Models\BoardingHouse;
use App\Models\BoardingHouseImage;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('admin boarding houses page keeps review controls and the professional property editor', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.boarding-houses'))
        ->assertOk()
        ->assertSee('Boarding Houses')
        ->assertSee('Add Boarding House')
        ->assertSee('Listings')
        ->assertDontSee('Search boarding houses or locations')
        ->assertDontSee('All Status')
        ->assertDontSee('All Locations')
        ->assertDontSee('Apply')
        ->assertSee('Boarding House Details')
        ->assertSee('data-property-photo-workspace', false)
        ->assertSee('data-property-photo-carousel', false)
        ->assertSee('data-property-location-map', false)
        ->assertSee('Add property photos')
        ->assertDontSee('data-owner-direct-editor', false)
        ->assertDontSee('Registered properties in your portfolio')
        ->assertDontSee('Owner portal')
        ->assertDontSee('Portfolio Listings')
        ->assertDontSee('Portfolio HQ')
        ->assertDontSee('BoardMatch Owner Portal')
        ->assertDontSee('Track portfolio performance, tenant activity, earnings, and action queues from one owner workspace.');
});

test('admin boarding house list displays each designated primary property photo', function () {
    Storage::fake('public');
    Storage::disk('public')->put('boarding-houses/first-upload.jpg', 'first image');
    Storage::disk('public')->put('boarding-houses/designated-primary.jpg', 'primary image');

    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $house = BoardingHouse::factory()->create(['name' => 'Photo Linked Boarding House']);
    BoardingHouseImage::query()->create([
        'boarding_house_id' => $house->id,
        'image_path' => 'boarding-houses/first-upload.jpg',
        'is_primary' => false,
        'sort_order' => 0,
    ]);
    BoardingHouseImage::query()->create([
        'boarding_house_id' => $house->id,
        'image_path' => 'boarding-houses/designated-primary.jpg',
        'is_primary' => true,
        'sort_order' => 1,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.boarding-houses'))
        ->assertOk()
        ->assertSee('Photo Linked Boarding House')
        ->assertSee('designated-primary.jpg')
        ->assertSee('2 photos uploaded');
});
