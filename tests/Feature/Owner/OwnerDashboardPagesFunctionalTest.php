<?php

use App\Models\BoardingHouse;
use App\Models\ComplianceRequirement;
use App\Models\Inquiry;
use App\Models\MaintenanceRequest;
use App\Models\Review;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('owner', 'web');
    Role::findOrCreate('tenant', 'web');
});

test('owner dashboard pages render with database backed data', function () {
    $owner = makeFunctionalOwner('owner-pages@example.com');
    $tenant = makeFunctionalTenant('tenant-pages@example.com');

    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'approval_status' => 'approved',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $room = Room::create([
        'boarding_house_id' => $house->id,
        'room_no' => 'P-101',
        'room_number' => 'P-101',
        'name' => 'Standard',
        'price' => 3500,
        'capacity' => 2,
        'available_slots' => 1,
        'status' => 'Available',
    ]);

    Inquiry::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'message' => 'Is this room available?',
        'status' => 'pending',
    ]);

    Review::create([
        'user_id' => $tenant->id,
        'boarding_house_id' => $house->id,
        'rating' => 5,
        'comment' => 'Clean and quiet.',
        'status' => 'approved',
    ]);

    MaintenanceRequest::create([
        'room_id' => $room->id,
        'user_id' => $tenant->id,
        'issue' => 'Light replacement',
        'priority' => 'Normal',
        'status' => 'Open',
    ]);

    ComplianceRequirement::create([
        'boarding_house_id' => $house->id,
        'submitted_by' => $owner->id,
        'requirement_name' => 'Business Permit',
        'uploaded_file' => 'compliance-documents/example.pdf',
        'submission_date' => now()->toDateString(),
        'validation_status' => 'pending',
    ]);

    foreach ([
        route('owner.dashboard'),
        route('owner.boarding-houses'),
        route('owner.rooms'),
        route('owner.inquiries.index'),
        route('owner.messages'),
        route('owner.bookings.index'),
        route('owner.feedback.index'),
        route('owner.compliance.index'),
        route('owner.reports'),
        route('owner.settings'),
        route('owner.maintenance'),
    ] as $url) {
        $this->actingAs($owner)->get($url)->assertOk();
    }
});

test('owner can upload and replace compliance document', function () {
    Storage::fake('public');

    $owner = makeFunctionalOwner('owner-documents@example.com');
    $house = BoardingHouse::factory()->create([
        'owner_id' => $owner->id,
        'approval_status' => 'pending',
        'status' => 'pending',
    ]);

    $this->actingAs($owner)
        ->post(route('owner.compliance.documents.store'), [
            'boarding_house_id' => $house->id,
            'requirement_name' => 'Fire Safety Certificate',
            'uploaded_file' => UploadedFile::fake()->create('fire.pdf', 32, 'application/pdf'),
        ])
        ->assertRedirect(route('owner.compliance.index'));

    $document = ComplianceRequirement::query()->firstOrFail();
    Storage::disk('public')->assertExists($document->uploaded_file);

    $this->actingAs($owner)
        ->put(route('owner.compliance.documents.update', $document), [
            'boarding_house_id' => $house->id,
            'requirement_name' => 'Updated Fire Safety Certificate',
            'uploaded_file' => UploadedFile::fake()->create('updated-fire.pdf', 32, 'application/pdf'),
        ])
        ->assertRedirect(route('owner.compliance.index'));

    expect($document->refresh()->requirement_name)->toBe('Updated Fire Safety Certificate');
});

function makeFunctionalOwner(string $email): User
{
    $owner = User::factory()->create([
        'email' => $email,
        'role' => 'owner',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $owner->syncRoles(['owner']);

    return $owner;
}

function makeFunctionalTenant(string $email): User
{
    $tenant = User::factory()->create([
        'email' => $email,
        'role' => 'tenant',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $tenant->syncRoles(['tenant']);

    return $tenant;
}
