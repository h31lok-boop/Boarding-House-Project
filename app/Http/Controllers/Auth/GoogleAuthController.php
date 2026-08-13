<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\BoardingHouseImage;
use App\Models\BoardingHousePhoto;
use App\Models\OwnerProfile;
use App\Models\TenantMatchProfile;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * @var array<string, array<int, string>>
     */
    private array $columnCache = [];

    /**
     * Redirect the user to Google's OAuth page.
     */
    public function redirect(): RedirectResponse
    {
        if (! $this->isConfigured()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in is not configured. Please contact the administrator.',
            ]);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Validate the student form before starting Google account linking.
     */
    public function register(Request $request): RedirectResponse
    {
        if (! $this->isConfigured()) {
            return redirect()->route('register')->withErrors([
                'email' => 'Google registration is not configured. Please contact the administrator.',
            ]);
        }

        $request->merge([
            'role' => 'tenant',
            'email' => strtolower(trim((string) $request->input('email'))),
            'phone' => trim((string) $request->input('phone')),
            'rental_budget' => $request->input('rental_budget') ?: $request->input('budget_min') ?: $request->input('budget_max'),
        ]);

        $validated = $request->validate([
            'role' => ['required', 'in:tenant'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'school' => ['required', 'string', 'max:255'],
            'course_year' => ['required', 'string', 'max:255'],
            'preferred_location' => ['required', 'string', 'max:255'],
            'rental_budget' => ['required', 'numeric', 'min:0', 'max:999999'],
            'budget_min' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'lifestyle_info' => ['required', 'string', 'max:3000'],
            'terms' => ['required', 'accepted'],
        ], [
            'school.required' => 'School or university is required before registering with Google.',
            'course_year.required' => 'Course or year level is required before registering with Google.',
            'preferred_location.required' => 'Preferred location is required before registering with Google.',
            'rental_budget.required' => 'Rental budget is required before registering with Google.',
            'lifestyle_info.required' => 'Lifestyle information is required before registering with Google.',
            'terms.accepted' => 'You must accept the Terms and Conditions.',
        ]);

        $validated['started_at'] = now()->timestamp;
        $this->deleteTemporaryOwnerUploads($request->session()->pull('google_registration'));
        $request->session()->put('google_registration', $validated);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Validate the complete owner application and preserve its uploads before
     * starting Google account linking. The account is only created on callback.
     */
    public function registerOwner(Request $request): RedirectResponse
    {
        if (! $this->isConfigured()) {
            return redirect()->route('register.owner')->withErrors([
                'email' => 'Google registration is not configured. Please contact the administrator.',
            ]);
        }

        $rentMin = trim((string) $request->input('rent_min', ''));
        $rentMax = trim((string) $request->input('rent_max', ''));

        $request->merge([
            'role' => 'owner',
            'email' => strtolower(trim((string) $request->input('email'))),
            'phone' => trim((string) $request->input('phone')),
            'bh_contact' => trim((string) $request->input('bh_contact')),
            'monthly_rent_range' => trim('PHP '.$rentMin.' - PHP '.$rentMax),
        ]);

        $rules = [
            'role' => ['required', 'in:owner'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'bh_name' => ['required', 'string', 'max:255'],
            'bh_address' => ['required', 'string', 'max:1000'],
            'bh_contact' => ['required', 'string', 'max:50'],
            'room_types' => ['required', 'array', 'min:1'],
            'room_types.*' => ['string', 'max:50'],
            'rent_min' => ['required', 'numeric', 'min:0', 'max:999999'],
            'rent_max' => ['required', 'numeric', 'min:0', 'max:999999', 'gte:rent_min'],
            'monthly_rent_range' => ['required', 'string', 'max:255'],
            'amenities' => ['required', 'array', 'min:1'],
            'amenities.*' => ['string', 'max:50'],
            'house_rules' => ['required', 'string', 'max:5000'],
            'business_permit_number' => ['nullable', 'string', 'max:120'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'proof_of_ownership' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'terms' => ['required', 'accepted'],
        ];

        if (Schema::hasColumn('users', 'username')) {
            $rules['username'] = ['nullable', 'string', 'alpha_dash', 'min:3', 'max:80', 'unique:users,username'];
        }

        $validated = $request->validate($rules, [
            'email.unique' => 'An account with this email already exists.',
            'room_types.required' => 'Select at least one room type.',
            'amenities.required' => 'Select at least one amenity.',
            'rent_max.gte' => 'Maximum rent must be greater than or equal to minimum rent.',
            'proof_of_ownership.required' => 'A valid business permit is required before an owner account can be reviewed.',
            'proof_of_ownership.mimes' => 'The business permit must be a JPG, PNG, or PDF.',
            'proof_of_ownership.max' => 'The business permit may not exceed 2 MB.',
            'terms.accepted' => 'You must accept the verification terms.',
        ]);

        $temporaryDirectory = 'google-owner-registrations/'.Str::uuid();

        try {
            $this->deleteTemporaryOwnerUploads($request->session()->pull('google_registration'));
            $permit = $request->file('proof_of_ownership');
            $permitPath = $permit->store($temporaryDirectory, 'local');
            $photos = [];

            foreach ($request->file('photos', []) as $photo) {
                $photos[] = [
                    'path' => $photo->store($temporaryDirectory.'/photos', 'local'),
                    'original_name' => $photo->getClientOriginalName(),
                ];
            }

            unset($validated['proof_of_ownership'], $validated['photos']);
            $validated['role'] = 'owner';
            $validated['permit_temp_path'] = $permitPath;
            $validated['permit_original_name'] = $permit->getClientOriginalName();
            $validated['photo_temp_files'] = $photos;
            $validated['temp_directory'] = $temporaryDirectory;
            $validated['started_at'] = now()->timestamp;

            $request->session()->put('google_registration', $validated);

            return Socialite::driver('google')->redirect();
        } catch (Throwable $exception) {
            Storage::disk('local')->deleteDirectory($temporaryDirectory);
            report($exception);

            return back()
                ->withInput($request->except(['password', 'password_confirmation', 'photos', 'proof_of_ownership']))
                ->withErrors(['registration' => 'The application files could not be prepared for Google registration. Please try again.']);
        }
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
        if (! $this->isConfigured()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in is not configured. Please contact the administrator.',
            ]);
        }

        $registration = request()->session()->pull('google_registration');

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            $this->deleteTemporaryOwnerUploads($registration);

            return redirect()->route($this->registrationRoute($registration))->withErrors([
                'email' => 'Google authentication was cancelled or failed. Please try again.',
            ]);
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));
        $name = trim((string) $googleUser->getName()) ?: $email;
        $googleId = (string) $googleUser->getId();
        $avatar = $googleUser->getAvatar();

        if (is_array($registration)
            && now()->timestamp - (int) ($registration['started_at'] ?? 0) > 1800) {
            $route = $this->registrationRoute($registration);
            $this->deleteTemporaryOwnerUploads($registration);

            return redirect()->route($route)->withErrors([
                'registration' => 'The Google registration session expired. Please review the form and upload the files again.',
            ]);
        }

        if ($email === '' || $googleId === '') {
            $route = is_array($registration) ? $this->registrationRoute($registration) : 'login';
            $this->deleteTemporaryOwnerUploads($registration);

            return redirect()->route($route)->withErrors([
                'email' => 'Google did not provide a valid account email. Please use another Google account.',
            ]);
        }

        if (is_array($registration)
            && strtolower((string) ($registration['email'] ?? '')) !== $email) {
            $route = $this->registrationRoute($registration);
            $this->deleteTemporaryOwnerUploads($registration);

            return redirect()->route($route)
                ->withInput($registration)
                ->withErrors([
                    'email' => 'The email in the form must match the Google account you choose.',
                ]);
        }

        $hasGoogleIdCol = Schema::hasColumn('users', 'google_id');

        // ── 1. Look up by google_id first (fastest, most reliable) ───────────
        $user = null;
        if ($hasGoogleIdCol) {
            $user = User::where('google_id', $googleId)->first();
        }

        // ── 2. Fall back to email lookup ─────────────────────────────────────
        if (! $user && $email) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $updates = [];

                if ($hasGoogleIdCol && ! $user->google_id) {
                    $updates['google_id'] = $googleId;
                }
                if (! $user->hasVerifiedEmail()) {
                    $updates['email_verified_at'] = now();
                }
                if (Schema::hasColumn('users', 'username') && blank($user->username)) {
                    $updates['username'] = $this->uniqueUsernameFromEmail($email);
                }

                if ($updates !== []) {
                    $user->forceFill($updates)->save();
                }
            }
        }

        if (! $user && ! is_array($registration)) {
            return redirect()->route('register')->withErrors([
                'email' => 'Complete the student registration fields first, then use Register with Google at the bottom of the form.',
            ]);
        }

        $registeredWithGoogle = false;

        // ── 3. Create a complete owner or student registration ───────────────
        if (! $user) {
            if (($registration['role'] ?? null) === 'owner') {
                try {
                    $user = $this->createGoogleOwnerApplication(
                        $googleId,
                        $email,
                        $avatar,
                        $hasGoogleIdCol,
                        $registration
                    );
                    event(new Registered($user));

                    return redirect()->route('login')->with(
                        'status',
                        'Your Google-linked owner application and business permit were submitted. An administrator must verify the permit before you can sign in.'
                    );
                } catch (Throwable $exception) {
                    report($exception);

                    return redirect()->route('register.owner')
                        ->withInput($registration)
                        ->withErrors([
                            'registration' => 'Owner registration could not be completed. Please upload the permit again and retry.',
                        ]);
                }
            }

            $user = DB::transaction(fn (): User => $this->createGoogleUser(
                $googleId,
                $name,
                $email,
                $avatar,
                $hasGoogleIdCol,
                $registration
            ));
            event(new Registered($user));
            $registeredWithGoogle = true;
        } elseif (is_array($registration)) {
            if (($registration['role'] ?? null) === 'owner') {
                $this->deleteTemporaryOwnerUploads($registration);

                return redirect()->route('register.owner')
                    ->withInput($registration)
                    ->withErrors([
                        'email' => 'An account with this email already exists. Sign in from the login page instead.',
                    ]);
            }

            if (! $user->isUser()) {
                return redirect()->route('login')->withErrors([
                    'email' => 'This Google email already belongs to a non-student account. Sign in from the login page instead.',
                ]);
            }

            DB::transaction(function () use ($user, $registration, $googleId, $hasGoogleIdCol): void {
                $updates = [
                    'name' => $registration['name'],
                    'phone' => $registration['phone'],
                    'phone_number' => $registration['phone'],
                    'contact_number' => $registration['phone'],
                    'email_verified_at' => now(),
                ];

                if ($hasGoogleIdCol) {
                    $updates['google_id'] = $googleId;
                }

                $user->forceFill($updates)->save();
                $this->saveTenantRegistration($user, $registration);
            });
        }

        if ($user->isUser() && ! $this->hasCompleteTenantRegistration($user)) {
            return redirect()->route('register')
                ->withInput([
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?: $user->phone_number,
                ])
                ->withErrors([
                    'registration' => 'Complete the required student profile and preference fields, then select Register with Google.',
                ]);
        }

        if ($message = $this->authenticationBlockMessage($user)) {
            return redirect()->route('login')->withErrors(['email' => $message]);
        }

        // ── 4. Log in ─────────────────────────────────────────────────────────
        Auth::guard('web')->login($user, remember: true);
        request()->session()->regenerate();

        return $registeredWithGoogle
            ? redirect()->route('user.dashboard')
                ->with('status', 'Your student account was created and linked with Google.')
            : redirect()->intended(route('dashboard', absolute: false));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }

    private function authenticationBlockMessage(User $user): ?string
    {
        $status = strtolower((string) ($user->status ?: ($user->account_status ?? 'active')));

        if (in_array($status, ['suspended', 'inactive', 'disabled', 'rejected', 'denied'], true)
            || $user->is_active === false) {
            return 'This account is not active. Please contact support.';
        }

        if ($status === 'pending') {
            return $user->isStrictOwner()
                ? 'Your owner account is waiting for administrator verification of the submitted business permit.'
                : 'This account is pending approval.';
        }

        if ($user->isStrictOwner()) {
            $profile = $user->ownerProfile;
            $hasPermit = filled($profile?->proof_of_ownership) || filled($profile?->valid_id_file);
            $isVerified = strtolower((string) $profile?->verification_status) === 'verified';

            if (! $hasPermit || ! $isVerified) {
                return 'This owner account cannot be used until an administrator verifies its business permit.';
            }
        }

        return null;
    }

    private function createGoogleUser(
        string $googleId,
        string $name,
        string $email,
        ?string $avatar,
        bool $hasGoogleIdCol,
        array $registration
    ): User {
        $hashedPwd = Hash::make(Str::random(32));

        $attrs = [
            'name' => $registration['name'] ?: $name,
            'email' => $email,
            'password' => $hashedPwd,
            'role' => 'tenant',
            'is_active' => true,
            'status' => 'active',
            'email_verified_at' => now(),
            'phone' => $registration['phone'],
            'phone_number' => $registration['phone'],
            'contact_number' => $registration['phone'],
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
        if (Schema::hasColumn('users', 'username')) {
            $attrs['username'] = $this->uniqueUsernameFromEmail($email);
        }

        $user = new User;
        $user->forceFill($attrs)->save();

        $this->saveTenantRegistration($user, $registration);

        // ── Spatie role sync ──────────────────────────────────────────────────
        if (method_exists($user, 'assignRole')) {
            $spatieRole = Role::findOrCreate('tenant', 'web');
            $user->syncRoles([$spatieRole]);
        }

        return $user;
    }

    /**
     * @param  array<string, mixed>  $registration
     */
    private function saveTenantRegistration(User $user, array $registration): void
    {
        TenantProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'school_university' => $registration['school'],
                'course_year_level' => $registration['course_year'],
                'preferred_location' => $registration['preferred_location'],
                'rental_budget' => $registration['rental_budget'],
                'lifestyle_information' => $registration['lifestyle_info'],
                'school_company' => $registration['school'],
                'course_or_position' => $registration['course_year'],
                'preferred_language' => 'english',
            ]
        );

        if (Schema::hasTable('tenant_match_profiles')) {
            TenantMatchProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'budget_min' => $registration['budget_min'] ?? $registration['rental_budget'],
                    'budget_max' => $registration['budget_max'] ?? $registration['rental_budget'],
                    'gender_preference' => 'no_preference',
                    'additional_notes' => implode("\n\n", [
                        'Preferred Location: '.$registration['preferred_location'],
                        $registration['lifestyle_info'],
                    ]),
                    'completed_at' => now(),
                ]
            );
        }
    }

    /**
     * Create a disabled owner and pending property only after Google confirms
     * the same email entered in the complete application.
     *
     * @param  array<string, mixed>  $registration
     */
    private function createGoogleOwnerApplication(
        string $googleId,
        string $email,
        ?string $avatar,
        bool $hasGoogleIdCol,
        array $registration
    ): User {
        $publicPaths = [];

        try {
            $proofPath = $this->publishTemporaryUpload(
                (string) ($registration['permit_temp_path'] ?? ''),
                'proof-of-ownership'
            );
            $publicPaths[] = $proofPath;

            $photos = [];
            foreach ($registration['photo_temp_files'] ?? [] as $photo) {
                $photoPath = $this->publishTemporaryUpload((string) ($photo['path'] ?? ''), 'boarding-house-photos');
                $publicPaths[] = $photoPath;
                $photos[] = [
                    'path' => $photoPath,
                    'original_name' => (string) ($photo['original_name'] ?? basename($photoPath)),
                ];
            }

            $user = DB::transaction(function () use (
                $googleId,
                $email,
                $avatar,
                $hasGoogleIdCol,
                $registration,
                $proofPath,
                $photos
            ): User {
                $hashedPassword = Hash::make(Str::random(40));
                $userAttributes = $this->attributesForTable('users', [
                    'name' => $registration['name'],
                    'email' => $email,
                    'phone' => $registration['phone'],
                    'phone_number' => $registration['phone'],
                    'contact_number' => $registration['phone'],
                    'role' => 'owner',
                    'password' => $hashedPassword,
                    'password_hash' => $hashedPassword,
                    'is_active' => false,
                    'status' => 'pending',
                    'account_status' => 'Pending',
                    'email_verified_at' => now(),
                ]);

                if ($hasGoogleIdCol) {
                    $userAttributes['google_id'] = $googleId;
                }
                if ($avatar && Schema::hasColumn('users', 'profile_image')) {
                    $userAttributes['profile_image'] = $avatar;
                }
                if (Schema::hasColumn('users', 'username')) {
                    $userAttributes['username'] = $registration['username'] ?? $this->uniqueUsernameFromEmail($email);
                }

                $user = new User;
                $user->forceFill($userAttributes)->save();

                $ownerProfile = OwnerProfile::create($this->attributesForTable('owner_profiles', [
                    'user_id' => $user->id,
                    'boarding_house_name' => $registration['bh_name'],
                    'boarding_house_address' => $registration['bh_address'],
                    'contact_number' => $registration['bh_contact'],
                    'room_types' => $this->listValue($registration['room_types'] ?? []),
                    'monthly_rent_range' => $registration['monthly_rent_range'],
                    'amenities' => $this->listValue($registration['amenities'] ?? []),
                    'house_rules' => $registration['house_rules'],
                    'proof_of_ownership' => $proofPath,
                    'company_name' => $registration['bh_name'],
                    'valid_id_type' => 'business_permit',
                    'valid_id_number' => $registration['business_permit_number'] ?? 'uploaded',
                    'valid_id_file' => $proofPath,
                    'verification_status' => 'pending',
                ]));

                $boardingHouse = BoardingHouse::create($this->attributesForTable('boarding_houses', [
                    'owner_id' => $user->id,
                    'user_id' => $user->id,
                    'owner_profile_id' => $ownerProfile->id,
                    'name' => $registration['bh_name'],
                    'slug' => $this->uniqueBoardingHouseSlug($registration['bh_name']),
                    'address' => $registration['bh_address'],
                    'full_address' => $registration['bh_address'],
                    'proof_of_ownership' => $proofPath,
                    'description' => implode("\n", [
                        'Room Types: '.$this->listValue($registration['room_types'] ?? []),
                        'Amenities: '.$this->listValue($registration['amenities'] ?? []),
                        'Monthly Rent: '.$registration['monthly_rent_range'],
                    ]),
                    'house_rules' => $registration['house_rules'],
                    'contact_name' => $registration['name'],
                    'contact_person' => $registration['name'],
                    'contact_phone' => $registration['bh_contact'],
                    'contact_number' => $registration['bh_contact'],
                    'price' => $registration['rent_min'],
                    'monthly_payment' => $registration['rent_min'] ?? $registration['monthly_rent_range'],
                    'is_active' => false,
                    'approval_status' => 'pending',
                    'status' => 'pending',
                ]));

                foreach ($photos as $index => $photo) {
                    BoardingHousePhoto::create($this->attributesForTable('boarding_house_photos', [
                        'owner_id' => $user->id,
                        'boarding_house_id' => $boardingHouse->id,
                        'photo_path' => $photo['path'],
                    ]));

                    BoardingHouseImage::create([
                        'boarding_house_id' => $boardingHouse->id,
                        'image_path' => $photo['path'],
                        'image_label' => Str::limit(pathinfo($photo['original_name'], PATHINFO_FILENAME), 100, ''),
                        'is_primary' => $index === 0,
                        'sort_order' => $index,
                    ]);

                    if ($index === 0) {
                        $boardingHouse->forceFill(['featured_image' => $photo['path']])->save();
                    }
                }

                if (method_exists($user, 'syncRoles') && Schema::hasTable('roles')) {
                    $user->syncRoles([Role::findOrCreate('owner', 'web')]);
                }

                return $user;
            });

            $this->deleteTemporaryOwnerUploads($registration);

            return $user;
        } catch (Throwable $exception) {
            if ($publicPaths !== []) {
                Storage::disk('public')->delete($publicPaths);
            }

            $this->deleteTemporaryOwnerUploads($registration);

            throw $exception;
        }
    }

    private function publishTemporaryUpload(string $temporaryPath, string $destinationDirectory): string
    {
        if ($temporaryPath === '' || ! Storage::disk('local')->exists($temporaryPath)) {
            throw new \RuntimeException('A required Google registration upload is missing.');
        }

        $extension = strtolower(pathinfo($temporaryPath, PATHINFO_EXTENSION));
        $destination = $destinationDirectory.'/'.Str::uuid().($extension !== '' ? '.'.$extension : '');
        $stream = Storage::disk('local')->readStream($temporaryPath);

        if ($stream === false) {
            throw new \RuntimeException('A Google registration upload could not be read.');
        }

        try {
            if (! Storage::disk('public')->put($destination, $stream)) {
                throw new \RuntimeException('A Google registration upload could not be saved.');
            }
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return $destination;
    }

    /**
     * @param  array<string, mixed>|mixed  $registration
     */
    private function deleteTemporaryOwnerUploads(mixed $registration): void
    {
        if (! is_array($registration) || ($registration['role'] ?? null) !== 'owner') {
            return;
        }

        $directory = (string) ($registration['temp_directory'] ?? '');
        if ($directory !== '' && Str::startsWith($directory, 'google-owner-registrations/')) {
            Storage::disk('local')->deleteDirectory($directory);
        }
    }

    /**
     * @param  array<string, mixed>|mixed  $registration
     */
    private function registrationRoute(mixed $registration): string
    {
        return is_array($registration) && ($registration['role'] ?? null) === 'owner'
            ? 'register.owner'
            : 'register';
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

    /**
     * @param  array<int, string>  $values
     */
    private function listValue(array $values): string
    {
        return implode(', ', array_values(array_filter($values, fn ($value) => filled($value))));
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

    private function hasCompleteTenantRegistration(User $user): bool
    {
        $profile = $user->tenantProfile;

        return filled($profile?->school_university ?: $profile?->school_company)
            && filled($profile?->course_year_level ?: $profile?->course_or_position)
            && filled($profile?->preferred_location)
            && $profile?->rental_budget !== null
            && filled($profile?->lifestyle_information);
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
}
