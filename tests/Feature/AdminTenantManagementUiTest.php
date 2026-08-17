<?php

use App\Models\BoardingHouse;
use App\Models\OwnerProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('admin user management separates tenants owners and administrators', function () {
    Storage::fake('public');

    $admin = User::factory()->create([
        'name' => 'Platform Administrator',
        'role' => 'admin',
        'status' => 'active',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $tenant = User::factory()->create([
        'name' => 'Separate Tenant',
        'role' => 'user',
        'status' => 'active',
        'is_active' => true,
    ]);
    $owner = User::factory()->create([
        'name' => 'Pending Property Owner',
        'role' => 'owner',
        'status' => 'pending',
        'is_active' => false,
    ]);
    $permit = UploadedFile::fake()->create('business-permit.pdf', 120, 'application/pdf')->store('proof-of-ownership', 'public');
    OwnerProfile::create([
        'user_id' => $owner->id,
        'boarding_house_name' => 'Review House',
        'boarding_house_address' => 'Digos City',
        'proof_of_ownership' => $permit,
        'valid_id_file' => $permit,
        'verification_status' => 'pending',
    ]);
    BoardingHouse::create([
        'owner_id' => $owner->id,
        'user_id' => $owner->id,
        'name' => 'Review House',
        'address' => 'Digos City',
        'is_active' => false,
        'status' => 'pending',
        'approval_status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.user-management', ['account_type' => 'tenant']))
        ->assertOk()
        ->assertSee('User Management')
        ->assertSee('Tenants')
        ->assertSee('Owners')
        ->assertSee('Administrators')
        ->assertSee($tenant->name)
        ->assertDontSee($owner->name);

    $this->actingAs($admin)
        ->get(route('admin.user-management', ['account_type' => 'owner']))
        ->assertOk()
        ->assertSee('Owner Applications')
        ->assertSee('Awaiting Review')
        ->assertSee('Review application')
        ->assertSee('Approve')
        ->assertSee('Approve this owner application')
        ->assertSee('Business Permit')
        ->assertSee('Property Photos')
        ->assertSee('Access locked:')
        ->assertSee($owner->name)
        ->assertDontSee($tenant->name);

    $this->actingAs($admin)
        ->get(route('admin.tenants.index'))
        ->assertOk()
        ->assertSee('User Management')
        ->assertSee($tenant->name)
        ->assertDontSee($owner->name);
});

test('admin owner applications keep the list compact and show linked houses in the review modal', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
        'is_active' => true,
    ]);
    $owner = User::factory()->create([
        'name' => 'Multi Property Owner',
        'role' => 'owner',
        'status' => 'active',
        'is_active' => true,
    ]);
    $profile = OwnerProfile::create([
        'user_id' => $owner->id,
        'verification_status' => 'verified',
        'is_seeded_demo' => true,
    ]);

    BoardingHouse::create([
        'owner_id' => $owner->id,
        'owner_profile_id' => $profile->id,
        'name' => 'First Linked House',
        'address' => 'First Address',
        'is_active' => true,
        'status' => 'approved',
        'approval_status' => 'approved',
    ]);
    BoardingHouse::create([
        'owner_id' => $owner->id,
        'owner_profile_id' => $profile->id,
        'name' => 'Casa Digos Boarding Stay',
        'address' => 'Casa Digos Address',
        'is_active' => true,
        'status' => 'approved',
        'approval_status' => 'approved',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.user-management', ['account_type' => 'owner']))
        ->assertOk()
        ->assertSee($owner->name)
        ->assertSee('data-owner-property-summary="2"', false)
        ->assertSee('2 boarding houses')
        ->assertSee('Open the application to view all properties.')
        ->assertSee('First Linked House')
        ->assertSee('Casa Digos Boarding Stay');
});

test('seeded owners are visibly approved without a permit while real owners remain locked for review', function () {
    Storage::fake('public');

    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
        'is_active' => true,
    ]);

    $seededOwner = User::factory()->create([
        'name' => 'Seeded Demo Owner',
        'role' => 'owner',
        'status' => 'active',
        'account_status' => 'Active',
        'is_active' => true,
    ]);
    OwnerProfile::create([
        'user_id' => $seededOwner->id,
        'boarding_house_name' => 'Seeded Demo House',
        'verification_status' => 'verified',
        'is_seeded_demo' => true,
    ]);
    BoardingHouse::create([
        'owner_id' => $seededOwner->id,
        'user_id' => $seededOwner->id,
        'name' => 'Seeded Demo House',
        'address' => 'Digos City',
        'is_active' => true,
        'status' => 'approved',
        'approval_status' => 'approved',
    ]);

    $realOwner = User::factory()->create([
        'name' => 'Real Pending Owner',
        'role' => 'owner',
        'status' => 'pending',
        'account_status' => 'Pending',
        'is_active' => false,
    ]);
    $permit = UploadedFile::fake()->create('real-permit.pdf', 120, 'application/pdf')
        ->store('proof-of-ownership', 'public');
    OwnerProfile::create([
        'user_id' => $realOwner->id,
        'boarding_house_name' => 'Real Pending House',
        'proof_of_ownership' => $permit,
        'valid_id_file' => $permit,
        'verification_status' => 'pending',
        'is_seeded_demo' => false,
    ]);
    BoardingHouse::create([
        'owner_id' => $realOwner->id,
        'user_id' => $realOwner->id,
        'name' => 'Real Pending House',
        'address' => 'Digos City',
        'is_active' => false,
        'status' => 'pending',
        'approval_status' => 'pending',
    ]);

    expect($seededOwner->fresh()->hasApprovedOwnerAccess())->toBeTrue()
        ->and($realOwner->fresh()->hasApprovedOwnerAccess())->toBeFalse();

    $this->actingAs($admin)
        ->get(route('admin.user-management', ['account_type' => 'owner']))
        ->assertOk()
        ->assertSee('Seeded demo exemption')
        ->assertSee('Demo account enabled')
        ->assertSee('Pending approval')
        ->assertSee('Login blocked')
        ->assertSee('Seeded permit exemption')
        ->assertSee('The permit exemption applies only to seeded owners');

    auth()->logout();

    $this->post(route('login'), [
        'email' => $seededOwner->email,
        'password' => 'password',
        'role' => 'owner',
    ])->assertRedirect(route('owner.dashboard', absolute: false));

    $this->assertAuthenticatedAs($seededOwner);
});

