<?php

use App\Models\BoardingHouse;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoommateMatchRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function tenantDashboardActors(): array
{
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

    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Casa Esperanza Dormitory',
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    return [$tenant, $owner, $house];
}

test('dashboard shows the real pending reservation with its hold countdown', function () {
    [$tenant, $owner, $house] = tenantDashboardActors();

    $room = Room::query()->create([
        'boarding_house_id' => $house->id,
        'room_no' => 'C-301',
        'price' => 3500,
        'status' => 'Reserved',
        'capacity' => 2,
        'available_slots' => 0,
    ]);

    Reservation::query()->create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'room_id' => $room->id,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'expires_at' => now()->addHours(36),
        'total_amount' => 3500,
    ]);

    $this->actingAs($tenant)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertSee('Casa Esperanza Dormitory')
        ->assertSee('Pending Review')
        ->assertSee('Hold expires')
        ->assertSee('Reservation Submitted')
        ->assertSee('C-301');
});

test('dashboard shows expired reservation state instead of a confirmed booking', function () {
    [$tenant, $owner, $house] = tenantDashboardActors();

    Reservation::query()->create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'status' => 'expired',
        'payment_status' => 'expired',
        'expired_at' => now()->subHour(),
        'expires_at' => now()->subHours(2),
        'total_amount' => 3500,
    ]);

    $this->actingAs($tenant)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertSee('Casa Esperanza Dormitory')
        ->assertSee('Expired')
        ->assertSee('Find a boarding house');
});

test('dashboard reports real roommate request counts and drops placeholder content', function () {
    [$tenant, $owner, $house] = tenantDashboardActors();

    $sender = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    RoommateMatchRequest::create([
        'sender_id' => $sender->id,
        'recipient_id' => $tenant->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($tenant)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertSee('Student dashboard')
        ->assertDontSee('Incoming Pending')
        ->assertDontSee('Maria Santos')
        ->assertDontSee('Sunrise Boarding House')
        ->assertDontSee('Maplewood Residences');

    expect($response->viewData('incomingPendingCount'))->toBe(1);
    expect($response->viewData('outgoingPendingCount'))->toBe(0);
});

test('dashboard formats saved listing activity when mysql returns timestamp text', function () {
    [$tenant, $owner, $house] = tenantDashboardActors();

    DB::table('favorites')->insert([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'created_at' => now()->subMinutes(5)->toDateTimeString(),
        'updated_at' => now()->subMinutes(5)->toDateTimeString(),
    ]);

    $this->actingAs($tenant)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertSee('Listing Saved')
        ->assertSee('Casa Esperanza Dormitory');
});
