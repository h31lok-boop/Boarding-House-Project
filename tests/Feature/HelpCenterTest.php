<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('user can view the help center page', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('user.help-center.index'))
        ->assertOk()
        ->assertSee('Help Center')
        ->assertSee('Still Need Help?');
});

test('user can submit a support request with a screenshot', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('user.help-center.store'), [
        'full_name' => 'Student User',
        'email' => 'student@example.com',
        'concern_type' => 'Technical Problem',
        'subject' => 'Cannot search listings',
        'message' => 'The search field does not return the expected results.',
        'screenshot' => UploadedFile::fake()->create('search-issue.pdf', 256, 'application/pdf'),
    ]);

    $response
        ->assertRedirect(route('user.help-center.index'))
        ->assertSessionHas('success', 'Your support request has been submitted successfully.');

    $this->assertDatabaseHas('support_requests', [
        'user_id' => $user->id,
        'full_name' => 'Student User',
        'email' => 'student@example.com',
        'concern_type' => 'Technical Problem',
        'subject' => 'Cannot search listings',
        'status' => 'Pending',
    ]);

    $path = \App\Models\SupportRequest::query()->value('screenshot');

    expect($path)->not->toBeNull();
    Storage::disk('public')->assertExists($path);
});

test('support request validates required fields', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('user.help-center.store'), [
            'email' => 'not-an-email',
        ])
        ->assertSessionHasErrors([
            'full_name',
            'email',
            'concern_type',
            'subject',
            'message',
        ]);
});
