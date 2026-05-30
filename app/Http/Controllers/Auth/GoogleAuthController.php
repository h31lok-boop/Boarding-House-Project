<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TenantMatchProfile;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth page.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google.
     *
     * Flow:
     *  1. Fetch the Google user profile.
     *  2. If a local user already has this google_id → log them in.
     *  3. If a local user exists with this email but no google_id → link the
     *     google_id, then log them in.
     *  4. If no local user exists → create one, build profiles, then log in.
     *  5. Any unexpected error → redirect back to login with a friendly message.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('login')
                ->with('error', 'Google authentication was cancelled or failed. Please try again.');
        }

        $email    = strtolower(trim((string) $googleUser->getEmail()));
        $name     = trim((string) $googleUser->getName()) ?: $email;
        $googleId = (string) $googleUser->getId();
        $avatar   = $googleUser->getAvatar();

        $hasGoogleIdCol = Schema::hasColumn('users', 'google_id');

        // ── 1. Look up by google_id first (fastest, most reliable) ───────────
        $user = null;
        if ($hasGoogleIdCol) {
            $user = User::where('google_id', $googleId)->first();
        }

        // ── 2. Fall back to email lookup ─────────────────────────────────────
        if (! $user && $email) {
            $user = User::where('email', $email)->first();

            // Link google_id to the existing account
            if ($user && $hasGoogleIdCol && ! $user->google_id) {
                $user->forceFill(['google_id' => $googleId])->save();
            }
        }

        // ── 3. Create new user if none found ─────────────────────────────────
        if (! $user) {
            $user = $this->createGoogleUser($googleId, $name, $email, $avatar, $hasGoogleIdCol);
        }

        // ── 4. Log in ─────────────────────────────────────────────────────────
        Auth::guard('web')->login($user, remember: true);
        request()->session()->regenerate();

        // New Google users land on settings so they can complete their profile
        $isNew = $user->wasRecentlyCreated;

        return $isNew
            ? redirect()->route('user.settings')
                ->with('status', 'Welcome to BoardMatch! Please complete your profile.')
            : redirect()->intended(route('dashboard', absolute: false));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function createGoogleUser(
        string $googleId,
        string $name,
        string $email,
        ?string $avatar,
        bool $hasGoogleIdCol
    ): User {
        $hashedPwd = Hash::make(Str::random(32));

        $attrs = [
            'name'              => $name,
            'email'             => $email,
            'password'          => $hashedPwd,
            'role'              => 'user',
            'is_active'         => true,
            'status'            => 'active',
            'email_verified_at' => now(),
        ];

        if ($hasGoogleIdCol) {
            $attrs['google_id'] = $googleId;
        }

        // Store avatar URL as profile_image if column exists and Google provided one
        if ($avatar && Schema::hasColumn('users', 'profile_image')) {
            $attrs['profile_image'] = $avatar;
        }

        if (Schema::hasColumn('users', 'password_hash')) {
            $attrs['password_hash'] = $hashedPwd;
        }

        $user = new User;
        $user->forceFill($attrs)->save();

        // ── Auxiliary profiles (same as RegisteredUserController for 'user') ──
        TenantProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'school_company'           => null,
                'course_or_position'       => null,
                'valid_id_type'            => null,
                'valid_id_number'          => null,
                'valid_id_file'            => null,
                'emergency_contact_name'   => null,
                'emergency_contact_number' => null,
                'preferred_language'       => 'english',
            ]
        );

        if (Schema::hasTable('tenant_match_profiles')) {
            TenantMatchProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['gender_preference' => 'no_preference']
            );
        }

        // ── Spatie role sync ──────────────────────────────────────────────────
        if (method_exists($user, 'assignRole')) {
            $spatieRole = Role::findOrCreate('user', 'web');
            $user->syncRoles([$spatieRole]);
        }

        return $user;
    }
}
