<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function settingsUser(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ], $attributes));
}

test('user can view the profile settings page', function () {
    $user = settingsUser();

    $this->actingAs($user)
        ->get(route('user.settings.index'))
        ->assertOk()
        ->assertSee('Settings')
        ->assertSee('Manage your profile, security, and preferences.')
        ->assertSee('Profile Information')
        ->assertSee('Account &amp; Security', false)
        ->assertSee('Notification Preferences')
        ->assertSee('Appearance')
        ->assertSee('Change Photo')
        ->assertSee('Emergency Contact')
        ->assertSee('Match recommendations')
        ->assertSee('Reservation decisions and recorded payment updates remain available in Notifications.')
        ->assertSee('x-on:submit="saving = true"', false)
        ->assertSee('Save Changes');
});

test('user can save profile, emergency contact, optional notifications, and appearance together', function () {
    $user = settingsUser(['name' => 'Old Name']);

    $this->actingAs($user)
        ->put(route('user.settings.profile.update'), [
            'first_name' => 'Hazel',
            'last_name' => 'Reyes',
            'phone_number' => '+63 912 345 6789',
            'emergency_contact_name' => 'Maria Reyes',
            'emergency_contact_number' => '+63 917 000 0000',
            'emergency_contact_relationship' => 'Parent',
            'rent_reminders' => '0',
            'match_recommendations' => '1',
            'theme' => 'dark',
        ])
        ->assertRedirect(route('user.settings.index'))
        ->assertSessionHas('success', 'Settings saved successfully.');

    $user->refresh();

    expect($user->name)->toBe('Hazel Reyes')
        ->and($user->phone_number)->toBe('+63 912 345 6789')
        ->and($user->notify_payment_reminders)->toBeFalse()
        ->and($user->allow_matchmaking_data)->toBeTrue()
        ->and(data_get($user->preferences, 'theme'))->toBe('dark');

    $this->assertDatabaseHas('tenant_profiles', [
        'user_id' => $user->id,
        'emergency_contact_name' => 'Maria Reyes',
        'emergency_contact_number' => '+63 917 000 0000',
        'emergency_contact_relationship' => 'Parent',
    ]);
});

test('user can update personal information and profile photo', function () {
    Storage::fake('public');

    $user = settingsUser();

    $response = $this->actingAs($user)->put(route('user.settings.personal.update'), [
        'first_name' => 'Hazel',
        'last_name' => 'Reyes',
        'date_of_birth' => '2001-02-03',
        'gender' => 'Female',
        'profile_photo' => testImageUpload('avatar.png'),
    ]);

    $response
        ->assertRedirect(route('user.settings.index'))
        ->assertSessionHas('success', 'Profile updated successfully.');

    $user->refresh();

    expect($user->first_name)->toBe('Hazel')
        ->and($user->last_name)->toBe('Reyes')
        ->and($user->name)->toBe('Hazel Reyes')
        ->and($user->gender)->toBe('Female')
        ->and($user->profile_photo)->toStartWith('profile-photos/')
        ->and($user->profile_image)->toBe($user->profile_photo)
        ->and($user->photo_path)->toBe($user->profile_photo);

    Storage::disk('public')->assertExists($user->profile_photo);
});

test('uploading a new profile photo safely removes all previous local photo variants', function () {
    Storage::fake('public');
    Storage::disk('public')->put('profile-photos/old-primary.png', 'old primary');
    Storage::disk('public')->put('profile-photos/old-legacy.png', 'old legacy');

    $user = settingsUser([
        'photo_path' => 'profile-photos/old-primary.png',
        'profile_photo' => 'profile-photos/old-legacy.png',
        'profile_image' => 'https://example.com/external-avatar.png',
    ]);

    $this->actingAs($user)->put(route('user.settings.personal.update'), [
        'first_name' => 'Hazel',
        'last_name' => 'Reyes',
        'profile_photo' => testImageUpload('new-avatar.png'),
    ])->assertRedirect(route('user.settings.index'));

    $user->refresh();

    Storage::disk('public')->assertMissing('profile-photos/old-primary.png');
    Storage::disk('public')->assertMissing('profile-photos/old-legacy.png');
    Storage::disk('public')->assertExists($user->photo_path);

    expect($user->profile_photo)->toBe($user->photo_path)
        ->and($user->profile_image)->toBe($user->photo_path);
});

test('profile photo accepts only supported images up to two megabytes', function () {
    $user = settingsUser();

    $this->actingAs($user)->put(route('user.settings.personal.update'), [
        'first_name' => 'Hazel',
        'last_name' => 'Reyes',
        'profile_photo' => UploadedFile::fake()->create('avatar.svg', 10, 'image/svg+xml'),
    ])->assertSessionHasErrors('profile_photo');

    $this->actingAs($user)->put(route('user.settings.personal.update'), [
        'first_name' => 'Hazel',
        'last_name' => 'Reyes',
        'profile_photo' => UploadedFile::fake()->create('avatar.jpg', 2049, 'image/jpeg'),
    ])->assertSessionHasErrors('profile_photo');
});

