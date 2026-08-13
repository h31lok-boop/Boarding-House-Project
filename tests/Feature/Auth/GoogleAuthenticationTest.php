<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as GoogleUserContract;
use Laravel\Socialite\Facades\Socialite;

function mockGoogleProvider(array $attributes = []): void
{
    $googleUser = Mockery::mock(GoogleUserContract::class);
    $googleUser->shouldReceive('getId')->andReturn($attributes['id'] ?? 'google-user-123');
    $googleUser->shouldReceive('getEmail')->andReturn($attributes['email'] ?? 'google-student@example.com');
    $googleUser->shouldReceive('getName')->andReturn($attributes['name'] ?? 'Google Student');
    $googleUser->shouldReceive('getAvatar')->andReturn($attributes['avatar'] ?? 'https://example.com/avatar.png');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn($googleUser);

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);
}

function googleRegistrationPayload(array $overrides = []): array
{
    return array_merge([
        'role' => 'tenant',
        'name' => 'Google Student',
        'email' => 'google-student@example.com',
        'phone' => '+639991234567',
        'school' => 'Davao del Sur State College',
        'course_year' => 'BSIT 3',
        'preferred_location' => 'Near DSSC Main Campus',
        'rental_budget' => 4500,
        'budget_min' => 3500,
        'budget_max' => 5000,
        'lifestyle_info' => 'Quiet, tidy, non-smoker, and studies in the evening.',
        'terms' => '1',
        'started_at' => now()->timestamp,
    ], $overrides);
}

function googleOwnerRegistrationPayload(array $overrides = []): array
{
    return array_merge([
        'role' => 'owner',
        'name' => 'Google Property Owner',
        'email' => 'google-owner@example.com',
        'phone' => '+639991112222',
        'username' => 'google_property_owner',
        'bh_name' => 'Google Owner Boarding House',
        'bh_address' => 'Roxas Street, Digos City',
        'bh_contact' => '+639991112222',
        'room_types' => ['Solo Room', 'Bedspace'],
        'rent_min' => 3000,
        'rent_max' => 5500,
        'monthly_rent_range' => 'PHP 3000 - PHP 5500',
        'amenities' => ['WiFi', 'Kitchen'],
        'house_rules' => 'Respect quiet hours and keep shared spaces clean.',
        'business_permit_number' => 'BP-2026-001',
        'permit_temp_path' => 'google-owner-registrations/test-owner/permit.png',
        'permit_original_name' => 'permit.png',
        'photo_temp_files' => [[
            'path' => 'google-owner-registrations/test-owner/photos/front.jpg',
            'original_name' => 'front.jpg',
        ]],
        'temp_directory' => 'google-owner-registrations/test-owner',
        'terms' => '1',
        'started_at' => now()->timestamp,
    ], $overrides);
}

beforeEach(function () {
    config([
        'services.google.client_id' => 'test-google-client-id',
        'services.google.client_secret' => 'test-google-client-secret',
        'services.google.redirect' => 'https://example.com/auth/google/callback',
    ]);
});

test('google redirect is available when oauth is configured', function () {
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('redirect')
        ->once()
        ->andReturn(redirect()->away('https://accounts.google.com/o/oauth2/auth'));

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

    $this->get(route('auth.google'))
        ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
});

test('google redirect reports a clear error when oauth is not configured', function () {
    config([
        'services.google.client_id' => null,
        'services.google.client_secret' => null,
    ]);

    $this->get(route('auth.google'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');
});

test('unknown google login cannot create an incomplete student account', function () {
    mockGoogleProvider();

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'google-student@example.com']);
});

test('student details are validated and stored before google registration begins', function () {
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('redirect')
        ->once()
        ->andReturn(redirect()->away('https://accounts.google.com/o/oauth2/auth'));

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

    $payload = googleRegistrationPayload();
    unset($payload['started_at']);

    $this->post(route('register.google'), $payload)
        ->assertRedirect('https://accounts.google.com/o/oauth2/auth')
        ->assertSessionHas('google_registration', fn (array $registration): bool => $registration['school'] === 'Davao del Sur State College'
            && $registration['lifestyle_info'] === $payload['lifestyle_info']);

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => $payload['email']]);
});

test('google registration requires the student profile fields before oauth', function () {
    $payload = googleRegistrationPayload([
        'school' => '',
        'preferred_location' => '',
        'lifestyle_info' => '',
    ]);
    unset($payload['started_at']);

    $this->post(route('register.google'), $payload)
        ->assertRedirect()
        ->assertSessionHasErrors(['school', 'preferred_location', 'lifestyle_info']);

    $this->assertGuest();
});

test('completed google registration creates an active student with full profiles', function () {
    mockGoogleProvider();

    $this->withSession(['google_registration' => googleRegistrationPayload()])
        ->get(route('auth.google.callback'))
        ->assertRedirect(route('user.dashboard'));

    $user = User::query()->where('email', 'google-student@example.com')->firstOrFail();

    $this->assertAuthenticatedAs($user);
    expect($user->isUser())->toBeTrue()
        ->and($user->status)->toBe('active')
        ->and($user->is_active)->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull();

    if (Schema::hasColumn('users', 'google_id')) {
        expect($user->google_id)->toBe('google-user-123');
    }

    $this->assertDatabaseHas('tenant_profiles', [
        'user_id' => $user->id,
        'school_university' => 'Davao del Sur State College',
        'course_year_level' => 'BSIT 3',
        'preferred_location' => 'Near DSSC Main Campus',
        'rental_budget' => 4500,
        'lifestyle_information' => 'Quiet, tidy, non-smoker, and studies in the evening.',
    ]);
    $this->assertDatabaseHas('tenant_match_profiles', [
        'user_id' => $user->id,
        'budget_min' => 3500,
        'budget_max' => 5000,
    ]);
});

