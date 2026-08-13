{{-- resources/views/auth/register.blade.php --}}
<!DOCTYPE html>
<html lang="en" data-theme="light" data-theme-mode="light-only">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — BoardMatch</title>
    <link rel="icon" type="image/png" href="{{ asset('images/boardmatch-final-logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { color-scheme: light; --surface: #fff; --surface-2: #f8fafc; --bg: #f4f7fb; --border: #dbe4ef; --text: #172033; --muted: #64748b; --brand-500: #2563eb; --brand-600: #1d4ed8; }

        body {
            font-family: 'Manrope', 'Segoe UI', sans-serif;
            background: #f4f7fb;
            color: #172033;
            min-height: 100vh;
        }

        .auth-page { width: min(1180px, calc(100% - 32px)); margin: 0 auto; padding: 32px 0 56px; }
        .auth-topbar { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 24px; }
        .auth-brand { display: inline-flex; align-items: center; gap: 10px; color: #172033; text-decoration: none; font-weight: 800; }
        .auth-brand img { width: 42px; height: 42px; border-radius: 12px; box-shadow: 0 8px 20px rgba(37,99,235,.2); }
        .auth-actions { display: flex; align-items: center; gap: 10px; }
        .auth-action { display: inline-flex; min-height: 40px; align-items: center; justify-content: center; border: 1px solid #dbe4ef; border-radius: 11px; background: #fff; padding: 0 14px; color: #475569; text-decoration: none; font-size: 12px; font-weight: 800; transition: .2s; }
        .auth-action:hover { border-color: #93b4e8; color: #1d4ed8; }

        .reg-card {
            margin: 0 auto;
        }

        .reg-card {
            width: 100%; max-width: 480px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: 0 24px 65px rgba(15,23,42,.09);
            overflow: hidden;
        }

        .reg-inner { padding: 32px 28px 28px; }
        @media (max-width: 520px) { .reg-inner { padding: 24px 18px 22px; } }

        /* ── Logo ───────────────────────────────────────────────── */
        .reg-logo { display:flex; align-items:center; justify-content:center; gap:10px; margin-bottom:6px; }
        .reg-logo-icon {
            height:40px; width:40px; border-radius:12px;
            overflow:hidden;
            box-shadow: 0 6px 16px rgba(37,99,235,.28);
        }
        .reg-logo-icon img { width:100%; height:100%; object-fit:cover; }
        .reg-logo-name { font-size:18px; font-weight:800; color:var(--text); }

        /* ── Headings ───────────────────────────────────────────── */
        .reg-head { text-align:center; margin-bottom:22px; }
        .reg-head h1 { font-size:20px; font-weight:700; color:var(--text); margin-bottom:4px; }
        .reg-head p  { font-size:13px; color:var(--muted); line-height:1.5; }

        /* ── Section label ──────────────────────────────────────── */
        .section-label {
            font-size:10.5px; font-weight:700; letter-spacing:.1em; text-transform:uppercase;
            color:var(--muted); margin:18px 0 10px; display:flex; align-items:center; gap:8px;
        }
        .section-label::after { content:''; flex:1; height:1px; background:var(--border); }

        /* ── Fields ─────────────────────────────────────────────── */
        .field { margin-bottom:12px; }
        .field-label { display:block; font-size:12px; font-weight:600; color:var(--text); margin-bottom:5px; }
        .field-label .req { color:#f87171; margin-left:2px; }
        .input-box {
            display:flex; align-items:center; gap:9px;
            border:1.5px solid var(--border); border-radius:12px;
            padding:11px 13px; background:var(--surface);
            transition:border-color .2s, box-shadow .2s;
        }
        .input-box:focus-within { border-color:var(--brand-500); box-shadow:0 0 0 3px rgba(37,99,235,.14); }
        .input-box.is-error  { border-color:#f87171; }
        .input-box.is-error:focus-within { box-shadow:0 0 0 3px rgba(248,113,113,.16); }
        .input-box.is-valid  { border-color:#34d399; }
        .input-icon { color:#9ca3af; flex-shrink:0; width:16px; height:16px; }
        .input-box input,
        .input-box select,
        .input-box textarea {
            border:none; outline:none; width:100%; font-size:13.5px;
            font-family:inherit; color:var(--text); background:transparent; resize:none;
        }
        .input-box input::placeholder,
        .input-box textarea::placeholder { color:#9ca3af; }
        .input-box select { cursor:pointer; }
        .eye-btn { background:none; border:none; cursor:pointer; color:#9ca3af; padding:0; display:flex; align-items:center; flex-shrink:0; transition:color .15s; }
        .eye-btn:hover { color:#6b7280; }
        .eye-btn.is-visible { color:var(--brand-600); }
        .eye-btn svg { width:16px; height:16px; }
        .field-hint { font-size:11.5px; color:var(--muted); margin-top:4px; line-height:1.4; }
        .field-error { font-size:11.5px; color:#dc2626; margin-top:5px; display:flex; align-items:flex-start; gap:4px; line-height:1.45; }
        .field-error svg { width:12px; height:12px; flex-shrink:0; margin-top:1px; }

        /* ── Role select card ──────────────────────────────────── */
        .role-wrap { position:relative; }
        .role-select {
            width:100%; appearance:none; -webkit-appearance:none;
            border:1.5px solid var(--border); border-radius:12px;
            padding:12px 40px 12px 14px; background:var(--surface);
            font-size:13.5px; font-family:inherit; color:var(--text); font-weight:600;
            cursor:pointer; outline:none; transition:border-color .2s, box-shadow .2s;
        }
        .role-select:focus { border-color:var(--brand-500); box-shadow:0 0 0 3px rgba(37,99,235,.14); }
        .role-caret {
            position:absolute; right:14px; top:50%; transform:translateY(-50%);
            pointer-events:none; color:#9ca3af;
        }
        .role-caret svg { width:16px; height:16px; }

        /* ── Budget / Rent range row ────────────────────────────── */
        .range-row { display:grid; grid-template-columns:1fr auto 1fr; gap:8px; align-items:center; }
        .range-sep { font-size:12px; color:var(--muted); text-align:center; padding-top:22px; }

        /* ── Checkboxes grid ────────────────────────────────────── */
        .check-grid { display:grid; grid-template-columns:1fr 1fr; gap:6px; }
        @media (max-width:360px) { .check-grid { grid-template-columns:1fr; } }
        .check-item {
            display:flex; align-items:center; gap:8px;
            padding:9px 11px; border:1.5px solid var(--border); border-radius:10px;
            cursor:pointer; transition:border-color .15s, background .15s;
            font-size:12.5px; color:var(--text); font-weight:500;
        }
        .check-item input[type="checkbox"] { width:14px; height:14px; accent-color:var(--brand-500); flex-shrink:0; cursor:pointer; }
        .check-item:has(input:checked) { border-color:var(--brand-500); background:rgba(37,99,235,.06); }
        .check-item svg { width:14px; height:14px; color:#9ca3af; flex-shrink:0; }

        /* ── File upload ────────────────────────────────────────── */
        .file-zone {
            border:2px dashed var(--border); border-radius:12px;
            padding:18px 16px; text-align:center; cursor:pointer;
            transition:border-color .2s, background .2s; position:relative;
            background:var(--surface);
        }
        .file-zone:hover { border-color:var(--brand-500); background:rgba(37,99,235,.04); }
        .file-zone input[type="file"] { position:absolute; inset:0; opacity:0; width:100%; height:100%; cursor:pointer; }
        .file-zone-icon { width:28px; height:28px; color:#9ca3af; margin:0 auto 6px; }
        .file-zone-title { font-size:13px; font-weight:600; color:var(--text); }
        .file-zone-sub { font-size:11.5px; color:var(--muted); margin-top:2px; }
        .file-preview { margin-top:8px; display:flex; flex-wrap:wrap; gap:6px; justify-content:center; }
        .file-chip {
            display:inline-flex; align-items:center; gap:5px;
            background:rgba(37,99,235,.1); border-radius:6px;
            padding:3px 8px; font-size:11px; color:#1d4ed8; font-weight:600;
        }
        .file-chip svg { width:10px; height:10px; }

        /* ── Password criteria ──────────────────────────────────── */
        .pwd-criteria { margin-top:8px; display:grid; grid-template-columns:1fr 1fr; gap:4px 10px; }
        .pwd-criterion { display:flex; align-items:center; gap:5px; font-size:11.5px; color:var(--muted); transition:color .2s; }
        .pwd-criterion .dot { width:14px; height:14px; border-radius:50%; border:1.5px solid #d1d5db; flex-shrink:0; display:flex; align-items:center; justify-content:center; transition:background .2s, border-color .2s; }
        .pwd-criterion.met .dot { background:#34d399; border-color:#34d399; }
        .pwd-criterion.met { color:#047857; }
        .pwd-criterion .dot svg { width:9px; height:9px; color:#fff; display:none; }
        .pwd-criterion.met .dot svg { display:block; }
        .strength-bar-wrap { margin-top:8px; }
        .strength-bars { display:flex; gap:3px; height:3px; margin-bottom:4px; }
        .strength-bar { flex:1; border-radius:999px; background:var(--border); transition:background .25s; }
        .strength-text { font-size:11px; color:var(--muted); transition:color .25s; }

        /* ── Terms ──────────────────────────────────────────────── */
        .terms-row { display:flex; align-items:flex-start; gap:9px; margin:16px 0 14px; }
        .terms-row input[type="checkbox"] { width:15px; height:15px; accent-color:var(--brand-500); flex-shrink:0; margin-top:2px; cursor:pointer; }
        .terms-text { font-size:12.5px; color:var(--muted); line-height:1.5; }
        .terms-text a { color:var(--brand-600); text-decoration:none; font-weight:600; }
        .terms-text a:hover { text-decoration:underline; }

        /* ── Submit button ──────────────────────────────────────── */
        .btn-primary-reg {
            width:100%; display:flex; align-items:center; justify-content:center; gap:8px;
            padding:13px 16px;
            background:linear-gradient(135deg, var(--brand-500), var(--brand-700));
            border:none; border-radius:12px;
            color:#fff; font-size:14px; font-weight:700; font-family:inherit;
            cursor:pointer; box-shadow:0 8px 20px rgba(37,99,235,.28);
            transition:transform .18s, box-shadow .18s, opacity .18s;
        }
        .btn-primary-reg:hover { transform:translateY(-1px); box-shadow:0 12px 24px rgba(37,99,235,.34); }
        .btn-primary-reg:active { transform:none; }
        .btn-primary-reg:disabled { opacity:.6; cursor:not-allowed; transform:none; }
        .btn-primary-reg svg { width:16px; height:16px; flex-shrink:0; }

        .btn-google-reg {
            width:100%; display:flex; align-items:center; justify-content:center; gap:10px;
            padding:11px 14px; background:var(--surface);
            border:1.5px solid var(--border); border-radius:12px;
            color:var(--text); font-size:13px; font-weight:700;
            text-decoration:none; cursor:pointer;
            transition:background .15s, border-color .15s, box-shadow .15s;
        }
        .btn-google-reg:hover { background:var(--surface-2); border-color:#9ca3af; box-shadow:0 2px 8px rgba(0,0,0,.06); }
        .or-row-reg { display:flex; align-items:center; gap:10px; margin:14px 0; font-size:11px; font-weight:700; color:#9ca3af; text-transform:uppercase; }
        .or-row-reg::before, .or-row-reg::after { content:''; flex:1; height:1px; background:var(--border); }
        .registration-methods { margin-top:4px; border:1px solid #dbe4ef; border-radius:16px; background:#f8fafc; padding:14px; }
        .registration-methods-title { margin:0 0 11px; color:#334155; font-size:12px; font-weight:800; text-align:center; }
        .registration-methods-copy { margin:10px 0 0; color:#64748b; font-size:11px; line-height:1.5; text-align:center; }
        .password-method-note { margin:-3px 0 12px; border:1px solid #dbeafe; border-radius:10px; background:#eff6ff; padding:8px 10px; color:#475569; font-size:11px; line-height:1.45; }

        .security-note {
            display:flex; gap:7px; align-items:flex-start;
            margin-top:7px; padding:8px 10px;
            border:1px solid rgba(16,185,129,.24); border-radius:10px;
            background:rgba(16,185,129,.06); color:#047857;
            font-size:11.5px; line-height:1.4;
        }
        .security-note svg { width:13px; height:13px; flex-shrink:0; margin-top:1px; }

        /* ── Server errors ──────────────────────────────────────── */
        .reg-alert {
            background:rgba(239,68,68,.06); border:1px solid rgba(239,68,68,.28);
            color:#b91c1c; padding:12px 14px; border-radius:12px;
            font-size:13px; margin-bottom:16px; line-height:1.55;
        }
        .reg-alert ul { padding-left:16px; }

        /* ── Sign in link ───────────────────────────────────────── */
        .signin-link { text-align:center; margin-top:18px; font-size:13px; color:var(--muted); }
        .signin-link a { color:var(--brand-600); text-decoration:none; font-weight:600; }
        .signin-link a:hover { text-decoration:underline; }

        [x-cloak] { display:none !important; }

        @media (max-width:480px) {
            .auth-page { width: min(100% - 20px, 1180px); padding-top: 18px; }
            .auth-topbar { align-items: flex-start; }
            .auth-actions { flex-direction: column; align-items: stretch; gap: 6px; }
            .auth-action { min-height: 34px; padding: 0 10px; font-size: 11px; }
            .pwd-criteria { grid-template-columns:1fr; }
            .range-row { grid-template-columns:1fr; }
            .range-sep { display:none; }
        }
    </style>
</head>
<body>

<main class="auth-page">
    <nav class="auth-topbar" aria-label="Registration navigation">
        <a class="auth-brand" href="{{ url('/') }}"><img src="{{ asset('images/boardmatch-final-logo.png') }}" alt=""><span>BoardMatch</span></a>
        <div class="auth-actions">
            <a class="auth-action" href="{{ route('login') }}">Sign in</a>
            <a class="auth-action" href="{{ route('register.owner') }}">Register as owner</a>
        </div>
    </nav>

<div class="reg-card"
     x-data="regApp()"
     x-init="init()">
<div class="reg-inner">

    <div class="reg-head">
        <h1>Create your student account</h1>
        <p>For DSSC students looking for a compatible boarding house near campus.</p>
    </div>

    @if ($errors->any())
        <div class="reg-alert">
            <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    @if (session('status'))
        <div style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.3);color:#065f46;padding:12px 14px;border-radius:12px;font-size:13px;margin-bottom:14px;">
            {{ session('status') }}
        </div>
    @endif

    @php
        $googleConfigured = filled(config('services.google.client_id'));
        $selectedRole = 'tenant';
    @endphp
    <div style="margin-bottom:14px;border:1px solid #dbeafe;border-radius:11px;background:#eff6ff;padding:10px 12px;color:#475569;font-size:11.5px;line-height:1.5;">
        Complete every required student detail below. At the bottom, choose whether to create a password or securely link the completed profile with Google.
    </div>

    <form id="regForm" method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" novalidate>
        @csrf

        <input id="role" name="role" type="hidden" value="tenant" x-model="role">

        {{-- Role-context badge --}}
        <div x-show="role === 'tenant'" x-cloak
             style="background:rgba(99,102,241,.07);border:1px solid rgba(99,102,241,.22);border-radius:10px;padding:9px 12px;font-size:12px;color:#4338ca;margin-bottom:4px;display:flex;gap:8px;align-items:center;">
            <svg style="width:14px;height:14px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>Registering as a <strong>Tenant / Student</strong> — find your perfect boarding house match.</span>
        </div>

        <div x-show="role" x-cloak>

            {{-- ════════════════════════════════════════════════════ --}}
            {{-- SHARED FIELDS                                       --}}
            {{-- ════════════════════════════════════════════════════ --}}
            <p class="section-label">Account Information</p>

            {{-- Full Name --}}
            <div class="field">
                <label class="field-label" for="name">Full Name<span class="req">*</span></label>
                <div class="input-box{{ $errors->has('name') ? ' is-error' : '' }}" id="box-name">
                    <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <input id="name" name="name" type="text" placeholder="Your full name" value="{{ old('name') }}" autocomplete="name">
                </div>
                <p class="field-error" id="err-name" style="{{ $errors->has('name') ? '' : 'display:none' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $errors->first('name') }}</span>
                </p>
            </div>

            {{-- Email --}}
            <div class="field">
                <label class="field-label" for="email">Email Address<span class="req">*</span></label>
                <div class="input-box{{ $errors->has('email') ? ' is-error' : '' }}" id="box-email">
                    <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                    <input id="email" name="email" type="email" placeholder="you@example.com" value="{{ old('email') }}" autocomplete="email">
                </div>
                <p class="field-error" id="err-email" style="{{ $errors->has('email') ? '' : 'display:none' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $errors->first('email') }}</span>
                </p>
            </div>

            {{-- Phone --}}
            <div class="field">
                <label class="field-label" for="phone">Phone Number<span class="req">*</span></label>
                <div class="input-box{{ $errors->has('phone') ? ' is-error' : '' }}" id="box-phone">
                    <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.338c0-.933.779-1.712 1.713-1.713h2.42c.379 0 .713.268.793.64l.927 4.167a.802.802 0 01-.228.77l-1.323 1.196a.802.802 0 00-.228.77c.43 1.852 1.677 3.403 3.45 4.56.228.143.519.115.72-.065l1.373-1.237a.802.802 0 01.77-.228l4.167.927c.372.08.64.414.64.793v2.42c0 .934-.78 1.713-1.713 1.713H6.337C4.047 21 2.25 19.204 2.25 16.913V6.338z"/></svg>
                    <input id="phone" name="phone" type="tel" placeholder="09XX XXX XXXX" value="{{ old('phone') }}" autocomplete="tel">
                </div>
                <p class="field-error" id="err-phone" style="{{ $errors->has('phone') ? '' : 'display:none' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $errors->first('phone') }}</span>
                </p>
            </div>

            {{-- Password --}}
            <div class="password-method-note"><strong>Password option:</strong> Complete these two password fields only when using “Create account with email.” They are not required when using “Register with Google.”</div>
            <div class="field">
                <label class="field-label" for="password">Password<span class="req">*</span></label>
                <div class="input-box{{ $errors->has('password') ? ' is-error' : '' }}" id="box-password">
                    <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 00-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    <input id="password" name="password" type="password" placeholder="Create a strong password">
                    <button type="button" class="eye-btn password-toggle" id="togglePwd" data-target="password" data-label-show="Show password" data-label-hide="Hide password" aria-label="Show password" aria-controls="password" aria-pressed="false">
                        <svg id="eyePwd" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </button>
                </div>
                <div class="pwd-criteria" id="pwdCriteria">
                    <div class="pwd-criterion" id="c-len"><span class="dot"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>8+ characters</div>
                    <div class="pwd-criterion" id="c-upper"><span class="dot"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>Uppercase (A–Z)</div>
                    <div class="pwd-criterion" id="c-lower"><span class="dot"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>Lowercase (a–z)</div>
                    <div class="pwd-criterion" id="c-number"><span class="dot"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>Number (0–9)</div>
                    <div class="pwd-criterion" id="c-special"><span class="dot"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>Special character</div>
                </div>
                <div class="strength-bar-wrap" id="strengthWrap" style="display:none">
                    <div class="strength-bars"><div class="strength-bar" id="sb1"></div><div class="strength-bar" id="sb2"></div><div class="strength-bar" id="sb3"></div><div class="strength-bar" id="sb4"></div><div class="strength-bar" id="sb5"></div></div>
                    <span class="strength-text" id="strengthText"></span>
                </div>
                <p class="field-error" id="err-password" style="display:none">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span></span>
                </p>
                <div class="security-note">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                    <span>Your password is securely encrypted and protected.</span>
                </div>
                @error('password')<p class="field-error" style="margin-top:6px">{{ $message }}</p>@enderror
            </div>

            {{-- Confirm Password --}}
            <div class="field">
                <label class="field-label" for="password_confirmation">Confirm Password<span class="req">*</span></label>
                <div class="input-box" id="box-confirm">
                    <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 00-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Re-enter your password">
                    <button type="button" class="eye-btn password-toggle" id="toggleConfirm" data-target="password_confirmation" data-label-show="Show confirm password" data-label-hide="Hide confirm password" aria-label="Show confirm password" aria-controls="password_confirmation" aria-pressed="false">
                        <svg id="eyeConfirm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </button>
                </div>
                <p class="field-error" id="err-confirm" style="display:none">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span></span>
                </p>
            </div>

            {{-- ════════════════════════════════════════════════════ --}}
            {{-- TENANT / STUDENT FIELDS                             --}}
            {{-- ════════════════════════════════════════════════════ --}}
            <div x-show="role === 'tenant'" x-cloak data-role-fields="tenant">

                <p class="section-label">Student Details</p>

                {{-- School / University --}}
                <div class="field">
                    <label class="field-label" for="school">School / University<span class="req">*</span></label>
                    <div class="input-box{{ $errors->has('school') ? ' is-error' : '' }}" id="box-school">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                        <input id="school" name="school" type="text" placeholder="e.g. University of Southeastern Philippines" value="{{ old('school') }}">
                    </div>
                    @error('school')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                </div>

                {{-- Course / Year Level --}}
                <div class="field">
                    <label class="field-label" for="course_year">Course / Year Level<span class="req">*</span></label>
                    <div class="input-box{{ $errors->has('course_year') ? ' is-error' : '' }}" id="box-course-year">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <input id="course_year" name="course_year" type="text" placeholder="e.g. BS Computer Science — 2nd Year" value="{{ old('course_year') }}">
                    </div>
                    @error('course_year')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                </div>

                <p class="section-label">Boarding Preferences</p>

                {{-- Preferred Location --}}
                <div class="field">
                    <label class="field-label" for="preferred_location">Preferred Location<span class="req">*</span></label>
                    <div class="input-box{{ $errors->has('preferred_location') ? ' is-error' : '' }}" id="box-preferred-location">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <input id="preferred_location" name="preferred_location" type="text" placeholder="e.g. Near USM, Brgy. Sto. Niño, Digos City" value="{{ old('preferred_location') }}">
                    </div>
                    <p class="field-hint">Area or barangay near your school where you'd like to stay.</p>
                    @error('preferred_location')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                </div>

                {{-- Rental Budget --}}
                <div class="field">
                    <label class="field-label" for="rental_budget">Rental Budget (PHP / month)<span class="req">*</span></label>
                    <div>
                        <div>
                            <div class="input-box{{ $errors->has('rental_budget') ? ' is-error' : '' }}" id="box-rental-budget">
                                <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <input id="rental_budget" name="rental_budget" type="number" min="0" step="0.01" placeholder="e.g. 3500" value="{{ old('rental_budget', old('budget_min')) }}">
                            </div>
                            <p style="font-size:10.5px;color:var(--muted);margin-top:3px;text-align:center">Monthly budget</p>
                        </div>
                        <div class="range-sep">—</div>
                        <div style="display:none">
                            <div class="input-box{{ $errors->has('budget_max') ? ' is-error' : '' }}">
                                <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <input id="budget_max" name="budget_max" type="number" min="0" placeholder="Max" value="{{ old('budget_max') }}">
                            </div>
                            <p style="font-size:10.5px;color:var(--muted);margin-top:3px;text-align:center">Maximum</p>
                        </div>
                    </div>
                </div>

                    @error('rental_budget')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                    @error('budget_max')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                {{-- Lifestyle Info --}}
                <div class="field">
                    <label class="field-label" for="lifestyle_info">Lifestyle Information for AI Recommendation<span class="req">*</span></label>
                    <div class="input-box{{ $errors->has('lifestyle_info') ? ' is-error' : '' }}" style="align-items:flex-start;padding-top:10px" id="box-lifestyle-info">
                        <svg class="input-icon" style="margin-top:2px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        <textarea id="lifestyle_info" name="lifestyle_info" rows="4"
                            placeholder="Describe your lifestyle to help our AI find your best match.&#10;&#10;Example: I sleep early (before 10 PM), prefer quiet environments, non-smoker, keep things tidy, mostly stay indoors to study, prefer female roommates.">{{ old('lifestyle_info') }}</textarea>
                    </div>
                    <p class="field-hint">Your AI-powered recommendations improve with more detail here.</p>
                    @error('lifestyle_info')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                </div>

            </div>{{-- /tenant fields --}}

            {{-- ════════════════════════════════════════════════════ --}}
            {{-- OWNER / ADMIN FIELDS                                --}}
            {{-- ════════════════════════════════════════════════════ --}}
            <div x-show="role === 'owner'" x-cloak data-role-fields="owner">

                <p class="section-label">Boarding House Information</p>

                {{-- Boarding House Name --}}
                <div class="field">
                    <label class="field-label" for="bh_name">Boarding House Name<span class="req">*</span></label>
                    <div class="input-box{{ $errors->has('bh_name') ? ' is-error' : '' }}" id="box-bh-name">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <input id="bh_name" name="bh_name" type="text" placeholder="e.g. Casa Verde Boarding House" value="{{ old('bh_name') }}">
                    </div>
                    @error('bh_name')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                </div>

                {{-- Boarding House Address --}}
                <div class="field">
                    <label class="field-label" for="bh_address">Boarding House Address<span class="req">*</span></label>
                    <div class="input-box{{ $errors->has('bh_address') ? ' is-error' : '' }}" id="box-bh-address" style="align-items:flex-start;padding-top:10px">
                        <svg class="input-icon" style="margin-top:2px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <textarea id="bh_address" name="bh_address" rows="2" placeholder="Street / Barangay / City / Province">{{ old('bh_address') }}</textarea>
                    </div>
                    @error('bh_address')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                </div>

                {{-- Contact Number --}}
                <div class="field">
                    <label class="field-label" for="bh_contact">Contact Number<span class="req">*</span></label>
                    <div class="input-box{{ $errors->has('bh_contact') ? ' is-error' : '' }}" id="box-bh-contact">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.338c0-.933.779-1.712 1.713-1.713h2.42c.379 0 .713.268.793.64l.927 4.167a.802.802 0 01-.228.77l-1.323 1.196a.802.802 0 00-.228.77c.43 1.852 1.677 3.403 3.45 4.56.228.143.519.115.72-.065l1.373-1.237a.802.802 0 01.77-.228l4.167.927c.372.08.64.414.64.793v2.42c0 .934-.78 1.713-1.713 1.713H6.337C4.047 21 2.25 19.204 2.25 16.913V6.338z"/></svg>
                        <input id="bh_contact" name="bh_contact" type="tel" placeholder="Boarding house landline or mobile" value="{{ old('bh_contact') }}">
                    </div>
                    <p class="field-hint">If different from your personal number above.</p>
                    @error('bh_contact')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                </div>

                {{-- Room Types --}}
                <div class="field">
                    <label class="field-label">Room Types Available<span class="req">*</span></label>
                    <div class="check-grid">
                        @php $oldRoomTypes = (array) old('room_types', []); @endphp
                        @foreach ([
                            'single'    => 'Single Room',
                            'shared'    => 'Double / Shared',
                            'studio'    => 'Studio Type',
                            'dorm'      => 'Dormitory',
                            'suite'     => 'Suite / Deluxe',
                        ] as $val => $label)
                        <label class="check-item">
                            <input type="checkbox" name="room_types[]" value="{{ $val }}"
                                   {{ in_array($val, $oldRoomTypes) ? 'checked' : '' }}>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                    @error('room_types')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                </div>

                {{-- Monthly Rent Range --}}
                <div class="field">
                    <label class="field-label">Monthly Rent Range (PHP)<span class="req">*</span></label>
                    <div class="range-row">
                        <div>
                            <div class="input-box{{ $errors->has('rent_min') ? ' is-error' : '' }}">
                                <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <input name="rent_min" type="number" min="0" placeholder="Min" value="{{ old('rent_min') }}">
                            </div>
                            <p style="font-size:10.5px;color:var(--muted);margin-top:3px;text-align:center">Minimum</p>
                        </div>
                        <div class="range-sep">—</div>
                        <div>
                            <div class="input-box{{ $errors->has('rent_max') ? ' is-error' : '' }}">
                                <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <input name="rent_max" type="number" min="0" placeholder="Max" value="{{ old('rent_max') }}">
                            </div>
                            <p style="font-size:10.5px;color:var(--muted);margin-top:3px;text-align:center">Maximum</p>
                        </div>
                    </div>
                    @error('rent_min')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                    @error('rent_max')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                    @error('monthly_rent_range')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                </div>

                {{-- Amenities --}}
                <div class="field">
                    <label class="field-label">Amenities Offered<span class="req">*</span></label>
                    <div class="check-grid">
                        @php $oldAmenities = (array) old('amenities', []); @endphp
                        @foreach ([
                            'wifi'      => 'WiFi / Internet',
                            'aircon'    => 'Air Conditioning',
                            'cctv'      => 'CCTV / Security',
                            'water'     => 'Water (24/7)',
                            'electric'  => 'Electricity Included',
                            'kitchen'   => 'Kitchen Access',
                            'laundry'   => 'Laundry Area',
                            'study'     => 'Study / Common Area',
                            'parking'   => 'Parking',
                            'guard'     => 'Security Guard',
                        ] as $val => $label)
                        <label class="check-item">
                            <input type="checkbox" name="amenities[]" value="{{ $val }}"
                                   {{ in_array($val, $oldAmenities) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                    @error('amenities')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                </div>

                {{-- House Rules --}}
                <div class="field">
                    <label class="field-label" for="house_rules">House Rules<span class="req">*</span></label>
                    <div class="input-box{{ $errors->has('house_rules') ? ' is-error' : '' }}" style="align-items:flex-start;padding-top:10px">
                        <svg class="input-icon" style="margin-top:2px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <textarea id="house_rules" name="house_rules" rows="4"
                            placeholder="e.g. No overnight visitors, curfew at 10 PM, no smoking inside the premises, weekly room inspections...">{{ old('house_rules') }}</textarea>
                    </div>
                    @error('house_rules')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                </div>

                <p class="section-label">Documents &amp; Photos</p>

                {{-- Boarding House Photos --}}
                <div class="field">
                    <label class="field-label">Boarding House Photos</label>
                    <div class="file-zone" id="photoZone">
                        <input type="file" name="photos[]" id="photosInput" accept="image/*" multiple
                               onchange="handleFiles(this, 'photoPreview', 'photoCount')">
                        <svg class="file-zone-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                        <p class="file-zone-title">Click or drag photos here</p>
                        <p class="file-zone-sub">JPG, PNG - up to 5 images, 2 MB each</p>
                        <div class="file-preview" id="photoPreview"></div>
                    </div>
                    <p id="photoCount" class="field-hint" style="margin-top:6px"></p>
                    @error('photos')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                    @error('photos.*')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                </div>

                {{-- Valid ID / Proof of Ownership --}}
                <div class="field">
                    <label class="field-label">Valid ID or Proof of Ownership<span class="req">*</span></label>
                    <div class="file-zone" id="idZone">
                        <input type="file" name="proof_of_ownership" id="validIdInput" accept=".jpg,.jpeg,.png,.pdf"
                               onchange="handleFiles(this, 'idPreview', 'idCount')">
                        <svg class="file-zone-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/></svg>
                        <p class="file-zone-title">Click or drag document here</p>
                        <p class="file-zone-sub">JPG, PNG, PDF - max 2 MB</p>
                        <div class="file-preview" id="idPreview"></div>
                    </div>
                    <p id="idCount" class="field-hint" style="margin-top:6px"></p>
                    <p class="field-hint">Government-issued ID, business permit, or land title as proof of ownership.</p>
                    @error('proof_of_ownership')<p class="field-error" style="margin-top:5px">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $message }}</span>
                    </p>@enderror
                </div>

                {{-- Pending review notice --}}
                <div style="background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.36);border-radius:10px;padding:10px 13px;font-size:12px;color:#92400e;display:flex;gap:9px;align-items:flex-start;margin-bottom:4px;">
                    <svg style="width:14px;height:14px;flex-shrink:0;margin-top:1px;color:#d97706" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Your listing will be reviewed by our team before it goes live. You can still access your dashboard while it's pending approval.</span>
                </div>

            </div>{{-- /owner fields --}}

            {{-- ── Terms & Conditions ────────────────────────────── --}}
            <div class="terms-row">
                <input type="checkbox" id="terms" name="terms" value="1" @checked(old('terms'))>
                <label class="terms-text" for="terms">
                    I agree to BoardMatch's <a href="#" tabindex="-1">Terms and Conditions</a> and <a href="#" tabindex="-1">Privacy Policy</a>.
                </label>
            </div>
            <p class="field-error" id="err-terms"
               style="{{ $errors->has('terms') ? '' : 'display:none' }};margin-top:-10px;margin-bottom:12px">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ $errors->first('terms') ?: 'You must accept the Terms and Conditions.' }}</span>
            </p>

            <div class="registration-methods">
                <p class="registration-methods-title">Choose how to create your account</p>
                <button type="submit" class="btn-primary-reg" id="submitBtn" name="registration_method" value="password">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Create account with email
                </button>

                <div class="or-row-reg">or securely link this completed profile</div>

                @if ($googleConfigured)
                    <button type="submit" class="btn-google-reg" id="googleSubmitBtn" name="registration_method" value="google" data-google-registration="true" formaction="{{ route('register.google') }}">
                        <svg width="18" height="18" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                        </svg>
                        Register with Google
                    </button>
                @else
                    <button type="button" class="btn-google-reg" disabled style="opacity:.55;cursor:not-allowed">Google registration is unavailable</button>
                @endif

                <p class="registration-methods-copy">Google must return the same email entered above. Your completed student details will be saved before dashboard access is granted.</p>
            </div>

        </div>{{-- /role-conditional wrapper --}}
    </form>

    <div class="signin-link">
        Already have an account? <a href="{{ route('login') }}">Sign in</a>
        <br>Own a boarding house? <a href="{{ route('register.owner') }}">Register as an owner</a>
    </div>

</div>
</div>{{-- /reg-card --}}
</main>

<script>
var PASSWORD_EYE_OPEN = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>';
var PASSWORD_EYE_OFF = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>';

function initPasswordToggles() {
    document.querySelectorAll('.password-toggle').forEach(function(button) {
        if (button.getAttribute('data-toggle-bound') === 'true') return;

        var targetId = button.getAttribute('data-target');
        if (!targetId) return;

        button.setAttribute('data-toggle-bound', 'true');
        button.addEventListener('click', function () {
            var input = document.getElementById(targetId);
            if (!input) return;

            var isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';

            button.classList.toggle('is-visible', isHidden);
            button.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            button.setAttribute('aria-label', isHidden
                ? (button.getAttribute('data-label-hide') || 'Hide password')
                : (button.getAttribute('data-label-show') || 'Show password'));

            var icon = button.querySelector('svg');
            if (icon) icon.innerHTML = isHidden ? PASSWORD_EYE_OFF : PASSWORD_EYE_OPEN;
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPasswordToggles);
} else {
    initPasswordToggles();
}
// ── File upload preview ──────────────────────────────────────────────────────
function handleFiles(input, previewId, countId) {
    var preview = document.getElementById(previewId);
    var count   = document.getElementById(countId);
    if (!preview || !input.files.length) return;
    preview.innerHTML = '';
    Array.from(input.files).forEach(function(f) {
        var chip = document.createElement('span');
        chip.className = 'file-chip';
        chip.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>' + f.name;
        preview.appendChild(chip);
    });
    if (count) count.textContent = input.files.length + ' file' + (input.files.length > 1 ? 's' : '') + ' selected';
}

function clearRoleFields(group) {
    var root = document.querySelector('[data-role-fields="' + group + '"]');
    if (!root) return;

    root.querySelectorAll('input, textarea, select').forEach(function(field) {
        if (field.type === 'checkbox' || field.type === 'radio') {
            field.checked = false;
            return;
        }

        field.value = '';
    });

    if (group === 'owner') {
        ['photoPreview', 'idPreview'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.innerHTML = '';
        });
        ['photoCount', 'idCount'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.textContent = '';
        });
    }
}

// ── Alpine component ─────────────────────────────────────────────────────────
function syncRoleFields(currentRole, clearHidden) {
    document.querySelectorAll('[data-role-fields]').forEach(function(root) {
        var isActive = root.getAttribute('data-role-fields') === currentRole;

        root.querySelectorAll('input, textarea, select').forEach(function(field) {
            field.disabled = !isActive;
        });

        if (clearHidden && !isActive) {
            clearRoleFields(root.getAttribute('data-role-fields'));
        }
    });
}

function regApp() {
    return {
        role: @js($selectedRole),

        init() {
            var self = this;
            this.$nextTick(function () {
                syncRoleFields(self.role, false);
                initFormLogic();
            });
        },

        onRoleChange() {
            syncRoleFields(this.role, true);
        }
    };
}

// ── Form logic (password, validation) ───────────────────────────────────────
function initFormLogic() {
    initPasswordToggles();

    // Password strength
    var pwdInput = document.getElementById('password');
    var criteria = {
        len:     { el: document.getElementById('c-len'),     test: function(v){ return v.length >= 8; } },
        upper:   { el: document.getElementById('c-upper'),   test: function(v){ return /[A-Z]/.test(v); } },
        lower:   { el: document.getElementById('c-lower'),   test: function(v){ return /[a-z]/.test(v); } },
        number:  { el: document.getElementById('c-number'),  test: function(v){ return /[0-9]/.test(v); } },
        special: { el: document.getElementById('c-special'), test: function(v){ return /[^A-Za-z0-9]/.test(v); } },
    };
    var bars = ['sb1','sb2','sb3','sb4','sb5'].map(function(id){ return document.getElementById(id); });
    var strengthWrap = document.getElementById('strengthWrap');
    var strengthText = document.getElementById('strengthText');
    var levels = [
        {label:'',color:'#d1d5db'},
        {label:'Weak',color:'#f87171'},
        {label:'Weak',color:'#f87171'},
        {label:'Medium',color:'#f59e0b'},
        {label:'Medium',color:'#f59e0b'},
        {label:'Strong',color:'#10b981'}
    ];

    function normalizedPassword(val) {
        return String(val || '').toLowerCase().replace(/\s+/g, '');
    }

    function hasRepeatedCharacters(val) {
        return /(.)\1{5,}/.test(normalizedPassword(val));
    }

    function hasSequentialRun(val) {
        var normalized = normalizedPassword(val);
        var sequences = [
            '0123456789',
            '9876543210',
            'abcdefghijklmnopqrstuvwxyz',
            'zyxwvutsrqponmlkjihgfedcba',
            'qwertyuiopasdfghjklzxcvbnm',
            'mnbvcxzlkjhgfdsa poiuytrewq'.replace(/\s+/g, '')
        ];

        return sequences.some(function(sequence) {
            for (var length = 6; length >= 4; length--) {
                for (var index = 0; index <= sequence.length - length; index++) {
                    if (normalized.includes(sequence.substr(index, length))) {
                        return true;
                    }
                }
            }
            return false;
        });
    }

    function isPredictablePassword(val) {
        var normalized = normalizedPassword(val);
        var blocked = ['password','password123','123456','12345678','123456789','qwerty','qwerty123','abcdef','abcdef123','admin123','user123','boardmatch123','abc123','111111','000000'];
        if (blocked.indexOf(normalized) !== -1) return true;
        return ['password','qwerty','boardmatch','admin123','user123'].some(function(token) {
            return normalized.includes(token);
        }) || hasRepeatedCharacters(val) || hasSequentialRun(val);
    }

    function updateCriteria(val) {
        var met = 0;
        Object.values(criteria).forEach(function(c){ var ok=c.test(val); if(ok)met++; if(c.el)c.el.classList.toggle('met',ok); });
        return met;
    }

    if (pwdInput) {
        pwdInput.addEventListener('input', function () {
            var val = pwdInput.value, met = updateCriteria(val);
            if (!val) { if(strengthWrap) strengthWrap.style.display='none'; return; }
            if (strengthWrap) strengthWrap.style.display = 'block';
            var info = levels[met] || levels[0];
            if (isPredictablePassword(val)) {
                info = {label:'Weak - This password is too predictable. Please create a stronger password.', color:'#f87171'};
            }
            bars.forEach(function(b,i){ if(b) b.style.background = i<met ? info.color : 'var(--border)'; });
            if (strengthText) { strengthText.textContent = info.label; strengthText.style.color = info.color; }
        });
    }

    // Confirm password match
    var confirmInput = document.getElementById('password_confirmation');
    var errConfirm   = document.getElementById('err-confirm');
    var boxConfirm   = document.getElementById('box-confirm');
    if (confirmInput) {
        confirmInput.addEventListener('input', function(){
            var pwd = pwdInput ? pwdInput.value : '';
            if (!confirmInput.value) { if(errConfirm) errConfirm.style.display='none'; if(boxConfirm){boxConfirm.classList.remove('is-error','is-valid');} return; }
            var match = confirmInput.value === pwd;
            if (errConfirm) { errConfirm.style.display=match?'none':'flex'; var sp=errConfirm.querySelector('span'); if(sp) sp.textContent='Passwords do not match.'; }
            if (boxConfirm) { boxConfirm.classList.toggle('is-error',!match); boxConfirm.classList.toggle('is-valid',match); }
        });
    }

    // Helpers
    function showErr(boxId, errId, msg) {
        var box=document.getElementById('box-'+boxId), err=document.getElementById('err-'+errId);
        if(box){box.classList.add('is-error');box.classList.remove('is-valid');}
        if(err){err.style.display='flex';var sp=err.querySelector('span');if(sp)sp.textContent=msg;}
    }
    function clearErr(boxId, errId) {
        var box=document.getElementById('box-'+boxId), err=document.getElementById('err-'+errId);
        if(box){box.classList.remove('is-error');box.classList.add('is-valid');}
        if(err) err.style.display='none';
    }
    function vName()    { var v=(document.getElementById('name')||{}).value||''; if(!v.trim()){showErr('name','name','Full name is required.');return false;} if(v.trim().length<2){showErr('name','name','At least 2 characters.');return false;} clearErr('name','name');return true; }
    function vEmail()   { var v=((document.getElementById('email')||{}).value||'').trim().toLowerCase(); if(!v){showErr('email','email','Email is required.');return false;} if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)){showErr('email','email','Enter a valid email address.');return false;} clearErr('email','email');return true; }
    function vPhone()   { var v=((document.getElementById('phone')||{}).value||'').trim(); if(!v){showErr('phone','phone','Phone number is required.');return false;} if(v.length>20){showErr('phone','phone','Phone number may not exceed 20 characters.');return false;} clearErr('phone','phone');return true; }
    function vPwd()     { var v=(pwdInput||{}).value||'', m=updateCriteria(v); if(!v){showErr('password','password','Password is required.');return false;} if(isPredictablePassword(v)){showErr('password','password','This password is too predictable. Please create a stronger password.');return false;} if(m<5){showErr('password','password','Password must meet all 5 requirements.');return false;} clearErr('password','password');return true; }

    [['name',vName],['email',vEmail],['phone',vPhone]].forEach(function(p){ var el=document.getElementById(p[0]); if(el) el.addEventListener('blur',p[1]); });
    if (pwdInput) pwdInput.addEventListener('blur', vPwd);

    // Submit
    var form = document.getElementById('regForm');
    var submitBtn = document.getElementById('submitBtn');
    if (form) {
        form.addEventListener('submit', function(e){
            var activeSubmit = e.submitter || submitBtn;
            var googleRegistration = activeSubmit && activeSubmit.getAttribute('data-google-registration') === 'true';
            var roleVal = (document.getElementById('role')||{}).value||'';
            if (!roleVal) { e.preventDefault(); alert('Please select your role to continue.'); return; }

            var ok = (vName()&vEmail()&vPhone()) === 1;

            if (!googleRegistration) {
                ok = (ok & vPwd()) === 1;

                // Confirm password for email registration only.
                var pwd=(pwdInput||{}).value||'', conf=(confirmInput||{}).value||'';
                if (!conf) { if(errConfirm){errConfirm.style.display='flex';var sp=errConfirm.querySelector('span');if(sp)sp.textContent='Please confirm your password.';} if(boxConfirm){boxConfirm.classList.add('is-error');boxConfirm.classList.remove('is-valid');} ok=false; }
                else if (conf!==pwd) { if(errConfirm){errConfirm.style.display='flex';var sp2=errConfirm.querySelector('span');if(sp2)sp2.textContent='Passwords do not match.';} if(boxConfirm){boxConfirm.classList.add('is-error');boxConfirm.classList.remove('is-valid');} ok=false; }
            }

            // Owner-specific
            if (roleVal === 'owner') {
                var bhName = (document.getElementById('bh_name')||{}).value||'';
                if (!bhName.trim()) { showErr('bh-name','bh-name','Boarding house name is required.'); ok=false; }
                var bhAddr = (document.getElementById('bh_address')||{}).value||'';
                if (!bhAddr.trim()) { showErr('bh-address','bh-address','Address is required.'); ok=false; }
            }

            // Terms
            var termsEl=document.getElementById('terms'), errTerms=document.getElementById('err-terms');
            if (termsEl&&!termsEl.checked) { if(errTerms) errTerms.style.display='flex'; ok=false; }
            else if(errTerms) errTerms.style.display='none';

            if (!ok) { e.preventDefault(); return; }
            if (activeSubmit) {
                activeSubmit.setAttribute('aria-disabled', 'true');
                activeSubmit.textContent = googleRegistration ? 'Connecting to Google…' : 'Creating account…';
            }
        });
    }
}

</script>

</body>
</html>
