<?php

use App\Models\BoardingHouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('tenant payment reminder follows confirmed move-in and unpaid rent lifecycle', function () {
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
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $tenantRecordId = DB::table('tenants')->insertGetId([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'move_in_date' => null,
        'move_out_date' => null,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('payments')->insert([
        'tenant_id' => $tenantRecordId,
        'boarding_house_id' => $house->id,
        'amount' => 2800,
        'due_date' => now()->addDays(3)->toDateString(),
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // A confirmed/approved account without a completed move-in is not eligible.
    $this->actingAs($tenant)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertDontSee('Due date approaching');

    DB::table('tenants')->where('id', $tenantRecordId)->update([
        'move_in_date' => now()->toDateString(),
    ]);

    // After move-in, the actual unpaid rent row drives the banner amount/date.
    $this->actingAs($tenant)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertSee('Due date approaching')
        ->assertSee('PHP 2,800.00');

    DB::table('payments')->where('tenant_id', $tenantRecordId)->update([
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    // Paying the rent removes the reminder.
    $this->actingAs($tenant)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertDontSee('Due date approaching');

    DB::table('payments')->where('tenant_id', $tenantRecordId)->update([
        'status' => 'pending',
        'paid_at' => null,
    ]);
    DB::table('tenants')->where('id', $tenantRecordId)->update([
        'move_out_date' => now()->subDay()->toDateString(),
    ]);

    // An ended tenancy cannot receive a rent reminder.
    $this->actingAs($tenant)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertDontSee('Due date approaching');
});
