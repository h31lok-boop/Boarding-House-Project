<?php

use App\Models\BoardingHouse;
use App\Models\Inquiry;
use App\Models\OwnerProfile;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Storage;

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
        ->assertSee('data-tenant-message-center', false)
        ->assertSee('Chats')
        ->assertSee('Property conversations')
        ->assertSee('Search messages')
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
    Storage::fake('public');

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
    Storage::disk('public')->put($ownerProfile->valid_id_file, 'Verified business permit');

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

test('tenant with no threads is guided to approved listings without exposing unrelated property names', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $tenant = User::factory()->create(['role' => 'user']);

    BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Visible Compose House',
        'approval_status' => 'approved',
        'is_active' => true,
    ]);
    BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Hidden Pending House',
        'approval_status' => 'pending',
        'is_active' => true,
    ]);

    $this->actingAs($tenant)
        ->get(route('user.messages.index'))
        ->assertOk()
        ->assertSee('data-tenant-message-center', false)
        ->assertDontSee('Visible Compose House')
        ->assertDontSee('Hidden Pending House')
        ->assertSee('Find boarding houses')
        ->assertSee('No conversations found');
});

test('tenant inbox keeps every message to the same owner in one contact conversation', function () {
    $owner = User::factory()->verifiedOwner()->create(['name' => 'Single Contact Owner']);
    $tenant = User::factory()->create(['name' => 'Conversation Tenant', 'role' => 'user']);
    $firstHouse = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'First Contact Property',
        'approval_status' => 'approved',
        'is_active' => true,
    ]);
    $secondHouse = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Second Contact Property',
        'approval_status' => 'approved',
        'is_active' => true,
    ]);

    Inquiry::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $firstHouse->id,
        'message' => 'Original message to this owner.',
        'status' => 'pending',
    ]);

    $this->actingAs($tenant)
        ->post(route('user.messages.store'), [
            'boarding_house_id' => $firstHouse->id,
            'message' => 'Follow-up in the existing conversation.',
        ])
        ->assertRedirect();

    $this->actingAs($tenant)
        ->post(route('user.messages.store'), [
            'boarding_house_id' => $secondHouse->id,
            'message' => 'Question about the same owner second property.',
        ])
        ->assertRedirect();

    $response = $this->actingAs($tenant)->get(route('user.messages.index'));

    $response->assertOk()
        ->assertSee('Original message to this owner.')
        ->assertSee('Follow-up in the existing conversation.')
        ->assertSee('Question about the same owner second property.')
        ->assertSee('First Contact Property +1 more')
        ->assertSee('if (selected.id) markRead(selected);', false);

    expect(substr_count($response->getContent(), 'data-tenant-conversation-thread'))->toBe(1);
});

test('owner inbox renders one navigation tab per tenant and property conversation', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $tenant = User::factory()->create(['name' => 'Grouped Tenant', 'role' => 'user']);
    $firstHouse = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Grouped House One',
    ]);
    $secondHouse = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Grouped House Two',
    ]);

    Inquiry::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $firstHouse->id,
        'message' => 'First message in the shared conversation.',
        'status' => 'pending',
    ]);
    Inquiry::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $firstHouse->id,
        'message' => 'Second message in the shared conversation.',
        'status' => 'open',
    ]);
    Inquiry::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $secondHouse->id,
        'message' => 'A separate property conversation.',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($owner)->get(route('owner.messages'));

    $response->assertOk()
        ->assertSee('First message in the shared conversation.')
        ->assertSee('Second message in the shared conversation.')
        ->assertSee('A separate property conversation.');

    expect(substr_count($response->getContent(), 'data-conversation-thread'))->toBe(2);
});

test('owner and admin keep independent read state for an open conversation', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->verifiedOwner()->create();
    $tenant = User::factory()->create(['role' => 'user']);
    $house = BoardingHouse::factory()->create(['owner_id' => $owner->id]);
    $first = Inquiry::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'message' => 'First unread message.',
        'status' => 'pending',
    ]);
    $second = Inquiry::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'message' => 'Second unread message.',
        'status' => 'pending',
    ]);

    $this->actingAs($owner)
        ->postJson(route('owner.messages.read', $second))
        ->assertOk();

    expect($first->fresh()->owner_read_at)->not->toBeNull()
        ->and($second->fresh()->owner_read_at)->not->toBeNull()
        ->and($first->fresh()->admin_read_at)->toBeNull()
        ->and($second->fresh()->admin_read_at)->toBeNull();

    $this->actingAs($admin)
        ->postJson(route('admin.messages.read', $first))
        ->assertOk();

    expect($first->fresh()->admin_read_at)->not->toBeNull()
        ->and($second->fresh()->admin_read_at)->not->toBeNull();
});