test('user can update contact information', function () {
    $user = settingsUser(['email' => 'old@example.com']);
    settingsUser(['email' => 'taken@example.com']);

    $response = $this->actingAs($user)->put(route('user.settings.contact.update'), [
        'email' => 'new@example.com',
        'country_code' => '+63',
        'phone_number' => '912 345 6789',
        'current_address' => 'Cebu City, Philippines',
    ]);

    $response
        ->assertRedirect(route('user.settings.index'))
        ->assertSessionHas('success', 'Contact information updated successfully.');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'new@example.com',
        'phone_number' => '+63 912 345 6789',
        'phone' => '+63 912 345 6789',
        'contact_number' => '+63 912 345 6789',
        'current_address' => 'Cebu City, Philippines',
    ]);
});

test('contact email must be unique except current user', function () {
    $user = settingsUser(['email' => 'student@example.com']);
    settingsUser(['email' => 'taken@example.com']);

    $this->actingAs($user)
        ->put(route('user.settings.contact.update'), [
            'email' => 'taken@example.com',
            'phone_number' => '+63 912 345 6789',
        ])
        ->assertSessionHasErrors('email');
});

test('user can update password with strong password', function () {
    $user = settingsUser([
        'password' => Hash::make('OldPass1!'),
    ]);

    $response = $this->actingAs($user)->put(route('user.settings.password.update'), [
        'current_password' => 'OldPass1!',
        'password' => 'BetterPass1!',
        'password_confirmation' => 'BetterPass1!',
    ]);

    $response
        ->assertRedirect(route('user.settings.index'))
        ->assertSessionHas('success', 'Password updated successfully.');

    expect(Hash::check('BetterPass1!', $user->fresh()->password))->toBeTrue();
});

test('password update rejects incorrect current password', function () {
    $user = settingsUser([
        'password' => Hash::make('OldPass1!'),
    ]);

    $this->actingAs($user)
        ->put(route('user.settings.password.update'), [
            'current_password' => 'WrongPass1!',
            'password' => 'BetterPass1!',
            'password_confirmation' => 'BetterPass1!',
        ])
        ->assertSessionHasErrors(['current_password' => 'Current password is incorrect.']);
});

test('password fields are never repopulated after validation fails', function () {
    $user = settingsUser(['password' => Hash::make('OldPass1!')]);

    $this->actingAs($user)
        ->from(route('user.settings.index'))
        ->put(route('user.settings.password.update'), [
            'current_password' => 'OldPass1!',
            'password' => 'BetterPass1!',
            'password_confirmation' => 'DoesNotMatch1!',
        ])
        ->assertRedirect(route('user.settings.index'));

    $this->actingAs($user)
        ->get(route('user.settings.index'))
        ->assertOk()
        ->assertDontSee('value="OldPass1!"', false)
        ->assertDontSee('value="BetterPass1!"', false)
        ->assertDontSee('value="DoesNotMatch1!"', false);
});

test('notification preference updates through json request', function () {
    $user = settingsUser();

    $this->actingAs($user)
        ->putJson(route('user.settings.notifications.update'), [
            'email_notifications' => false,
            'promotions_updates' => true,
        ])
        ->assertOk()
        ->assertJson(['message' => 'Notification preference updated.']);

    $this->assertDatabaseHas('user_notification_preferences', [
        'user_id' => $user->id,
        'email_notifications' => false,
        'promotions_updates' => true,
    ]);

    expect($user->fresh()->notify_ticket_updates)->toBeFalse();
});

test('sms two factor requires a phone number before enabling', function () {
    $user = settingsUser([
        'phone' => null,
        'phone_number' => null,
        'contact_number' => null,
    ]);

    $this->actingAs($user)
        ->putJson(route('user.settings.two-factor.update'), [
            'sms_two_factor_enabled' => true,
        ])
        ->assertStatus(422)
        ->assertJson(['message' => 'Please add your phone number before enabling SMS authentication.']);
});

test('user can save privacy settings', function () {
    $user = settingsUser();

    $response = $this->actingAs($user)->put(route('user.settings.privacy.update'), [
        'show_profile_to_owners' => 0,
        'allow_owner_messages' => 1,
        'allow_matchmaking_data' => 0,
    ]);

    $response
        ->assertRedirect(route('user.settings.index'))
        ->assertSessionHas('success', 'Privacy settings updated successfully.');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'show_profile_to_owners' => false,
        'allow_owner_messages' => true,
        'allow_matchmaking_data' => false,
    ]);
});

test('user can log out other devices with current password', function () {
    $user = settingsUser([
        'password' => Hash::make('OldPass1!'),
    ]);

    $this->actingAs($user)
        ->post(route('user.settings.logout-other-devices'), [
            'logout_current_password' => 'OldPass1!',
        ])
        ->assertRedirect(route('user.settings.index'))
        ->assertSessionHas('success', 'Other devices have been logged out successfully.');
});
