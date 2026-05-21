@php
    $propertyTypes = ['Boarding House', 'Apartment', 'Dormitory', 'Bed Space', 'Shared Room'];
    $amenityOptions = ['Wi-Fi', 'CCTV', 'Laundry Area', 'Study Area', 'Kitchen Access', 'Water Tank', 'Parking Area', 'Private CR'];
    $regionOptions = ['Davao Region', 'SOCCSKSARGEN', 'Northern Mindanao'];
    $provinceOptions = ['Davao del Sur', 'Davao City', 'Davao Occidental', 'Davao del Norte', 'Davao Oriental'];
    $cityOptions = ['Digos City', 'Bansalan', 'Hagonoy', 'Kiblawan', 'Magsaysay', 'Matanao', 'Padada', 'Santa Cruz', 'Sulop'];

    $documentFields = [
        'valid_id' => ['label' => 'Valid ID', 'accept' => '.jpg,.jpeg,.png,.pdf'],
        'business_permit' => ['label' => 'Business Permit', 'accept' => '.jpg,.jpeg,.png,.pdf'],
        'fire_safety_certificate' => ['label' => 'Fire Safety Certificate', 'accept' => '.jpg,.jpeg,.png,.pdf'],
        'sanitary_permit' => ['label' => 'Sanitary Permit', 'accept' => '.jpg,.jpeg,.png,.pdf'],
        'boarding_house_permit' => ['label' => 'Boarding House Permit', 'accept' => '.jpg,.jpeg,.png,.pdf'],
        'proof_of_ownership' => ['label' => 'Proof of Ownership or Lease Agreement', 'accept' => '.jpg,.jpeg,.png,.pdf'],
        'house_rules_document' => ['label' => 'House Rules Document', 'accept' => '.jpg,.jpeg,.png,.pdf,.doc,.docx'],
    ];

    $initialForm = [
        'email' => old('email', ''),
        'password' => '',
        'password_confirmation' => '',
        'full_name' => old('full_name', ''),
        'contact_number' => old('contact_number', ''),
        'boarding_house_name' => old('boarding_house_name', ''),
        'property_type' => old('property_type', 'Boarding House'),
        'description' => old('description', ''),
        'number_of_rooms' => old('number_of_rooms', ''),
        'available_slots' => old('available_slots', ''),
        'min_price' => old('min_price', ''),
        'max_price' => old('max_price', ''),
        'region' => old('region', 'Davao Region'),
        'province' => old('province', 'Davao del Sur'),
        'city' => old('city', 'Digos City'),
        'barangay' => old('barangay', ''),
        'street' => old('street', ''),
        'complete_address' => old('complete_address', ''),
        'latitude' => old('latitude', '6.74400000'),
        'longitude' => old('longitude', '125.35500000'),
        'amenities' => old('amenities', []),
        'terms_conditions' => (bool) old('terms_conditions'),
        'privacy_policy' => (bool) old('privacy_policy'),
        'osas_review_consent' => (bool) old('osas_review_consent'),
        'accuracy_confirmation' => (bool) old('accuracy_confirmation'),
        'notification_consent' => (bool) old('notification_consent'),
    ];
@endphp

<x-auth.shell
    title="Create Owner Account | DSSC Boarding House System"
    form-title="Create Owner Account"
    subtitle="Join DSSC Boarding to manage your boarding house listings, rooms, and inquiries."
    panel-headline="Owner Registration"
    panel-description="Submit your owner account, property details, location pin, and verification documents for OSAS/admin review."
    :wide="true"
