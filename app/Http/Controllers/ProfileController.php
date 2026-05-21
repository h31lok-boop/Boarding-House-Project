<?php

namespace App\Http\Controllers;

use App\Models\OwnerProfile;
use App\Support\OwnerActivityService;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    private function editRouteName(Request $request): string
    {
        if ($request->routeIs('superduperadmin.profile*')) {
            return 'superduperadmin.profile';
        }

        if ($request->routeIs('owner.profile*')) {
            return 'owner.profile';
        }

        return 'profile.edit';
    }

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
            'phone' => $validated['phone'] ?? null,
            'contact_number' => $validated['phone'] ?? null,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->boolean('profile_image_remove') && $user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
            $user->profile_image = null;
        }

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $user->profile_image = $request->file('profile_image')->store('profile-images', 'public');
        }

        if ($user->isOwner()) {
            OwnerProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $validated['company_name'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'business_permit_number' => $validated['business_permit_number'] ?? null,
                    'valid_id_type' => $validated['valid_id_type'] ?: 'other',
                    'valid_id_number' => $validated['valid_id_number'] ?: ('PENDING-'.$user->id),
                    'valid_id_file' => $user->ownerProfile?->valid_id_file ?: 'pending-upload.txt',
                    'verification_status' => $user->ownerProfile?->verification_status ?: 'pending',
                ]
            );
        }

        $user->save();

        OwnerActivityService::audit($user->id, 'profile_updated', 'Owner profile information updated.', [
            'route' => $request->route()?->getName(),
        ]);

        return Redirect::route($this->editRouteName($request))->with('status', 'profile-updated');
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

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
