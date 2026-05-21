<?php

use App\Models\BoardingHouse;
use App\Models\BoardingHouseApplication;
use App\Models\Inquiry;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['admin', 'owner', 'tenant'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

test('admin approval creates a tenant occupancy record', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $admin->syncRoles(['admin']);

    $owner = User::factory()->create([
        'role' => 'owner',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $owner->syncRoles(['owner']);

    $tenant = User::factory()->create([
        'role' => 'tenant',
        'is_active' => false,
        'email_verified_at' => now(),
    ]);
    $tenant->syncRoles(['tenant']);

    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $application = BoardingHouseApplication::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.applications.approve', $application));

    $response->assertRedirect(route('admin.boarding-houses.index'));

    $tenant->refresh();

    expect((int) $tenant->boarding_house_id)->toBe($house->id)
        ->and($tenant->is_active)->toBeTrue();

    $this->assertDatabaseHas('tenants', [
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'status' => 'active',
    ]);

    $this->assertDatabaseHas('boarding_house_applications', [
        'id' => $application->id,
        'status' => 'approved',
    ]);
});

test('tenant dashboard shows owner booking updates, inquiry replies, and applications', function () {
    $owner = User::factory()->create([
        'name' => 'Owner Reply',
        'role' => 'owner',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $owner->syncRoles(['owner']);

    $tenant = User::factory()->create([
        'name' => 'Tenant View',
        'role' => 'tenant',
        'boarding_house_id' => null,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $tenant->syncRoles(['tenant']);

    $house = BoardingHouse::factory()->create([
        'name' => 'Connected House',
        'owner_id' => $owner->id,
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $room = Room::create([
        'boarding_house_id' => $house->id,
        'room_no' => 'C-301',
        'room_number' => 'C-301',
        'name' => 'Corner Room',
        'price' => 3800,
        'capacity' => 2,
        'available_slots' => 1,
        'status' => 'Available',
    ]);

    BoardingHouseApplication::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'status' => 'approved',
    ]);

    Reservation::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'check_in_date' => now()->addDays(7)->toDateString(),
        'check_out_date' => now()->addDays(37)->toDateString(),
        'status' => 'confirmed',
        'notes' => 'Tenant reservation notes.',
        'owner_notes' => 'Approved for move-in.',
        'processed_at' => now(),
        'processed_by' => $owner->id,
    ]);

    Inquiry::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'message' => 'Is the room still available next week?',
        'response_message' => 'Yes, the room is reserved for your requested dates.',
        'status' => 'replied',
        'responded_by' => $owner->id,
        'replied_at' => now(),
    ]);

    $response = $this->actingAs($tenant)->get(route('tenant.dashboard', ['section' => 'messages']));

    $response->assertOk()
        ->assertSee('Reservation Management')
        ->assertSee('Approved for move-in.')
        ->assertSee('Messages / Communication')
        ->assertSee('Yes, the room is reserved for your requested dates.')
        ->assertSee('Application Management')
        ->assertSee('Connected House');
});
