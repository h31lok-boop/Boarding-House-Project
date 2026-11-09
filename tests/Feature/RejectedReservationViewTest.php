<?php

use App\Models\BoardingHouse;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('rejected reservation is a closed request and does not replace the tenant primary reservation', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
    $confirmedHouse = BoardingHouse::factory()->create(['owner_id' => $owner->id, 'name' => 'Cornejo House']);
    $rejectedHouse = BoardingHouse::factory()->create(['owner_id' => $owner->id, 'name' => 'Greenleaf House']);
    $confirmedRoom = Room::create(['boarding_house_id' => $confirmedHouse->id, 'room_no' => 'S-01', 'status' => 'Reserved', 'available_slots' => 0, 'price' => 2800]);
    $rejectedRoom = Room::create(['boarding_house_id' => $rejectedHouse->id, 'room_no' => 'G-02', 'status' => 'Available', 'available_slots' => 1, 'price' => 3500]);

    Tenant::create(['user_id' => $tenant->id, 'boarding_house_id' => $confirmedHouse->id, 'room_id' => $confirmedRoom->id, 'status' => 'active']);
    Reservation::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $confirmedHouse->id,
        'room_id' => $confirmedRoom->id,
        'status' => 'confirmed',
        'approved_at' => now()->subDay(),
        'total_amount' => 2800,
    ]);
    $rejected = Reservation::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $rejectedHouse->id,
        'room_id' => $rejectedRoom->id,
        'status' => 'rejected',
        'rejection_reason' => 'The requested room is no longer available.',
        'rejected_at' => now()->setTimezone('Asia/Manila')->subHours(3),
        'rejected_by' => $owner->id,
        'total_amount' => 3500,
    ]);

    $this->actingAs($tenant)->get(route('user.reservations.index', ['reservation' => $rejected->id]))
        ->assertOk()
        ->assertSee('Rejected by Owner')
        ->assertSee('Your reservation was not approved.')
        ->assertSee('The requested room is no longer available.')
        ->assertSee('Reviewed by Owner')
        ->assertSee('Rejected')
        ->assertDontSee('Owner Approved')
        ->assertDontSee('Cash Downpayment Recorded')
        ->assertDontSee('Cancel Request');

    $rejected->forceFill(['rejected_at' => null])->save();
    $this->actingAs($tenant)->get(route('user.reservations.index', ['reservation' => $rejected->id]))
        ->assertOk()
        ->assertSee('Date unavailable');

    $this->actingAs($tenant)->get(route('user.dashboard'))
        ->assertOk()
        ->assertSee('Cornejo House')
        ->assertDontSee('Greenleaf House');

    $otherTenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
    $this->actingAs($otherTenant)->get(route('user.reservations.index', ['reservation' => $rejected->id]))->assertNotFound();
});

test('owner rejection requires a reason, records its audit fields, and is idempotent', function () {
    $owner = User::factory()->verifiedOwner()->create();
    $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
    $house = BoardingHouse::factory()->create(['owner_id' => $owner->id]);
    $room = Room::create(['boarding_house_id' => $house->id, 'room_no' => 'R-01', 'status' => 'Reserved', 'available_slots' => 0, 'price' => 2800]);
    $reservation = Reservation::create(['user_id' => $tenant->id, 'boarding_house_id' => $house->id, 'room_id' => $room->id, 'status' => 'pending', 'total_amount' => 2800]);

    $this->actingAs($owner)->patch(route('owner.reservations.update', $reservation), ['status' => 'rejected'])
        ->assertSessionHasErrors('notes');
    expect($reservation->fresh()->status)->toBe('pending');

    $this->actingAs($owner)->patch(route('owner.reservations.update', $reservation), ['status' => 'rejected', 'notes' => 'Please choose an available room.'])
        ->assertRedirect();
    $rejected = $reservation->fresh();
    expect($rejected->status)->toBe('rejected')
        ->and($rejected->rejection_reason)->toBe('Please choose an available room.')
        ->and($rejected->rejected_by)->toBe($owner->id)
        ->and($rejected->rejected_at)->not->toBeNull();

    $notificationCount = DB::table('notifications')->where('user_id', $tenant->id)->where('reference_id', 'reservation:'.$reservation->id.':rejected')->count();
    $this->actingAs($owner)->patch(route('owner.reservations.update', $reservation), ['status' => 'rejected', 'notes' => 'A changed reason'])->assertRedirect();
    expect(DB::table('notifications')->where('user_id', $tenant->id)->where('reference_id', 'reservation:'.$reservation->id.':rejected')->count())->toBe($notificationCount)
        ->and($reservation->fresh()->rejection_reason)->toBe('Please choose an available room.');
});
