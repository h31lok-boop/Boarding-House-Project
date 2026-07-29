<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200)
        ->assertSee('Owner / Admin')
        ->assertSee('Tenant / Student')
        ->assertDontSee('Caretaker')
        ->assertDontSee('OSAS');
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

test('owners can register from the public form', function () {
    Storage::fake('public');

    $response = $this->post('/register', [
        'name' => 'Admin User',
        'email' => 'admin-user@example.com',
        'role' => 'owner',
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
        'proof_of_ownership' => UploadedFile::fake()->image('permit.jpg'),
        'photos' => [
            UploadedFile::fake()->image('front.jpg'),
            UploadedFile::fake()->image('room.png'),
        ],
        'terms' => '1',
    ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertRedirect(route('owner.dashboard', absolute: false));

    $owner = User::where('email', 'admin-user@example.com')->first();
    $boardingHouse = DB::table('boarding_houses')->where('user_id', $owner->id)->first();

    $this->assertAuthenticatedAs($owner);
    expect($owner?->role)->toBe('owner');
    expect($owner?->isOwner())->toBeTrue();
    expect($owner?->isAdmin())->toBeFalse();
    expect($owner?->email_verified_at)->not->toBeNull();
    expect($owner?->status)->toBe('pending');
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
