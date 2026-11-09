<?php

use App\Models\BoardingHouse;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('reserve now create mode keeps the selected property and does not open the primary tracker or create a request', function () {
    $tenant = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $owner = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $primaryHouse = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Primary House',
        'approval_status' => 'approved',
        'is_active' => true,
    ]);
    $selectedHouse = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Selected House',
        'approval_status' => 'approved',
        'is_active' => true,
    ]);

    $selectedRoom = Room::query()->create([
        'boarding_house_id' => $selectedHouse->id,
        'room_no' => 'S-01',
        'price' => 2800,
        'status' => 'Available',
        'capacity' => 2,
        'available_slots' => 2,
    ]);

    Reservation::query()->create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $primaryHouse->id,
        'status' => 'confirmed',
        'payment_status' => 'unpaid',
    ]);

    $response = $this->actingAs($tenant)->get(route('user.reservations.index', [
        'mode' => 'create',
        'property_id' => $selectedHouse->id,
        'room_id' => $selectedRoom->id,
    ]));

    $response->assertOk()
        ->assertViewHas('createMode', true)
        ->assertViewHas('selectedHouse', fn ($house) => $house?->is($selectedHouse))
        ->assertViewHas('selectedRoom', fn ($room) => $room?->is($selectedRoom))
        ->assertViewHas('trackingReservation', null)
        ->assertSee('Selected House')
        ->assertSee('Submit Reservation');

    expect(Reservation::query()->count())->toBe(1);
});

test('submitting create mode redirects to the exact new pending reservation', function () {
    $tenant = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $owner = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'approval_status' => 'approved',
        'is_active' => true,
    ]);
    $room = Room::query()->create([
        'boarding_house_id' => $house->id,
        'room_no' => 'S-02',
        'price' => 2800,
        'status' => 'Available',
        'capacity' => 1,
        'available_slots' => 1,
    ]);

    $response = $this->actingAs($tenant)->post(route('user.boarding-houses.reservations.store', $house), [
        'first_name' => 'Test',
        'last_name' => 'Tenant',
        'email' => $tenant->email,
        'phone' => '09123456789',
        'room_id' => $room->id,
        'check_in_date' => now()->addWeek()->toDateString(),
        'stay_duration' => 1,
        'occupants' => 1,
        'emergency_contact_name' => 'Emergency Contact',
        'emergency_contact_number' => '09123456788',
        'terms_accepted' => '1',
    ]);

    $reservation = Reservation::query()->latest('id')->firstOrFail();

    $response->assertRedirect(route('user.reservations.index', ['reservation' => $reservation->id]));
    expect($reservation->status)->toBe('pending');
});
