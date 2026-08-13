<x-layouts.dashboard>
<x-admin.shell>
<div class="space-y-5">
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400">Payment configuration</p>
        <h1 class="mt-1 text-2xl font-black text-slate-950 dark:text-white">PayMongo settings</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">One secure PayMongo Hosted Checkout configuration is used by every boarding house owner.</p>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-400/20 dark:bg-red-400/10 dark:text-red-200">
            <p class="font-bold">The PayMongo settings could not be saved.</p>
            <ul class="mt-1 list-disc space-y-0.5 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900 dark:border-blue-400/20 dark:bg-blue-400/10 dark:text-blue-100">
        <p class="font-bold">Webhook URL</p>
        <div class="mt-2 flex flex-wrap items-center gap-2">
            <code class="min-w-0 break-all rounded-lg bg-white/80 px-3 py-2 text-xs text-blue-800 ring-1 ring-blue-200 dark:bg-slate-950/50 dark:text-blue-200 dark:ring-blue-400/20">{{ route('paymongo.webhook') }}</code>
            <span class="text-xs">Subscribe it to <strong>checkout_session.payment.paid</strong> in PayMongo.</span>
        </div>
    </div>

    @if ($usesSharedPaymongo)
        <section class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm dark:border-emerald-400/20 dark:bg-slate-900">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </span>
                        <div>
                            <h2 class="font-bold text-slate-950 dark:text-white">Shared gateway is active</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Credentials are loaded securely from the server environment.</p>
                        </div>
                    </div>
                </div>
                <span class="w-fit rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">Checkout ready</span>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Public key</p>
                    <p class="mt-1 text-sm font-bold text-emerald-700 dark:text-emerald-300">Configured</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Secret key</p>
                    <p class="mt-1 text-sm font-bold text-emerald-700 dark:text-emerald-300">Configured</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Webhook signing</p>
                    <p class="mt-1 text-sm font-bold {{ filled($sharedCredentials['webhook_secret']) ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}">{{ filled($sharedCredentials['webhook_secret']) ? 'Configured' : 'Optional setup pending' }}</p>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-700">
                <h2 class="font-bold text-slate-950 dark:text-white">Owner coverage</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Every owner below automatically uses the same platform gateway.</p>
            </div>
            <div class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse ($owners as $owner)
                    <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-100 text-xs font-black text-blue-700">@if ($owner->photo_url)<img src="{{ $owner->photo_url }}" alt="{{ $owner->name }}" class="h-full w-full object-cover" loading="lazy">@else{{ strtoupper(substr($owner->name ?: 'O', 0, 2)) }}@endif</span>
                            <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-950 dark:text-white">{{ $owner->name }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $owner->email }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700 dark:bg-blue-400/10 dark:text-blue-300">Shared .env gateway</span>
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">Checkout ready</span>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-slate-500 dark:text-slate-400">No owner accounts found.</p>
                @endforelse
            </div>
        </section>
    @else
    <div class="grid gap-4 lg:grid-cols-2">
        @forelse ($owners as $owner)
            @php
                $profile = $owner->ownerProfile;
                $checkoutReady = (bool) ($profile?->paymongo_enabled && $profile?->paymongo_secret_key);
                $webhookReady = (bool) $profile?->paymongo_webhook_secret;
            @endphp
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-100 text-xs font-black text-blue-700">@if ($owner->photo_url)<img src="{{ $owner->photo_url }}" alt="{{ $owner->name }}" class="h-full w-full object-cover" loading="lazy">@else{{ strtoupper(substr($owner->name ?: 'O', 0, 2)) }}@endif</span><div class="min-w-0"><h2 class="truncate font-bold text-slate-950 dark:text-white">{{ $owner->name }}</h2><p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $owner->email }} · {{ ucfirst($owner->role) }}</p></div></div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $checkoutReady ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300' }}">
                        {{ $checkoutReady ? 'Checkout ready' : 'Setup incomplete' }}
                    </span>
                </div>
                @if ($checkoutReady && ! $webhookReady)
                    <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2.5 text-xs leading-5 text-blue-800 dark:border-blue-400/20 dark:bg-blue-400/10 dark:text-blue-200">
                        Checkout is ready and payments are verified after PayMongo redirects the tenant back. Add a webhook signing secret after deploying to a public HTTPS URL for automatic background confirmation.
                    </div>
                @endif
                <form method="POST" action="{{ route(request()->routeIs('owner.*') ? 'owner.payment-settings.update' : 'admin.payment-settings.update') }}" class="space-y-3">
                    @csrf @method('PUT')
                    <input type="hidden" name="owner_id" value="{{ $owner->id }}">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">
                        Public key
                        <input type="password" name="paymongo_public_key" autocomplete="off" placeholder="{{ $profile?->paymongo_public_key ? 'Configured — leave blank to keep' : 'pk_test_...' }}" class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm text-slate-950 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    </label>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">
                        Secret key
                        <input type="password" name="paymongo_secret_key" autocomplete="new-password" placeholder="{{ $profile?->paymongo_secret_key ? 'Configured — leave blank to keep' : 'sk_test_...' }}" class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm text-slate-950 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    </label>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">
                        Webhook signing secret
                        <input type="password" name="paymongo_webhook_secret" autocomplete="new-password" placeholder="{{ $profile?->paymongo_webhook_secret ? 'Configured — leave blank to keep' : 'Paste the secret from the PayMongo webhook' }}" class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm text-slate-950 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    </label>
                    <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        <input type="hidden" name="paymongo_enabled" value="0">
                        <input type="checkbox" name="paymongo_enabled" value="1" @checked(old('owner_id') == $owner->id ? old('paymongo_enabled') : $profile?->paymongo_enabled) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        Enable PayMongo checkout for this owner
                    </label>
                    <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">Save PayMongo settings</button>
                </form>
            </section>
        @empty
            <p class="text-sm text-slate-500 dark:text-slate-400">No owner profiles found.</p>
        @endforelse
    </div>
    @endif
</div>
</x-admin.shell>
</x-layouts.dashboard>
