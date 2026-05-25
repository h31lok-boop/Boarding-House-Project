<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Accreditation;
use App\Models\Amenity;
use App\Models\BoardingHouse;
use App\Models\BoardingHouseApplication;
use App\Models\BoardingHouseImage;
use App\Models\ComplianceRequirement;
use App\Models\Location;
use App\Models\OwnerProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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

    public function createOwner(): View
    {
        return view('auth.owner-register');
    }

    public function storeTenant(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\\-\\s]{7,30}$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'move_in_date' => ['required', 'date'],
            'preferred_room_type' => ['required', 'string', 'in:Single Room,Double Room,Bed Space,Shared Room'],
            'profile_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'terms' => ['accepted'],
        ], [
            'phone.regex' => 'The contact number must be a valid phone number.',
            'terms.accepted' => 'Please accept the Terms and Privacy Policy before registering.',
        ]);

        $hashedPassword = Hash::make($validated['password']);

        $user = new User;
        $user->forceFill([
            'role' => 'tenant',
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'contact_number' => $validated['phone'],
            'move_in_date' => $validated['move_in_date'],
            'room_number' => $validated['preferred_room_type'],
            'password' => $hashedPassword,
            'password_hash' => $hashedPassword,
            'is_active' => true,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        if ($request->hasFile('profile_photo')) {
            $user->profile_image = $request->file('profile_photo')->store('tenant-registrations/profile-photos', 'public');
        }

        $user->save();

        if (method_exists($user, 'assignRole')) {
            $tenantRole = Role::findOrCreate('tenant', 'web');
            $user->syncRoles([$tenantRole]);
        }

        event(new Registered($user));

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect(route('tenant.dashboard', absolute: false))
            ->with('status', 'Your tenant account has been created.');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => ['nullable', 'string', 'in:user,admin,tenant,owner'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:30'],
            'boarding_house_id' => ['nullable', 'integer', 'exists:boarding_houses,id'],
            'institution_name' => ['nullable', 'string', 'max:255'],
            'move_in_date' => ['nullable', 'date'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['sometimes', 'accepted'],
        ]);

        $role = match (strtolower((string) $request->input('role', 'user'))) {
            'admin', 'owner' => 'owner',
            default => 'tenant',
        };
        $hashedPassword = Hash::make($request->password);

        $attributes = [
            'role' => $role,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'contact_number' => $request->phone,
            'institution_name' => $role === 'tenant' ? $request->institution_name : null,
            'move_in_date' => $role === 'tenant' ? $request->move_in_date : null,
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
            $userRole = Role::findOrCreate($role, 'web');
            $user->syncRoles([$userRole]);
        }

        if ($role === 'owner') {
            OwnerProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => null,
                    'business_permit_number' => null,
                    'valid_id_type' => 'other',
                    'valid_id_number' => 'PENDING-'.$user->id,
                    'valid_id_file' => 'pending-upload.txt',
                    'verification_status' => 'pending',
                ]
            );
        }

        $boardingHouseId = $request->integer('boarding_house_id');
        if ($role === 'tenant' && $boardingHouseId > 0) {
            BoardingHouseApplication::updateOrCreate(
                ['user_id' => $user->id, 'boarding_house_id' => $boardingHouseId],
                ['status' => 'pending']
            );
        }

        event(new Registered($user));

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        // Use a single, predictable redirect. The dashboard route itself
        // handles per-role forwarding so callers and tests always see the
        // same target coming out of registration.
        return redirect(route('dashboard', absolute: false));
    }

    public function storeOwner(Request $request): RedirectResponse
    {
        if ($request->input('registration_mode') === 'quick') {
            return $this->storeQuickOwner($request);
        }

        if ($request->input('registration_mode') === 'owner_map') {
            return $this->storeMapOwner($request);
        }

        $validated = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:30'],
            'mobile_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\\-\\s]{7,30}$/'],
            'alternative_contact_number' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\\-\\s]{7,30}$/'],
            'profile_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'boarding_house_name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'property_type' => ['required', 'string', 'in:Boarding House,Apartment,Dormitory,Bed Space,Shared Room'],
            'number_of_rooms' => ['nullable', 'integer', 'min:0'],
            'available_slots' => ['nullable', 'integer', 'min:0'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'description' => ['required', 'string', 'max:5000'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string', 'max:100'],
            'region' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'barangay' => ['nullable', 'string', 'max:120'],
            'street' => ['nullable', 'string', 'max:255'],
            'complete_address' => ['required', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'valid_id' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'business_permit' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'fire_safety_certificate' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'sanitary_permit' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'boarding_house_permit' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'proof_of_ownership' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'boarding_house_photos' => ['required', 'array', 'min:1'],
            'boarding_house_photos.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'house_rules_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:8192'],
        ], [
            'mobile_number.regex' => 'The mobile number must be a valid phone number.',
            'alternative_contact_number.regex' => 'The alternative contact number must be a valid phone number.',
            'max_price.gte' => 'The maximum monthly price must be greater than or equal to the minimum monthly price.',
            'boarding_house_photos.required' => 'At least one boarding house photo is required.',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $fullName = collect([
                $validated['first_name'],
                $validated['middle_name'] ?? null,
                $validated['last_name'],
                $validated['suffix'] ?? null,
            ])->filter()->implode(' ');

            $hashedPassword = Hash::make($validated['password']);

            $user = new User;
            $user->forceFill([
                'role' => 'owner',
                'name' => $fullName,
                'email' => $validated['email'],
                'phone' => $validated['mobile_number'],
                'contact_number' => $validated['mobile_number'],
                'password' => $hashedPassword,
                'password_hash' => $hashedPassword,
                'is_active' => false,
                'status' => 'pending_verification',
                'email_verified_at' => now(),
            ]);

            if ($request->hasFile('profile_photo')) {
                $user->profile_image = $request->file('profile_photo')->store('owner-registrations/profile-photos', 'public');
            }

            $user->save();

            if (method_exists($user, 'assignRole')) {
                $ownerRole = Role::findOrCreate('owner', 'web');
                $user->syncRoles([$ownerRole]);
            }

            $validIdPath = $request->file('valid_id')->store("owner-registrations/{$user->id}/documents", 'public');

            $ownerProfile = OwnerProfile::create([
                'user_id' => $user->id,
                'company_name' => $validated['business_name'] ?: $validated['boarding_house_name'],
                'address' => $validated['complete_address'],
                'business_permit_number' => 'PENDING-'.$user->id,
                'valid_id_type' => 'government_id',
                'valid_id_number' => 'PENDING-'.$user->id,
                'valid_id_file' => $validIdPath,
                'verification_status' => 'pending',
            ]);

            $boardingHouse = new BoardingHouse;
            $boardingHouse->fill([
                'owner_profile_id' => $ownerProfile->id,
                'owner_id' => $user->id,
                'name' => $validated['boarding_house_name'],
                'address' => $validated['complete_address'],
                'full_address' => $validated['complete_address'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'landmark' => $validated['street'] ?? null,
                'description' => $validated['description'],
                'contact_name' => $fullName,
                'contact_number' => $validated['mobile_number'],
                'contact_phone' => $validated['mobile_number'],
                'monthly_payment' => $this->formatOwnerRegistrationPriceRange($validated['min_price'] ?? null, $validated['max_price'] ?? null),
                'capacity' => $validated['number_of_rooms'] ?? 0,
                'available_rooms' => $validated['available_slots'] ?? 0,
                'price' => $validated['min_price'] ?? null,
                'approval_status' => 'pending',
                'status' => 'pending_review',
                'is_active' => false,
                'room_types' => $validated['property_type'],
                'safety_features' => implode(', ', $validated['amenities'] ?? []),
                'landlord_info' => $validated['business_name'] ?: $fullName,
            ]);
            $boardingHouse->save();

            $this->syncOwnerRegistrationAmenities($boardingHouse, $validated['amenities'] ?? []);
            $this->storeOwnerRegistrationLocation($boardingHouse, $validated);
            $this->createOwnerRegistrationReviewRecord($boardingHouse);
            $this->storeOwnerRegistrationDocuments($request, $user, $boardingHouse, $validIdPath);

            event(new Registered($user));

            return $user;
        });

        return redirect()
            ->route('login')
            ->with('status', 'Your owner account has been submitted for review.');
    }

    private function storeMapOwner(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'full_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\\-\\s]{7,30}$/'],
            'profile_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'boarding_house_name' => ['required', 'string', 'max:255'],
            'property_type' => ['required', 'string', 'in:Boarding House,Apartment,Dormitory,Bed Space,Shared Room'],
            'description' => ['nullable', 'string', 'max:5000'],
            'number_of_rooms' => ['required', 'integer', 'min:0'],
            'available_slots' => ['required', 'integer', 'min:0'],
            'min_price' => ['required', 'numeric', 'min:0'],
            'max_price' => ['required', 'numeric', 'min:0', 'gte:min_price'],
            'region' => ['required', 'string', 'max:120'],
            'province' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'barangay' => ['required', 'string', 'max:120'],
            'street' => ['required', 'string', 'max:255'],
            'complete_address' => ['required', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string', 'max:100'],
            'valid_id' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'business_permit' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'fire_safety_certificate' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'sanitary_permit' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'boarding_house_permit' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'proof_of_ownership' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'house_rules_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:8192'],
            'boarding_house_photos' => ['required', 'array', 'min:1'],
            'boarding_house_photos.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'terms_conditions' => ['accepted'],
            'privacy_policy' => ['accepted'],
            'accuracy_confirmation' => ['accepted'],
            'notification_consent' => ['nullable', 'boolean'],
        ], [
            'contact_number.regex' => 'The contact number must be a valid phone number.',
            'max_price.gte' => 'The maximum monthly price must be greater than or equal to the minimum monthly price.',
            'boarding_house_photos.required' => 'At least one boarding house photo is required.',
            'terms_conditions.accepted' => 'Please agree to the Terms and Conditions.',
            'privacy_policy.accepted' => 'Please agree to the Privacy Policy.',
            'accuracy_confirmation.accepted' => 'Please confirm that the information is accurate.',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $hashedPassword = Hash::make($validated['password']);

            $user = new User;
            $user->forceFill([
                'role' => 'owner',
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['contact_number'],
                'contact_number' => $validated['contact_number'],
                'password' => $hashedPassword,
                'password_hash' => $hashedPassword,
                'is_active' => false,
                'status' => 'pending_verification',
                'email_verified_at' => now(),
            ]);

            if ($request->hasFile('profile_photo')) {
                $user->profile_image = $request->file('profile_photo')->store('owner-registrations/profile-photos', 'public');
            }

            $user->save();

            if (method_exists($user, 'assignRole')) {
                $ownerRole = Role::findOrCreate('owner', 'web');
                $user->syncRoles([$ownerRole]);
            }

            $validIdPath = $request->file('valid_id')->store("owner-registrations/{$user->id}/documents", 'public');

            $ownerProfile = OwnerProfile::create([
                'user_id' => $user->id,
                'company_name' => $validated['boarding_house_name'],
                'address' => $validated['complete_address'],
                'business_permit_number' => 'PENDING-'.$user->id,
                'valid_id_type' => 'government_id',
                'valid_id_number' => 'PENDING-'.$user->id,
                'valid_id_file' => $validIdPath,
                'verification_status' => 'pending',
            ]);

            $boardingHouse = new BoardingHouse;
            $boardingHouse->fill([
                'owner_profile_id' => $ownerProfile->id,
                'owner_id' => $user->id,
                'name' => $validated['boarding_house_name'],
                'address' => $validated['complete_address'],
                'full_address' => $validated['complete_address'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'landmark' => $validated['street'],
                'description' => $validated['description'] ?: 'Submitted through admin registration and pending review.',
                'contact_name' => $validated['full_name'],
                'contact_number' => $validated['contact_number'],
                'contact_phone' => $validated['contact_number'],
                'monthly_payment' => $this->formatOwnerRegistrationPriceRange($validated['min_price'], $validated['max_price']),
                'capacity' => $validated['number_of_rooms'],
                'available_rooms' => $validated['available_slots'],
                'price' => $validated['min_price'],
                'approval_status' => 'pending',
                'status' => 'pending_review',
                'is_active' => false,
                'room_types' => $validated['property_type'],
                'safety_features' => implode(', ', $validated['amenities'] ?? []),
                'landlord_info' => $validated['full_name'],
            ]);
            $boardingHouse->save();

            $this->syncOwnerRegistrationAmenities($boardingHouse, $validated['amenities'] ?? []);
            $this->storeOwnerRegistrationLocation($boardingHouse, $validated);
            $this->createOwnerRegistrationReviewRecord($boardingHouse);
            $this->storeOwnerRegistrationDocuments($request, $user, $boardingHouse, $validIdPath);

            event(new Registered($user));
        });

        return redirect()
            ->route('login')
            ->with('status', 'Owner registration submitted for review.');
    }

    private function storeQuickOwner(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\\-\\s]{7,30}$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'boarding_house_name' => ['required', 'string', 'max:255'],
            'boarding_house_address' => ['required', 'string', 'max:1000'],
            'region' => ['required', 'string', 'max:120'],
            'province' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'boarding_house_photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'owner_id_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'supporting_documents' => ['required', 'array', 'min:1'],
            'supporting_documents.*' => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:8192'],
            'terms' => ['accepted'],
        ], [
            'phone.regex' => 'The contact number must be a valid phone number.',
            'boarding_house_photo.required' => 'Please upload a boarding house photo.',
            'owner_id_document.required' => 'Please upload the owner ID document.',
            'supporting_documents.required' => 'Please upload at least one supporting document.',
            'terms.accepted' => 'Please accept the Terms and Privacy Policy before registering.',
        ]);

        $user = DB::transaction(function () use ($request, $validated) {
            $hashedPassword = Hash::make($validated['password']);

            $user = new User;
            $user->forceFill([
                'role' => 'owner',
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'contact_number' => $validated['phone'],
                'password' => $hashedPassword,
                'password_hash' => $hashedPassword,
                'is_active' => true,
                'status' => 'pending_verification',
                'email_verified_at' => now(),
            ]);
            $user->save();

            if (method_exists($user, 'assignRole')) {
                $ownerRole = Role::findOrCreate('owner', 'web');
                $user->syncRoles([$ownerRole]);
            }

            $ownerIdPath = $request->file('owner_id_document')->store("owner-registrations/{$user->id}/documents", 'public');

            $ownerProfile = OwnerProfile::create([
                'user_id' => $user->id,
                'company_name' => $validated['boarding_house_name'],
                'address' => $validated['boarding_house_address'],
                'business_permit_number' => 'PENDING-'.$user->id,
                'valid_id_type' => 'government_id',
                'valid_id_number' => 'PENDING-'.$user->id,
                'valid_id_file' => $ownerIdPath,
                'verification_status' => 'pending',
            ]);

            $boardingHouse = new BoardingHouse;
            $boardingHouse->fill([
                'owner_profile_id' => $ownerProfile->id,
                'owner_id' => $user->id,
                'name' => $validated['boarding_house_name'],
                'address' => $validated['boarding_house_address'],
                'full_address' => $validated['boarding_house_address'],
                'landmark' => trim($validated['region'].' / '.$validated['province'].' / '.$validated['city']),
                'description' => 'Submitted through quick admin registration and pending review.',
                'contact_name' => $validated['name'],
                'contact_number' => $validated['phone'],
                'contact_phone' => $validated['phone'],
                'capacity' => 0,
                'available_rooms' => 0,
                'approval_status' => 'pending',
                'status' => 'pending_review',
                'is_active' => false,
                'landlord_info' => $validated['name'],
            ]);
            $boardingHouse->save();

            $photoPath = $request->file('boarding_house_photo')->store("owner-registrations/{$user->id}/photos", 'public');
            $this->storeQuickOwnerRegistrationDocuments($request, $user, $boardingHouse, $ownerIdPath, $photoPath);
            $this->storeOwnerRegistrationBoardingHouseImage($boardingHouse, $photoPath, 0);
            $this->createOwnerRegistrationReviewRecord($boardingHouse);

            return $user;
        });

        event(new Registered($user));

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect(route('owner.dashboard', absolute: false))
            ->with('status', 'Your owner account has been submitted for review.');
    }

    private function storeQuickOwnerRegistrationDocuments(Request $request, User $user, BoardingHouse $boardingHouse, string $ownerIdPath, string $photoPath): void
    {
        if (! Schema::hasTable('compliance_requirements')) {
            return;
        }

        $documents = [
            ['label' => 'Valid ID of Owner', 'path' => $ownerIdPath],
            ['label' => 'Photo of Boarding House 1', 'path' => $photoPath],
        ];

        foreach ($request->file('supporting_documents', []) as $index => $document) {
            $documents[] = [
                'label' => 'Owner Supporting Document '.($index + 1),
                'path' => $document->store("owner-registrations/{$user->id}/documents", 'public'),
            ];
        }

        foreach ($documents as $document) {
            ComplianceRequirement::create([
                'boarding_house_id' => $boardingHouse->id,
                'submitted_by' => $user->id,
                'requirement_name' => $document['label'],
                'uploaded_file' => $document['path'],
                'submission_date' => now()->toDateString(),
                'validation_status' => 'pending',
            ]);
        }
    }

    private function formatOwnerRegistrationPriceRange($minPrice, $maxPrice): ?string
    {
        if ($minPrice === null && $maxPrice === null) {
            return null;
        }

        if ($minPrice !== null && $maxPrice !== null) {
            return 'PHP '.number_format((float) $minPrice, 2).' - PHP '.number_format((float) $maxPrice, 2);
        }

        return 'PHP '.number_format((float) ($minPrice ?? $maxPrice), 2);
    }

    private function syncOwnerRegistrationAmenities(BoardingHouse $boardingHouse, array $amenities): void
    {
        if (! Schema::hasTable('amenities') || ! Schema::hasTable('boarding_house_amenities')) {
            return;
        }

        $amenityIds = collect($amenities)
            ->filter()
            ->map(fn ($name) => Amenity::firstOrCreate(['name' => $name])->id)
            ->values()
            ->all();

        $boardingHouse->amenities()->sync($amenityIds);
    }

    private function storeOwnerRegistrationLocation(BoardingHouse $boardingHouse, array $validated): void
    {
        if (! Schema::hasTable('locations')) {
            return;
        }

        Location::updateOrCreate(
            ['boarding_house_id' => $boardingHouse->id],
            [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'landmark' => $validated['street'] ?? null,
            ]
        );
    }

    private function createOwnerRegistrationReviewRecord(BoardingHouse $boardingHouse): void
    {
        if (! Schema::hasTable('accreditations')) {
            return;
        }

        Accreditation::firstOrCreate(
            ['boarding_house_id' => $boardingHouse->id],
            [
                'status' => 'Pending Review',
                'decision_log' => 'Submitted through admin registration on '.now()->toDateTimeString(),
            ]
        );
    }

    private function storeOwnerRegistrationDocuments(Request $request, User $user, BoardingHouse $boardingHouse, string $validIdPath): void
    {
        if (! Schema::hasTable('compliance_requirements')) {
            return;
        }

        $documents = [
            'valid_id' => ['label' => 'Valid ID of Owner', 'path' => $validIdPath],
            'business_permit' => ['label' => 'Business Permit'],
            'fire_safety_certificate' => ['label' => 'Fire Safety Certificate'],
            'sanitary_permit' => ['label' => 'Sanitary Permit'],
            'boarding_house_permit' => ['label' => 'Boarding House Permit'],
            'proof_of_ownership' => ['label' => 'Proof of Ownership or Lease Agreement'],
            'house_rules_document' => ['label' => 'House Rules Document'],
        ];

        foreach ($documents as $field => $document) {
            $path = $document['path'] ?? null;

            if (! $path && $request->hasFile($field)) {
                $path = $request->file($field)->store("owner-registrations/{$user->id}/documents", 'public');
            }

            if (! $path) {
                continue;
            }

            ComplianceRequirement::create([
                'boarding_house_id' => $boardingHouse->id,
                'submitted_by' => $user->id,
                'requirement_name' => $document['label'],
                'uploaded_file' => $path,
                'submission_date' => now()->toDateString(),
                'validation_status' => 'pending',
            ]);
        }

        foreach ($request->file('boarding_house_photos', []) as $index => $photo) {
            $path = $photo->store("owner-registrations/{$user->id}/photos", 'public');

            ComplianceRequirement::create([
                'boarding_house_id' => $boardingHouse->id,
                'submitted_by' => $user->id,
                'requirement_name' => 'Photo of Boarding House '.($index + 1),
                'uploaded_file' => $path,
                'submission_date' => now()->toDateString(),
                'validation_status' => 'pending',
            ]);

            $this->storeOwnerRegistrationBoardingHouseImage($boardingHouse, $path, $index);
        }
    }

    private function storeOwnerRegistrationBoardingHouseImage(BoardingHouse $boardingHouse, string $path, int $index): void
    {
        if (Schema::hasTable('boarding_house_images')) {
            BoardingHouseImage::create([
                'boarding_house_id' => $boardingHouse->id,
                'image_path' => $path,
                'image_label' => 'Admin registration photo '.($index + 1),
                'is_primary' => $index === 0,
                'sort_order' => $index,
            ]);
        }

        if ($index !== 0) {
            return;
        }

        $updates = [];
        if (Schema::hasColumn('boarding_houses', 'featured_image')) {
            $updates['featured_image'] = $path;
        }
        if (Schema::hasColumn('boarding_houses', 'exterior_image')) {
            $updates['exterior_image'] = $path;
        }

        if ($updates !== []) {
            $boardingHouse->forceFill($updates)->save();
        }
    }
}
