<?php

use App\Models\BoardingHouse;
use App\Models\User;

test('my boarding house only shows properties owned by the signed in owner account', function () {
    $owner = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $otherOwner = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Owner Scoped House',
        'address' => 'Matti, Digos City',
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    BoardingHouse::factory()->create([
        'owner_id' => $otherOwner->id,
        'name' => 'Other Owner House',
        'address' => 'Poblacion, Digos City',
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    BoardingHouse::factory()->create([
        'owner_id' => null,
        'name' => 'Unassigned House',
        'address' => 'Aplaya, Digos City',
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    $this->actingAs($owner)
        ->get(route('admin.my-boarding-house'))
        ->assertOk()
        ->assertSee('Boarding Houses')
        ->assertSee('Owner Scoped House')
        ->assertDontSee('Other Owner House')
        ->assertDontSee('Unassigned House');
});

test('my boarding house actions redirect back to the owner scoped page', function () {
    $owner = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Redirect Test House',
        'address' => 'Matti, Digos City',
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    $this->actingAs($owner)
        ->put(route('admin.listings.update', $house), [
            'name' => 'Redirect Test House Updated',
            'address' => 'Matti, Digos City',
            'return_to_my_boarding_house' => '1',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.my-boarding-house'));

    $this->actingAs($owner)
        ->delete(route('admin.listings.destroy', $house), [
            'return_to_my_boarding_house' => '1',
        ])
        ->assertRedirect(route('admin.my-boarding-house'));
});
