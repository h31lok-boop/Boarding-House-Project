<?php

use App\Models\BoardingHouse;
use App\Models\Inquiry;
use App\Models\OwnerProfile;
use App\Models\User;
use App\Models\UserNotification;

test('admin owner and tenant message pages are scoped to their roles', function () {
    $admin = User::factory()->create(['name' => 'Scope Admin', 'role' => 'admin']);
    $ownerA = User::factory()->verifiedOwner()->create(['name' => 'Owner Alpha']);
    $ownerB = User::factory()->verifiedOwner()->create(['name' => 'Owner Bravo']);
    $tenantA = User::factory()->create(['name' => 'Tenant Alpha Scope', 'role' => 'user']);
    $tenantB = User::factory()->create(['name' => 'Tenant Bravo Scope', 'role' => 'user']);

    $houseA = BoardingHouse::factory()->create([
        'owner_id' => $ownerA->id,
        'name' => 'Alpha Role House',
        'approval_status' => 'approved',
        'is_active' => true,
    ]);
    $houseB = BoardingHouse::factory()->create([
        'owner_id' => $ownerB->id,
        'name' => 'Bravo Role House',
        'approval_status' => 'approved',
        'is_active' => true,
    ]);

    Inquiry::create([
        'user_id' => $tenantA->id,
        'boarding_house_id' => $houseA->id,
        'message' => 'Alpha private role message',
        'status' => 'pending',
    ]);
    $bravoInquiry = Inquiry::create([
        'user_id' => $tenantB->id,
        'boarding_house_id' => $houseB->id,
        'message' => 'Bravo private role message',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.messages'))
        ->assertOk()
        ->assertSee('Alpha private role message')
        ->assertSee('Bravo private role message');

    $this->actingAs($ownerA)
        ->get(route('owner.messages'))
        ->assertOk()
        ->assertSee('Alpha private role message')
        ->assertDontSee('Bravo private role message');

    $this->actingAs($tenantA)
        ->get(route('user.messages.index'))
        ->assertOk()
        ->assertSee('Alpha Role House')
        ->assertDontSee('Bravo Role House');

    $this->actingAs($admin)
        ->patch(route('admin.inquiries.update', $bravoInquiry), [
            'status' => 'replied',
            'reply' => 'Admin role response',
        ])
        ->assertRedirect();

    $adminReply = UserNotification::query()
        ->where('user_id', $tenantB->id)
        ->where('reference_id', 'inquiry:'.$bravoInquiry->id)
        ->sole();

    expect($adminReply->data['sender_name'])->toBe('Scope Admin')
        ->and($adminReply->data['sender_role'])->toBe('BoardMatch Admin');

    $this->actingAs($tenantB)
        ->get(route('user.messages.index'))
        ->assertOk()
        ->assertSee('Admin role response')
        ->assertSee('Scope Admin')
        ->assertSee('BoardMatch Admin');
});

test('tenant messages notify only the selected property owner and preserve reply identity', function () {
    $owner = User::factory()->create([
        'name' => 'Maria Owner',
        'role' => 'owner',
        'status' => 'active',
        'is_active' => true,
    ]);
    $otherOwner = User::factory()->create(['name' => 'Other Owner', 'role' => 'owner']);
    $tenant = User::factory()->create(['name' => 'Paolo Tenant', 'role' => 'user']);

    $ownerProfile = OwnerProfile::create([
        'user_id' => $owner->id,
        'valid_id_type' => 'National ID',
        'valid_id_number' => 'OWNER-MSG-001',
        'valid_id_file' => 'tests/owner-id.png',
        'verification_status' => 'verified',
    ]);

    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'owner_profile_id' => $ownerProfile->id,
        'name' => 'Role Delivery House',
        'approval_status' => 'approved',
        'is_active' => true,
    ]);

    $this->actingAs($tenant)
        ->post(route('user.messages.store'), [
            'boarding_house_id' => $house->id,
            'message' => '<b>Is a room available?</b>',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $inquiry = Inquiry::query()->where('user_id', $tenant->id)->sole();

    expect($inquiry->boarding_house_id)->toBe($house->id)
        ->and($inquiry->owner_profile_id)->toBe($ownerProfile->id)
        ->and($inquiry->message)->toBe('Is a room available?')
        ->and($inquiry->inquiry_number)->not->toBeNull();

    $ownerAlert = UserNotification::query()
        ->where('user_id', $owner->id)
        ->where('reference_id', 'inquiry:'.$inquiry->id.':owner')
        ->sole();

    expect($ownerAlert->is_read)->toBeFalse()
        ->and(UserNotification::query()->where('user_id', $otherOwner->id)->count())->toBe(0);

    $this->actingAs($owner)
        ->patch(route('owner.inquiries.update', $inquiry), [
            'status' => 'replied',
            'reply' => 'Yes, a room is available this week.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $tenantReply = UserNotification::query()
        ->where('user_id', $tenant->id)
        ->where('reference_id', 'inquiry:'.$inquiry->id)
        ->sole();

    expect($tenantReply->title)->toBe('Property Owner replied')
        ->and($tenantReply->data['sender_name'])->toBe('Maria Owner')
        ->and($tenantReply->data['sender_role'])->toBe('Property Owner')
        ->and($ownerAlert->fresh()->is_read)->toBeTrue();

    $this->actingAs($tenant)
        ->get(route('user.messages.index'))
        ->assertOk()
        ->assertSee('Yes, a room is available this week.');
});

test('tenant cannot message an inactive or unapproved property', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $tenant = User::factory()->create(['role' => 'user']);
    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'approval_status' => 'pending',
        'is_active' => false,
    ]);

    $this->actingAs($tenant)
        ->from(route('user.messages.index'))
        ->post(route('user.messages.store'), [
            'boarding_house_id' => $house->id,
            'message' => 'This should not be delivered.',
        ])
        ->assertRedirect(route('user.messages.index'))
        ->assertSessionHasErrors('boarding_house_id');

    expect(Inquiry::query()->where('user_id', $tenant->id)->exists())->toBeFalse();
});
