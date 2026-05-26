<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OwnerProfile;
use App\Models\TenantMatchProfile;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['nullable', 'in:admin,user'],
            'phone' => ['nullable', 'string', 'max:30'],
            'institution_name' => ['nullable', 'string', 'max:255'],
            'move_in_date' => ['nullable', 'date'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['sometimes', 'accepted'],
        ]);

        $hashedPassword = Hash::make($request->password);
        $role = $request->input('role', 'user');

        $attributes = [
            'role' => $role,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'contact_number' => $request->phone,
            'institution_name' => $request->institution_name,
            'move_in_date' => $request->move_in_date,
            'password' => $hashedPassword,
            'password_hash' => $hashedPassword,
            'is_active' => true,
            'status' => 'active',
            'email_verified_at' => now(),
        ];

        $user = new User;
        $user->forceFill($attributes);
        $user->save();

        if ($role === 'user') {
            TenantProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'school_company' => $request->institution_name ?: 'Not provided',
                    'course_or_position' => null,
                    'valid_id_type' => 'pending',
                    'valid_id_number' => 'PENDING-'.$user->id,
                    'valid_id_file' => 'pending',
                    'emergency_contact_name' => 'Not provided',
                    'emergency_contact_number' => 'N/A',
                    'preferred_language' => 'english',
                ]
            );

            TenantMatchProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['gender_preference' => 'no_preference']
            );
        } else {
            OwnerProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $request->name,
                    'valid_id_type' => 'pending',
                    'valid_id_number' => 'PENDING-'.$user->id,
                    'valid_id_file' => 'pending',
                    'verification_status' => 'pending',
                ]
            );
        }

        if (method_exists($user, 'assignRole')) {
            $spatieRole = Role::findOrCreate($role, 'web');
            $user->syncRoles([$spatieRole]);
        }

        event(new Registered($user));

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        // Use a single, predictable redirect. The dashboard route itself
        // handles per-role forwarding so callers and tests always see the
        // same target coming out of registration.
        return redirect(route('dashboard', absolute: false));
    }
}
