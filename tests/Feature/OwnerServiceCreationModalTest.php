<?php

use App\Models\BoardingHouse;
use App\Models\BoardingHouseService;
use App\Models\User;

test('owner creates a property service from the shared modal', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    $this->actingAs($owner)
        ->get(route('owner.services.index'))
        ->assertOk()
        ->assertSee('data-add-service-trigger', false)
        ->assertSee('data-create-service-modal', false)
        ->assertSee('data-owner-service-property', false)
        ->assertSee('Add Service')
        ->assertSee($house->name)
        ->assertSee('This service will be linked automatically to your property.')
        ->assertDontSee('Select a boarding house')
        ->assertDontSee('Create a service');

    $this->actingAs($owner)
        ->post(route('owner.services.store'), [
            'form_context' => 'create_service',
            'name' => 'Laundry Service',
            'description' => 'Wash and dry one regular load.',
            'price' => 120,
            'billing_type' => 'per_use',
            'is_active' => 1,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(BoardingHouseService::query()
        ->where('boarding_house_id', $house->id)
        ->where('name', 'Laundry Service')
        ->exists())->toBeTrue();
});

test('owner service validation errors reopen the creation modal', function () {
    $owner = User::factory()->verifiedOwner()->create();

    $response = $this->actingAs($owner)
        ->from(route('owner.services.index'))
        ->post(route('owner.services.store'), [
            'form_context' => 'create_service',
        ]);

    $response->assertRedirect(route('owner.services.index'))
        ->assertSessionHasErrors(['boarding_house_id', 'name', 'price', 'billing_type']);

    $this->actingAs($owner)
        ->get(route('owner.services.index'))
        ->assertOk()
        ->assertSee('createOpen: true', false)
        ->assertSee('Please check the service details.');
});
