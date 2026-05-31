<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
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
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $role = $request->input('role') === 'admin' ? 'admin' : 'user';

        // Normalise email before validation so stray spaces never cause false failures
        $request->merge(['email' => strtolower(trim($request->input('email', '')))]);

        // ── Shared validation ────────────────────────────────────────────────
        $rules = [
            'role'     => ['required', 'in:user,admin'],
            'name'     => ['required', 'string', 'min:2', 'max:255'],
            'email'    => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'phone'    => ['required', 'string', 'regex:/^(\+63|0)9\d{9}$/', 'max:20'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
            'terms'    => ['required', 'accepted'],
        ];

        // ── Tenant-specific rules ────────────────────────────────────────────
        if ($role === 'user') {
            $rules['school']             = ['required', 'string', 'max:255'];
            $rules['course_year']        = ['nullable', 'string', 'max:255'];
            $rules['preferred_location'] = ['nullable', 'string', 'max:255'];
            $rules['budget_min']         = ['nullable', 'numeric', 'min:0', 'max:999999'];
            $rules['budget_max']         = ['nullable', 'numeric', 'min:0', 'max:999999', 'gte:budget_min'];
            $rules['lifestyle_info']     = ['nullable', 'string', 'max:3000'];
        }

        // ── Owner-specific rules ─────────────────────────────────────────────
        if ($role === 'admin') {
            $rules['bh_name']       = ['required', 'string', 'max:255'];
            $rules['bh_address']    = ['required', 'string', 'max:1000'];
            $rules['bh_contact']    = ['nullable', 'string', 'max:50'];
            $rules['room_types']    = ['nullable', 'array'];
            $rules['room_types.*']  = ['string', 'max:50'];
            $rules['rent_min']      = ['nullable', 'numeric', 'min:0', 'max:999999'];
            $rules['rent_max']      = ['nullable', 'numeric', 'min:0', 'max:999999'];
            $rules['amenities']     = ['nullable', 'array'];
            $rules['amenities.*']   = ['string', 'max:50'];
            $rules['house_rules']   = ['nullable', 'string', 'max:5000'];
            $rules['photos']        = ['nullable', 'array', 'max:5'];
            $rules['photos.*']      = ['file', 'image', 'max:5120'];
            $rules['valid_id_file'] = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
        }

        $messages = [
            'name.required'          => 'Full name is required.',
            'name.min'               => 'Full name must be at least 2 characters.',
            'email.required'         => 'Email address is required.',
            'email.email'            => 'Please enter a valid email address.',
            'email.unique'           => 'An account with this email already exists.',
            'phone.required'         => 'Phone number is required.',
            'phone.regex'            => 'Enter a valid Philippine number, e.g. 09XX XXX XXXX.',
            'password.required'      => 'Password is required.',
            'password.confirmed'     => 'Passwords do not match.',
            'password.min'           => 'Password must be at least 8 characters.',
            'terms.accepted'         => 'You must accept the Terms of Service to continue.',
            'school.required'        => 'School or university is required.',
            'budget_max.gte'         => 'Maximum budget must be greater than or equal to minimum.',
            'bh_name.required'       => 'Boarding house name is required.',
            'bh_address.required'    => 'Boarding house address is required.',
            'photos.max'             => 'You may upload a maximum of 5 photos.',
            'photos.*.image'         => 'Each photo must be an image file.',
            'photos.*.max'           => 'Each photo may not exceed 5 MB.',
            'valid_id_file.mimes'    => 'Valid ID must be a JPG, PNG, or PDF.',
            'valid_id_file.max'      => 'Valid ID file may not exceed 5 MB.',
        ];

        $validated = $request->validate($rules, $messages);

        // ── Create user ──────────────────────────────────────────────────────
        $hashedPassword = Hash::make($validated['password']);

        $attributes = [
            'name'              => $validated['name'],
            'email'             => strtolower($validated['email']),
            'phone'             => $validated['phone'],
            'contact_number'    => $validated['phone'],
            'role'              => $role,
            'password'          => $hashedPassword,
            'is_active'         => true,
            'status'            => 'active',
            'email_verified_at' => now(),
        ];

        if (Schema::hasColumn('users', 'password_hash')) {
            $attributes['password_hash'] = $hashedPassword;
        }

        $user = new User;
        $user->forceFill($attributes);
        $user->save();

        // ── Tenant auxiliary data ────────────────────────────────────────────
        if ($role === 'user') {
            TenantProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'school_company'           => $validated['school'],
                    'course_or_position'       => $validated['course_year'] ?? null,
                    'valid_id_type'            => null,
                    'valid_id_number'          => null,
                    'valid_id_file'            => null,
                    'emergency_contact_name'   => null,
                    'emergency_contact_number' => null,
                    'preferred_language'       => 'english',
                ]
            );

            if (Schema::hasTable('tenant_match_profiles')) {
                // Build additional notes from preferred location + lifestyle info
                $noteParts = [];
                if (! empty($validated['preferred_location'])) {
                    $noteParts[] = 'Preferred Location: ' . $validated['preferred_location'];
                }
                if (! empty($validated['lifestyle_info'])) {
                    $noteParts[] = $validated['lifestyle_info'];
                }

                $hasAnyPreference = ! empty($validated['budget_min'])
                    || ! empty($validated['budget_max'])
                    || ! empty($validated['preferred_location'])
                    || ! empty($validated['lifestyle_info']);

                TenantMatchProfile::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'budget_min'        => $validated['budget_min'] ?? null,
                        'budget_max'        => $validated['budget_max'] ?? null,
                        'additional_notes'  => $noteParts ? implode("\n\n", $noteParts) : null,
                        'gender_preference' => 'no_preference',
                        'completed_at'      => $hasAnyPreference ? now() : null,
                    ]
                );
            }
        }

        // ── Owner auxiliary data ─────────────────────────────────────────────
        if ($role === 'admin') {
            // Handle valid ID upload
            $validIdPath = null;
            if ($request->hasFile('valid_id_file') && $request->file('valid_id_file')->isValid()) {
                $validIdPath = $request->file('valid_id_file')->store('owner-ids', 'public');
            }

            $ownerProfile = OwnerProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name'        => $validated['bh_name'],
                    'valid_id_type'       => $validIdPath ? 'uploaded' : 'pending',
                    'valid_id_number'     => 'pending',
                    'valid_id_file'       => $validIdPath ?? 'pending',
                    'verification_status' => 'pending',
                ]
            );

            // Build description from room types, amenities, rent range
            $descParts = [];
            $roomTypes  = $validated['room_types'] ?? [];
            $amenities  = $validated['amenities']  ?? [];

            if (! empty($roomTypes)) {
                $descParts[] = 'Room Types: ' . implode(', ', $roomTypes);
            }
            if (! empty($amenities)) {
                $descParts[] = 'Amenities: ' . implode(', ', $amenities);
            }
            if (! empty($validated['rent_min']) || ! empty($validated['rent_max'])) {
                $descParts[] = 'Monthly Rent: ₱' . ($validated['rent_min'] ?? 0)
                             . ' – ₱' . ($validated['rent_max'] ?? 0);
            }

            // Handle photo uploads
            $featuredImage = null;
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    if (! $photo->isValid()) {
                        continue;
                    }
                    $path = $photo->store('boarding-house-photos', 'public');
                    if ($index === 0) {
                        $featuredImage = $path;
                    }
                }
            }

            BoardingHouse::create([
                'owner_id'        => $user->id,
                'owner_profile_id'=> $ownerProfile->id,
                'name'            => $validated['bh_name'],
                'address'         => $validated['bh_address'],
                'description'     => implode("\n", $descParts) ?: null,
                'house_rules'     => $validated['house_rules'] ?? null,
                'contact_name'    => $validated['name'],
                'contact_phone'   => $validated['bh_contact'] ?? $validated['phone'],
                'contact_number'  => $validated['bh_contact'] ?? $validated['phone'],
                'price'           => $validated['rent_min'] ?? null,
                'is_active'       => false,
                'approval_status' => 'pending',
                'featured_image'  => $featuredImage,
            ]);
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
