<x-auth.shell
    title="Verify Email | DSSC Boarding House System"
    form-title="Verify Email"
    subtitle="Before getting started, verify your email address using the link we sent you."
    panel-headline="Almost Ready"
    panel-description="Verify your DSSC Boarding account to keep your tenant or owner workspace secure."
>
    <div class="auth-alert auth-alert-success mb-4">
        Thanks for signing up. If you did not receive the email, you can request another verification link.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="auth-alert auth-alert-success mb-4">
            A new verification link has been sent to the email address you provided during registration.
        </div>
    @endif

    <div class="grid gap-3 sm:grid-cols-2">
        <form method="POST" action="{{ route('verification.send') }}" data-auth-submit>
            @csrf
            <button type="submit" class="auth-primary-button" data-auth-submit-button data-loading-text="Sending...">
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="auth-primary-button bg-slate-700 hover:bg-slate-800">
                Log Out
            </button>
        </form>
    </div>

    <div class="auth-footer-links">
        <p><a class="auth-secondary-link" href="{{ url('/') }}">Back to Homepage</a></p>
    </div>
</x-auth.shell>
