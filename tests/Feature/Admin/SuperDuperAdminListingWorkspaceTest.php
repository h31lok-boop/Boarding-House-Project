<?php

use App\Models\BoardingHouse;
use App\Models\User;

test('superduperadmin dashboard no longer renders map or create listing sections inline', function () {
    $user = makeSuperDuperAdminUser();

    $response = $this->actingAs($user)->get(route('superduperadmin.dashboard'));

    $response->assertOk()
        ->assertSee('Owner Workspace')
        ->assertSee('Create Listing')
        ->assertDontSee('id="boardingHouseMap"', false)
        ->assertDontSee('id="createMap"', false)
        ->assertDontSee(route('superduperadmin.boarding-houses.store'), false);
});

test('superduperadmin map page shows marker data without rendering the create listing form', function () {
    $user = makeSuperDuperAdminUser();

    BoardingHouse::factory()->create([
        'name' => 'Map House Alpha',
        'owner_id' => $user->id,
        'status' => 'approved',
        'price' => 4500,
        'available_rooms' => 3,
    ]);

    $response = $this->actingAs($user)->get(route('superduperadmin.map'));

    $response->assertOk()
        ->assertSee('Boarding House Map')
        ->assertSee('Geotagging Controls')
        ->assertSee('Map House Alpha')
        ->assertSee('id="boardingHouseMap"', false)
        ->assertDontSee(route('superduperadmin.boarding-houses.store'), false);
});

test('superduperadmin create listing page renders the add boarding house form on its own route', function () {
    $user = makeSuperDuperAdminUser();

    $response = $this->actingAs($user)->get(route('superduperadmin.boarding-houses.create'));

    $response->assertOk()
        ->assertSee('Add Boarding House')
        ->assertSee('Location Picker')
        ->assertSee('id="createMap"', false)
        ->assertSee(route('superduperadmin.boarding-houses.store'), false)
        ->assertDontSee('id="boardingHouseMap"', false);
});

test('superduperadmin listing table page stays separate from the create listing form', function () {
    $user = makeSuperDuperAdminUser();

    BoardingHouse::factory()->create([
        'name' => 'Table House Beta',
        'owner_id' => $user->id,
        'status' => 'pending',
        'price' => 5200,
        'available_rooms' => 2,
    ]);

    $response = $this->actingAs($user)->get(route('superduperadmin.boarding-houses.index'));

    $response->assertOk()
        ->assertSee('All Boarding Houses')
        ->assertSee('Table House Beta')
        ->assertDontSee(route('superduperadmin.boarding-houses.store'), false)
        ->assertDontSee('id="createMap"', false)
        ->assertDontSee('id="boardingHouseMap"', false);
});

test('creating a boarding house from create listing redirects to listing table and assigns the current owner', function () {
    $user = makeSuperDuperAdminUser('creator@example.com');

    $response = $this->actingAs($user)->post(route('superduperadmin.boarding-houses.store'), [
        'name' => 'Created House',
        'address' => '123 Sample Street',
        'description' => 'Fresh listing from the dedicated create page.',
        'latitude' => '6.7284000',
        'longitude' => '125.3567000',
        'price' => '4800',
        'available_rooms' => '4',
        'contact_number' => '+639171234567',
        'status' => 'draft',
        'amenities' => 'Wi-Fi, Laundry Area',
    ]);

    $response->assertRedirect(route('superduperadmin.boarding-houses.index'))
        ->assertSessionHas('success');

    $house = BoardingHouse::where('name', 'Created House')->firstOrFail();

    expect((int) $house->owner_id)->toBe($user->id);
    expect((string) $house->status)->toBe('draft');
    expect((float) $house->latitude)->toBe(6.7284);
    expect((float) $house->longitude)->toBe(125.3567);
});

function makeSuperDuperAdminUser(string $email = 'owner.workspace@example.com'): User
{
    return User::factory()->create([
        'name' => 'Workspace Owner',
        'email' => $email,
        'role' => 'superduperadmin',
        'is_active' => true,
        'is_archived' => false,
    ]);
}
