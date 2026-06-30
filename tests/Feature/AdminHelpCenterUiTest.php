<?php

use App\Models\SupportRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin help center renders the owner support dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    SupportRequest::query()->create([
        'user_id' => $admin->id,
        'full_name' => 'Owner Admin',
        'email' => 'owner@example.com',
        'concern_type' => 'Payment Concern',
        'subject' => 'Receipt verification follow-up',
        'message' => 'A tenant payment receipt still needs confirmation.',
        'status' => 'Pending',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.help-center.index'))
        ->assertOk()
        ->assertSee('Owner Support Dashboard')
        ->assertSee('Professional property management support')
        ->assertSee('Support Overview')
        ->assertSee('Popular Help Categories')
        ->assertSee('Reservations')
        ->assertSee('Payments')
        ->assertSee('Tenants')
        ->assertSee('Listings')
        ->assertSee('Messages')
        ->assertSee('Searchable FAQ Section')
        ->assertSee('Recent Support Activity')
        ->assertSee('Quick Actions')
        ->assertSee('Resource Links')
        ->assertSee('System Status')
        ->assertSee('Contact Support')
        ->assertSee('Email Support')
        ->assertSee('support@boardmatch.ph')
        ->assertSee('Receipt verification follow-up')
        ->assertSee('Open Reservation Queue')
        ->assertDontSee('Portfolio HQ')
        ->assertDontSee('Track portfolio performance, tenant activity, earnings, and action queues from one owner workspace.');
});
