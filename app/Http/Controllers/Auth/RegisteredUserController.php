<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\BoardingHousePhoto;
use App\Models\OwnerProfile;
use App\Models\TenantMatchProfile;
use App\Models\TenantProfile;
use App\Models\User;
use App\Rules\BoardMatchStrongPassword;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Throwable;

class RegisteredUserController extends Controller
{
    /**
     * @var array<string, array<int, string>>
     */
    private array $columnCache = [];

    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function create(): View
    {
        return $this->showRegistrationForm();
    }

    public function register(Request $request): RedirectResponse
    {
        return $this->store($request);
    }

    public function store(Request $request): RedirectResponse
    {
        $rentMin = trim((string) $request->input('rent_min', ''));
        $rentMax = trim((string) $request->input('rent_max', ''));
        $monthlyRentRange = trim((string) $request->input('monthly_rent_range', ''));
        if ($monthlyRentRange === '' && ($rentMin !== '' || $rentMax !== '')) {
            $monthlyRentRange = trim('PHP '.$rentMin.' - PHP '.$rentMax);
        }

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email', ''))),
            'phone' => trim((string) ($request->input('phone') ?: $request->input('phone_number', ''))),
            'bh_contact' => trim((string) ($request->input('bh_contact') ?: $request->input('contact_number', ''))),
            'role' => $this->normalizeRole($request->input('role')),
            'rental_budget' => $request->input('rental_budget') ?: $request->input('budget_min') ?: $request->input('budget_max'),
            'room_types' => $this->submittedList($request->input('room_types')),
            'monthly_rent_range' => $monthlyRentRange,
            'amenities' => $this->submittedList($request->input('amenities')),
        ]);

        $role = (string) $request->input('role');
        $isOwnerRegistration = $role === 'owner';

        $validated = $request->validate(
            $this->rules($role),
            $this->messages()
        );

        $uploadedPaths = [];

        try {
            DB::beginTransaction();

            $hashedPassword = Hash::make($validated['password']);
            $user = $this->createUser($validated, $role, $hashedPassword, $isOwnerRegistration);

            if ($role === 'tenant') {
                $this->createTenantData($user, $validated);
            }

            if ($isOwnerRegistration) {
                $proofPath = $request->file('proof_of_ownership')->store('proof-of-ownership', 'public');
                $uploadedPaths[] = $proofPath;

                $ownerProfile = $this->createOwnerProfile($user, $validated, $proofPath);
                $boardingHouse = $this->createBoardingHouse($user, $ownerProfile, $validated, $proofPath);

                foreach ($request->file('photos', []) as $index => $photo) {
                    $photoPath = $photo->store('boarding-house-photos', 'public');
                    $uploadedPaths[] = $photoPath;

                    BoardingHousePhoto::create($this->attributesForTable('boarding_house_photos', [
                        'owner_id' => $user->id,
                        'boarding_house_id' => $boardingHouse->id,
                        'photo_path' => $photoPath,
                    ]));

                    if ($index === 0) {
                        $boardingHouse->forceFill(['featured_image' => $photoPath])->save();
                    }
                }
            }

            if (method_exists($user, 'syncRoles') && Schema::hasTable('roles')) {
                $spatieRole = Role::findOrCreate($role, 'web');
                $user->syncRoles([$spatieRole]);
            }

            DB::commit();

            event(new Registered($user));

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()
                ->route($user->dashboardRouteName())
                ->with('status', $role === 'tenant'
                    ? 'Your account has been created successfully.'
                    : 'Your owner account has been submitted successfully and is pending verification.');
        } catch (Throwable $e) {
            DB::rollBack();

            if ($uploadedPaths !== []) {
                Storage::disk('public')->delete($uploadedPaths);
            }

            report($e);

            return back()
                ->withInput($request->except(['password', 'password_confirmation', 'photos', 'proof_of_ownership']))
                ->withErrors([
                    'registration' => 'Registration failed. Please review your details and try again.',
                ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(string $role): array
    {
        $rules = [
            'role' => ['required', 'in:tenant,owner'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => $this->passwordRules(),
            'terms' => ['required', 'accepted'],
        ];

        if (Schema::hasColumn('users', 'username')) {
            $rules['username'] = ['nullable', 'string', 'alpha_dash', 'min:3', 'max:80', 'unique:users,username'];
        }

        if ($role === 'tenant') {
            $rules['school'] = ['required', 'string', 'max:255'];
            $rules['course_year'] = ['required', 'string', 'max:255'];
            $rules['preferred_location'] = ['required', 'string', 'max:255'];
            $rules['rental_budget'] = ['required', 'numeric', 'min:0', 'max:999999'];
            $rules['budget_min'] = ['nullable', 'numeric', 'min:0', 'max:999999'];
            $rules['budget_max'] = ['nullable', 'numeric', 'min:0', 'max:999999'];
            $rules['lifestyle_info'] = ['required', 'string', 'max:3000'];
        }

        if ($role === 'owner') {
            $rules['bh_name'] = ['required', 'string', 'max:255'];
            $rules['bh_address'] = ['required', 'string', 'max:1000'];
            $rules['bh_contact'] = ['required', 'string', 'max:50'];
            $rules['room_types'] = ['required', 'array', 'min:1'];
            $rules['room_types.*'] = ['string', 'max:50'];
            $rules['rent_min'] = ['nullable', 'numeric', 'min:0', 'max:999999'];
            $rules['rent_max'] = ['nullable', 'numeric', 'min:0', 'max:999999', 'gte:rent_min'];
            $rules['monthly_rent_range'] = ['required', 'string', 'max:255'];
            $rules['amenities'] = ['required', 'array', 'min:1'];
            $rules['amenities.*'] = ['string', 'max:50'];
            $rules['house_rules'] = ['required', 'string', 'max:5000'];
            $rules['photos'] = ['nullable', 'array'];
            $rules['photos.*'] = ['image', 'mimes:jpg,jpeg,png', 'max:2048'];
            $rules['proof_of_ownership'] = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'role.required' => 'Please select whether you are registering as a tenant/student or owner/admin.',
            'role.in' => 'Please select a valid account role.',
            'name.required' => 'Full name is required.',
            'name.max' => 'Full name may not exceed 100 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'An account with this email already exists.',
            'username.unique' => 'This username is already taken.',
            'username.alpha_dash' => 'Username may only contain letters, numbers, dashes, and underscores.',
            'phone.required' => 'Phone number is required.',
            'phone.max' => 'Phone number may not exceed 20 characters.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'terms.required' => 'You must accept the Terms and Conditions.',
            'terms.accepted' => 'You must accept the Terms and Conditions.',
            'school.required' => 'School or university is required.',
            'course_year.required' => 'Course or year level is required.',
            'preferred_location.required' => 'Preferred location is required.',
            'rental_budget.required' => 'Rental budget is required.',
            'rental_budget.numeric' => 'Rental budget must be a number.',
            'lifestyle_info.required' => 'Lifestyle information is required for AI recommendations.',
            'bh_name.required' => 'Boarding house name is required.',
            'bh_address.required' => 'Boarding house address is required.',
            'bh_contact.required' => 'Boarding house contact number is required.',
            'room_types.required' => 'Select at least one room type.',
            'room_types.min' => 'Select at least one room type.',
            'monthly_rent_range.required' => 'Monthly rent range is required.',
            'rent_max.gte' => 'Maximum rent must be greater than or equal to minimum rent.',
            'amenities.required' => 'Select at least one amenity.',
            'amenities.min' => 'Select at least one amenity.',
            'house_rules.required' => 'House rules are required.',
            'photos.*.image' => 'Each boarding house photo must be an image.',
            'photos.*.mimes' => 'Each photo must be a JPG or PNG image.',
            'photos.*.max' => 'Each photo may not exceed 2 MB.',
            'proof_of_ownership.required' => 'Valid ID or proof of ownership is required.',
            'proof_of_ownership.mimes' => 'Proof of ownership must be a JPG, PNG, or PDF.',
            'proof_of_ownership.max' => 'Proof of ownership may not exceed 2 MB.',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function passwordRules(): array
    {
        return [
            'required',
            'confirmed',
            'string',
            'min:8',
            function (string $attribute, mixed $value, \Closure $fail): void {
                $password = (string) $value;

                if (! preg_match('/[A-Z]/', $password)) {
                    $fail('Password must contain an uppercase letter.');
                }

                if (! preg_match('/[a-z]/', $password)) {
                    $fail('Password must contain a lowercase letter.');
                }

                if (! preg_match('/[0-9]/', $password)) {
                    $fail('Password must contain a number.');
                }

                if (! preg_match('/[@$!%*?&#]/', $password)) {
                    $fail('Password must contain a special character.');
                }
            },
            new BoardMatchStrongPassword,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function createUser(array $validated, string $role, string $hashedPassword, bool $isOwnerRegistration): User
    {
        $status = $isOwnerRegistration ? 'pending' : 'active';
        $phone = $validated['phone'];

        $attributes = $this->attributesForTable('users', [
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone' => $phone,
            'phone_number' => $phone,
            'contact_number' => $phone,
            'role' => $role,
            'password' => $hashedPassword,
            'password_hash' => $hashedPassword,
            'is_active' => true,
            'status' => $status,
            'account_status' => $status === 'active' ? 'Active' : 'Pending',
            'email_verified_at' => now(),
        ]);

        if (Schema::hasColumn('users', 'username')) {
            $attributes['username'] = $validated['username'] ?? $this->uniqueUsernameFromEmail($validated['email']);
        }

        $user = new User;
        $user->forceFill($attributes)->save();

        return $user;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function createTenantData(User $user, array $validated): void
    {
        TenantProfile::create($this->attributesForTable('tenant_profiles', [
            'user_id' => $user->id,
            'school_university' => $validated['school'],
            'course_year_level' => $validated['course_year'],
            'preferred_location' => $validated['preferred_location'],
            'rental_budget' => $validated['rental_budget'],
            'lifestyle_information' => $validated['lifestyle_info'],
            'school_company' => $validated['school'],
            'course_or_position' => $validated['course_year'],
            'valid_id_type' => null,
            'valid_id_number' => null,
            'valid_id_file' => null,
            'emergency_contact_name' => null,
            'emergency_contact_number' => null,
            'preferred_language' => 'english',
        ]));

        if (! Schema::hasTable('tenant_match_profiles')) {
            return;
        }

        TenantMatchProfile::create([
            'user_id' => $user->id,
            'budget_min' => $validated['budget_min'] ?? $validated['rental_budget'],
            'budget_max' => $validated['budget_max'] ?? $validated['rental_budget'],
            'additional_notes' => implode("\n\n", [
                'Preferred Location: '.$validated['preferred_location'],
                $validated['lifestyle_info'],
            ]),
            'gender_preference' => 'no_preference',
            'completed_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function createOwnerProfile(User $user, array $validated, string $proofPath): OwnerProfile
    {
        $roomTypes = $this->listValue($validated['room_types'] ?? []);
        $amenities = $this->listValue($validated['amenities'] ?? []);
        $rentRange = $this->rentRangeValue($validated);

        return OwnerProfile::create($this->attributesForTable('owner_profiles', [
            'user_id' => $user->id,
            'boarding_house_name' => $validated['bh_name'],
            'boarding_house_address' => $validated['bh_address'],
            'contact_number' => $validated['bh_contact'],
            'room_types' => $roomTypes,
            'monthly_rent_range' => $rentRange,
            'amenities' => $amenities,
            'house_rules' => $validated['house_rules'],
            'proof_of_ownership' => $proofPath,
            'company_name' => $validated['bh_name'],
            'valid_id_type' => 'proof_of_ownership',
            'valid_id_number' => 'pending',
            'valid_id_file' => $proofPath,
            'verification_status' => 'pending',
        ]));
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function createBoardingHouse(User $user, OwnerProfile $ownerProfile, array $validated, string $proofPath): BoardingHouse
    {
        $roomTypes = $validated['room_types'] ?? [];
        $amenities = $validated['amenities'] ?? [];
        $description = $this->boardingHouseDescription($roomTypes, $amenities, $validated);

        return BoardingHouse::create($this->attributesForTable('boarding_houses', [
            'owner_id' => $user->id,
            'user_id' => $user->id,
            'owner_profile_id' => $ownerProfile->id,
            'name' => $validated['bh_name'],
            'slug' => $this->uniqueBoardingHouseSlug($validated['bh_name']),
            'address' => $validated['bh_address'],
            'full_address' => $validated['bh_address'],
            'proof_of_ownership' => $proofPath,
            'description' => $description,
            'house_rules' => $validated['house_rules'],
            'contact_name' => $validated['name'],
            'contact_person' => $validated['name'],
            'contact_phone' => $validated['bh_contact'],
            'contact_number' => $validated['bh_contact'],
            'price' => $validated['rent_min'] ?? null,
            'monthly_payment' => $validated['rent_min'] ?? $validated['monthly_rent_range'],
            'is_active' => false,
            'approval_status' => 'pending',
            'status' => 'pending',
        ]));
    }

    /**
     * @param  array<int, string>  $roomTypes
     * @param  array<int, string>  $amenities
     * @param  array<string, mixed>  $validated
     */
    private function boardingHouseDescription(array $roomTypes, array $amenities, array $validated): string
    {
        return implode("\n", [
            'Room Types: '.$this->listValue($roomTypes),
            'Amenities: '.$this->listValue($amenities),
            'Monthly Rent: '.$this->rentRangeValue($validated),
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function rentRangeValue(array $validated): string
    {
        if (! empty($validated['monthly_rent_range'])) {
            return $validated['monthly_rent_range'];
        }

        return 'PHP '.($validated['rent_min'] ?? '').' - PHP '.($validated['rent_max'] ?? '');
    }

    /**
     * @param  array<int, string>  $values
     */
    private function listValue(array $values): string
    {
        return implode(', ', array_values(array_filter($values, fn ($value) => filled($value))));
    }

    private function normalizeRole(mixed $role): ?string
    {
        $role = strtolower(trim((string) $role));

        return match ($role) {
            'tenant', 'student', 'user' => 'tenant',
            'owner', 'admin' => 'owner',
            default => $role !== '' ? $role : null,
        };
    }

    /**
     * @return array<int, string>
     */
    private function submittedList(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, fn ($item): bool => filled($item)));
        }

        if (! is_string($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (string $item): string => trim($item),
            preg_split('/[\r\n,]+/', $value) ?: []
        )));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function attributesForTable(string $table, array $attributes): array
    {
        if (! Schema::hasTable($table)) {
            return $attributes;
        }

        if (! isset($this->columnCache[$table])) {
            $this->columnCache[$table] = Schema::getColumnListing($table);
        }

        return array_intersect_key($attributes, array_flip($this->columnCache[$table]));
    }

    private function uniqueUsernameFromEmail(string $email): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9_]+/', '_', Str::before($email, '@')) ?? '');
        $base = trim($base, '_');

        if (strlen($base) < 3) {
            $base = 'boardmatch_user';
        }

        $base = substr($base, 0, 50);
        $candidate = $base;
        $suffix = 1;

        while (User::query()->where('username', $candidate)->exists()) {
            $suffix++;
            $stem = substr($base, 0, max(1, 80 - strlen((string) $suffix) - 1));
            $candidate = $stem.'_'.$suffix;
        }

        return $candidate;
    }

    private function uniqueBoardingHouseSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'boarding-house';
        $candidate = $base;
        $suffix = 1;

        while (BoardingHouse::query()->where('slug', $candidate)->exists()) {
            $suffix++;
            $candidate = $base.'-'.$suffix;
        }

        return $candidate;
    }
}