>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">

    @if ($errors->any())
        <div class="auth-alert mb-4" role="alert">
            <p class="font-black">Please review the highlighted fields.</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div
        x-data="ownerMapRegistration(@js($initialForm), @js($documentFields), @js(session()->hasOldInput()))"
        x-init="init()"
        class="owner-map-registration"
    >
        <div x-show="formError" x-cloak class="auth-alert mb-4" role="alert" x-text="formError"></div>
        <div x-show="draftMessage" x-cloak class="auth-alert auth-alert-success mb-4" role="status" x-text="draftMessage"></div>

        <form method="POST" action="{{ route('register.owner.store') }}" enctype="multipart/form-data" novalidate @submit="submitForm($event)">
            @csrf
            <input type="hidden" name="role" value="owner">
            <input type="hidden" name="registration_mode" value="owner_map">

            <section class="owner-form-section">
                <div class="owner-form-section-header">
                    <span>01</span>
                    <div>
                        <h3>Account Information</h3>
                        <p>Set the credentials for secure owner access.</p>
                    </div>
                </div>

                <div class="owner-register-grid">
                    <div class="auth-field">
                        <label for="email">Email Address</label>
                        <div class="auth-input-wrap @error('email') is-invalid @enderror" :class="{ 'is-invalid': errors.email }">
                            <input id="email" name="email" type="email" x-model.trim="form.email" placeholder="owner@example.com" required autocomplete="username" aria-describedby="email-error" @blur="validateField('email')" @input="clearError('email')">
                        </div>
                        <p id="email-error" x-show="errors.email" class="auth-error" x-text="errors.email"></p>
                        @error('email') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field">
                        <label for="password">Password</label>
                        <div class="auth-input-wrap @error('password') is-invalid @enderror" :class="{ 'is-invalid': errors.password }">
                            <input id="password" name="password" type="password" x-model="form.password" placeholder="Create at least 8 characters" required autocomplete="new-password" aria-describedby="password-error password-strength" @blur="validateField('password')" @input="clearError('password')">
                            <button type="button" class="auth-password-toggle" data-auth-password-toggle="password" aria-label="Show or hide password">Show</button>
                        </div>
                        <div id="password-strength" class="owner-password-strength" :class="passwordStrengthClass()" x-text="passwordStrengthLabel()"></div>
                        <p id="password-error" x-show="errors.password" class="auth-error" x-text="errors.password"></p>
                        @error('password') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field">
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="auth-input-wrap @error('password_confirmation') is-invalid @enderror" :class="{ 'is-invalid': errors.password_confirmation }">
                            <input id="password_confirmation" name="password_confirmation" type="password" x-model="form.password_confirmation" placeholder="Re-enter your password" required autocomplete="new-password" aria-describedby="password-confirmation-error" @blur="validateField('password_confirmation')" @input="clearError('password_confirmation')">
                            <button type="button" class="auth-password-toggle" data-auth-password-toggle="password_confirmation" aria-label="Show or hide confirm password">Show</button>
                        </div>
                        <p id="password-confirmation-error" x-show="errors.password_confirmation" class="auth-error" x-text="errors.password_confirmation"></p>
                        @error('password_confirmation') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="owner-form-section">
                <div class="owner-form-section-header">
                    <span>02</span>
                    <div>
                        <h3>Personal Information</h3>
                        <p>Identify the owner or authorized property representative.</p>
                    </div>
                </div>

                <div class="owner-register-grid">
                    <div class="auth-field">
                        <label for="full_name">Full Name</label>
                        <div class="auth-input-wrap @error('full_name') is-invalid @enderror" :class="{ 'is-invalid': errors.full_name }">
                            <input id="full_name" name="full_name" type="text" x-model.trim="form.full_name" placeholder="Juan Dela Cruz" required autocomplete="name" aria-describedby="full-name-error" @blur="validateField('full_name')" @input="clearError('full_name')">
                        </div>
                        <p id="full-name-error" x-show="errors.full_name" class="auth-error" x-text="errors.full_name"></p>
                        @error('full_name') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field">
                        <label for="contact_number">Contact Number</label>
                        <div class="auth-input-wrap @error('contact_number') is-invalid @enderror" :class="{ 'is-invalid': errors.contact_number }">
                            <input id="contact_number" name="contact_number" type="tel" inputmode="tel" x-model.trim="form.contact_number" placeholder="09171234567" required autocomplete="tel" aria-describedby="contact-number-error" @blur="validateField('contact_number')" @input="clearError('contact_number')">
                        </div>
                        <p id="contact-number-error" x-show="errors.contact_number" class="auth-error" x-text="errors.contact_number"></p>
                        @error('contact_number') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field owner-upload-grid-span">
                        <label for="profile_photo">Profile Photo</label>
                        <label class="owner-upload-card @error('profile_photo') is-invalid @enderror" for="profile_photo">
                            <input id="profile_photo" name="profile_photo" type="file" accept=".jpg,.jpeg,.png,.webp" @change="onFile('profile_photo', $event)">
                            <span>
                                <strong>Upload Profile Photo</strong>
                                <small x-text="files.profile_photo || 'Optional JPG, PNG, or WEBP up to 4MB'"></small>
                            </span>
                        </label>
                        @error('profile_photo') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="owner-form-section">
                <div class="owner-form-section-header">
                    <span>03</span>
                    <div>
                        <h3>Boarding House Information</h3>
                        <p>Describe the property, room capacity, price range, and available amenities.</p>
                    </div>
                </div>

                <div class="owner-register-grid">
                    <div class="auth-field">
                        <label for="boarding_house_name">Boarding House Name</label>
                        <div class="auth-input-wrap @error('boarding_house_name') is-invalid @enderror" :class="{ 'is-invalid': errors.boarding_house_name }">
                            <input id="boarding_house_name" name="boarding_house_name" type="text" x-model.trim="form.boarding_house_name" placeholder="Digos Nest Boarding House" required @blur="validateField('boarding_house_name')" @input="clearError('boarding_house_name')">
                        </div>
                        <p x-show="errors.boarding_house_name" class="auth-error" x-text="errors.boarding_house_name"></p>
                        @error('boarding_house_name') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field">
                        <label for="property_type">Boarding House Type</label>
                        <div class="auth-input-wrap @error('property_type') is-invalid @enderror" :class="{ 'is-invalid': errors.property_type }">
                            <select id="property_type" name="property_type" x-model="form.property_type" required @change="clearError('property_type')">
                                @foreach ($propertyTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <p x-show="errors.property_type" class="auth-error" x-text="errors.property_type"></p>
                        @error('property_type') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field">
                        <label for="number_of_rooms">Number of Rooms</label>
                        <div class="auth-input-wrap @error('number_of_rooms') is-invalid @enderror" :class="{ 'is-invalid': errors.number_of_rooms }">
                            <input id="number_of_rooms" name="number_of_rooms" type="number" min="0" x-model.trim="form.number_of_rooms" placeholder="12" required @blur="validateField('number_of_rooms')" @input="clearError('number_of_rooms')">
                        </div>
                        <p x-show="errors.number_of_rooms" class="auth-error" x-text="errors.number_of_rooms"></p>
                        @error('number_of_rooms') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field">
                        <label for="available_slots">Number of Available Slots</label>
                        <div class="auth-input-wrap @error('available_slots') is-invalid @enderror" :class="{ 'is-invalid': errors.available_slots }">
                            <input id="available_slots" name="available_slots" type="number" min="0" x-model.trim="form.available_slots" placeholder="24" required @blur="validateField('available_slots')" @input="clearError('available_slots')">
                        </div>
                        <p x-show="errors.available_slots" class="auth-error" x-text="errors.available_slots"></p>
                        @error('available_slots') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field">
                        <label for="min_price">Minimum Price</label>
                        <div class="auth-input-wrap @error('min_price') is-invalid @enderror" :class="{ 'is-invalid': errors.min_price }">
                            <input id="min_price" name="min_price" type="number" min="0" step="0.01" x-model.trim="form.min_price" placeholder="3500" required @blur="validateField('min_price')" @input="clearError('min_price')">
                        </div>
                        <p x-show="errors.min_price" class="auth-error" x-text="errors.min_price"></p>
                        @error('min_price') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field">
                        <label for="max_price">Maximum Price</label>
                        <div class="auth-input-wrap @error('max_price') is-invalid @enderror" :class="{ 'is-invalid': errors.max_price }">
                            <input id="max_price" name="max_price" type="number" min="0" step="0.01" x-model.trim="form.max_price" placeholder="7200" required @blur="validateField('max_price')" @input="clearError('max_price')">
                        </div>
                        <p x-show="errors.max_price" class="auth-error" x-text="errors.max_price"></p>
                        @error('max_price') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field owner-upload-grid-span">
                        <label for="description">Description</label>
                        <div class="auth-input-wrap @error('description') is-invalid @enderror">
                            <textarea id="description" name="description" x-model.trim="form.description" rows="4" placeholder="Describe the rooms, environment, policies, and nearby landmarks."></textarea>
                        </div>
                        @error('description') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="auth-field mt-4">
                    <p class="auth-field-label">Amenities</p>
                    <div class="owner-amenity-grid">
                        @foreach ($amenityOptions as $amenity)
                            <label class="owner-chip-option">
                                <input type="checkbox" name="amenities[]" value="{{ $amenity }}" x-model="form.amenities">
                                <span>{{ $amenity }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="owner-form-section">
                <div class="owner-form-section-header">
                    <span>04</span>
                    <div>
                        <h3>Address and Map Location</h3>
                        <p>Pin the exact boarding house location. Coordinates update automatically when the marker moves.</p>
                    </div>
                </div>

                <div class="owner-register-grid">
                    <div class="auth-field">
                        <label for="region">Region</label>
                        <div class="auth-input-wrap @error('region') is-invalid @enderror" :class="{ 'is-invalid': errors.region }">
                            <select id="region" name="region" x-model="form.region" required @change="clearError('region')">
                                <option value="">Select region</option>
                                @foreach ($regionOptions as $region)
                                    <option value="{{ $region }}">{{ $region }}</option>
                                @endforeach
                            </select>
                        </div>
                        <p x-show="errors.region" class="auth-error" x-text="errors.region"></p>
                        @error('region') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field">
                        <label for="province">Province</label>
                        <div class="auth-input-wrap @error('province') is-invalid @enderror" :class="{ 'is-invalid': errors.province }">
                            <select id="province" name="province" x-model="form.province" required @change="clearError('province')">
                                <option value="">Select province</option>
                                @foreach ($provinceOptions as $province)
                                    <option value="{{ $province }}">{{ $province }}</option>
                                @endforeach
                            </select>
                        </div>
                        <p x-show="errors.province" class="auth-error" x-text="errors.province"></p>
                        @error('province') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field">
                        <label for="city">City / Municipality</label>
                        <div class="auth-input-wrap @error('city') is-invalid @enderror" :class="{ 'is-invalid': errors.city }">
                            <select id="city" name="city" x-model="form.city" required @change="clearError('city')">
                                <option value="">Select city or municipality</option>
                                @foreach ($cityOptions as $city)
                                    <option value="{{ $city }}">{{ $city }}</option>
                                @endforeach
                            </select>
                        </div>
                        <p x-show="errors.city" class="auth-error" x-text="errors.city"></p>
                        @error('city') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field">
                        <label for="barangay">Barangay</label>
                        <div class="auth-input-wrap @error('barangay') is-invalid @enderror" :class="{ 'is-invalid': errors.barangay }">
                            <input id="barangay" name="barangay" type="text" x-model.trim="form.barangay" placeholder="Zone 3" required @blur="validateField('barangay')" @input="clearError('barangay')">
                        </div>
                        <p x-show="errors.barangay" class="auth-error" x-text="errors.barangay"></p>
                        @error('barangay') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field">
                        <label for="street">Street / Purok / Zone</label>
                        <div class="auth-input-wrap @error('street') is-invalid @enderror" :class="{ 'is-invalid': errors.street }">
                            <input id="street" name="street" type="text" x-model.trim="form.street" placeholder="Purok 5" required @blur="validateField('street')" @input="clearError('street')">
                        </div>
                        <p x-show="errors.street" class="auth-error" x-text="errors.street"></p>
                        @error('street') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field">
                        <label for="complete_address">Complete Address</label>
                        <div class="auth-input-wrap @error('complete_address') is-invalid @enderror" :class="{ 'is-invalid': errors.complete_address }">
                            <input id="complete_address" name="complete_address" type="text" x-model.trim="form.complete_address" placeholder="Purok 5, Zone 3, Digos City, Davao del Sur" required @blur="validateField('complete_address')" @input="clearError('complete_address')">
                        </div>
                        <p x-show="errors.complete_address" class="auth-error" x-text="errors.complete_address"></p>
                        @error('complete_address') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="owner-map-card">
                    <div class="owner-map-card-header">
                        <div>
                            <h4>Interactive Map Location Picker</h4>
                            <p>Click the map or drag the marker to set the exact location.</p>
                        </div>
                        <p class="owner-map-status" aria-live="polite" x-text="mapStatus"></p>
                    </div>

                    <div id="ownerRegistrationMap" class="owner-map-frame" role="application" aria-label="Interactive map for selecting boarding house location"></div>
                </div>

                <div class="owner-register-grid mt-4">
                    <div class="auth-field">
                        <label for="latitude">Latitude</label>
                        <div class="auth-input-wrap @error('latitude') is-invalid @enderror" :class="{ 'is-invalid': errors.latitude }">
                            <input id="latitude" name="latitude" type="number" step="0.00000001" x-model.trim="form.latitude" required @change="syncMapFromFields()" @blur="validateField('latitude')" @input="clearError('latitude')">
                        </div>
                        <p x-show="errors.latitude" class="auth-error" x-text="errors.latitude"></p>
                        @error('latitude') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field">
                        <label for="longitude">Longitude</label>
                        <div class="auth-input-wrap @error('longitude') is-invalid @enderror" :class="{ 'is-invalid': errors.longitude }">
                            <input id="longitude" name="longitude" type="number" step="0.00000001" x-model.trim="form.longitude" required @change="syncMapFromFields()" @blur="validateField('longitude')" @input="clearError('longitude')">
                        </div>
                        <p x-show="errors.longitude" class="auth-error" x-text="errors.longitude"></p>
                        @error('longitude') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="owner-form-section">
                <div class="owner-form-section-header">
                    <span>05</span>
                    <div>
                        <h3>Verification Documents</h3>
                        <p>Upload clear files so OSAS/admin reviewers can verify the property.</p>
                    </div>
                </div>

                <div class="owner-upload-grid">
                    @foreach ($documentFields as $field => $document)
                        <div class="auth-field">
                            <label class="owner-upload-card @error($field) is-invalid @enderror" :class="{ 'is-invalid': errors.{{ $field }} }" for="{{ $field }}">
                                <input id="{{ $field }}" name="{{ $field }}" type="file" accept="{{ $document['accept'] }}" @change="onFile('{{ $field }}', $event)">
                                <span>
                                    <strong>{{ $document['label'] }} <em>Required</em></strong>
                                    <small x-text="files.{{ $field }} || 'Accepted: {{ $document['accept'] }}'"></small>
                                </span>
                            </label>
                            <p x-show="errors.{{ $field }}" class="auth-error" x-text="errors.{{ $field }}"></p>
                            @error($field) <p class="auth-error">{{ $message }}</p> @enderror
                        </div>
                    @endforeach

                    <div class="auth-field owner-upload-grid-span">
                        <label class="owner-upload-card @error('boarding_house_photos') is-invalid @enderror" :class="{ 'is-invalid': errors.boarding_house_photos }" for="boarding_house_photos">
                            <input id="boarding_house_photos" name="boarding_house_photos[]" type="file" accept=".jpg,.jpeg,.png,.webp" multiple @change="onFile('boarding_house_photos', $event, true)">
                            <span>
                                <strong>Boarding House Photo(s) <em>Required</em></strong>
                                <small x-text="files.boarding_house_photos || 'Upload at least one JPG, PNG, or WEBP photo'"></small>
                            </span>
                        </label>
                        <p x-show="errors.boarding_house_photos" class="auth-error" x-text="errors.boarding_house_photos"></p>
                        @error('boarding_house_photos') <p class="auth-error">{{ $message }}</p> @enderror
                        @error('boarding_house_photos.*') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="owner-form-section">
                <div class="owner-form-section-header">
                    <span>06</span>
                    <div>
                        <h3>Terms and Agreement</h3>
                        <p>Confirm consent and accuracy before submitting for review.</p>
                    </div>
                </div>

                <div class="owner-agreement-grid">
                    <label class="auth-check-label">
                        <input type="checkbox" name="terms_conditions" value="1" x-model="form.terms_conditions">
                        <span>I agree to the <a href="{{ url('/terms') }}" class="auth-secondary-link">Terms and Conditions</a>.</span>
                    </label>
                    <label class="auth-check-label">
                        <input type="checkbox" name="privacy_policy" value="1" x-model="form.privacy_policy">
                        <span>I agree to the <a href="{{ url('/privacy') }}" class="auth-secondary-link">Privacy Policy</a>.</span>
                    </label>
                    <label class="auth-check-label">
                        <input type="checkbox" name="osas_review_consent" value="1" x-model="form.osas_review_consent">
                        <span>I consent to OSAS/admin review of this owner registration.</span>
                    </label>
                    <label class="auth-check-label">
                        <input type="checkbox" name="accuracy_confirmation" value="1" x-model="form.accuracy_confirmation">
                        <span>I confirm that the information submitted is accurate.</span>
                    </label>
                    <label class="auth-check-label owner-upload-grid-span">
                        <input type="checkbox" name="notification_consent" value="1" x-model="form.notification_consent">
                        <span>I consent to receive registration and listing notifications.</span>
                    </label>
                </div>

                <p x-show="errors.agreements" class="auth-error mt-3" x-text="errors.agreements"></p>
                @error('terms_conditions') <p class="auth-error mt-2">{{ $message }}</p> @enderror
                @error('privacy_policy') <p class="auth-error mt-2">{{ $message }}</p> @enderror
                @error('osas_review_consent') <p class="auth-error mt-2">{{ $message }}</p> @enderror
                @error('accuracy_confirmation') <p class="auth-error mt-2">{{ $message }}</p> @enderror
            </section>

            <div class="owner-form-actions">
                <a href="{{ route('login') }}" class="landing-button landing-button-secondary">Cancel</a>
                <button type="button" class="landing-button landing-button-secondary" @click="saveDraft()">Save as Draft</button>
                <button type="submit" class="auth-primary-button owner-submit-button" :disabled="submitting || ! canSubmit()">
                    <span x-text="submitting ? 'Submitting for review...' : 'Submit for Review'"></span>
                </button>
            </div>
        </form>
    </div>

    <x-slot:scripts>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
        <script>
            function ownerMapRegistration(initialForm, documentFields, hasServerOldInput) {
                return {
                    form: initialForm,
                    errors: {},
                    files: {},
                    documentFields,
                    draftMessage: '',
                    formError: '',
                    mapStatus: 'Map ready. Default center is Digos City, Davao del Sur.',
                    map: null,
                    marker: null,
                    submitting: false,

                    init() {
                        const draft = localStorage.getItem('ownerRegistrationMapDraft');
                        if (draft && ! hasServerOldInput) {
                            try {
                                this.form = { ...this.form, ...JSON.parse(draft) };
                            } catch (error) {
                                localStorage.removeItem('ownerRegistrationMapDraft');
                            }
                        }

                        this.$nextTick(() => this.initMap());
                    },

                    clearError(field) {
                        delete this.errors[field];
                        this.formError = '';
                    },

                    setError(field, message) {
                        this.errors[field] = message;
                    },

                    hasText(field) {
                        return String(this.form[field] || '').trim().length > 0;
                    },

                    validEmailValue() {
                        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email || '');
                    },

                    validPhoneValue() {
                        return /^[0-9+()\-\s]{7,30}$/.test(String(this.form.contact_number || '').trim());
                    },

                    validNumberValue(field) {
                        const value = String(this.form[field] || '').trim();
                        return value !== '' && Number.isFinite(Number(value));
                    },

                    validPriceRange() {
                        return this.validNumberValue('min_price')
                            && this.validNumberValue('max_price')
                            && Number(this.form.max_price) >= Number(this.form.min_price);
                    },

                    requiredDocumentsSelected() {
                        return Object.keys(this.documentFields).every((field) => Boolean(this.files[field]))
                            && Boolean(this.files.boarding_house_photos);
                    },

                    requiredAgreementsAccepted() {
                        return this.form.terms_conditions
                            && this.form.privacy_policy
                            && this.form.osas_review_consent
                            && this.form.accuracy_confirmation;
                    },

                    canSubmit() {
                        return [
                            'email',
                            'password',
                            'password_confirmation',
                            'full_name',
                            'contact_number',
                            'boarding_house_name',
                            'property_type',
                            'number_of_rooms',
                            'available_slots',
                            'min_price',
                            'max_price',
                            'region',
                            'province',
                            'city',
                            'barangay',
                            'street',
                            'complete_address',
                            'latitude',
                            'longitude',
                        ].every((field) => this.hasText(field))
                            && this.validEmailValue()
                            && this.validPhoneValue()
                            && this.form.password.length >= 8
                            && this.form.password === this.form.password_confirmation
                            && this.validNumberValue('number_of_rooms')
                            && this.validNumberValue('available_slots')
                            && this.validPriceRange()
                            && this.requiredDocumentsSelected()
                            && this.requiredAgreementsAccepted();
                    },

                    validateField(field) {
                        this.clearError(field);

                        const labels = {
                            email: 'Email address',
                            password: 'Password',
                            password_confirmation: 'Confirm password',
                            full_name: 'Full name',
                            contact_number: 'Contact number',
                            boarding_house_name: 'Boarding house name',
                            property_type: 'Boarding house type',
                            number_of_rooms: 'Number of rooms',
                            available_slots: 'Number of available slots',
                            min_price: 'Minimum price',
                            max_price: 'Maximum price',
                            region: 'Region',
                            province: 'Province',
                            city: 'City / Municipality',
                            barangay: 'Barangay',
                            street: 'Street / Purok / Zone',
                            complete_address: 'Complete address',
                            latitude: 'Latitude',
                            longitude: 'Longitude',
                        };

                        if (! this.hasText(field)) {
                            this.setError(field, `${labels[field]} is required.`);
                            return false;
                        }

                        if (field === 'email' && ! this.validEmailValue()) {
                            this.setError('email', 'Enter a valid email address.');
                            return false;
                        }

                        if (field === 'password' && this.form.password.length < 8) {
                            this.setError('password', 'Password must be at least 8 characters.');
                            return false;
                        }

                        if (field === 'password_confirmation' && this.form.password !== this.form.password_confirmation) {
                            this.setError('password_confirmation', 'Confirm password must match password.');
                            return false;
                        }

                        if (field === 'contact_number' && ! this.validPhoneValue()) {
                            this.setError('contact_number', 'Contact number must be a valid phone number.');
                            return false;
                        }

                        if (['number_of_rooms', 'available_slots', 'min_price', 'max_price', 'latitude', 'longitude'].includes(field) && ! this.validNumberValue(field)) {
                            this.setError(field, `${labels[field]} must be numeric.`);
                            return false;
                        }

                        if (field === 'max_price' && this.validNumberValue('min_price') && this.validNumberValue('max_price') && Number(this.form.max_price) < Number(this.form.min_price)) {
                            this.setError('max_price', 'Maximum price must be greater than or equal to minimum price.');
                            return false;
                        }

                        return true;
                    },

                    validateAll() {
                        this.errors = {};
                        this.formError = '';

                        const fields = [
                            'email',
                            'password',
                            'password_confirmation',
                            'full_name',
                            'contact_number',
                            'boarding_house_name',
                            'property_type',
                            'number_of_rooms',
                            'available_slots',
                            'min_price',
                            'max_price',
                            'region',
                            'province',
                            'city',
                            'barangay',
                            'street',
                            'complete_address',
                            'latitude',
                            'longitude',
                        ];

                        let valid = fields.map((field) => this.validateField(field)).every(Boolean);

                        Object.entries(this.documentFields).forEach(([field, document]) => {
                            if (! this.files[field]) {
                                this.setError(field, `${document.label} is required.`);
                                valid = false;
                            }
                        });

                        if (! this.files.boarding_house_photos) {
                            this.setError('boarding_house_photos', 'At least one boarding house photo is required.');
                            valid = false;
                        }

                        if (! this.requiredAgreementsAccepted()) {
                            this.setError('agreements', 'Please accept all required agreements before submitting.');
                            valid = false;
                        }

                        if (! valid) {
                            this.formError = 'Please complete the required fields and uploads before submitting.';
                        }

                        return valid;
                    },

                    submitForm(event) {
                        if (! this.validateAll()) {
                            event.preventDefault();
                            this.$nextTick(() => {
                                const firstInvalid = document.querySelector('.is-invalid input, .is-invalid select, .is-invalid textarea, .auth-error:not([style*="display: none"])');
                                firstInvalid?.focus?.();
                            });
                            return false;
                        }

                        this.submitting = true;
                        localStorage.removeItem('ownerRegistrationMapDraft');
                        return true;
                    },

                    onFile(field, event, multiple = false) {
                        const selected = Array.from(event.target.files || []);
                        this.files[field] = multiple
                            ? selected.map((file) => file.name).join(', ')
                            : (selected[0]?.name || '');

                        if (this.files[field]) {
                            this.clearError(field);
                        }
                    },

                    saveDraft() {
                        localStorage.setItem('ownerRegistrationMapDraft', JSON.stringify(this.form));
                        this.draftMessage = 'Draft saved in this browser. Upload files must be selected again before submission.';
                        window.setTimeout(() => this.draftMessage = '', 3500);
                    },

                    initMap() {
                        if (! window.L) {
                            this.mapStatus = 'Map assets are still loading. Refresh if the map does not appear.';
                            return;
                        }

                        if (this.map) {
                            window.setTimeout(() => this.map.invalidateSize(), 50);
                            return;
                        }

                        const lat = this.safeCoordinate(this.form.latitude, 6.744);
                        const lng = this.safeCoordinate(this.form.longitude, 125.355);

                        this.map = L.map('ownerRegistrationMap').setView([lat, lng], 14);
                        this.map.getContainer().setAttribute('aria-label', 'Interactive map for selecting boarding house location');

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors',
                        }).addTo(this.map);

                        this.marker = L.marker([lat, lng], {
                            draggable: true,
                            alt: 'Selected boarding house location',
                        }).addTo(this.map);

                        this.map.on('click', (event) => this.setLocation(event.latlng.lat, event.latlng.lng));
                        this.marker.on('dragend', () => {
                            const point = this.marker.getLatLng();
                            this.setLocation(point.lat, point.lng, false);
                        });

                        window.setTimeout(() => this.map.invalidateSize(), 150);
                    },

                    safeCoordinate(value, fallback) {
                        const parsed = Number(value);
                        return Number.isFinite(parsed) ? parsed : fallback;
                    },

                    setLocation(lat, lng, moveMarker = true) {
                        const cleanLat = Number(lat);
                        const cleanLng = Number(lng);
                        if (! Number.isFinite(cleanLat) || ! Number.isFinite(cleanLng)) {
                            return;
                        }

                        this.form.latitude = cleanLat.toFixed(8);
                        this.form.longitude = cleanLng.toFixed(8);
                        this.clearError('latitude');
                        this.clearError('longitude');

                        if (moveMarker && this.marker) {
                            this.marker.setLatLng([cleanLat, cleanLng]);
                        }

                        this.reverseGeocode(cleanLat, cleanLng);
                    },

                    syncMapFromFields() {
                        const lat = Number(this.form.latitude);
                        const lng = Number(this.form.longitude);
                        if (! Number.isFinite(lat) || ! Number.isFinite(lng) || ! this.marker || ! this.map) {
                            return;
                        }

                        this.marker.setLatLng([lat, lng]);
                        this.map.setView([lat, lng], this.map.getZoom());
                    },

                    async reverseGeocode(lat, lng) {
                        this.mapStatus = 'Looking up address from selected map location...';

                        try {
                            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&addressdetails=1&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&accept-language=en`, {
                                headers: { Accept: 'application/json' },
                            });

                            if (! response.ok) {
                                throw new Error('Reverse geocoding failed.');
                            }

                            const data = await response.json();
                            const address = data.address || {};

                            this.form.region = this.normalizeRegion(address.region || address.state || this.form.region);
                            this.form.province = this.normalizeProvince(address.state_district || address.county || address.province || this.form.province);
                            this.form.city = this.normalizeCity(address.city || address.town || address.municipality || address.village || this.form.city);
                            this.form.barangay = address.suburb || address.neighbourhood || address.quarter || address.hamlet || address.village || this.form.barangay;
                            this.form.street = address.road || address.pedestrian || address.residential || this.form.street;

                            if (data.display_name) {
                                this.form.complete_address = data.display_name;
                            }

                            ['region', 'province', 'city', 'barangay', 'street', 'complete_address'].forEach((field) => this.clearError(field));
                            this.mapStatus = 'Address fields updated from the selected location.';
                        } catch (error) {
                            this.mapStatus = 'Coordinates updated. Address lookup was unavailable, so you can complete the address manually.';
                        }
                    },

                    normalizeRegion(value) {
                        if (/davao/i.test(value || '')) {
                            return 'Davao Region';
                        }

                        return value || '';
                    },

                    normalizeProvince(value) {
                        if (/davao del sur/i.test(value || '')) {
                            return 'Davao del Sur';
                        }

                        return value || '';
                    },

                    normalizeCity(value) {
                        if (/digos/i.test(value || '')) {
                            return 'Digos City';
                        }

                        return value || '';
                    },

                    passwordStrengthLabel() {
                        if (! this.form.password) {
                            return 'Password strength: not started';
                        }

                        const length = this.form.password.length >= 8;
                        const mixed = /[a-z]/.test(this.form.password) && /[A-Z]/.test(this.form.password);
                        const number = /\d/.test(this.form.password);
                        const symbol = /[^A-Za-z0-9]/.test(this.form.password);
                        const score = [length, mixed, number, symbol].filter(Boolean).length;

                        if (score >= 4) {
                            return 'Password strength: strong';
                        }

                        if (score >= 2) {
                            return 'Password strength: moderate';
                        }

                        return 'Password strength: weak';
                    },

                    passwordStrengthClass() {
                        const label = this.passwordStrengthLabel();
                        return {
                            'is-strong': label.includes('strong'),
                            'is-moderate': label.includes('moderate'),
                            'is-weak': label.includes('weak'),
                        };
                    },
                };
            }
        </script>
    </x-slot:scripts>
</x-auth.shell>
