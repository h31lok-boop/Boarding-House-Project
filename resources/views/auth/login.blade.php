{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | BoardMatch</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function () {
            const t = localStorage.getItem('theme');
            if (t) document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: radial-gradient(circle at top, var(--surface) 0%, var(--bg) 60%, var(--surface-2) 100%);
            font-family: 'Manrope', 'Segoe UI', sans-serif;
        }

        .login-wrap {
            width: 100%;
            max-width: 400px;
        }

        /* ── Brand mark ── */
        .brand-mark {
            width: 56px; height: 56px;
            border-radius: 16px;
            display: block;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.28);
            margin: 0 auto 1rem;
        }

        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ── Card ── */
        .login-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem 2rem 1.75rem;
            box-shadow: 0 10px 40px rgba(26, 18, 15, 0.08);
        }

        /* ── Labels ── */
        .field-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.45rem;
        }

        /* ── Inputs ── */
        .field-wrap { position: relative; }

        .field-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px; height: 16px;
            color: #9ca3af;
            pointer-events: none;
        }

        .field-input {
            width: 100%;
            padding: 0.7rem 0.875rem 0.7rem 2.5rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            background: var(--surface);
            color: var(--text);
            font-size: 0.9rem;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
            box-sizing: border-box;
        }

        .field-input:focus {
            border-color: var(--brand-500);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
        }

        .field-input::placeholder { color: #9ca3af; }

        /* Password show/hide toggle */
        .pw-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            padding: 2px;
            color: #9ca3af;
            display: flex; align-items: center;
        }
        .pw-toggle:hover { color: var(--brand-500); }

        /* ── Google button ── */
        .btn-google {
            width: 100%;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            padding: 0.72rem 1rem;
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.9rem; font-weight: 600;
            color: var(--text);
            cursor: pointer;
            transition: background .15s, border-color .15s, box-shadow .15s;
            text-decoration: none;
        }
        .btn-google:hover {
            background: var(--surface-2);
            border-color: #9ca3af;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }
        .btn-google svg { flex-shrink: 0; }

        /* ── OR divider ── */
        .or-row {
            display: flex; align-items: center; gap: 10px;
            margin: 1rem 0; font-size: 0.75rem;
            font-weight: 600; color: #9ca3af;
        }
        .or-row::before, .or-row::after {
            content: ''; flex: 1; height: 1px; background: var(--border);
        }

        /* ── Sign In button ── */
        .btn-signin {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--brand-500), var(--accent-500));
            color: #fff;
            font-size: 0.9375rem;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .15s, transform .15s, box-shadow .15s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-signin:hover {
            opacity: .9;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.28);
        }
        .btn-signin:active { transform: translateY(0); }
        .btn-signin:disabled { opacity: .65; cursor: not-allowed; transform: none; }

        /* ── Spinner ── */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner {
            width: 16px; height: 16px;
            border: 2.5px solid rgba(255,255,255,.35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            display: none;
        }
        .btn-signin.loading .spinner { display: block; }
        .btn-signin.loading .btn-text { display: none; }

        /* ── Alert ── */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 0.8125rem;
            margin-bottom: 1.25rem;
            display: flex; align-items: flex-start; gap: 8px;
        }
        .alert-error {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #be123c;
        }
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }

        /* ── Divider ── */
        .divider {
            height: 1px;
            background: var(--border);
            margin: 1.5rem 0;
        }

        /* ── Links ── */
        a.brand-link { color: var(--brand-500); text-decoration: none; font-weight: 600; }
        a.brand-link:hover { text-decoration: underline; }
        a.muted-link { color: #9ca3af; text-decoration: none; font-size: 0.8125rem; }
        a.muted-link:hover { color: var(--text); }
    </style>
</head>
<body>

    <div class="login-wrap">

        {{-- ── Brand ── --}}
        <div style="text-align:center; margin-bottom:1.75rem">
            <div class="brand-mark">
                <img src="{{ asset('images/boardmatch-mark.svg') }}" alt="BoardMatch">
            </div>
            <h1 style="font-size:1.5rem; font-weight:800; color:var(--text); margin:0 0 .25rem">BoardMatch</h1>
            <p style="font-size:0.875rem; color:var(--muted); margin:0">Sign in to your account</p>
        </div>

        {{-- ── Card ── --}}
        <div class="login-card">

            {{-- Session status --}}
            @if (session('status'))
                <div class="alert alert-success">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="alert alert-error">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                {{-- Email / Username --}}
                <div style="margin-bottom:1rem">
                    <label for="email" class="field-label">Email or Username</label>
                    <div class="field-wrap">
                        <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <input id="email"
                               class="field-input"
                               type="text"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               autocomplete="username"
                               placeholder="owner, student, or you@example.com">
                    </div>
                </div>

                {{-- Password --}}
                <div style="margin-bottom:1rem">
                    <label for="password" class="field-label">Password</label>
                    <div class="field-wrap">
                        <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        <input id="password"
                               class="field-input"
                               type="password"
                               name="password"
                               required
                               autocomplete="current-password"
                               placeholder="••••••••"
                               style="padding-right:2.5rem">
                        <button type="button" class="pw-toggle" id="pwToggle" aria-label="Toggle password visibility">
                            {{-- Eye icon (show) --}}
                            <svg id="eyeShow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            {{-- Eye-off icon (hide) --}}
                            <svg id="eyeHide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Remember + Forgot --}}
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem">
                    <label style="display:flex; align-items:center; gap:7px; cursor:pointer; font-size:0.8375rem; color:var(--text)">
                        <input type="checkbox"
                               name="remember"
                               id="remember"
                               style="width:15px; height:15px; border-radius:4px; accent-color:var(--brand-500); cursor:pointer">
                        Remember me
                    </label>
                    @if (Route::has('password.request'))
                        <a class="brand-link" href="{{ route('password.request') }}" style="font-size:0.8375rem">
                            Forgot password?
                        </a>
                    @endif
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-signin" id="submitBtn">
                    <span class="spinner"></span>
                    <span class="btn-text" style="display:flex;align-items:center;gap:7px">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                        Sign In
                    </span>
                </button>
            </form>

            {{-- ── OR divider ── --}}
            <div class="or-row" style="margin-top:1.25rem">or</div>

            {{-- ── Continue with Google ── --}}
            @php $googleConfigured = filled(config('services.google.client_id')); @endphp

            @if ($googleConfigured)
                <a href="{{ route('auth.google') }}" class="btn-google">
                    <svg width="20" height="20" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    Continue with Google
                </a>
            @else
                {{-- Show a disabled button when credentials are not yet configured --}}
                <button type="button" class="btn-google"
                        style="opacity:.5; cursor:not-allowed"
                        title="Google sign-in is not configured on this server"
                        onclick="alert('Google sign-in is not configured yet. Please contact the administrator.')">
                    <svg width="20" height="20" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    Continue with Google
                </button>
            @endif

            <div class="divider" style="margin-top:1.25rem"></div>

            {{-- Register --}}
            <p style="text-align:center; font-size:0.875rem; color:var(--muted); margin:0">
                Don't have an account?
                <a class="brand-link" href="{{ route('register') }}" style="margin-left:3px">Create one</a>
            </p>

        </div>

        {{-- Back link --}}
        <p style="text-align:center; margin-top:1.25rem">
            <a class="muted-link" href="{{ url('/') }}">
                ← Back to Homepage
            </a>
        </p>

    </div>

    <script>
        // Password show/hide toggle
        const pwInput  = document.getElementById('password');
        const pwToggle = document.getElementById('pwToggle');
        const eyeShow  = document.getElementById('eyeShow');
        const eyeHide  = document.getElementById('eyeHide');

        pwToggle.addEventListener('click', function () {
            const isPassword = pwInput.type === 'password';
            pwInput.type     = isPassword ? 'text' : 'password';
            eyeShow.style.display = isPassword ? 'none'  : 'block';
            eyeHide.style.display = isPassword ? 'block' : 'none';
        });

        // Loading state on submit
        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            btn.disabled = true;
        });
    </script>

</body>
</html>
