<?php

use App\Models\BoardingHouse;
use App\Models\User;

test('owner property delete confirmation is a compact accessible dialog', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Compact Confirmation House',
    ]);

    $this->actingAs($owner)
        ->get(route('owner.boarding-houses', ['owner' => 'mine']))
        ->assertOk()
        ->assertSee('Delete this boarding house?')
        ->assertSee('Compact Confirmation House')
        ->assertSee('Go Back')
        ->assertSee('Yes, Delete')
        ->assertSee('bm-delete-confirmation', false)
        ->assertSee('aria-labelledby="delete-boarding-house-title"', false)
        ->assertSee('confirmSubmitting', false)
        ->assertDontSee('Delete this property?');
});
