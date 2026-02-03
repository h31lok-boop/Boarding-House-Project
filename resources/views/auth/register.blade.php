{{-- resources/views/auth/register.blade.php --}}
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Create Account | StaySafe Finder</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=manrope:400,500,600,700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function () {
            const stored = localStorage.getItem('theme');
            if (stored) {
                document.documentElement.setAttribute('data-theme', stored);
            }
        })();
    </script>
    <style>
        :root {
            --primary: var(--brand-500);
            --primary-soft: rgba(255, 126, 95, 0.12);
            --text: var(--text);
            --muted: var(--muted);
            --border: var(--border);
            --bg: var(--bg);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Manrope', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at top, var(--surface) 0%, var(--bg) 55%, var(--surface-2) 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        .auth-card {
            width: 100%;
            max-width: 480px;
            background: var(--surface);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 20px 40px rgba(17, 24, 39, 0.08);
            position: relative;
        }

        .progress-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--surface-2);
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 999px;
        }

        .auth-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .auth-subtitle {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 24px;
        }

        .alert {
            background: #fff4f4;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .alert ul {
            padding-left: 18px;
        }

        .role-block {
            margin-bottom: 20px;
        }

        .role-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            margin-bottom: 10px;
        }

        .role-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .role-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .role-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 2px solid var(--border);
            padding: 16px 10px;
            border-radius: 16px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            transition: all 0.2s ease;
            background: var(--surface);
            text-align: center;
        }

        .role-card i {
            font-size: 20px;
            color: var(--muted);
        }

        .role-option input:checked + .role-card {
            border-color: var(--primary);
            background: var(--primary-soft);
            color: var(--primary);
        }

        .role-option input:checked + .role-card i {
            color: var(--primary);
        }

        .field {
            margin-bottom: 16px;
        }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
        }

        .input-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px 14px;
            background: var(--surface);
            transition: border 0.2s ease, box-shadow 0.2s ease;
        }

        .input-wrap:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 108, 255, 0.12);
        }

        .input-wrap i {
            color: #9ca3af;
        }

        .input-wrap input,
        .input-wrap select {
            border: none;
            outline: none;
            width: 100%;
            font-size: 14px;
            font-family: inherit;
            color: var(--text);
            background: transparent;
        }

        .input-wrap input::placeholder {
            color: #9ca3af;
        }

        .toggle-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            font-size: 14px;
        }

        .field-error {
            display: block;
            font-size: 12px;
            color: #b91c1c;
            margin-top: 6px;
        }

        .terms {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            font-size: 12px;
            color: var(--muted);
            margin: 18px 0 22px;
        }

        .terms a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 14px;
            background: var(--primary);
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(79, 108, 255, 0.25);
        }

        .signin-link {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            color: var(--muted);
        }

        .signin-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .tenant-only[hidden] {
            display: none;
        }

    </style>
</head>
<body>
    <div class="auth-card">
        <button type="button" class="theme-toggle" data-theme-toggle style="position:absolute; top:20px; left:20px;">
            <span>Theme:</span>
            <span data-theme-label>Light</span>
        </button>
        <span class="progress-badge">0%</span>
        <h1 class="auth-title">Create Account</h1>
        <p class="auth-subtitle">Sign up to start as a tenant.</p>

        @if ($errors->any())
            <div class="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            {{-- removed photo upload to streamline sign-up --}}

            <div class="field">
                <label for="name">Full Name</label>
                <div class="input-wrap">
                    <i class="fas fa-user"></i>
                    <input id="name" name="name" type="text" placeholder="Enter your full name" value="{{ old('name') }}" required>
                </div>
                @error('name')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input id="email" name="email" type="email" placeholder="Enter your email" value="{{ old('email') }}" required>
                </div>
                @error('email')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="phone">Phone Number</label>
                <div class="input-wrap">
                    <i class="fas fa-phone"></i>
                    <input id="phone" name="phone" type="tel" placeholder="+1 (555) 123-4567" value="{{ old('phone') }}" required>
                </div>
                @error('phone')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input id="password" name="password" type="password" placeholder="Create a strong password" required>
                    <button class="toggle-btn" type="button" data-toggle="password" data-target="password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @error('password')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field tenant-only" data-tenant-only>
                <label for="institution_name">Institution Name</label>
                <div class="input-wrap">
                    <i class="fas fa-graduation-cap"></i>
                    <input id="institution_name" name="institution_name" type="text" list="institution-list" placeholder="Select or enter your institution" value="{{ old('institution_name') }}" data-requires="tenant">
                    <datalist id="institution-list">
                        <option value="Greenfield University"></option>
                        <option value="Sunshine College"></option>
                        <option value="Metro Tech Institute"></option>
                        <option value="City Business School"></option>
                    </datalist>
                </div>
                @error('institution_name')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field tenant-only" data-tenant-only>
                <label for="move_in_date">Expected Move-in Date</label>
                <div class="input-wrap">
                    <i class="fas fa-calendar"></i>
                    <input id="move_in_date" name="move_in_date" type="date" value="{{ old('move_in_date') }}" data-requires="tenant">
                </div>
                @error('move_in_date')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <label class="terms">
                <input type="checkbox" name="terms" value="1" @checked(old('terms')) required>
                <span>
                    I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
                </span>
            </label>
            @error('terms')
                <span class="field-error">{{ $message }}</span>
            @enderror

            <button type="submit" class="submit-btn">Create Account</button>
        </form>

        <div class="signin-link">
            Already have an account? <a href="{{ route('login') }}">Sign In</a>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-toggle="password"]').forEach(button => {
            button.addEventListener('click', () => {
                const targetId = button.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (!input) {
                    return;
                }
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                button.innerHTML = isPassword ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
            });
        });
    </script>
</body>
</html>
