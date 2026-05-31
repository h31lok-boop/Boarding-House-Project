{{-- resources/views/auth/register.blade.php --}}
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — BoardMatch</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function () {
            var t = localStorage.getItem('theme');
            if (t) document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Manrope', 'Segoe UI', sans-serif;
            background: radial-gradient(ellipse at top left, var(--surface) 0%, var(--bg) 55%, var(--surface-2) 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 32px 16px 48px;
        }

        .reg-card {
            width: 100%; max-width: 480px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(26,18,15,.10);
            overflow: hidden;
        }

        .reg-inner { padding: 32px 28px 28px; }
        @media (max-width: 520px) { .reg-inner { padding: 24px 18px 22px; } }

        /* ── Logo ───────────────────────────────────────────────── */
        .reg-logo { display:flex; align-items:center; justify-content:center; gap:10px; margin-bottom:6px; }
        .reg-logo-icon {
            height:40px; width:40px; border-radius:12px;
            background: linear-gradient(135deg, #ff7e5f, #feb47b);
            display:flex; align-items:center; justify-content:center;
            box-shadow: 0 6px 16px rgba(255,126,95,.32);
        }
        .reg-logo-icon svg { width:20px; height:20px; color:#fff; }
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
        .input-box:focus-within { border-color:var(--brand-500); box-shadow:0 0 0 3px rgba(255,126,95,.13); }
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
        .role-select:focus { border-color:var(--brand-500); box-shadow:0 0 0 3px rgba(255,126,95,.13); }
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
        .check-item:has(input:checked) { border-color:var(--brand-500); background:rgba(255,126,95,.06); }
        .check-item svg { width:14px; height:14px; color:#9ca3af; flex-shrink:0; }

        /* ── File upload ────────────────────────────────────────── */
        .file-zone {
            border:2px dashed var(--border); border-radius:12px;
            padding:18px 16px; text-align:center; cursor:pointer;
            transition:border-color .2s, background .2s; position:relative;
            background:var(--surface);
        }
        .file-zone:hover { border-color:var(--brand-500); background:rgba(255,126,95,.03); }
        .file-zone input[type="file"] { position:absolute; inset:0; opacity:0; width:100%; height:100%; cursor:pointer; }
        .file-zone-icon { width:28px; height:28px; color:#9ca3af; margin:0 auto 6px; }
        .file-zone-title { font-size:13px; font-weight:600; color:var(--text); }
        .file-zone-sub { font-size:11.5px; color:var(--muted); margin-top:2px; }
        .file-preview { margin-top:8px; display:flex; flex-wrap:wrap; gap:6px; justify-content:center; }
        .file-chip {
            display:inline-flex; align-items:center; gap:5px;
            background:rgba(255,126,95,.1); border-radius:6px;
            padding:3px 8px; font-size:11px; color:#c2410c; font-weight:600;
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
            background:linear-gradient(135deg, #ff7e5f, #feb47b);
            border:none; border-radius:12px;
            color:#fff; font-size:14px; font-weight:700; font-family:inherit;
            cursor:pointer; box-shadow:0 8px 20px rgba(255,126,95,.28);
            transition:transform .18s, box-shadow .18s, opacity .18s;
        }
        .btn-primary-reg:hover { transform:translateY(-1px); box-shadow:0 12px 24px rgba(255,126,95,.36); }
        .btn-primary-reg:active { transform:none; }
        .btn-primary-reg:disabled { opacity:.6; cursor:not-allowed; transform:none; }
        .btn-primary-reg svg { width:16px; height:16px; flex-shrink:0; }

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
            .pwd-criteria { grid-template-columns:1fr; }
            .range-row { grid-template-columns:1fr; }
            .range-sep { display:none; }
        }
    </style>
</head>
<body>

<div class="reg-card"
     x-data="regApp()"
     x-init="init()">
<div class="reg-inner">

    {{-- Logo --}}
    <div class="reg-logo">
        <div class="reg-logo-icon">
            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
        </div>
        <span class="reg-logo-name">BoardMatch</span>
    </div>

    <div class="reg-head">
        <h1>Create your account</h1>
        <p>Join the BoardMatch community — find or list boarding houses with ease.</p>
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

    <form id="regForm" method="POST" action="{{ route('register') }}" enctype="multipart/form-data" novalidate>
        @csrf

        {{-- ── Role Dropdown ────────────────────────────────────── --}}
        <div class="field">
            <label class="field-label" for="role">
                I am a…<span class="req">*</span>
            </label>
            <div class="role-wrap">
                <select id="role" name="role" class="role-select"
                        x-model="role"
                        @change="onRoleChange()">
                    <option value="" disabled>Select your role…</option>
                    <option value="user"  {{ old('role','') === 'user'  ? 'selected' : '' }}>Tenant / Student</option>
                    <option value="admin" {{ old('role','') === 'admin' ? 'selected' : '' }}>Owner / Admin</option>
                </select>
                <span class="role-caret">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </span>
            </div>
            @error('role')<p class="field-error" style="margin-top:5px">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ $message }}</span>
            </p>@enderror
        </div>

        {{-- Role-context badge --}}
        <div x-show="role === 'user'" x-cloak
             style="background:rgba(99,102,241,.07);border:1px solid rgba(99,102,241,.22);border-radius:10px;padding:9px 12px;font-size:12px;color:#4338ca;margin-bottom:4px;display:flex;gap:8px;align-items:center;">
            <svg style="width:14px;height:14px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>Registering as a <strong>Tenant / Student</strong> — find your perfect boarding house match.</span>
        </div>
        <div x-show="role === 'admin'" x-cloak
             style="background:rgba(255,126,95,.07);border:1px solid rgba(255,126,95,.25);border-radius:10px;padding:9px 12px;font-size:12px;color:#c2410c;margin-bottom:4px;display:flex;gap:8px;align-items:center;">
            <svg style="width:14px;height:14px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Registering as an <strong>Owner / Admin</strong> — list and manage your boarding house.</span>
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
            <div class="field">
                <label class="field-label" for="password">Password<span class="req">*</span></label>
                <div class="input-box{{ $errors->has('password') ? ' is-error' : '' }}" id="box-password">
                    <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 00-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    <input id="password" name="password" type="password" placeholder="Create a strong password">
                    <button type="button" class="eye-btn" id="togglePwd" aria-label="Toggle password">
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
                @error('password')<p class="field-error" style="margin-top:6px">{{ $message }}</p>@enderror
            </div>

            {{-- Confirm Password --}}
            <div class="field">
                <label class="field-label" for="password_confirmation">Confirm Password<span class="req">*</span></label>
                <div class="input-box" id="box-confirm">
                    <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 00-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Re-enter your password">
                    <button type="button" class="eye-btn" id="toggleConfirm" aria-label="Toggle confirm password">
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
            <div x-show="role === 'user'" x-cloak>

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
                    <label class="field-label" for="course_year">Course / Year Level</label>
                    <div class="input-box{{ $errors->has('course_year') ? ' is-error' : '' }}">
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
                    <label class="field-label" for="preferred_location">Preferred Location</label>
                    <div class="input-box{{ $errors->has('preferred_location') ? ' is-error' : '' }}">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <input id="preferred_location" name="preferred_location" type="text" placeholder="e.g. Near USM, Brgy. Sto. Niño, Digos City" value="{{ old('preferred_location') }}">
                    </div>
                    <p class="field-hint">Area or barangay near your school where you'd like to stay.</p>
                </div>

                {{-- Rental Budget --}}
                <div class="field">
                    <label class="field-label" for="budget_min">Rental Budget (₱ / month)</label>
                    <div class="range-row">
                        <div>
                            <div class="input-box{{ $errors->has('budget_min') ? ' is-error' : '' }}">
                                <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <input id="budget_min" name="budget_min" type="number" min="0" placeholder="Min" value="{{ old('budget_min') }}">
                            </div>
                            <p style="font-size:10.5px;color:var(--muted);margin-top:3px;text-align:center">Minimum</p>
                        </div>
                        <div class="range-sep">—</div>
                        <div>
                            <div class="input-box{{ $errors->has('budget_max') ? ' is-error' : '' }}">
                                <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <input id="budget_max" name="budget_max" type="number" min="0" placeholder="Max" value="{{ old('budget_max') }}">
                            </div>
                            <p style="font-size:10.5px;color:var(--muted);margin-top:3px;text-align:center">Maximum</p>
                        </div>
                    </div>
                </div>

                {{-- Lifestyle Info --}}
                <div class="field">
                    <label class="field-label" for="lifestyle_info">Lifestyle & Preferences <span style="font-weight:400;color:var(--muted)">(for AI Matching)</span></label>
                    <div class="input-box" style="align-items:flex-start;padding-top:10px">
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
            <div x-show="role === 'admin'" x-cloak>

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
                    <label class="field-label" for="bh_contact">Contact Number</label>
                    <div class="input-box{{ $errors->has('bh_contact') ? ' is-error' : '' }}">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.338c0-.933.779-1.712 1.713-1.713h2.42c.379 0 .713.268.793.64l.927 4.167a.802.802 0 01-.228.77l-1.323 1.196a.802.802 0 00-.228.77c.43 1.852 1.677 3.403 3.45 4.56.228.143.519.115.72-.065l1.373-1.237a.802.802 0 01.77-.228l4.167.927c.372.08.64.414.64.793v2.42c0 .934-.78 1.713-1.713 1.713H6.337C4.047 21 2.25 19.204 2.25 16.913V6.338z"/></svg>
                        <input id="bh_contact" name="bh_contact" type="tel" placeholder="Boarding house landline or mobile" value="{{ old('bh_contact') }}">
                    </div>
                    <p class="field-hint">If different from your personal number above.</p>
                </div>

                {{-- Room Types --}}
                <div class="field">
                    <label class="field-label">Room Types Available</label>
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
                </div>

                {{-- Monthly Rent Range --}}
                <div class="field">
                    <label class="field-label">Monthly Rent Range (₱)</label>
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
                </div>

                {{-- Amenities --}}
                <div class="field">
                    <label class="field-label">Amenities Offered</label>
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
                </div>

                {{-- House Rules --}}
                <div class="field">
                    <label class="field-label" for="house_rules">House Rules</label>
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
                        <p class="file-zone-sub">JPG, PNG — up to 5 images, 5 MB each</p>
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
                    <label class="field-label">Valid ID or Proof of Ownership</label>
                    <div class="file-zone" id="idZone">
                        <input type="file" name="valid_id_file" id="validIdInput" accept=".jpg,.jpeg,.png,.pdf"
                               onchange="handleFiles(this, 'idPreview', 'idCount')">
                        <svg class="file-zone-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/></svg>
                        <p class="file-zone-title">Click or drag document here</p>
                        <p class="file-zone-sub">JPG, PNG, PDF — max 5 MB</p>
                        <div class="file-preview" id="idPreview"></div>
                    </div>
                    <p id="idCount" class="field-hint" style="margin-top:6px"></p>
                    <p class="field-hint">Government-issued ID, business permit, or land title as proof of ownership.</p>
                    @error('valid_id_file')<p class="field-error" style="margin-top:5px">
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
                    I agree to BoardMatch's <a href="#" tabindex="-1">Terms of Service</a> and <a href="#" tabindex="-1">Privacy Policy</a>.
                </label>
            </div>
            <p class="field-error" id="err-terms"
               style="{{ $errors->has('terms') ? '' : 'display:none' }};margin-top:-10px;margin-bottom:12px">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ $errors->first('terms') ?: 'You must accept the Terms of Service.' }}</span>
            </p>

            <button type="submit" class="btn-primary-reg" id="submitBtn">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Create Account
            </button>

        </div>{{-- /role-conditional wrapper --}}
    </form>

    <div class="signin-link">
        Already have an account? <a href="{{ route('login') }}">Sign in</a>
    </div>

</div>
</div>{{-- /reg-card --}}

<script>
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

// ── Alpine component ─────────────────────────────────────────────────────────
function regApp() {
    return {
        role: '{{ old('role', '') }}',

        init() {
            var self = this;
            this.$nextTick(function () { initFormLogic(); });
        },

        onRoleChange() {
            // nothing extra needed; x-show handles visibility
        }
    };
}

// ── Form logic (password, validation) ───────────────────────────────────────
function initFormLogic() {
    var EYE_OPEN  = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>';
    var EYE_SLASH = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>';

    function setupEye(btnId, inputId, iconId) {
        var btn = document.getElementById(btnId), inp = document.getElementById(inputId), ico = document.getElementById(iconId);
        if (!btn || !inp) return;
        btn.addEventListener('click', function () {
            var shown = inp.type === 'text';
            inp.type = shown ? 'password' : 'text';
            if (ico) ico.innerHTML = shown ? EYE_OPEN : EYE_SLASH;
        });
    }
    setupEye('togglePwd', 'password', 'eyePwd');
    setupEye('toggleConfirm', 'password_confirmation', 'eyeConfirm');

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
        {label:'',color:'#d1d5db'},{label:'Too weak',color:'#f87171'},{label:'Weak',color:'#fb923c'},
        {label:'Fair',color:'#facc15'},{label:'Good',color:'#34d399'},{label:'Strong 🔒',color:'#10b981'}
    ];

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
    function vPhone()   { var v=((document.getElementById('phone')||{}).value||'').replace(/\s/g,''); if(!v){showErr('phone','phone','Phone number is required.');return false;} if(!/^(\+63|0)9\d{9}$/.test(v)){showErr('phone','phone','Valid PH number required (09XX XXX XXXX).');return false;} clearErr('phone','phone');return true; }
    function vPwd()     { var v=(pwdInput||{}).value||'', m=updateCriteria(v); if(!v){showErr('password','password','Password is required.');return false;} if(m<5){showErr('password','password','Password must meet all 5 requirements.');return false;} clearErr('password','password');return true; }

    [['name',vName],['email',vEmail],['phone',vPhone]].forEach(function(p){ var el=document.getElementById(p[0]); if(el) el.addEventListener('blur',p[1]); });
    if (pwdInput) pwdInput.addEventListener('blur', vPwd);

    // Submit
    var form = document.getElementById('regForm');
    var submitBtn = document.getElementById('submitBtn');
    if (form) {
        form.addEventListener('submit', function(e){
            var roleVal = (document.getElementById('role')||{}).value||'';
            if (!roleVal) { e.preventDefault(); alert('Please select your role to continue.'); return; }

            var ok = (vName()&vEmail()&vPhone()&vPwd()) === 1;

            // Confirm password
            var pwd=(pwdInput||{}).value||'', conf=(confirmInput||{}).value||'';
            if (!conf) { if(errConfirm){errConfirm.style.display='flex';var sp=errConfirm.querySelector('span');if(sp)sp.textContent='Please confirm your password.';} if(boxConfirm){boxConfirm.classList.add('is-error');boxConfirm.classList.remove('is-valid');} ok=false; }
            else if (conf!==pwd) { if(errConfirm){errConfirm.style.display='flex';var sp2=errConfirm.querySelector('span');if(sp2)sp2.textContent='Passwords do not match.';} if(boxConfirm){boxConfirm.classList.add('is-error');boxConfirm.classList.remove('is-valid');} ok=false; }

            // Owner-specific
            if (roleVal === 'admin') {
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
            if (submitBtn) { submitBtn.disabled=true; submitBtn.textContent='Creating account…'; }
        });
    }
}

@if($errors->any())
document.addEventListener('DOMContentLoaded', function(){ initFormLogic(); });
@endif
</script>

</body>
</html>
