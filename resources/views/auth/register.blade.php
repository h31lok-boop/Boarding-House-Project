@php
    $selectedRole = old('role') === 'owner' ? 'owner' : 'tenant';

    $roomTypes = ['Single Room', 'Double Room', 'Bed Space', 'Shared Room'];
    $regions = ['Davao Region', 'SOCCSKSARGEN', 'Northern Mindanao'];
    $provinces = ['Davao del Sur', 'Davao City', 'Davao Occidental', 'Davao del Norte', 'Davao Oriental'];
    $cities = ['Digos City', 'Bansalan', 'Hagonoy', 'Kiblawan', 'Magsaysay', 'Matanao', 'Padada', 'Santa Cruz', 'Sulop'];
@endphp

<x-auth.shell
    title="Create Account | DSSC Boarding House System"
    form-title="Create Account"
    subtitle="Choose a Tenant or Owner account and complete your registration details."
    panel-headline="Join DSSC Boarding"
    panel-description="Create a tenant account to find student-friendly boarding houses or register as an owner to manage listings, rooms, inquiries, and review documents."
    :wide="true"
>
    @if ($errors->has('form'))
        <div class="auth-alert mb-4">
            {{ $errors->first('form') }}
        </div>
    @endif

    <form
        method="POST"
        action="{{ $selectedRole === 'owner' ? route('register.owner.store') : route('register.tenant.store') }}"
        enctype="multipart/form-data"
        x-data="{ role: @js($selectedRole) }"
        x-bind:action="role === 'owner' ? @js(route('register.owner.store')) : @js(route('register.tenant.store'))"
        data-auth-submit
    >
        @csrf

        <input type="hidden" name="role" x-bind:value="role">
        <input type="hidden" name="registration_mode" value="quick" x-bind:disabled="role !== 'owner'">

        <div class="auth-field">
            <p class="auth-field-label">Register as</p>
            <div class="auth-role-grid">
                <button
                    type="button"
                    @class(['register-role-button', 'is-active' => $selectedRole === 'tenant'])
                    x-bind:class="{ 'is-active': role === 'tenant' }"
                    aria-pressed="{{ $selectedRole === 'tenant' ? 'true' : 'false' }}"
                    x-bind:aria-pressed="role === 'tenant'"
                    x-on:click="role = 'tenant'"
                >
                    <span class="register-role-icon">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="8" r="4" stroke-width="1.8" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 20a7 7 0 0 1 14 0" />
                        </svg>
                    </span>
                    Tenant
                </button>

                <button
                    type="button"
                    @class(['register-role-button', 'is-active' => $selectedRole === 'owner'])
                    x-bind:class="{ 'is-active': role === 'owner' }"
                    aria-pressed="{{ $selectedRole === 'owner' ? 'true' : 'false' }}"
                    x-bind:aria-pressed="role === 'owner'"
                    x-on:click="role = 'owner'"
                >
                    <span class="register-role-icon">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 11.5 12 5l8 6.5V20H4v-8.5Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 20v-5h6v5" />
                        </svg>
                    </span>
                    Owner
                </button>
            </div>
            @error('role')
                <p class="auth-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="register-form-section">
            <div class="register-grid">
                <div class="auth-field">
                    <label for="name">Full Name</label>
                    <div class="auth-input-wrap @error('name') is-invalid @enderror">
                        <span class="shrink-0 text-slate-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="12" cy="8" r="4" stroke-width="1.8" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 20a7 7 0 0 1 14 0" />
                            </svg>
                        </span>
                        <input id="name" name="name" type="text" placeholder="Enter your full name" value="{{ old('name') }}" required autocomplete="name">
                    </div>
                    @error('name')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="email">Email Address</label>
                    <div class="auth-input-wrap @error('email') is-invalid @enderror">
                        <span class="shrink-0 text-slate-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6.5h16v11H4z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 8 7 5 7-5" />
                            </svg>
                        </span>
                        <input id="email" name="email" type="email" placeholder="Enter your email" value="{{ old('email') }}" required autocomplete="username">
                    </div>
                    @error('email')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="phone">Contact Number</label>
                    <div class="auth-input-wrap @error('phone') is-invalid @enderror">
                        <span class="shrink-0 text-slate-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 5h3l1.4 4-2 1.2a12 12 0 0 0 5.4 5.4l1.2-2L19 15v3a2 2 0 0 1-2.2 2A15 15 0 0 1 4 7.2 2 2 0 0 1 6 5Z" />
                            </svg>
                        </span>
                        <input id="phone" name="phone" type="tel" inputmode="tel" placeholder="09171234567" value="{{ old('phone') }}" required autocomplete="tel">
                    </div>
                    @error('phone')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <div class="auth-input-wrap @error('password') is-invalid @enderror">
                        <span class="shrink-0 text-slate-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="5" y="10" width="14" height="10" rx="2" stroke-width="1.8" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10V8a4 4 0 1 1 8 0v2" />
                            </svg>
                        </span>
                        <input id="password" name="password" type="password" placeholder="Create at least 8 characters" required autocomplete="new-password">
                        <button type="button" class="auth-password-toggle" data-auth-password-toggle="password">Show</button>
                    </div>
                    @error('password')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="auth-input-wrap @error('password_confirmation') is-invalid @enderror">
                        <span class="shrink-0 text-slate-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="5" y="10" width="14" height="10" rx="2" stroke-width="1.8" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10V8a4 4 0 1 1 8 0v2" />
                            </svg>
                        </span>
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Re-enter your password" required autocomplete="new-password">
                        <button type="button" class="auth-password-toggle" data-auth-password-toggle="password_confirmation">Show</button>
                    </div>
                    @error('password_confirmation')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <section class="register-form-section" x-show="role === 'tenant'" x-transition x-cloak>
            <div class="register-section-heading">
                <h3>Tenant Details</h3>
                <p>Set your move-in preference and room type.</p>
            </div>

            <div class="register-grid">
                <div class="auth-field">
                    <label for="move_in_date">Preferred Move-In Date</label>
                    <div class="auth-input-wrap @error('move_in_date') is-invalid @enderror">
                        <span class="shrink-0 text-slate-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.8" />
                                <path stroke-linecap="round" stroke-width="1.8" d="M8 3v4M16 3v4M7 11h10" />
                            </svg>
                        </span>
                        <input id="move_in_date" name="move_in_date" type="date" value="{{ old('move_in_date') }}" x-bind:required="role === 'tenant'" x-bind:disabled="role !== 'tenant'">
                    </div>
                    @error('move_in_date')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="preferred_room_type">Preferred Room Type</label>
                    <div class="auth-input-wrap @error('preferred_room_type') is-invalid @enderror">
                        <span class="shrink-0 text-slate-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 10h16M6 10V7h12v3M6 10v8M18 10v8M4 18h16" />
                            </svg>
                        </span>
                        <select id="preferred_room_type" name="preferred_room_type" x-bind:required="role === 'tenant'" x-bind:disabled="role !== 'tenant'">
                            <option value="">Select room type</option>
                            @foreach ($roomTypes as $roomType)
                                <option value="{{ $roomType }}" @selected(old('preferred_room_type') === $roomType)>{{ $roomType }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('preferred_room_type')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field register-grid-span">
                    <label class="owner-upload-card @error('profile_photo') is-invalid @enderror" for="profile_photo">
                        <input id="profile_photo" name="profile_photo" type="file" accept="image/png,image/jpeg,image/webp" x-bind:disabled="role !== 'tenant'">
                        <span>
                            <strong>Profile Picture</strong>
                            <small>Optional JPG, PNG, or WebP upload.</small>
                        </span>
                    </label>
                    @error('profile_photo')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section class="register-form-section" x-show="role === 'owner'" x-transition x-cloak>
            <div class="register-section-heading">
                <h3>Boarding House Details</h3>
                <p>Provide property information and documents for OSAS/admin review.</p>
            </div>

            <div class="register-grid">
                <div class="auth-field">
                    <label for="boarding_house_name">Boarding House Name</label>
                    <div class="auth-input-wrap @error('boarding_house_name') is-invalid @enderror">
                        <span class="shrink-0 text-slate-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 11.5 12 5l8 6.5V20H4v-8.5Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 20v-5h6v5" />
                            </svg>
                        </span>
                        <input id="boarding_house_name" name="boarding_house_name" type="text" placeholder="Enter boarding house name" value="{{ old('boarding_house_name') }}" x-bind:required="role === 'owner'" x-bind:disabled="role !== 'owner'">
                    </div>
                    @error('boarding_house_name')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="region">Region</label>
                    <div class="auth-input-wrap @error('region') is-invalid @enderror">
                        <select id="region" name="region" x-bind:required="role === 'owner'" x-bind:disabled="role !== 'owner'">
                            <option value="">Select region</option>
                            @foreach ($regions as $region)
                                <option value="{{ $region }}" @selected(old('region') === $region)>{{ $region }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('region')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="province">Province</label>
                    <div class="auth-input-wrap @error('province') is-invalid @enderror">
                        <select id="province" name="province" x-bind:required="role === 'owner'" x-bind:disabled="role !== 'owner'">
                            <option value="">Select province</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province }}" @selected(old('province') === $province)>{{ $province }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('province')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="city">City / Municipality</label>
                    <div class="auth-input-wrap @error('city') is-invalid @enderror">
                        <select id="city" name="city" x-bind:required="role === 'owner'" x-bind:disabled="role !== 'owner'">
                            <option value="">Select city or municipality</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city }}" @selected(old('city') === $city)>{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('city')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field register-grid-span">
                    <label for="boarding_house_address">Boarding House Address</label>
                    <div class="auth-input-wrap @error('boarding_house_address') is-invalid @enderror">
                        <span class="shrink-0 text-slate-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s7-5.1 7-11a7 7 0 1 0-14 0c0 5.9 7 11 7 11Z" />
                                <circle cx="12" cy="10" r="2.5" stroke-width="1.8" />
                            </svg>
                        </span>
                        <textarea id="boarding_house_address" name="boarding_house_address" rows="3" placeholder="Street, purok/zone, barangay, city, province" x-bind:required="role === 'owner'" x-bind:disabled="role !== 'owner'">{{ old('boarding_house_address') }}</textarea>
                    </div>
                    @error('boarding_house_address')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="register-section-heading mt-5">
                <h3>Verification Uploads</h3>
                <p>Accepted files: JPG, PNG, WebP, PDF, DOC, or DOCX depending on the field.</p>
            </div>

            <div class="owner-upload-grid">
                <div class="auth-field">
                    <label class="owner-upload-card @error('boarding_house_photo') is-invalid @enderror" for="boarding_house_photo">
                        <input id="boarding_house_photo" name="boarding_house_photo" type="file" accept="image/png,image/jpeg,image/webp" x-bind:required="role === 'owner'" x-bind:disabled="role !== 'owner'">
                        <span>
                            <strong>Upload Boarding House Photo <em>Required</em></strong>
                            <small>Front or exterior photo of the property.</small>
                        </span>
                    </label>
                    @error('boarding_house_photo')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field">
                    <label class="owner-upload-card @error('owner_id_document') is-invalid @enderror" for="owner_id_document">
                        <input id="owner_id_document" name="owner_id_document" type="file" accept="image/png,image/jpeg,application/pdf" x-bind:required="role === 'owner'" x-bind:disabled="role !== 'owner'">
                        <span>
                            <strong>Upload Owner ID <em>Required</em></strong>
                            <small>Government ID for identity verification.</small>
                        </span>
                    </label>
                    @error('owner_id_document')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="auth-field owner-upload-grid-span">
                    <label class="owner-upload-card @error('supporting_documents') is-invalid @enderror" for="supporting_documents">
                        <input id="supporting_documents" name="supporting_documents[]" type="file" multiple accept="image/png,image/jpeg,application/pdf,.doc,.docx" x-bind:required="role === 'owner'" x-bind:disabled="role !== 'owner'">
                        <span>
                            <strong>Upload Owner ID and Other Required Documents <em>Required</em></strong>
                            <small>Business permit, lease agreement, safety certificate, or other proof documents.</small>
                        </span>
                    </label>
                    @error('supporting_documents')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                    @error('supporting_documents.*')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <label class="auth-check-label mt-5 text-sm font-semibold text-slate-600">
            <input type="checkbox" name="terms" value="1" @checked(old('terms')) required>
            <span>I agree to the <a href="{{ url('/terms') }}" class="auth-secondary-link">Terms</a> and <a href="{{ url('/privacy') }}" class="auth-secondary-link">Privacy Policy</a>.</span>
        </label>
        @error('terms')
            <p class="auth-error mt-2">{{ $message }}</p>
        @enderror

        <button
            type="submit"
            class="auth-primary-button mt-5"
            data-auth-submit-button
            x-bind:data-loading-text="role === 'owner' ? 'Registering owner...' : 'Registering tenant...'"
        >
            <span x-text="role === 'owner' ? 'Register as Owner' : 'Register as Tenant'">{{ $selectedRole === 'owner' ? 'Register as Owner' : 'Register as Tenant' }}</span>
        </button>
    </form>

    <div class="auth-footer-links">
        <p>Already have an account? <a class="auth-secondary-link" href="{{ route('login') }}">Sign In</a></p>
        <p><a class="auth-secondary-link auth-small-link" href="{{ url('/terms') }}">Terms</a> · <a class="auth-secondary-link auth-small-link" href="{{ url('/privacy') }}">Privacy</a></p>
        <p><a class="auth-secondary-link" href="{{ url('/') }}">Back to Homepage</a></p>
    </div>
</x-auth.shell>