test('admin owner approval requires both a stored permit and boarding house application', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'is_active' => true]);
    $owner = User::factory()->create(['role' => 'owner', 'status' => 'pending', 'is_active' => false]);
    $permit = UploadedFile::fake()->create('permit.pdf', 120, 'application/pdf')->store('proof-of-ownership', 'public');
    OwnerProfile::create([
        'user_id' => $owner->id,
        'proof_of_ownership' => $permit,
        'valid_id_file' => $permit,
        'verification_status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.owners.verify', $owner))
        ->assertSessionHas('error', 'Owner verification failed because the submitted boarding house application could not be found.');

    expect($owner->fresh()->is_active)->toBeFalse()
        ->and($owner->fresh()->status)->toBe('pending')
        ->and($owner->ownerProfile->fresh()->verification_status)->toBe('pending');
});

test('rejecting an owner keeps access and the submitted listing disabled with a review reason', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'is_active' => true]);
    $owner = User::factory()->create(['role' => 'owner', 'status' => 'pending', 'is_active' => false]);
    $permit = UploadedFile::fake()->create('permit.pdf', 120, 'application/pdf')->store('proof-of-ownership', 'public');
    OwnerProfile::create([
        'user_id' => $owner->id,
        'proof_of_ownership' => $permit,
        'valid_id_file' => $permit,
        'verification_status' => 'pending',
    ]);
    $house = BoardingHouse::create([
        'owner_id' => $owner->id,
        'user_id' => $owner->id,
        'name' => 'Rejected Review House',
        'address' => 'Digos City',
        'is_active' => false,
        'status' => 'pending',
        'approval_status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.owners.reject', $owner), [
            'rejection_reason' => 'The permit number is unreadable. Upload a clearer copy.',
        ])
        ->assertSessionHas('success');

    $this->assertDatabaseHas('users', ['id' => $owner->id, 'status' => 'rejected', 'is_active' => false]);
    $this->assertDatabaseHas('owner_profiles', ['user_id' => $owner->id, 'verification_status' => 'rejected']);
    $this->assertDatabaseHas('boarding_houses', [
        'id' => $house->id,
        'status' => 'rejected',
        'approval_status' => 'rejected',
        'is_active' => false,
        'rejection_reason' => 'The permit number is unreadable. Upload a clearer copy.',
    ]);
});

test('a verified owner cannot self publish an additional boarding house before admin listing approval', function () {
    Storage::fake('public');

    $owner = User::factory()->verifiedOwner()->create();
    Storage::disk('public')->put($owner->ownerProfile->proof_of_ownership, 'verified permit');

    $this->actingAs($owner)
        ->post(route('owner.listings.store'), [
            'name' => 'Second Review House',
            'address' => 'Matti, Digos City',
            'is_active' => '1',
            'approval_status' => 'approved',
        ])
        ->assertRedirect(route('owner.boarding-houses'));

    $this->assertDatabaseHas('boarding_houses', [
        'owner_id' => $owner->id,
        'name' => 'Second Review House',
        'status' => 'pending',
        'approval_status' => 'pending',
        'is_active' => false,
    ]);
});

test('the boarding house editor cannot publish a listing for an unverified owner', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'is_active' => true]);
    $owner = User::factory()->create(['role' => 'owner', 'status' => 'pending', 'is_active' => false]);
    $house = BoardingHouse::create([
        'owner_id' => $owner->id,
        'user_id' => $owner->id,
        'name' => 'Locked Application House',
        'address' => 'Digos City',
        'is_active' => false,
        'status' => 'pending',
        'approval_status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.listings.update', $house), [
            'name' => $house->name,
            'address' => $house->address,
            'owner_id' => $owner->id,
            'is_active' => '1',
            'approval_status' => 'approved',
        ])
        ->assertSessionHasErrors('approval_status');

    expect($house->fresh()->is_active)->toBeFalse()
        ->and($house->fresh()->approval_status)->toBe('pending');
});
