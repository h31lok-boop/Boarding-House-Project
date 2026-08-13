<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200)
        ->assertSee('data-theme-mode="light-only"', false)
        ->assertSee('Create your student account')
        ->assertSee('Tenant / Student')
        ->assertSee('Register with Google')
        ->assertSee('formaction="'.route('register.google').'"', false)
        ->assertDontSee('href="'.route('auth.google').'"', false)
        ->assertSee(route('register.owner'))
        ->assertDontSee('Caretaker')
        ->assertDontSee('OSAS');

    expect(strpos($response->getContent(), 'id="googleSubmitBtn"'))
        ->toBeGreaterThan(strpos($response->getContent(), 'Lifestyle Information for AI Recommendation'));
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'tenant',
        'phone' => '+639991234567',
        'school' => 'GeoBoard University',
        'course_year' => 'BSIT 3',
        'preferred_location' => 'Near campus',
        'budget_min' => 2500,
        'budget_max' => 5000,
        'lifestyle_info' => 'Quiet, tidy, and prefers early study hours.',
        'password' => 'BoardSafe9!',
        'password_confirmation' => 'BoardSafe9!',
        'terms' => '1',
    ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertRedirect(route('user.dashboard', absolute: false));

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'role' => 'tenant',
        'phone_number' => '+639991234567',
        'status' => 'active',
    ]);

    $user = User::where('email', 'test@example.com')->first();

    $this->assertAuthenticatedAs($user);
    expect($user?->is_active)->toBeTrue();
    expect($user?->email_verified_at)->not->toBeNull();

    $storedPassword = DB::table('users')->where('id', $user->id)->value('password');

    expect($storedPassword)->not->toBe('BoardSafe9!')
        ->and($storedPassword)->toStartWith('$2y$')
        ->and(Hash::check('BoardSafe9!', $storedPassword))->toBeTrue()
        ->and(Schema::hasColumn('users', 'password_confirmation'))->toBeFalse();

    $this->post('/logout');

    $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'BoardSafe9!',
        'role' => 'tenant',
    ])->assertRedirect(route('user.dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);

    $this->assertDatabaseHas('tenant_profiles', [
        'user_id' => $user->id,
        'school_university' => 'GeoBoard University',
        'course_year_level' => 'BSIT 3',
        'preferred_location' => 'Near campus',
        'rental_budget' => 2500,
    ]);
});

test('owners register through the dedicated permit form and remain blocked until admin verification', function () {
    Storage::fake('public');

    $this->get(route('register.owner'))
        ->assertOk()
        ->assertSee('data-theme-mode="light-only"', false)
        ->assertSee('Upload business permit')
        ->assertSee('Submit and link with Google')
        ->assertSee('formaction="'.route('register.owner.google').'"', false)
        ->assertSee('Your account opens only after approval.');

    $response = $this->post(route('register.owner.store'), [
        'name' => 'Admin User',
        'email' => 'admin-user@example.com',
        'phone' => '+639991111111',
        'password' => 'OwnerSafe9!',
        'password_confirmation' => 'OwnerSafe9!',
        'bh_name' => 'Admin Boarding House',
        'bh_address' => '123 Owner Street',
        'bh_contact' => '+639991111111',
        'room_types' => ['single', 'shared'],
        'rent_min' => 3000,
        'rent_max' => 6500,
        'amenities' => ['wifi', 'kitchen'],
        'house_rules' => 'No smoking inside the premises.',
        'proof_of_ownership' => testImageUpload('permit.png'),
        'photos' => [
            testImageUpload('front.png'),
            testImageUpload('room.png'),
        ],
        'terms' => '1',
    ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertRedirect(route('login', absolute: false));

    $owner = User::where('email', 'admin-user@example.com')->first();
    $boardingHouse = DB::table('boarding_houses')->where('user_id', $owner->id)->first();

    $this->assertGuest();
    expect($owner?->role)->toBe('owner');
    expect($owner?->isOwner())->toBeTrue();
    expect($owner?->isAdmin())->toBeFalse();
    expect($owner?->email_verified_at)->not->toBeNull();
    expect($owner?->status)->toBe('pending');
    expect($owner?->is_active)->toBeFalse();
    expect($boardingHouse)->not->toBeNull();

    $this->assertDatabaseHas('owner_profiles', [
        'user_id' => $owner->id,
        'boarding_house_name' => 'Admin Boarding House',
        'boarding_house_address' => '123 Owner Street',
        'contact_number' => '+639991111111',
        'monthly_rent_range' => 'PHP 3000 - PHP 6500',
        'verification_status' => 'pending',
    ]);
    $this->assertDatabaseHas('boarding_houses', [
        'id' => $boardingHouse->id,
        'name' => 'Admin Boarding House',
        'address' => '123 Owner Street',
        'status' => 'pending',
        'approval_status' => 'pending',
    ]);
    $this->assertDatabaseCount('boarding_house_photos', 2);
    $this->assertDatabaseHas('boarding_house_photos', [
        'owner_id' => $owner->id,
        'boarding_house_id' => $boardingHouse->id,
    ]);
    $this->assertDatabaseMissing('tenant_profiles', [
        'user_id' => $owner->id,
    ]);

    Storage::disk('public')->assertExists($boardingHouse->proof_of_ownership);
    expect($boardingHouse->proof_of_ownership)->toStartWith('proof-of-ownership/');

    $this->post('/login', [
        'email' => 'admin-user@example.com',
        'password' => 'OwnerSafe9!',
        'role' => 'owner',
    ])->assertSessionHasErrors('email');
    $this->assertGuest();

    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.owners.verify', $owner))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'id' => $owner->id,
        'status' => 'active',
        'is_active' => true,
    ]);
    $this->assertDatabaseHas('owner_profiles', [
        'user_id' => $owner->id,
        'verification_status' => 'verified',
        'verified_by' => $admin->id,
    ]);
    $this->assertDatabaseHas('boarding_houses', [
        'id' => $boardingHouse->id,
        'status' => 'approved',
        'approval_status' => 'approved',
        'is_active' => true,
    ]);

    $this->post('/logout');
    $this->post('/login', [
        'email' => 'admin-user@example.com',
        'password' => 'OwnerSafe9!',
        'role' => 'owner',
    ])->assertRedirect(route('owner.dashboard', absolute: false));
    $this->assertAuthenticatedAs($owner);
});

