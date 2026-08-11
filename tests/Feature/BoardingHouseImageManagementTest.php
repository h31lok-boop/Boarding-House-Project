<?php

use App\Models\BoardingHouse;
use App\Models\BoardingHouseImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function imageAdmin(): User
{
    return User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
}

function imageTenant(): User
{
    return User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
}

test('admin can upload multiple boarding house photos and select a cover', function () {
    Storage::fake('public');
    $admin = imageAdmin();

    $response = $this->actingAs($admin)->post(route('admin.listings.store'), [
        'name' => 'Photo Ready Residence',
        'address' => 'Matti, Digos City',
        'approval_status' => 'approved',
        'is_active' => '1',
        'photos' => [
            testImageUpload('front.png'),
            testImageUpload('room.png'),
        ],
        'cover_selection' => 'new:1',
    ]);

    $response->assertRedirect(route('admin.listings'));

    $house = BoardingHouse::where('name', 'Photo Ready Residence')->firstOrFail();
    $images = $house->images()->get();

    expect($images)->toHaveCount(2)
        ->and($images->where('is_primary', true))->toHaveCount(1)
        ->and($images->firstWhere('is_primary', true)?->image_label)->toBe('room');

    $images->each(fn (BoardingHouseImage $image) => Storage::disk('public')->assertExists($image->image_path));
});

test('admin can remove reorder and replace boarding house photos', function () {
    Storage::fake('public');
    $admin = imageAdmin();
    $house = BoardingHouse::factory()->create([
        'owner_id' => $admin->id,
        'name' => 'Managed Photos House',
        'address' => 'Poblacion, Digos City',
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    Storage::disk('public')->put('boarding-houses/old-cover.jpg', 'old cover');
    Storage::disk('public')->put('boarding-houses/keep.jpg', 'keep');

    $oldCover = $house->images()->create([
        'image_path' => 'boarding-houses/old-cover.jpg',
        'is_primary' => true,
        'sort_order' => 0,
    ]);
    $keep = $house->images()->create([
        'image_path' => 'boarding-houses/keep.jpg',
        'is_primary' => false,
        'sort_order' => 1,
    ]);
    $house->forceFill(['featured_image' => $oldCover->image_path])->save();

    $response = $this->actingAs($admin)->put(route('admin.listings.update', $house), [
        'name' => $house->name,
        'address' => $house->address,
        'approval_status' => 'approved',
        'is_active' => '1',
        'remove_image_ids' => [$oldCover->id],
        'image_order' => [$keep->id],
        'photos' => [testImageUpload('replacement.png')],
        'cover_selection' => 'new:0',
    ]);

    $response->assertRedirect(route('admin.listings'));

    $house->refresh()->load('images');
    $replacement = $house->images->firstWhere('image_label', 'replacement');

    expect($house->images)->toHaveCount(2)
        ->and($house->images->pluck('id'))->not->toContain($oldCover->id)
        ->and($replacement)->not->toBeNull()
        ->and($replacement->is_primary)->toBeTrue()
        ->and($house->featured_image)->toBe($replacement->image_path);

    Storage::disk('public')->assertMissing('boarding-houses/old-cover.jpg');
    Storage::disk('public')->assertExists('boarding-houses/keep.jpg');
    Storage::disk('public')->assertExists($replacement->image_path);
});

test('admin edit form can upload a new cover without landlord info validation errors', function () {
    Storage::fake('public');
    $admin = imageAdmin();
    $house = BoardingHouse::factory()->create([
        'owner_id' => $admin->id,
        'name' => 'Inline Upload House',
        'address' => 'Purok 3, Matti, Digos City',
        'landlord_info' => null,
        'contact_name' => null,
        'contact_phone' => null,
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    $response = $this->actingAs($admin)->put(route('admin.listings.update', $house), [
        'name' => $house->name,
        'address' => $house->address,
        'landlord_info' => '',
        'contact_name' => '',
        'contact_phone' => '',
        'approval_status' => 'approved',
        'is_active' => '1',
        'photos' => [testImageUpload('new-cover.png')],
        'cover_selection' => 'new:0',
    ]);

    $response->assertRedirect(route('admin.listings'));

    $house->refresh()->load('images');
    $cover = $house->images->firstWhere('is_primary', true);

    expect($cover)->not->toBeNull()
        ->and($cover->image_label)->toBe('new-cover')
        ->and($house->featured_image)->toBe($cover->image_path)
        ->and($house->cover_image_url)->toContain($cover->image_path);

    Storage::disk('public')->assertExists($cover->image_path);
});

test('tenant details gallery uses uploaded photos and listings use the placeholder without photos', function () {
    Storage::fake('public');
    $tenant = imageTenant();
    $withPhoto = BoardingHouse::factory()->create([
        'name' => 'Gallery House',
        'address' => 'Matti, Digos City',
        'is_active' => true,
        'approval_status' => 'approved',
        'status' => 'approved',
        'available_rooms' => 1,
    ]);
    $withoutPhoto = BoardingHouse::factory()->create([
        'name' => 'Placeholder House',
        'address' => 'Poblacion, Digos City',
        'is_active' => true,
        'approval_status' => 'approved',
        'status' => 'approved',
        'available_rooms' => 1,
    ]);

    Storage::disk('public')->put('boarding-houses/gallery.jpg', 'gallery');
    $withPhoto->images()->create([
        'image_path' => 'boarding-houses/gallery.jpg',
        'is_primary' => true,
        'sort_order' => 0,
    ]);

    $this->actingAs($tenant)
        ->get(route('user.boarding-houses.show', $withPhoto))
        ->assertOk()
        ->assertSee('galleryMainImage')
        ->assertSee('boarding-houses/gallery.jpg')
        ->assertSee('data-renter-photo-carousel', false)
        ->assertDontSee('gallery-thumbnail');

    $this->actingAs($tenant)
        ->get(route('user.boarding-houses.index', ['tab' => 'all']))
        ->assertOk()
        ->assertSee('Placeholder House')
        ->assertSee('images/boarding-house-placeholder.svg');
});
