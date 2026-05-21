<x-auth.shell
    title="Forgot Password | DSSC Boarding House System"
    form-title="Forgot Password"
    subtitle="Enter your email address and we'll send you a password reset link."
    panel-headline="Recover Access"
    panel-description="Reset your DSSC Boarding account password and return to your boarding house workspace securely."
>
    @if (session('status'))
        <div class="auth-alert auth-alert-success mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" data-auth-submit>
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
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Enter your email address">
            </div>
            @error('email')
                <p class="auth-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="auth-primary-button mt-5" data-auth-submit-button data-loading-text="Sending reset link...">
            Send Reset Link
        </button>
    </form>

    <div class="auth-footer-links">
        <p><a class="auth-secondary-link" href="{{ route('login') }}">Back to Sign In</a></p>
        <p><a class="auth-secondary-link" href="{{ url('/') }}">Back to Homepage</a></p>
    </div>
</x-auth.shell>
