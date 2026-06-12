@props(['twoFactorEnabled' => false])

<article class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm shadow-slate-900/5">
    <h2 class="text-lg font-bold text-slate-950">Security</h2>
    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <button
            type="button"
            @click="passwordOpen = true"
            class="flex items-center gap-4 rounded-2xl border border-transparent bg-white p-4 text-left transition hover:border-blue-100 hover:bg-blue-50"
        >
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 10V8a5 5 0 0 1 10 0v2M6 10h12v10H6z"/>
                </svg>
            </span>
            <span class="min-w-0 flex-1">
                <span class="block font-bold text-slate-900">Change Password</span>
                <span class="mt-1 block text-sm text-slate-500">Update your password regularly.</span>
            </span>
            <svg class="h-5 w-5 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/>
            </svg>
        </button>

        <form method="POST" action="{{ route('admin.settings.two-factor.update') }}" class="flex items-center gap-4 rounded-2xl border border-transparent bg-white p-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="two_factor_enabled" value="0">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 22s-8-4-8-10V5l8-3 8 3v7c0 6-8 10-8 10Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4"/>
                </svg>
            </span>
            <span class="min-w-0 flex-1">
                <span class="block font-bold text-slate-900">Two-Factor Authentication</span>
                <span class="mt-1 block text-sm text-slate-500">Add an extra layer of security.</span>
            </span>
            <label class="relative inline-flex cursor-pointer items-center">
                <input
                    type="checkbox"
                    name="two_factor_enabled"
                    value="1"
                    @checked($twoFactorEnabled)
                    onchange="this.form.submit()"
                    class="peer sr-only"
                >
                <span class="h-7 w-12 rounded-full bg-slate-200 transition peer-checked:bg-blue-600"></span>
                <span class="absolute left-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
            </label>
        </form>

        <button
            type="button"
            @click="sessionOpen = true"
            class="flex items-center gap-4 rounded-2xl border border-transparent bg-white p-4 text-left transition hover:border-blue-100 hover:bg-blue-50"
        >
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <rect x="4" y="5" width="16" height="12" rx="2" stroke-width="1.8"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 21h6M12 17v4"/>
                </svg>
            </span>
            <span class="min-w-0 flex-1">
                <span class="block font-bold text-slate-900">Active Session</span>
                <span class="mt-1 block text-sm text-slate-500">1 active session</span>
            </span>
            <svg class="h-5 w-5 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/>
            </svg>
        </button>
    </div>
</article>
