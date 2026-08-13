<?php

use App\Models\BoardingHouse;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function ownerIsolationFixture(): array
{
    $owner = User::factory()->verifiedOwner()->create();
    $otherOwner = User::factory()->verifiedOwner()->create();
    $tenantUser = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
    $otherTenantUser = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);

    $house = BoardingHouse::factory()->create(['owner_id' => $owner->id]);
    $otherHouse = BoardingHouse::factory()->create(['owner_id' => $otherOwner->id]);
    $room = Room::create([
        'boarding_house_id' => $house->id,
        'room_no' => 'A-1',
        'status' => 'available',
        'price' => 3500,
    ]);
    $otherRoom = Room::create([
        'boarding_house_id' => $otherHouse->id,
        'room_no' => 'B-1',
        'status' => 'available',
        'price' => 4500,
    ]);
    $tenant = Tenant::create([
        'user_id' => $tenantUser->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'status' => 'active',
    ]);
    $otherTenant = Tenant::create([
        'user_id' => $otherTenantUser->id,
        'boarding_house_id' => $otherHouse->id,
        'room_id' => $otherRoom->id,
        'status' => 'active',
    ]);

    return compact(
        'owner',
        'otherOwner',
        'tenantUser',
        'otherTenantUser',
        'house',
        'otherHouse',
        'room',
        'otherRoom',
        'tenant',
        'otherTenant'
    );
}

test('owners cannot inspect rooms from another owners property API', function () {
    $data = ownerIsolationFixture();

    $this->actingAs($data['owner'])
        ->get(route('owner.api.boarding-houses.available-rooms', $data['otherHouse']))
        ->assertForbidden();

    $this->actingAs($data['owner'])
        ->get(route('owner.api.boarding-houses.available-rooms', $data['house']))
        ->assertOk()
        ->assertJsonPath('rooms.0.id', $data['room']->id);
});

test('owners cannot update another owners payments inquiries or tenants', function () {
    $data = ownerIsolationFixture();
    $foreignPayment = Payment::create([
        'tenant_id' => $data['otherTenant']->id,
        'boarding_house_id' => $data['otherHouse']->id,
        'amount' => 4500,
        'status' => 'pending',
    ]);
    $foreignInquiry = Inquiry::create([
        'user_id' => $data['otherTenantUser']->id,
        'boarding_house_id' => $data['otherHouse']->id,
        'message' => 'Private inquiry',
        'status' => 'new',
    ]);

    $this->actingAs($data['owner'])
        ->patch(route('owner.payments.update', $foreignPayment), [
            'status' => 'paid',
        ])
        ->assertForbidden();

    $this->actingAs($data['owner'])
        ->patch(route('owner.inquiries.update', $foreignInquiry), [
            'status' => 'closed',
        ])
        ->assertForbidden();

    $this->actingAs($data['owner'])
        ->patch(route('owner.tenant-profiles.update', $data['otherTenantUser']), [
            'name' => 'Changed by foreign owner',
        ])
        ->assertForbidden();

    expect($foreignPayment->fresh()->status)->toBe('pending');
    expect($foreignInquiry->fresh()->status)->toBe('new');
    expect($data['otherTenantUser']->fresh()->name)->not->toBe('Changed by foreign owner');
});

test('owner notification actions affect only the signed in owner', function () {
    $data = ownerIsolationFixture();

    $ownId = DB::table('notifications')->insertGetId([
        'user_id' => $data['owner']->id,
        'type' => 'system',
        'title' => 'Owner notice',
        'message' => 'Visible to this owner',
        'is_read' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $foreignId = DB::table('notifications')->insertGetId([
        'user_id' => $data['otherOwner']->id,
        'type' => 'system',
        'title' => 'Foreign notice',
        'message' => 'Must remain private',
        'is_read' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($data['owner'])
        ->patch(route('owner.notifications.update', $foreignId), ['action' => 'mark_read'])
        ->assertSessionHas('error');

    $this->actingAs($data['owner'])
        ->delete(route('owner.notifications.clear'))
        ->assertSessionHas('success');

    expect(DB::table('notifications')->where('id', $ownId)->exists())->toBeFalse();
    expect(DB::table('notifications')->where('id', $foreignId)->exists())->toBeTrue();
    expect((bool) DB::table('notifications')->where('id', $foreignId)->value('is_read'))->toBeFalse();
});

test('owners can manage reviews only for their own properties', function () {
    $data = ownerIsolationFixture();
    $ownReview = Review::create([
        'user_id' => $data['tenantUser']->id,
        'boarding_house_id' => $data['house']->id,
        'rating' => 5,
        'comment' => 'A verified review for the owner.',
        'status' => 'pending',
    ]);
    $foreignReview = Review::create([
        'user_id' => $data['otherTenantUser']->id,
        'boarding_house_id' => $data['otherHouse']->id,
        'rating' => 2,
        'comment' => 'This review belongs to another owner.',
        'status' => 'pending',
    ]);

    $this->actingAs($data['owner'])
        ->get(route('owner.reviews'))
        ->assertOk()
        ->assertSee('A verified review for the owner.')
        ->assertDontSee('This review belongs to another owner.');

    $this->actingAs($data['owner'])
        ->patch(route('owner.reviews.update', $foreignReview), ['status' => 'hidden'])
        ->assertForbidden();

    $this->actingAs($data['owner'])
        ->patch(route('owner.reviews.update', $ownReview), ['status' => 'published'])
        ->assertRedirect();

    expect($ownReview->fresh()->status)->toBe('published')
        ->and($foreignReview->fresh()->status)->toBe('pending');
});