test('owner registration cannot be submitted without a business permit', function () {
    Storage::fake('public');

    $this->post(route('register.owner.store'), [
        'name' => 'Permitless Owner',
        'email' => 'permitless@example.com',
        'phone' => '+639991111112',
        'password' => 'OwnerSafe9!',
        'password_confirmation' => 'OwnerSafe9!',
        'bh_name' => 'Permitless House',
        'bh_address' => 'Digos City',
        'bh_contact' => '+639991111112',
        'room_types' => ['Solo Room'],
        'rent_min' => 3000,
        'rent_max' => 5000,
        'amenities' => ['WiFi'],
        'house_rules' => 'Respect quiet hours.',
        'terms' => '1',
    ])->assertSessionHasErrors([
        'proof_of_ownership' => 'A valid business permit is required before an owner account can be reviewed.',
    ]);

    $this->assertDatabaseMissing('users', ['email' => 'permitless@example.com']);
    $this->assertGuest();
});

test('registration rejects weak passwords with clear backend messages', function (string $password, string $confirmation, string $message) {
    $response = $this->post('/register', [
        'name' => 'Weak Password User',
        'email' => fake()->unique()->safeEmail(),
        'role' => 'tenant',
        'phone' => '+639991234567',
        'school' => 'GeoBoard University',
        'course_year' => 'BSIT 3',
        'preferred_location' => 'Near campus',
        'budget_min' => 2500,
        'budget_max' => 5000,
        'lifestyle_info' => 'Quiet, tidy, and prefers early study hours.',
        'password' => $password,
        'password_confirmation' => $confirmation,
        'terms' => '1',
    ]);

    $response->assertSessionHasErrors([
        'password' => $message,
    ]);

    $this->assertGuest();
})->with([
    'too short' => ['Aa1!', 'Aa1!', 'Password must be at least 8 characters.'],
    'missing uppercase' => ['boardsafe9!', 'boardsafe9!', 'Password must contain an uppercase letter.'],
    'missing lowercase' => ['BOARDSAFE9!', 'BOARDSAFE9!', 'Password must contain a lowercase letter.'],
    'missing number' => ['BoardSafe!', 'BoardSafe!', 'Password must contain a number.'],
    'missing special character' => ['BoardSafe9', 'BoardSafe9', 'Password must contain a special character.'],
    'confirmation mismatch' => ['BoardSafe9!', 'Different9!', 'Password confirmation does not match.'],
    'predictable password' => ['Password123!', 'Password123!', 'This password is too predictable. Please create a stronger password.'],
]);
