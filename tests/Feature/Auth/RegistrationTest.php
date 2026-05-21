<?php

use App\Models\Accreditation;
use App\Models\BoardingHouse;
use App\Models\ComplianceRequirement;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200)
        ->assertSee('Join DSSC Boarding')
        ->assertSee('Tenant')
        ->assertSee('Owner')
        ->assertSee('Preferred Move-In Date')
        ->assertSee('Boarding House Details')
        ->assertSee('Upload Owner ID and Other Required Documents')
        ->assertSee('Already have an account?');
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+639991234567',
        'institution_name' => 'GeoBoard University',
        'move_in_date' => now()->addWeek()->toDateString(),
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'terms' => '1',
    ]);

    $response->assertSessionDoesntHaveErrors();
    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'role' => 'tenant',
    ]);

    expect(User::where('email', 'test@example.com')->first()?->is_active)->toBeTrue();
});

test('tenants can register from the role based registration form', function () {
    Storage::fake('public');

    $response = $this->post('/register/tenant', [
        'name' => 'Tenant One',
        'email' => 'tenant-role-form@example.com',
        'phone' => '09171234567',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'move_in_date' => now()->addDays(10)->toDateString(),
        'preferred_room_type' => 'Bed Space',
        'profile_photo' => UploadedFile::fake()->image('tenant-profile.jpg'),
        'terms' => '1',
    ]);

    $response->assertSessionDoesntHaveErrors();
    $this->assertAuthenticated();
    $response->assertRedirect(route('tenant.dashboard', absolute: false));

    $tenant = User::where('email', 'tenant-role-form@example.com')->firstOrFail();

    expect($tenant->role)->toBe('tenant')
        ->and($tenant->phone)->toBe('09171234567')
        ->and($tenant->room_number)->toBe('Bed Space')
        ->and($tenant->is_active)->toBeTrue();

    Storage::disk('public')->assertExists($tenant->profile_image);
});

test('owners can register from the role based registration form', function () {
    Storage::fake('public');

    $response = $this->post('/register/owner', [
        'registration_mode' => 'quick',
        'role' => 'owner',
        'name' => 'Owner Quick',
        'email' => 'owner-role-form@example.com',
        'phone' => '09179876543',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'boarding_house_name' => 'Quick Nest Boarding House',
        'boarding_house_address' => 'Purok 2, Barangay Zone 1, Digos City, Davao del Sur',
        'region' => 'Davao Region',
        'province' => 'Davao del Sur',
        'city' => 'Digos City',
        'boarding_house_photo' => UploadedFile::fake()->image('boarding-house-front.jpg'),
        'owner_id_document' => UploadedFile::fake()->create('owner-id.pdf', 120, 'application/pdf'),
        'supporting_documents' => [
            UploadedFile::fake()->create('business-permit.pdf', 120, 'application/pdf'),
        ],
        'terms' => '1',
    ]);

    $response->assertSessionDoesntHaveErrors();
    $this->assertAuthenticated();
    $response->assertRedirect(route('owner.dashboard', absolute: false));
    $response->assertSessionHas('status', 'Your owner account has been submitted for review.');

    $owner = User::where('email', 'owner-role-form@example.com')->firstOrFail();
    $boardingHouse = BoardingHouse::where('owner_id', $owner->id)->firstOrFail();

    expect($owner->role)->toBe('owner')
        ->and($owner->status)->toBe('pending_verification')
        ->and($owner->is_active)->toBeTrue()
        ->and($boardingHouse->name)->toBe('Quick Nest Boarding House')
        ->and($boardingHouse->status)->toBe('pending_review')
        ->and($boardingHouse->approval_status)->toBe('pending');

    $this->assertDatabaseHas('owner_profiles', [
        'user_id' => $owner->id,
        'company_name' => 'Quick Nest Boarding House',
        'verification_status' => 'pending',
    ]);
    $this->assertDatabaseHas('compliance_requirements', [
        'boarding_house_id' => $boardingHouse->id,
        'requirement_name' => 'Valid ID of Owner',
        'validation_status' => 'pending',
    ]);
    $this->assertDatabaseHas('boarding_house_images', [
        'boarding_house_id' => $boardingHouse->id,
        'is_primary' => true,
    ]);

    expect(ComplianceRequirement::where('boarding_house_id', $boardingHouse->id)->count())->toBe(3);
    ComplianceRequirement::where('boarding_house_id', $boardingHouse->id)
        ->pluck('uploaded_file')
        ->each(fn (string $path) => Storage::disk('public')->assertExists($path));
});

test('owners can register and receive an owner profile', function () {
    $response = $this->post('/register', [
        'role' => 'owner',
        'name' => 'Owner User',
        'email' => 'owner-register@example.com',
        'phone' => '+639998887777',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'terms' => '1',
    ]);

    $response->assertSessionDoesntHaveErrors();
    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $owner = User::where('email', 'owner-register@example.com')->first();

    $this->assertNotNull($owner);
    $this->assertDatabaseHas('users', [
        'email' => 'owner-register@example.com',
        'role' => 'owner',
    ]);
    $this->assertDatabaseHas('owner_profiles', [
        'user_id' => $owner->id,
    ]);

    expect($owner?->dashboardRouteName())->toBe('owner.dashboard');
});

