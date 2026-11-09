<?php

use App\Models\BoardingHouse;
use App\Models\BoardingHouseService;
use App\Models\User;

test('owner services starts with owned boarding houses and scopes selected property', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $other = User::factory()->verifiedOwner()->create();
    $alpha = BoardingHouse::factory()->create(['owner_id' => $owner->id, 'name' => 'Alpha House']);
    $beta = BoardingHouse::factory()->create(['owner_id' => $owner->id, 'name' => 'Beta House']);
    $foreign = BoardingHouse::factory()->create(['owner_id' => $other->id, 'name' => 'Foreign House']);
    BoardingHouseService::create(['boarding_house_id' => $alpha->id, 'name' => 'Laundry', 'description' => 'Wash service', 'pricing_type' => 'additional', 'price' => 100, 'billing_type' => 'per_use', 'is_active' => true]);
    BoardingHouseService::create(['boarding_house_id' => $foreign->id, 'name' => 'Foreign Service', 'description' => '', 'pricing_type' => 'additional', 'price' => 50, 'billing_type' => 'per_use', 'is_active' => true]);

    $this->actingAs($owner)->get(route('owner.services.index'))
        ->assertOk()
        ->assertSee('Select a boarding house to manage its services.')
        ->assertSee('Alpha House')
        ->assertSee('Beta House')
        ->assertDontSee('Foreign House')
        ->assertDontSee('+ Add Service');

    $this->actingAs($owner)->get(route('owner.services.index', ['boarding_house_id' => $alpha->id]))
        ->assertOk()
        ->assertSee('Alpha House')
        ->assertSee('Laundry')
        ->assertDontSee('Foreign Service');
});

test('owner service mutations keep property ownership and explicit pricing type', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $other = User::factory()->verifiedOwner()->create();
    $house = BoardingHouse::factory()->create(['owner_id' => $owner->id]);
    $foreignHouse = BoardingHouse::factory()->create(['owner_id' => $other->id]);
    $foreignService = BoardingHouseService::create(['boarding_house_id' => $foreignHouse->id, 'name' => 'Foreign', 'description' => '', 'pricing_type' => 'additional', 'price' => 10, 'billing_type' => 'per_use', 'is_active' => true]);

    $this->actingAs($owner)->post(route('owner.services.store'), [
        'boarding_house_id' => $house->id,
        'name' => 'Included Wi-Fi',
        'description' => 'Included with rent',
        'pricing_type' => 'included',
        'billing_type' => 'monthly',
        'is_active' => 1,
    ])->assertRedirect();

    expect(BoardingHouseService::query()->where('boarding_house_id', $house->id)->where('name', 'Included Wi-Fi')->first()->price)->toEqual('0.00');

    $this->actingAs($owner)->put(route('owner.services.update', $foreignService), [
        'name' => 'Changed', 'description' => '', 'pricing_type' => 'additional', 'price' => 20, 'billing_type' => 'per_use', 'is_active' => 1,
    ])->assertForbidden();
});