test('complete owner fields and uploads are preserved before google registration begins', function () {
    Storage::fake('local');
    Storage::fake('public');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('redirect')
        ->once()
        ->andReturn(redirect()->away('https://accounts.google.com/o/oauth2/auth'));

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

    $response = $this->post(route('register.owner.google'), [
        'name' => 'Google Property Owner',
        'email' => 'google-owner@example.com',
        'phone' => '+639991112222',
        'username' => 'google_property_owner',
        'bh_name' => 'Google Owner Boarding House',
        'bh_address' => 'Roxas Street, Digos City',
        'bh_contact' => '+639991112222',
        'room_types' => ['Solo Room', 'Bedspace'],
        'rent_min' => 3000,
        'rent_max' => 5500,
        'amenities' => ['WiFi', 'Kitchen'],
        'house_rules' => 'Respect quiet hours and keep shared spaces clean.',
        'business_permit_number' => 'BP-2026-001',
        'proof_of_ownership' => testImageUpload('permit.png'),
        'photos' => [testImageUpload('front.jpg')],
        'terms' => '1',
    ]);

    $response->assertRedirect('https://accounts.google.com/o/oauth2/auth')
        ->assertSessionHas('google_registration', fn (array $registration): bool => $registration['role'] === 'owner'
            && $registration['bh_name'] === 'Google Owner Boarding House'
            && filled($registration['permit_temp_path'])
            && count($registration['photo_temp_files']) === 1
            && ! array_key_exists('password', $registration));

    $registration = session('google_registration');
    Storage::disk('local')->assertExists($registration['permit_temp_path']);
    Storage::disk('local')->assertExists($registration['photo_temp_files'][0]['path']);
    $this->assertDatabaseMissing('users', ['email' => 'google-owner@example.com']);
    $this->assertGuest();
});

test('google owner callback creates a disabled pending application with its permit and property photo', function () {
    Storage::fake('local');
    Storage::fake('public');

    $registration = googleOwnerRegistrationPayload();
    Storage::disk('local')->put($registration['permit_temp_path'], 'permit-file');
    Storage::disk('local')->put($registration['photo_temp_files'][0]['path'], 'property-photo');

    mockGoogleProvider([
        'id' => 'google-owner-id',
        'email' => 'google-owner@example.com',
        'name' => 'Google Property Owner',
    ]);

    $this->withSession(['google_registration' => $registration])
        ->get(route('auth.google.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('status');

    $owner = User::query()->where('email', 'google-owner@example.com')->firstOrFail();
    $boardingHouse = $owner->boardingHouses()->firstOrFail();

    $this->assertGuest();
    expect($owner->isStrictOwner())->toBeTrue()
        ->and($owner->status)->toBe('pending')
        ->and($owner->is_active)->toBeFalse();

    if (Schema::hasColumn('users', 'google_id')) {
        expect($owner->google_id)->toBe('google-owner-id');
    }

    $this->assertDatabaseHas('owner_profiles', [
        'user_id' => $owner->id,
        'boarding_house_name' => 'Google Owner Boarding House',
        'verification_status' => 'pending',
    ]);
    $this->assertDatabaseHas('boarding_houses', [
        'id' => $boardingHouse->id,
        'approval_status' => 'pending',
        'is_active' => false,
    ]);
    $this->assertDatabaseCount('boarding_house_photos', 1);

    Storage::disk('public')->assertExists($owner->ownerProfile->proof_of_ownership);
    Storage::disk('public')->assertExists($boardingHouse->featured_image);
    Storage::disk('local')->assertMissing($registration['permit_temp_path']);
});

test('google registration rejects a google account with a different email', function () {
    mockGoogleProvider(['email' => 'different-google@example.com']);

    $this->withSession(['google_registration' => googleRegistrationPayload()])
        ->get(route('auth.google.callback'))
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'different-google@example.com']);
});

test('an incomplete existing google student must finish registration before dashboard access', function () {
    $student = User::factory()->create([
        'email' => 'incomplete-google@example.com',
        'role' => 'user',
        'status' => 'active',
        'is_active' => true,
    ]);

    mockGoogleProvider([
        'id' => 'incomplete-google-id',
        'email' => $student->email,
        'name' => $student->name,
    ]);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('registration');

    $this->assertGuest();
});

test('google callback cannot bypass pending owner permit verification', function () {
    $owner = User::factory()->create([
        'email' => 'pending-owner@example.com',
        'role' => 'owner',
        'status' => 'pending',
        'is_active' => false,
    ]);

    mockGoogleProvider([
        'id' => 'pending-owner-google-id',
        'email' => $owner->email,
        'name' => $owner->name,
    ]);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('verified owners can sign in with their linked google email', function () {
    $owner = User::factory()->verifiedOwner()->create([
        'email' => 'verified-owner@example.com',
    ]);

    mockGoogleProvider([
        'id' => 'verified-owner-google-id',
        'email' => $owner->email,
        'name' => $owner->name,
    ]);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($owner);
});
