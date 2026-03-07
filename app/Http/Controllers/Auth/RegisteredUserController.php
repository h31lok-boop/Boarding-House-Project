<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
            'phone' => ['nullable', 'string', 'max:30'],
            'institution_name' => ['nullable', 'string', 'max:255'],
            'move_in_date' => ['nullable', 'date'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['sometimes', 'accepted'],
        ]);

        $hashedPassword = Hash::make($request->password);

        $attributes = [
            'role' => 'tenant',
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

        // sync Spatie role
        if (method_exists($user, 'assignRole')) {
            $tenantRole = Role::findOrCreate('tenant', 'web');
            $user->syncRoles([$tenantRole]);
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
