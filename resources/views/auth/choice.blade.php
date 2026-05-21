<x-auth.shell
    title="Authentication | DSSC Boarding House System"
    form-title="Welcome Back"
    subtitle="Choose how you want to continue."
    panel-headline="Account Access"
    panel-description="Use your DSSC Boarding account to access tenant, owner, and system workspaces."
>
    <div class="grid gap-3">
        <a href="{{ route('login') }}" class="landing-card block p-5 transition hover:border-blue-200 hover:bg-blue-50/60">
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-sm font-black text-blue-700">IN</span>
                <span>
                    <span class="block font-black text-slate-950">Login</span>
                    <span class="block text-sm font-semibold text-slate-500">Access your existing account.</span>
                </span>
            </div>
            <span class="mt-4 inline-flex text-sm font-black text-blue-700">Continue to login</span>
        </a>

        @if (Route::has('register'))
            <a href="{{ route('register') }}" class="landing-card block p-5 transition hover:border-emerald-200 hover:bg-emerald-50/60">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-black text-emerald-700">UP</span>
                    <span>
                        <span class="block font-black text-slate-950">Create Account</span>
                        <span class="block text-sm font-semibold text-slate-500">Register as a tenant or owner.</span>
                    </span>
                </div>
                <span class="mt-4 inline-flex text-sm font-black text-emerald-700">Continue to register</span>
            </a>
        @endif

        @if (Route::has('register.owner'))
            <a href="{{ route('register.owner') }}" class="landing-card block p-5 transition hover:border-blue-200 hover:bg-blue-50/60">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-sm font-black text-blue-700">BH</span>
                    <span>
                        <span class="block font-black text-slate-950">Owner Registration</span>
                        <span class="block text-sm font-semibold text-slate-500">Submit your boarding house for OSAS review.</span>
                    </span>
                </div>
                <span class="mt-4 inline-flex text-sm font-black text-blue-700">Register as owner</span>
            </a>
        @endif
    </div>

    <div class="auth-footer-links">
        <p><a class="auth-secondary-link" href="{{ url('/') }}">Back to Homepage</a></p>
    </div>
</x-auth.shell>
