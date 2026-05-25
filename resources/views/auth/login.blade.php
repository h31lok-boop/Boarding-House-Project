<x-auth.shell
    title="Sign In | DSSC Boarding House System"
    form-title="Sign In"
    subtitle="Access your DSSC Boarding account to manage your listings, rooms, and inquiries."
    panel-headline="Welcome Back"
    panel-description="Access your DSSC Boarding account to manage listings, rooms, inquiries, reservations, and compliance."
>
    @if (session('status'))
        <div class="auth-alert auth-alert-success mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" data-auth-submit data-login-form>
        @csrf

        <div class="auth-field">
            <label for="email">Email Address</label>
            <div class="auth-input-wrap @error('email') is-invalid @enderror">
                <span class="shrink-0 text-slate-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6.5h16v11H4z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 8 7 5 7-5" />
                    </svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="Enter your email">
            </div>
            @error('email')
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
                <input id="password" type="password" name="password" required minlength="8" autocomplete="current-password" placeholder="Enter your password">
                <button type="button" class="auth-password-toggle" data-auth-password-toggle="password">Show</button>
            </div>
            @error('password')
                <p class="auth-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-field">
            <label for="security_answer">Security Check</label>
            <p class="mb-2 text-sm font-medium text-slate-500">{{ $securityQuestion ?? 'Answer the security question.' }}</p>
            <div class="auth-input-wrap @error('security_answer') is-invalid @enderror">
                <span class="shrink-0 text-slate-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3.75 19 6v5.25c0 4.2-2.8 7.7-7 9-4.2-1.3-7-4.8-7-9V6l7-2.25Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9 12 2 2 4-5" />
                    </svg>
                </span>
                <input id="security_answer" type="text" name="security_answer" required inputmode="numeric" autocomplete="off" placeholder="Enter your answer">
            </div>
            @error('security_answer')
                <p class="auth-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-3 text-right text-sm">
            @if (Route::has('password.request'))
                <a class="auth-secondary-link auth-small-link" href="{{ route('password.request') }}">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="auth-primary-button mt-5" data-auth-submit-button data-loading-text="Signing in...">
            Sign In
        </button>
    </form>

    <div class="auth-footer-links">
        @if (Route::has('register'))
            <p>Need an account? <a class="auth-secondary-link" href="{{ route('register') }}">Register here</a></p>
        @endif
        <p><a class="auth-secondary-link" href="{{ url('/') }}">Back to Homepage</a></p>
    </div>
</x-auth.shell>
