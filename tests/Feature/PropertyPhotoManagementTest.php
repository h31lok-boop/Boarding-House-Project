<?php

use App\Models\BoardingHouse;
use App\Models\BoardingHouseImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function tinyPropertyPng(string $name): UploadedFile
{
    $contents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2nWQAAAAASUVORK5CYII=');

    return UploadedFile::fake()->createWithContent($name, $contents);
}

it('lets an owner upload property photos that are displayed to tenants', function () {
    Storage::fake('public');
    $owner = User::factory()->verifiedOwner()->create();
    $tenant = User::factory()->create(['role' => 'user', 'is_active' => true, 'email_verified_at' => now()]);
    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'is_active' => true,
        'available_rooms' => 3,
        'approval_status' => 'approved',
        'status' => 'approved',
        'latitude' => 6.7587400,
        'longitude' => 125.3090900,
    ]);

    $this->actingAs($owner)
        ->post(route('owner.listings.photos.store', $house), [
            'photos' => [tinyPropertyPng('front.png'), tinyPropertyPng('room.png')],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $images = $house->fresh()->images;
    expect($images)->toHaveCount(2)
        ->and($images->where('is_primary', true))->toHaveCount(1);
    Storage::disk('public')->assertExists($images->first()->image_path);

    $this->actingAs($owner)
        ->get(route('owner.my-boarding-house'))
        ->assertOk()
        ->assertSee('Property Photos')
        ->assertSee('The first photo is used as the listing background image.')
        ->assertDontSee('Set as cover')
        ->assertSee('Upload photos');

    $this->actingAs($owner)
        ->get(route('owner.boarding-houses'))
        ->assertOk()
        ->assertSee('data-owner-direct-editor', false)
        ->assertSee('data-property-photo-workspace', false)
        ->assertSee('data-property-photo-carousel', false)
        ->assertSee('data-property-location-map', false)
        ->assertSee('Add property photos')
        ->assertSee('Previous property photo')
        ->assertSee('Next property photo')
        ->assertSee('Tag the property on the map')
        ->assertDontSee('Boarding House Details');

    $imageUrl = $images->firstWhere('is_primary', true)->url;
    $this->actingAs($tenant)
        ->get(route('user.boarding-houses.index', ['tab' => 'all']))
        ->assertOk()
        ->assertSee(basename($images->firstWhere('is_primary', true)->image_path))
        ->assertSee(basename($images->last()->image_path))
        ->assertSee('data-renter-quick-photo-carousel', false)
        ->assertSee('Previous property photo')
        ->assertSee('Next property photo')
        ->assertSee('data-renter-quick-location-map', false)
        ->assertSee('Property location')
        ->assertSee('Boarding house location map')
        ->assertSee('Zoom out map')
        ->assertSee('Zoom in map')
        ->assertSee('Minimize map')
        ->assertSee('quickMapEmbedUrl()', false)
        ->assertSee('openstreetmap.org', false);
    $this->actingAs($tenant)
        ->get(route('user.boarding-houses.show', $house))
        ->assertOk()
        ->assertSee($imageUrl, false)
        ->assertSee('data-renter-photo-carousel', false)
        ->assertSee('Previous property photo')
        ->assertSee('Next property photo')
        ->assertDontSee('gallery-thumbnail');
});

it('uses the first ordered photo as the listing background and safely promotes the next photo after removal', function () {
    Storage::fake('public');
    $owner = User::factory()->verifiedOwner()->create();
    $house = BoardingHouse::factory()->create(['owner_id' => $owner->id]);
    $first = BoardingHouseImage::create([
        'boarding_house_id' => $house->id,
        'image_path' => tinyPropertyPng('first.png')->store('boarding-houses', 'public'),
        'is_primary' => true,
        'sort_order' => 0,
    ]);
    $second = BoardingHouseImage::create([
        'boarding_house_id' => $house->id,
        'image_path' => tinyPropertyPng('second.png')->store('boarding-houses', 'public'),
        'is_primary' => false,
        'sort_order' => 1,
    ]);

    $this->actingAs($owner)
        ->put(route('owner.listings.update', $house), [
            'name' => $house->name,
            'address' => $house->address,
            'image_order' => [$second->id, $first->id],
            'is_active' => '1',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($first->refresh()->is_primary)->toBeFalse()
        ->and($second->refresh()->is_primary)->toBeTrue()
        ->and($house->fresh()->images->first()->is($second))->toBeTrue()
        ->and($house->fresh()->cover_image_path)->toBe($second->image_path);

    $removedPath = $second->image_path;
    $this->actingAs($owner)
        ->delete(route('owner.listings.photos.destroy', [$house, $second]))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('boarding_house_images', ['id' => $second->id]);
    Storage::disk('public')->assertMissing($removedPath);
    expect($first->refresh()->is_primary)->toBeTrue();
});

it('prevents an owner from changing another owners property photos', function () {
    Storage::fake('public');
    $owner = User::factory()->verifiedOwner()->create();
    $otherOwner = User::factory()->verifiedOwner()->create();
    $house = BoardingHouse::factory()->create(['owner_id' => $owner->id]);

    $this->actingAs($otherOwner)
        ->post(route('owner.listings.photos.store', $house), [
            'photos' => [tinyPropertyPng('unauthorized.png')],
        ])
        ->assertForbidden();

    expect($house->images()->count())->toBe(0);
});
