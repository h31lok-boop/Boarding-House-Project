<!DOCTYPE html>
<html lang="en" data-theme="light" data-theme-mode="light-only">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Registration — BoardMatch</title>
    <link rel="icon" type="image/png" href="{{ asset('images/boardmatch-final-logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html { color-scheme: light; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: #f4f7fb; color: #172033; font-family: Manrope, sans-serif; }
        .page { width: min(1180px, calc(100% - 32px)); margin: 0 auto; padding: 32px 0 56px; }
        .topbar { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 24px; }
        .brand { display: inline-flex; align-items: center; gap: 10px; color: #172033; text-decoration: none; font-weight: 800; }
        .brand img { width: 42px; height: 42px; border-radius: 12px; box-shadow: 0 8px 20px rgba(37,99,235,.2); }
        .back { color: #475569; text-decoration: none; font-size: 13px; font-weight: 700; }
        .shell { display: grid; grid-template-columns: minmax(0, 1fr) 340px; overflow: hidden; border: 1px solid #dbe4ef; border-radius: 24px; background: #fff; box-shadow: 0 24px 65px rgba(15,23,42,.09); }
        .form-side { padding: 34px; }
        .aside { position: relative; padding: 34px; border-left: 1px solid #dbeafe; background: linear-gradient(155deg, #eff6ff 0%, #e7efff 62%, #ecfdf5 130%); color: #172033; }
        .aside-inner { position: sticky; top: 24px; }
        .eyebrow { color: #2563eb; font-size: 11px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
        h1 { margin: 8px 0 8px; font-size: clamp(26px, 4vw, 38px); line-height: 1.12; letter-spacing: -.035em; }
        .lead { margin: 0 0 28px; color: #64748b; font-size: 14px; line-height: 1.65; }
        .alert { margin-bottom: 22px; border: 1px solid #fecaca; border-radius: 14px; background: #fff1f2; padding: 14px 16px; color: #b91c1c; font-size: 13px; }
        .alert ul { margin: 0; padding-left: 18px; }
        .section { margin-top: 26px; padding-top: 24px; border-top: 1px solid #e2e8f0; }
        .section:first-of-type { margin-top: 0; padding-top: 0; border-top: 0; }
        .section-title { margin: 0 0 5px; font-size: 16px; font-weight: 800; }
        .section-copy { margin: 0 0 16px; color: #64748b; font-size: 12px; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 15px; }
        .wide { grid-column: 1 / -1; }
        label.field { display: block; color: #334155; font-size: 12px; font-weight: 700; }
        label.field span { color: #dc2626; }
        input, textarea { width: 100%; margin-top: 6px; border: 1px solid #cbd5e1; border-radius: 11px; background: #fff; padding: 11px 12px; color: #172033; font: inherit; font-size: 13px; outline: none; }
        input:focus, textarea:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
        textarea { min-height: 98px; resize: vertical; }
        .choices { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 8px; margin-top: 8px; }
        .choice { display: flex; align-items: center; gap: 7px; border: 1px solid #dbe4ef; border-radius: 10px; padding: 9px; color: #475569; font-size: 12px; font-weight: 600; }
        .choice input { width: 15px; margin: 0; accent-color: #2563eb; }
        .upload-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-top: 16px; }
        .upload { position: relative; display: flex; min-width: 0; min-height: 150px; align-items: center; justify-content: center; overflow: hidden; border: 2px dashed #93b4e8; border-radius: 16px; background: #f6f9ff; padding: 22px; text-align: center; cursor: pointer; transition: border-color .2s, background-color .2s, transform .2s; }
        .upload:hover { border-color: #2563eb; background: #eff6ff; }
        .upload:focus-within { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
        .upload input[type='file'] { position: absolute; z-index: 2; inset: 0; display: block; width: 100%; height: 100%; margin: 0; padding: 0; border: 0; opacity: 0; cursor: pointer; }
        .upload-content { position: relative; z-index: 1; display: flex; min-width: 0; flex-direction: column; align-items: center; pointer-events: none; }
        .upload-icon { display: grid; width: 42px; height: 42px; margin-bottom: 11px; place-items: center; border-radius: 12px; background: #dbeafe; color: #1d4ed8; }
        .upload-icon svg { width: 21px; height: 21px; }
        .upload strong { display: block; color: #1e3a8a; font-size: 14px; }
        .upload small { display: block; margin-top: 5px; color: #64748b; line-height: 1.45; }
        .file-name { display: block; max-width: 100%; margin-top: 10px; overflow: hidden; color: #0f766e; font-size: 11px; font-weight: 800; text-overflow: ellipsis; white-space: nowrap; }
        .terms { display: flex; align-items: flex-start; gap: 9px; margin: 22px 0; color: #64748b; font-size: 12px; }
        .terms input { width: 16px; margin: 2px 0 0; accent-color: #2563eb; }
        .submit { width: 100%; border: 0; border-radius: 12px; background: #2563eb; padding: 13px 18px; color: #fff; font: inherit; font-size: 14px; font-weight: 800; cursor: pointer; box-shadow: 0 12px 26px rgba(37,99,235,.24); }
        .submit:hover { background: #1d4ed8; }
        .method-note { display: flex; gap: 10px; margin: -10px 0 26px; border: 1px solid #bfdbfe; border-radius: 13px; background: #eff6ff; padding: 12px 14px; color: #334155; font-size: 12px; line-height: 1.55; }
        .method-note svg { flex: 0 0 auto; width: 18px; height: 18px; margin-top: 1px; color: #2563eb; }
        .registration-methods { margin-top: 22px; border: 1px solid #dbe4ef; border-radius: 16px; background: #f8fafc; padding: 16px; }
        .registration-methods h2 { margin: 0 0 4px; font-size: 14px; }
        .registration-methods > p { margin: 0 0 14px; color: #64748b; font-size: 11px; line-height: 1.55; }
        .method-divider { display: flex; align-items: center; gap: 12px; margin: 13px 0; color: #94a3b8; font-size: 10px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .method-divider::before, .method-divider::after { height: 1px; flex: 1; background: #dbe4ef; content: ''; }
        .google-submit { display: flex; width: 100%; align-items: center; justify-content: center; gap: 10px; border: 1px solid #cbd5e1; border-radius: 12px; background: #fff; padding: 12px 18px; color: #172033; font: inherit; font-size: 14px; font-weight: 800; cursor: pointer; }
        .google-submit:hover { border-color: #94a3b8; background: #f8fafc; }
        .google-submit svg { width: 19px; height: 19px; flex: 0 0 auto; }
        .google-submit:disabled { cursor: not-allowed; opacity: .55; }
        .method-footnote { margin: 11px 2px 0; color: #64748b; font-size: 10px; line-height: 1.5; text-align: center; }
        .aside h2 { margin: 48px 0 12px; font-size: 26px; line-height: 1.2; }
        .aside-inner > p { color: #52627a; font-size: 13px; line-height: 1.65; }
        .steps { display: grid; gap: 18px; margin-top: 30px; }
        .step { display: grid; grid-template-columns: 34px 1fr; gap: 12px; }
        .step b { display: flex; width: 34px; height: 34px; align-items: center; justify-content: center; border: 1px solid #bfdbfe; border-radius: 50%; background: rgba(255,255,255,.8); color: #1d4ed8; }
        .step strong { display: block; font-size: 13px; }
        .step span { display: block; margin-top: 3px; color: #64748b; font-size: 11px; line-height: 1.5; }
        .notice { margin-top: 30px; border: 1px solid #bfdbfe; border-radius: 14px; background: rgba(255,255,255,.72); padding: 15px; color: #334155; font-size: 12px; line-height: 1.6; }
        @media (max-width: 900px) { .shell { grid-template-columns: 1fr; } .aside { order: -1; } .aside-inner { position: static; } .aside h2 { margin-top: 4px; } .steps { grid-template-columns: repeat(3,1fr); } .step { grid-template-columns: 1fr; } }
        @media (max-width: 620px) { .page { width: min(100% - 20px, 1180px); padding-top: 18px; } .topbar { align-items: flex-start; } .back { max-width: 150px; text-align: right; line-height: 1.4; } .form-side, .aside { padding: 24px 18px; } .grid, .choices, .steps, .upload-grid { grid-template-columns: 1fr; } .wide { grid-column: auto; } }
    </style>
</head>
<body>
<main class="page">
    <div class="topbar">
        <a class="brand" href="{{ url('/') }}"><img src="{{ asset('images/boardmatch-final-logo.png') }}" alt=""><span>BoardMatch</span></a>
        <a class="back" href="{{ route('register') }}">Register as a student instead</a>
    </div>

    <div class="shell">
        <section class="form-side">
            <p class="eyebrow">Boarding house owner application</p>
            <h1>Register your property</h1>
            <p class="lead">Submit your account, property information, and business permit. Your account will remain unavailable until an administrator verifies the permit.</p>
            <div class="method-note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01" stroke-linecap="round"/></svg>
                <span>Complete every required field and upload your business permit first. At the bottom, choose either email and password or a Google-linked owner application.</span>
            </div>

            @if ($errors->any())
                <div class="alert"><strong>Please review your application.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <form id="ownerRegistrationForm" method="POST" action="{{ route('register.owner.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="role" value="owner">

                <div class="section">
                    <h2 class="section-title">Owner account</h2>
                    <p class="section-copy">Use contact details the administrator can verify.</p>
                    <div class="grid">
                        <label class="field">Full name <span>*</span><input name="name" value="{{ old('name') }}" required autocomplete="name"></label>
                        <label class="field">Username<input name="username" value="{{ old('username') }}" autocomplete="username" placeholder="Optional"></label>
                        <label class="field">Email address <span>*</span><input name="email" type="email" value="{{ old('email') }}" required autocomplete="email"></label>
                        <label class="field">Mobile number <span>*</span><input name="phone" value="{{ old('phone') }}" required autocomplete="tel"></label>
                        <label class="field">Password <small>(email registration only)</small><input id="ownerPassword" name="password" type="password" autocomplete="new-password"></label>
                        <label class="field">Confirm password <small>(email registration only)</small><input id="ownerPasswordConfirmation" name="password_confirmation" type="password" autocomplete="new-password"></label>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">Boarding house information</h2>
                    <p class="section-copy">This creates a pending listing connected to your owner application.</p>
                    <div class="grid">
                        <label class="field">Boarding house name <span>*</span><input name="bh_name" value="{{ old('bh_name') }}" required></label>
                        <label class="field">Property contact number <span>*</span><input name="bh_contact" value="{{ old('bh_contact', old('phone')) }}" required></label>
                        <label class="field wide">Complete address <span>*</span><textarea name="bh_address" required>{{ old('bh_address') }}</textarea></label>
                        <label class="field">Minimum monthly rent <span>*</span><input name="rent_min" type="number" min="0" value="{{ old('rent_min') }}" required></label>
                        <label class="field">Maximum monthly rent <span>*</span><input name="rent_max" type="number" min="0" value="{{ old('rent_max') }}" required></label>
                        <div class="field wide">Room types <span>*</span><div class="choices">
                            @foreach (['Solo Room' => 'Solo room', 'Shared Room' => 'Shared room', 'Bedspace' => 'Bedspace'] as $value => $label)
                                <label class="choice"><input type="checkbox" name="room_types[]" value="{{ $value }}" @checked(in_array($value, old('room_types', []), true))>{{ $label }}</label>
                            @endforeach
                        </div></div>
                        <div class="field wide">Amenities <span>*</span><div class="choices">
                            @foreach (['WiFi', 'Kitchen', 'Laundry', 'Study Area', 'Parking', 'CCTV'] as $amenity)
                                <label class="choice"><input type="checkbox" name="amenities[]" value="{{ $amenity }}" @checked(in_array($amenity, old('amenities', []), true))>{{ $amenity }}</label>
                            @endforeach
                        </div></div>
                        <label class="field wide">House rules <span>*</span><textarea name="house_rules" required placeholder="State the important rules tenants must follow.">{{ old('house_rules') }}</textarea></label>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">Business permit verification</h2>
                    <p class="section-copy">Required. Applications without an uploaded permit cannot be approved or used.</p>
                    <label class="field">Business permit number<input name="business_permit_number" value="{{ old('business_permit_number') }}" placeholder="Optional reference number"></label>
                    <div class="upload-grid">
                        <label class="upload">
                            <input id="permit" name="proof_of_ownership" type="file" accept=".jpg,.jpeg,.png,.pdf" required>
                            <span class="upload-content">
                                <span class="upload-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <strong>Upload business permit</strong>
                                <small>Required · JPG, PNG, or PDF · maximum 2 MB</small>
                                <span class="file-name" id="permit-name">No permit selected</span>
                            </span>
                        </label>

                        <label class="upload">
                            <input id="photos" name="photos[]" type="file" accept=".jpg,.jpeg,.png,.webp" multiple>
                            <span class="upload-content">
                                <span class="upload-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m4 17 4.5-4 3.5 3 2.5-2 5.5 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <strong>Add property photos</strong>
                                <small>Optional · up to 10 photos · maximum 5 MB each</small>
                                <span class="file-name" id="photo-count">No property photos selected</span>
                            </span>
                        </label>
                    </div>
                </div>

                <label class="terms"><input name="terms" type="checkbox" value="1" required><span>I confirm that the information and uploaded permit are genuine, and I agree to administrator verification before account access is granted.</span></label>
                @php $googleConfigured = filled(config('services.google.client_id')); @endphp
                <div class="registration-methods">
                    <h2>Choose how to create the owner account</h2>
                    <p>Both methods submit the same property and permit application for administrator review.</p>
                    <button class="submit" id="ownerEmailSubmit" type="submit" name="registration_method" value="email">Submit with email and password</button>
                    <div class="method-divider">or</div>
                    @if ($googleConfigured)
                        <button class="google-submit" id="ownerGoogleSubmit" type="submit" name="registration_method" value="google" data-google-registration="true" formaction="{{ route('register.owner.google') }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M21.6 12.23c0-.71-.06-1.4-.18-2.06H12v3.9h5.38a4.6 4.6 0 0 1-2 3.02v2.53h3.24c1.9-1.75 2.98-4.33 2.98-7.39Z"/><path fill="#34A853" d="M12 22c2.7 0 4.97-.9 6.62-2.38l-3.24-2.53c-.9.6-2.05.96-3.38.96-2.61 0-4.82-1.76-5.61-4.13H3.04v2.61A10 10 0 0 0 12 22Z"/><path fill="#FBBC05" d="M6.39 13.92A6.02 6.02 0 0 1 6.08 12c0-.67.11-1.32.31-1.92V7.47H3.04A10 10 0 0 0 2 12c0 1.61.38 3.14 1.04 4.53l3.35-2.61Z"/><path fill="#EA4335" d="M12 5.95c1.47 0 2.78.5 3.81 1.49l2.88-2.88A9.66 9.66 0 0 0 12 2a10 10 0 0 0-8.96 5.47l3.35 2.61C7.18 7.71 9.39 5.95 12 5.95Z"/></svg>
                            Submit and link with Google
                        </button>
                    @else
                        <button class="google-submit" type="button" disabled>Google registration is unavailable</button>
                    @endif
                    <div class="method-footnote">For Google registration, the Google email you choose must match the email entered above. Approval is still required.</div>
                </div>
            </form>
        </section>

        <aside class="aside">
            <div class="aside-inner">
                <p class="eyebrow" style="color:#2563eb">Verification process</p>
                <h2>Your account opens only after approval.</h2>
                <p>This protects students from unverified listings and keeps property ownership records accountable.</p>
                <div class="steps">
                    <div class="step"><b>1</b><div><strong>Submit application</strong><span>Provide account, property, and permit information.</span></div></div>
                    <div class="step"><b>2</b><div><strong>Admin reviews permit</strong><span>The uploaded business permit must be present and valid.</span></div></div>
                    <div class="step"><b>3</b><div><strong>Owner access is enabled</strong><span>After verification, you can sign in and manage the property.</span></div></div>
                </div>
                <div class="notice"><strong>Important:</strong> submitting the form does not sign you in. Pending and rejected owner accounts cannot access the owner workspace.</div>
            </div>
        </aside>
    </div>
</main>
<script>
    document.getElementById('permit')?.addEventListener('change', function () {
        document.getElementById('permit-name').textContent = this.files[0]?.name || 'No permit selected';
    });
    document.getElementById('photos')?.addEventListener('change', function () {
        document.getElementById('photo-count').textContent = this.files.length ? `${this.files.length} property photo(s) selected` : 'No property photos selected';
    });

    document.getElementById('ownerRegistrationForm')?.addEventListener('submit', function (event) {
        const submitter = event.submitter;
        const googleRegistration = submitter?.dataset.googleRegistration === 'true';
        const password = document.getElementById('ownerPassword');
        const confirmation = document.getElementById('ownerPasswordConfirmation');

        password?.setCustomValidity('');
        confirmation?.setCustomValidity('');

        if (!googleRegistration) {
            if (!password?.value) {
                event.preventDefault();
                password?.setCustomValidity('Password is required for email registration.');
                password?.reportValidity();
                return;
            }

            if (!confirmation?.value) {
                event.preventDefault();
                confirmation?.setCustomValidity('Please confirm your password.');
                confirmation?.reportValidity();
                return;
            }
        }

        if (submitter) {
            submitter.setAttribute('aria-disabled', 'true');
            submitter.textContent = googleRegistration ? 'Connecting to Google…' : 'Submitting application…';
        }
    });
</script>
</body>
</html>
