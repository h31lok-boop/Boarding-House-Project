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
            justify-content: center;
            padding: 28px 16px;
        }

        .reg-card {
            width: 100%; max-width: 420px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(26,18,15,.10);
            overflow: hidden;
        }

        .reg-inner { padding: 32px 28px 26px; }
        @media (max-width: 480px) { .reg-inner { padding: 24px 18px 22px; } }

        /* Logo */
        .reg-logo { display:flex; align-items:center; justify-content:center; gap:10px; margin-bottom:22px; }
        .reg-logo-icon {
            height:40px; width:40px; border-radius:12px;
            background: linear-gradient(135deg, #ff7e5f, #feb47b);
            display:flex; align-items:center; justify-content:center;
            box-shadow: 0 6px 16px rgba(255,126,95,.32);
        }
        .reg-logo-icon svg { width:20px; height:20px; color:#fff; }
        .reg-logo-name { font-size:18px; font-weight:800; color:var(--text); }

        /* Headings */
        .step-head { text-align:center; margin-bottom:24px; }
        .step-head h1 { font-size:20px; font-weight:700; color:var(--text); margin-bottom:4px; }
        .step-head p  { font-size:13px; color:var(--muted); line-height:1.5; }

        /* Google button */
        .btn-google {
            width:100%; display:flex; align-items:center; justify-content:center; gap:10px;
            padding:12px 16px;
            background:var(--surface); border:1.5px solid var(--border);
            border-radius:12px; font-size:14px; font-weight:600; color:var(--text);
            cursor:pointer; transition:background .18s, border-color .18s, box-shadow .18s;
            font-family:inherit;
        }
        .btn-google:hover { background:var(--surface-2); border-color:#9ca3af; box-shadow:0 2px 8px rgba(0,0,0,.06); }
        .btn-google svg { width:20px; height:20px; flex-shrink:0; }

        /* OR divider */
        .or-divider {
            display:flex; align-items:center; gap:12px;
            margin:14px 0; font-size:12px; color:var(--muted); font-weight:600;
        }
        .or-divider::before, .or-divider::after { content:''; flex:1; height:1px; background:var(--border); }

        /* Primary button */
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

        /* Google unavailable notice */
        .google-notice {
            background:rgba(251,191,36,.08); border:1px solid rgba(251,191,36,.36);
            border-radius:12px; padding:12px 14px;
            display:flex; align-items:flex-start; gap:9px;
            font-size:12.5px; color:#92400e; margin-bottom:16px; line-height:1.5;
        }
        .google-notice svg { width:16px; height:16px; flex-shrink:0; margin-top:1px; color:#d97706; }

        /* Back button */
        .back-btn {
            display:inline-flex; align-items:center; gap:5px;
            background:none; border:none; cursor:pointer;
            font-size:13px; color:var(--muted); font-family:inherit;
            padding:0; margin-bottom:20px; transition:color .15s;
        }
        .back-btn:hover { color:var(--text); }
        .back-btn svg { width:14px; height:14px; }

        /* Server error */
        .reg-alert {
            background:rgba(239,68,68,.06); border:1px solid rgba(239,68,68,.28);
            color:#b91c1c; padding:12px 14px; border-radius:12px;
            font-size:13px; margin-bottom:16px; line-height:1.55;
        }
        .reg-alert ul { padding-left:16px; }

        /* Fields */
        .field { margin-bottom:13px; }
        .field-label { display:block; font-size:12px; font-weight:600; color:var(--text); margin-bottom:6px; }
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
        .input-box input { border:none; outline:none; width:100%; font-size:13.5px; font-family:inherit; color:var(--text); background:transparent; }
        .input-box input::placeholder { color:#9ca3af; }
        .eye-btn { background:none; border:none; cursor:pointer; color:#9ca3af; padding:0; display:flex; align-items:center; flex-shrink:0; transition:color .15s; }
        .eye-btn:hover { color:#6b7280; }
        .eye-btn svg { width:16px; height:16px; }
        .field-error { font-size:11.5px; color:#dc2626; margin-top:5px; display:flex; align-items:flex-start; gap:4px; line-height:1.45; }
        .field-error svg { width:12px; height:12px; flex-shrink:0; margin-top:1px; }

        /* Password criteria */
        .pwd-criteria { margin-top:9px; display:grid; grid-template-columns:1fr 1fr; gap:4px 10px; }
        .pwd-criterion { display:flex; align-items:center; gap:5px; font-size:11.5px; color:var(--muted); transition:color .2s; }
        .pwd-criterion .dot { width:14px; height:14px; border-radius:50%; border:1.5px solid #d1d5db; flex-shrink:0; display:flex; align-items:center; justify-content:center; transition:background .2s, border-color .2s; }
        .pwd-criterion.met .dot { background:#34d399; border-color:#34d399; }
        .pwd-criterion.met { color:#047857; }
        .pwd-criterion .dot svg { width:9px; height:9px; color:#fff; display:none; }
        .pwd-criterion.met .dot svg { display:block; }

        /* Strength bar */
        .strength-bar-wrap { margin-top:8px; }
        .strength-bars { display:flex; gap:3px; height:3px; margin-bottom:4px; }
        .strength-bar { flex:1; border-radius:999px; background:var(--border); transition:background .25s; }
        .strength-text { font-size:11px; color:var(--muted); transition:color .25s; }

        /* Account type */
        .acct-label { font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--muted); margin-bottom:8px; }
        .acct-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:4px; }
        .acct-option { position:relative; }
        .acct-option input[type="radio"] { position:absolute; inset:0; opacity:0; width:100%; height:100%; cursor:pointer; margin:0; }
        .acct-card { display:flex; align-items:center; gap:10px; padding:11px 13px; border:2px solid var(--border); border-radius:13px; background:var(--surface); cursor:pointer; transition:border-color .17s, background .17s, box-shadow .17s; }
        .acct-card-icon { width:32px; height:32px; border-radius:9px; background:var(--surface-2); display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:background .17s; }
        .acct-card-icon svg { width:16px; height:16px; color:var(--muted); transition:color .17s; }
        .acct-card-body { min-width:0; }
        .acct-card-title { font-size:13px; font-weight:700; color:var(--muted); transition:color .17s; }
        .acct-card-sub { font-size:11px; color:var(--muted); opacity:.65; line-height:1.3; margin-top:1px; }
        .acct-option input[type="radio"]:checked ~ .acct-card { border-color:var(--brand-500); background:rgba(255,126,95,.06); box-shadow:0 0 0 3px rgba(255,126,95,.14); }
        .acct-option input[type="radio"]:checked ~ .acct-card .acct-card-icon { background:rgba(255,126,95,.14); }
        .acct-option input[type="radio"]:checked ~ .acct-card .acct-card-icon svg { color:var(--brand-500); }
        .acct-option input[type="radio"]:checked ~ .acct-card .acct-card-title { color:var(--brand-600); }

        /* Invite notice */
        .invite-notice { background:rgba(251,191,36,.08); border:1px solid rgba(251,191,36,.4); border-radius:12px; padding:11px 13px; font-size:12px; color:#92400e; display:flex; gap:9px; align-items:flex-start; margin-bottom:10px; }
        .invite-notice svg { width:15px; height:15px; flex-shrink:0; margin-top:1px; color:#d97706; }

        /* Terms */
        .terms-row { display:flex; align-items:flex-start; gap:9px; margin:14px 0 16px; }
        .terms-row input[type="checkbox"] { width:15px; height:15px; accent-color:var(--brand-500); flex-shrink:0; margin-top:2px; cursor:pointer; }
        .terms-text { font-size:12.5px; color:var(--muted); line-height:1.5; }
        .terms-text a { color:var(--brand-600); text-decoration:none; font-weight:600; }
        .terms-text a:hover { text-decoration:underline; }

        /* Divider */
        .reg-divider { height:1px; background:var(--border); margin:14px 0; }

        /* Sign in link */
        .signin-link { text-align:center; margin-top:18px; font-size:13px; color:var(--muted); }
        .signin-link a { color:var(--brand-600); text-decoration:none; font-weight:600; }
        .signin-link a:hover { text-decoration:underline; }

        [x-cloak] { display:none !important; }

        @media (max-width:480px) { .pwd-criteria { grid-template-columns:1fr; } }
    </style>
</head>
<body>

<div class="reg-card"
     x-data="regApp()"
     x-init="init()">

    {{-- ══════════════════════════════════════════════ --}}
    {{-- STEP 1 — Choose sign-up method                --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div x-show="step === 1"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="reg-inner">

        <div class="reg-logo">
            <div class="reg-logo-icon">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
            </div>
            <span class="reg-logo-name">BoardMatch</span>
        </div>

        <div class="step-head">
            <h1>Create your account</h1>
            <p>Join thousands of students finding their perfect boarding house match.</p>
        </div>

        {{-- Google unavailable notice --}}
        <div x-show="googleNotice" x-cloak class="google-notice">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Google sign-in is not configured on this server. Please use email to create your account.</span>
        </div>

        {{-- Continue with Google --}}
        <button type="button" class="btn-google" @click="googleNotice = true">
            <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                <path fill="none" d="M0 0h48v48H0z"/>
            </svg>
            Continue with Google
        </button>

        <div class="or-divider">or</div>

        {{-- Continue with Email --}}
        <button type="button" class="btn-primary-reg" @click="step = 2; googleNotice = false">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Continue with Email
        </button>

        <div class="signin-link">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- STEP 2 — Email input                          --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div x-show="step === 2" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="reg-inner">

        <button type="button" class="back-btn" @click="step = 1">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Back
        </button>

        <div class="reg-logo">
            <div class="reg-logo-icon">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
            </div>
            <span class="reg-logo-name">BoardMatch</span>
        </div>

        <div class="step-head">
            <h1>What's your email?</h1>
            <p>We'll use this as your BoardMatch account email.</p>
        </div>

        <div class="field">
            <label class="field-label" for="step2Email">Email Address</label>
            <div class="input-box" :class="{ 'is-error': emailErr, 'is-valid': emailOk }">
                <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                </svg>
                <input id="step2Email"
                       type="email"
                       placeholder="you@example.com"
                       x-model="email"
                       @keydown.enter.prevent="goToStep3()"
                       @blur="liveEmail()"
                       autocomplete="email"
                       autofocus>
            </div>
            <p class="field-error" x-show="emailErr" x-cloak>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-text="emailErr"></span>
            </p>
        </div>

        <button type="button" class="btn-primary-reg" @click="goToStep3()">
            Continue
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>

        <p style="text-align:center;font-size:11.5px;color:var(--muted);margin-top:14px;line-height:1.5;">
            By continuing you agree to BoardMatch's
            <a href="#" style="color:var(--brand-600);font-weight:600;">Terms</a> and
            <a href="#" style="color:var(--brand-600);font-weight:600;">Privacy Policy</a>.
        </p>

        <div class="signin-link">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- STEP 3 — Full registration form               --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div x-show="step === 3" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="reg-inner">

        <button type="button" class="back-btn"
                @click="step = {{ $errors->any() ? 1 : 2 }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Back
        </button>

        <div class="reg-logo" style="margin-bottom:14px">
            <div class="reg-logo-icon">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
            </div>
            <span class="reg-logo-name">BoardMatch</span>
        </div>

        <div class="step-head" style="margin-bottom:18px">
            <h1>Complete your profile</h1>
            <p>Fill in your details to finish creating your account.</p>
        </div>

        @if ($errors->any())
            <div class="reg-alert">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @if (session('status'))
            <div style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.3);color:#065f46;padding:12px 14px;border-radius:12px;font-size:13px;margin-bottom:14px;">
                {{ session('status') }}
            </div>
        @endif

        <form id="regForm" method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            @php $selectedRole = old('role', 'user'); @endphp

            {{-- Account Type --}}
            <p class="acct-label">I am a…</p>
            <div class="acct-grid" style="margin-bottom:12px">
                <label class="acct-option">
                    <input type="radio" name="role" value="user" id="roleUser" @checked($selectedRole === 'user')>
                    <div class="acct-card">
                        <div class="acct-card-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div class="acct-card-body">
                            <div class="acct-card-title">Student</div>
                            <div class="acct-card-sub">Tenant / Boarder</div>
                        </div>
                    </div>
                </label>
                <label class="acct-option">
                    <input type="radio" name="role" value="admin" id="roleAdmin" @checked($selectedRole === 'admin')>
                    <div class="acct-card">
                        <div class="acct-card-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        </div>
                        <div class="acct-card-body">
                            <div class="acct-card-title">Owner</div>
                            <div class="acct-card-sub">House Manager</div>
                        </div>
                    </div>
                </label>
            </div>
            @error('role')<p class="field-error" style="margin-bottom:10px">{{ $message }}</p>@enderror

            <div class="reg-divider"></div>

            {{-- Full Name --}}
            <div class="field">
                <label class="field-label" for="name">Full Name</label>
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
                <label class="field-label" for="email">Email Address</label>
                <div class="input-box{{ $errors->has('email') ? ' is-error' : '' }}" id="box-email">
                    <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                    <input id="email" name="email" type="email" placeholder="you@example.com"
                           :value="email" x-on:input="email = $event.target.value"
                           autocomplete="email">
                </div>
                <p class="field-error" id="err-email" style="{{ $errors->has('email') ? '' : 'display:none' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $errors->first('email') }}</span>
                </p>
            </div>

            {{-- Phone --}}
            <div class="field">
                <label class="field-label" for="phone">Phone Number</label>
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
                <label class="field-label" for="password">Password</label>
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
                <label class="field-label" for="password_confirmation">Confirm Password</label>
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

            {{-- Admin invite code --}}
            <div id="inviteSection" style="display:none">
                <div class="invite-notice">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Admin accounts require an <strong>invite code</strong> from the system administrator. Each code is single-use.</span>
                </div>
                <div class="field">
                    <label class="field-label" for="invite_code">Admin Invite Code</label>
                    <div class="input-box{{ $errors->has('invite_code') ? ' is-error' : '' }}" id="box-invite">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                        <input id="invite_code" name="invite_code" type="text" placeholder="XXXX-XXXX-XXXX" value="{{ old('invite_code') }}" autocomplete="off" style="text-transform:uppercase;letter-spacing:.08em">
                    </div>
                    <p class="field-error" id="err-invite" style="{{ $errors->has('invite_code') ? '' : 'display:none' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $errors->first('invite_code') }}</span>
                    </p>
                </div>
            </div>

            {{-- Terms --}}
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
                Create Account
            </button>
        </form>

        <div class="signin-link">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
        </div>
    </div>

</div>{{-- /reg-card --}}

<script>
function regApp() {
    return {
        step: {{ $errors->any() ? 3 : 1 }},
        email: '{{ addslashes(old('email', '')) }}',
        googleNotice: false,
        emailErr: '',
        emailOk: false,

        init() {
            var self = this;
            this.$nextTick(function () { initFormLogic(self); });
        },

        liveEmail() {
            var v = this.email.trim();
            if (!v)                                         { this.emailErr = 'Email address is required.'; this.emailOk = false; return false; }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v))    { this.emailErr = 'Enter a valid email address.'; this.emailOk = false; return false; }
            this.emailErr = ''; this.emailOk = true; return true;
        },

        goToStep3() {
            if (!this.liveEmail()) return;
            this.step = 3;
            var self = this;
            this.$nextTick(function () {
                var nameEl = document.getElementById('name');
                if (nameEl) nameEl.focus();
                initFormLogic(self);
            });
        }
    };
}

function initFormLogic(alpine) {
    // Eye toggles
    var EYE_OPEN  = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>';
    var EYE_SLASH = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>';

    function setupEye(btnId, inputId, iconId) {
        var btn = document.getElementById(btnId);
        var inp = document.getElementById(inputId);
        var ico = document.getElementById(iconId);
        if (!btn || !inp) return;
        btn.addEventListener('click', function () {
            var shown = inp.type === 'text';
            inp.type = shown ? 'password' : 'text';
            if (ico) ico.innerHTML = shown ? EYE_OPEN : EYE_SLASH;
        });
    }
    setupEye('togglePwd', 'password', 'eyePwd');
    setupEye('toggleConfirm', 'password_confirmation', 'eyeConfirm');

    // Password criteria + strength
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
    var levels = [{label:'',color:'#d1d5db'},{label:'Too weak',color:'#f87171'},{label:'Weak',color:'#fb923c'},{label:'Fair',color:'#facc15'},{label:'Good',color:'#34d399'},{label:'Strong 🔒',color:'#10b981'}];

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

    // Role → invite code
    var roleInputs = document.querySelectorAll('input[name="role"]');
    var inviteSection = document.getElementById('inviteSection');
    var inviteInput = document.getElementById('invite_code');
    function syncRole() {
        var isAdmin = (document.querySelector('input[name="role"]:checked')||{}).value === 'admin';
        if (inviteSection) inviteSection.style.display = isAdmin ? 'block' : 'none';
        if (inviteInput) inviteInput.required = isAdmin;
    }
    roleInputs.forEach(function(r){ r.addEventListener('change', syncRole); });
    syncRole();

    // Invite code auto-format
    if (inviteInput) {
        inviteInput.addEventListener('input', function(){
            var c = inviteInput.value.replace(/[^A-Za-z0-9]/g,'').toUpperCase(), parts=[];
            for(var i=0;i<c.length;i+=4) parts.push(c.slice(i,i+4));
            inviteInput.value = parts.join('-').slice(0,14);
        });
    }

    // Confirm password
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

    // Validation helpers
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
    function vEmail()   { var v=(document.getElementById('email')||{}).value||''; if(!v.trim()){showErr('email','email','Email is required.');return false;} if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)){showErr('email','email','Enter a valid email.');return false;} clearErr('email','email');return true; }
    function vPhone()   { var v=((document.getElementById('phone')||{}).value||'').replace(/\s/g,''); if(!v){showErr('phone','phone','Phone number is required.');return false;} if(!/^(\+63|0)9\d{9}$/.test(v)){showErr('phone','phone','Valid PH number required (09XX XXX XXXX).');return false;} clearErr('phone','phone');return true; }
    function vPwd()     { var v=(pwdInput||{}).value||'', m=updateCriteria(v); if(!v){showErr('password','password','Password is required.');return false;} if(m<5){showErr('password','password','Password must meet all 5 requirements.');return false;} clearErr('password','password');return true; }
    function vInvite()  { var role=(document.querySelector('input[name="role"]:checked')||{}).value||'user'; if(role!=='admin') return true; var v=(document.getElementById('invite_code')||{}).value||''; if(!v.trim()){showErr('invite','invite','Invite code is required.');return false;} if(v.replace(/-/g,'').length<6){showErr('invite','invite','Enter a valid invite code.');return false;} clearErr('invite','invite');return true; }

    [['name',vName],['email',vEmail],['phone',vPhone]].forEach(function(p){ var el=document.getElementById(p[0]); if(el) el.addEventListener('blur',p[1]); });
    if (pwdInput) pwdInput.addEventListener('blur', vPwd);

    // Form submit
    var form = document.getElementById('regForm');
    var submitBtn = document.getElementById('submitBtn');
    if (form) {
        form.addEventListener('submit', function(e){
            var ok = (vName()&vEmail()&vPhone()&vPwd()&vInvite()) === 1;
            var pwd=(pwdInput||{}).value||'', conf=(confirmInput||{}).value||'';
            if (!conf) { if(errConfirm){errConfirm.style.display='flex';var sp=errConfirm.querySelector('span');if(sp)sp.textContent='Please confirm your password.';} if(boxConfirm){boxConfirm.classList.add('is-error');boxConfirm.classList.remove('is-valid');} ok=false; }
            else if (conf!==pwd) { if(errConfirm){errConfirm.style.display='flex';var sp2=errConfirm.querySelector('span');if(sp2)sp2.textContent='Passwords do not match.';} if(boxConfirm){boxConfirm.classList.add('is-error');boxConfirm.classList.remove('is-valid');} ok=false; }
            var termsEl=document.getElementById('terms'), errTerms=document.getElementById('err-terms');
            if (termsEl&&!termsEl.checked) { if(errTerms) errTerms.style.display='flex'; ok=false; }
            else if(errTerms) errTerms.style.display='none';
            if (!ok) { e.preventDefault(); return; }
            if (submitBtn) { submitBtn.disabled=true; submitBtn.textContent='Creating account…'; }
        });
    }
}

// If returning from server with errors, form is shown immediately — init directly
@if($errors->any())
document.addEventListener('DOMContentLoaded', function(){ initFormLogic(null); });
@endif
</script>

</body>
</html>
