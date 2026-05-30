<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminInviteCode;
use App\Models\OwnerProfile;
use App\Models\TenantMatchProfile;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * Security measures applied here:
     *  - CSRF: enforced by the `web` middleware stack (VerifyCsrfToken)
     *  - Rate limiting: `throttle:6,1` applied to the route in auth.php
     *  - Input validation & XSS: Illuminate Validator + Blade auto-escaping
     *  - SQL injection: Eloquent uses PDO parameterized queries throughout
     *  - Password hashing: bcrypt via Hash::make()
     *  - Role integrity: only 'user' or 'admin' accepted; admin requires a
     *    valid, unused, non-expired invite code
     *  - Secure errors: validation messages never expose DB structure or stack
     */
    public function store(Request $request): RedirectResponse
    {
        $role = $request->input('role', 'user');

        // Normalise role to prevent injection of arbitrary values
        if (! in_array($role, ['user', 'admin'], true)) {
            $role = 'user';
        }

        // ── Validation ──────────────────────────────────────────────────────
        $rules = [
            'name'     => ['required', 'string', 'min:2', 'max:255'],
            'email'    => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'phone'    => ['required', 'string', 'regex:/^(\+63|0)9\d{9}$/', 'max:20'],
            'role'     => ['nullable', 'in:user,admin'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'terms'    => ['required', 'accepted'],
        ];

        $messages = [
            'name.required'           => 'Full name is required.',
            'name.min'                => 'Full name must be at least 2 characters.',
            'email.required'          => 'Email address is required.',
            'email.email'             => 'Please enter a valid email address.',
            'email.unique'            => 'An account with this email already exists.',
            'phone.required'          => 'Phone number is required.',
            'phone.regex'             => 'Enter a valid Philippine number, e.g. 09XX XXX XXXX.',
            'password.required'       => 'Password is required.',
            'password.confirmed'      => 'Passwords do not match.',
            'password.min'            => 'Password must be at least 8 characters.',
            'terms.accepted'          => 'You must accept the Terms of Service to continue.',
        ];

        // Admin-specific: require invite code
        if ($role === 'admin') {
            $rules['invite_code'] = ['required', 'string', 'min:6'];
        }

        $validated = $request->validate($rules, $messages);

        // ── Admin invite code check ──────────────────────────────────────────
        $inviteRecord = null;
        if ($role === 'admin') {
            $inviteRecord = AdminInviteCode::where('code', strtoupper(trim($validated['invite_code'])))->first();

            if (! $inviteRecord || ! $inviteRecord->isValid()) {
                throw ValidationException::withMessages([
                    'invite_code' => 'This invite code is invalid, has already been used, or has expired.',
                ]);
            }

            // If the code is email-restricted, verify it matches
            if ($inviteRecord->email !== null &&
                strtolower($inviteRecord->email) !== strtolower($validated['email'])) {
                throw ValidationException::withMessages([
                    'invite_code' => 'This invite code is not valid for the provided email address.',
                ]);
            }
        }

        // ── Create the user ─────────────────────────────────────────────────
        $hashedPassword = Hash::make($validated['password']);

        $attributes = [
            'name'             => $validated['name'],
            'email'            => strtolower($validated['email']),
            'phone'            => $validated['phone'],
            'contact_number'   => $validated['phone'],
            'role'             => $role,
            'password'         => $hashedPassword,
            'is_active'        => true,
            'status'           => 'active',
            // Auto-verify in development; remove this line to enforce email verification
            'email_verified_at'=> now(),
        ];

        if (Schema::hasColumn('users', 'password_hash')) {
            $attributes['password_hash'] = $hashedPassword;
        }

        $user = new User;
        $user->forceFill($attributes);
        $user->save();

        // ── Consume invite code ──────────────────────────────────────────────
        if ($inviteRecord) {
            $inviteRecord->consume($user);
        }

        // ── Auxiliary profiles ───────────────────────────────────────────────
        if ($role === 'user') {
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
        } else {
            OwnerProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name'        => $user->name,
                    'valid_id_type'       => null,
                    'valid_id_number'     => null,
                    'valid_id_file'       => null,
                    'verification_status' => 'pending',
                ]
            );
        }

        // ── Spatie role sync ─────────────────────────────────────────────────
        if (method_exists($user, 'assignRole')) {
            $spatieRole = Role::findOrCreate($role, 'web');
            $user->syncRoles([$spatieRole]);
        }

        event(new Registered($user));

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect(route('dashboard', absolute: false));
    }
}