test('owner registration flow submits a pending boarding house for review', function () {
    Storage::fake('public');

    $this->get('/register/owner')
        ->assertOk()
        ->assertSee('Create Owner Account')
        ->assertSee('Interactive Map Location Picker')
        ->assertSee('Address and Map Location')
        ->assertSee('Verification Documents')
        ->assertSee('Terms and Agreement');

    $response = $this->post('/register/owner', [
        'registration_mode' => 'owner_map',
        'email' => 'review-owner@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'full_name' => 'Ana Santos Reyes',
        'contact_number' => '+639171112222',
        'profile_photo' => UploadedFile::fake()->image('profile.jpg'),
        'boarding_house_name' => 'Digos Nest House',
        'property_type' => 'Boarding House',
        'number_of_rooms' => 12,
        'available_slots' => 24,
        'min_price' => 3500,
        'max_price' => 7200,
        'description' => 'A student-friendly boarding house near the DSSC campus.',
        'amenities' => ['Wi-Fi', 'CCTV', 'Study Area'],
        'region' => 'Davao Region',
        'province' => 'Davao del Sur',
        'city' => 'Digos City',
        'barangay' => 'Zone 3',
        'street' => 'Purok 5',
        'complete_address' => 'Purok 5, Zone 3, Digos City, Davao del Sur',
        'latitude' => '6.74400000',
        'longitude' => '125.35500000',
        'valid_id' => UploadedFile::fake()->create('valid-id.pdf', 120, 'application/pdf'),
        'business_permit' => UploadedFile::fake()->create('business-permit.pdf', 120, 'application/pdf'),
        'fire_safety_certificate' => UploadedFile::fake()->create('fire-safety.pdf', 120, 'application/pdf'),
        'sanitary_permit' => UploadedFile::fake()->create('sanitary-permit.pdf', 120, 'application/pdf'),
        'boarding_house_permit' => UploadedFile::fake()->create('boarding-house-permit.pdf', 120, 'application/pdf'),
        'proof_of_ownership' => UploadedFile::fake()->create('proof-of-ownership.pdf', 120, 'application/pdf'),
        'boarding_house_photos' => [
            UploadedFile::fake()->image('front.jpg'),
            UploadedFile::fake()->image('room.jpg'),
        ],
        'house_rules_document' => UploadedFile::fake()->create('house-rules.pdf', 120, 'application/pdf'),
        'terms_conditions' => '1',
        'privacy_policy' => '1',
        'osas_review_consent' => '1',
        'accuracy_confirmation' => '1',
        'notification_consent' => '1',
    ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertRedirect(route('login', absolute: false));
    $response->assertSessionHas('status', 'Owner registration submitted for review.');
    $this->assertGuest();

    $owner = User::where('email', 'review-owner@example.com')->firstOrFail();
    $boardingHouse = BoardingHouse::where('owner_id', $owner->id)->firstOrFail();

    expect($owner->role)->toBe('owner')
        ->and($owner->name)->toBe('Ana Santos Reyes')
        ->and($owner->is_active)->toBeFalse()
        ->and($owner->status)->toBe('pending_verification')
        ->and($boardingHouse->name)->toBe('Digos Nest House')
        ->and($boardingHouse->landlord_info)->toBe('Ana Santos Reyes')
        ->and($boardingHouse->status)->toBe('pending_review')
        ->and($boardingHouse->approval_status)->toBe('pending')
        ->and((float) $boardingHouse->latitude)->toBe(6.744)
        ->and((float) $boardingHouse->longitude)->toBe(125.355);

    $this->assertDatabaseHas('owner_profiles', [
        'user_id' => $owner->id,
        'verification_status' => 'pending',
    ]);
    $this->assertDatabaseHas('locations', [
        'boarding_house_id' => $boardingHouse->id,
        'landmark' => 'Purok 5',
    ]);
    $this->assertDatabaseHas('boarding_house_images', [
        'boarding_house_id' => $boardingHouse->id,
        'is_primary' => true,
    ]);
    $this->assertDatabaseHas('compliance_requirements', [
        'boarding_house_id' => $boardingHouse->id,
        'requirement_name' => 'Valid ID of Owner',
        'validation_status' => 'pending',
    ]);
    $this->assertDatabaseHas('accreditations', [
        'boarding_house_id' => $boardingHouse->id,
        'status' => 'Pending Review',
    ]);

    expect(ComplianceRequirement::where('boarding_house_id', $boardingHouse->id)->count())->toBe(9)
        ->and(Accreditation::where('boarding_house_id', $boardingHouse->id)->exists())->toBeTrue();

    Storage::disk('public')->assertExists($owner->profile_image);
    ComplianceRequirement::where('boarding_house_id', $boardingHouse->id)
        ->pluck('uploaded_file')
        ->each(fn (string $path) => Storage::disk('public')->assertExists($path));
});
