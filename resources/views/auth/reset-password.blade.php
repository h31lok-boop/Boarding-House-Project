<x-auth.shell
    title="Reset Password | DSSC Boarding House System"
    form-title="Reset Password"
    subtitle="Create a new password for your account."
    panel-headline="Set A New Password"
    panel-description="Create a secure password so you can continue using your DSSC Boarding account."
>
    @if ($errors->any())
        <div class="auth-alert mb-4">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}" data-auth-submit>
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="auth-field">
            <label for="email">Email Address</label>
            <div class="auth-input-wrap @error('email') is-invalid @enderror">
                <span class="shrink-0 text-slate-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6.5h16v11H4z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 8 7 5 7-5" />
                    </svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" placeholder="Enter your email address">
            </div>
            @error('email')
                <p class="auth-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password">New Password</label>
            <div class="auth-input-wrap @error('password') is-invalid @enderror">
                <span class="shrink-0 text-slate-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="5" y="10" width="14" height="10" rx="2" stroke-width="1.8" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10V8a4 4 0 1 1 8 0v2" />
                    </svg>
                </span>
                <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Create a new password">
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
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Re-enter your new password">
                <button type="button" class="auth-password-toggle" data-auth-password-toggle="password_confirmation">Show</button>
            </div>
            @error('password_confirmation')
                <p class="auth-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="auth-primary-button mt-5" data-auth-submit-button data-loading-text="Resetting password...">
            Reset Password
        </button>
    </form>

    <div class="auth-footer-links">
        <p><a class="auth-secondary-link" href="{{ route('login') }}">Back to Sign In</a></p>
        <p><a class="auth-secondary-link" href="{{ url('/') }}">Back to Homepage</a></p>
    </div>
</x-auth.shell>
