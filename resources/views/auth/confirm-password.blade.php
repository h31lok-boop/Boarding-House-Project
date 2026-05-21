<x-auth.shell
    title="Confirm Password | DSSC Boarding House System"
    form-title="Confirm Password"
    subtitle="This is a secure area. Please confirm your password before continuing."
    panel-headline="Secure Confirmation"
    panel-description="DSSC Boarding protects sensitive account actions by confirming your credentials first."
>
    <form method="POST" action="{{ route('password.confirm') }}" data-auth-submit>
        @csrf

        <div class="auth-field">
            <label for="password">Password</label>
            <div class="auth-input-wrap @error('password') is-invalid @enderror">
                <span class="shrink-0 text-slate-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="5" y="10" width="14" height="10" rx="2" stroke-width="1.8" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10V8a4 4 0 1 1 8 0v2" />
                    </svg>
                </span>
                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
                <button type="button" class="auth-password-toggle" data-auth-password-toggle="password">Show</button>
            </div>
            @error('password')
                <p class="auth-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="auth-primary-button mt-5" data-auth-submit-button data-loading-text="Confirming...">
            Confirm
        </button>
    </form>

    <div class="auth-footer-links">
        <p><a class="auth-secondary-link" href="{{ url('/') }}">Back to Homepage</a></p>
    </div>
</x-auth.shell>
