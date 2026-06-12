<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\BoardMatchStrongPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed', new BoardMatchStrongPassword],
        ]);

        $hashed = Hash::make($validated['password']);
        $attributes = ['password' => $hashed];

        if (Schema::hasColumn('users', 'password_hash')) {
            $attributes['password_hash'] = $hashed;
        }

        $request->user()->forceFill($attributes)->save();

        return back()->with('status', 'password-updated');
    }
}
