<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if (($request->boolean('profile_image_remove') || $request->hasFile('profile_image')) && $user->effective_photo_path) {
            collect([$user->photo_path, $user->profile_photo, $user->profile_image])
                ->filter()->unique()
                ->reject(fn ($path) => str_starts_with((string) $path, 'http://') || str_starts_with((string) $path, 'https://') || str_starts_with((string) $path, '/'))
                ->each(fn ($path) => Storage::disk('public')->delete((string) $path));

            if ($request->boolean('profile_image_remove')) {
                $user->photo_path = null;
                $user->profile_photo = null;
                $user->profile_image = null;
            }
        }

        if ($request->hasFile('profile_image')) {
            $photoPath = $request->file('profile_image')->store('profile-images', 'public');
            $user->photo_path = $photoPath;
            $user->profile_photo = $photoPath;
            $user->profile_image = $photoPath;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        collect([$user->photo_path, $user->profile_photo, $user->profile_image])
            ->filter()
            ->unique()
            ->reject(fn ($path) => str_starts_with((string) $path, 'http://') || str_starts_with((string) $path, 'https://') || str_starts_with((string) $path, '/'))
            ->each(fn ($path) => Storage::disk('public')->delete((string) $path));

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
