@php
    $tenant = $tenant ?? auth()->user();
    $displayName = filled($tenant?->name) ? $tenant->name : 'Tenant';
    $phone = $tenant?->phone ?: ($tenant?->contact_number ?: 'Not provided');
    $statusLabel = ($tenant?->is_active ?? true) ? 'Active' : 'Pending';
    $emailStatus = $tenant?->email_verified_at ? 'Verified' : 'Unverified';
    $memberSince = $tenant?->created_at ? $tenant->created_at->format('M d, Y') : 'N/A';

    $summaryCards = [
        ['label' => 'Account Status', 'value' => $statusLabel, 'description' => 'Tenant workspace access'],
        ['label' => 'Email Status', 'value' => $emailStatus, 'description' => 'Used for account updates'],
        ['label' => 'Contact Number', 'value' => $phone, 'description' => 'Owner and support contact'],
        ['label' => 'Member Since', 'value' => $memberSince, 'description' => 'DSSC Boarding account'],
    ];

    $notificationItems = [
        ['label' => 'Application Updates', 'description' => 'Status changes, owner notes, and OSAS review updates.', 'status' => 'Enabled'],
        ['label' => 'Reservation Reminders', 'description' => 'Move-in dates, booking decisions, and room updates.', 'status' => 'Enabled'],
        ['label' => 'Message Alerts', 'description' => 'New replies from boarding house owners and support.', 'status' => 'Enabled'],
        ['label' => 'Review Updates', 'description' => 'Pending and approved tenant review notifications.', 'status' => 'Enabled'],
    ];

    $quickLinks = [
        ['label' => 'Profile and Contact Details', 'description' => 'Update your name, email, phone number, and profile photo.', 'href' => route('profile.edit')],
        ['label' => 'Applications', 'description' => 'Check submitted boarding house applications and decisions.', 'href' => route('tenant.applications')],
        ['label' => 'Reservations', 'description' => 'Review move-in details and owner reservation responses.', 'href' => route('tenant.reservations')],
        ['label' => 'Messages', 'description' => 'Open recent conversations with owners and support.', 'href' => route('tenant.messages')],
    ];

    $securityItems = [
        ['label' => 'Password', 'value' => 'Managed from Profile', 'description' => 'Change your password from the tenant profile page.', 'href' => route('profile.edit')],
        ['label' => 'Two-Factor Authentication', 'value' => 'Not enabled', 'description' => 'Add a second verification step when signing in.', 'action' => 'two_factor'],
        ['label' => 'Email Verification', 'value' => $emailStatus, 'description' => 'Verified email helps protect account recovery and alerts.'],
        ['label' => 'Account Deletion', 'value' => 'Available from Profile', 'description' => 'Review permanent account removal from your profile page.', 'href' => route('profile.edit')],
    ];
@endphp

<x-layouts.caretaker>
<x-tenant.shell :message-count="$messageCount ?? 0" :notification-count="$notificationCount ?? 0" :show-header="false">
    <div class="mx-auto max-w-7xl space-y-6" x-data="{ twoFactorOpen: false }" @keydown.escape.window="twoFactorOpen = false">
        <section class="tenant-card overflow-hidden">
            <div class="flex flex-col gap-5 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Settings</h1>
<<<<<<< Updated upstream
                    <a href="{{ route('tenant.settings', absolute: false) }}" class="sr-only">Settings permalink</a>
=======
>>>>>>> Stashed changes
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base">
                        Manage your DSSC Boarding tenant preferences, account access, notifications, and support shortcuts.
                    </p>
                </div>

                <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center">
                    <x-theme-toggle class="h-11 justify-center rounded-xl border-slate-200 bg-white px-4 text-sm shadow-sm" show-label prefix="Theme" />
                    <a href="{{ route('profile.edit') }}" class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-700 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200">
                        Edit Profile
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($summaryCards as $card)
                <article class="tenant-card p-5">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-3 truncate text-xl font-bold text-slate-950">{{ $card['value'] }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $card['description'] }}</p>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(360px,0.85fr)]">
            <div class="space-y-6">
                <article class="tenant-card p-5 sm:p-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-950">Notification Preferences</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600">
                                These categories control the updates shown in your tenant workspace.
                            </p>
                        </div>
                        <a href="{{ route('tenant.notifications') }}" class="text-sm font-bold text-blue-700 hover:text-blue-800">View Alerts</a>
                    </div>

                    <div class="mt-5 grid gap-3 md:grid-cols-2">
                        @foreach ($notificationItems as $item)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-950">{{ $item['label'] }}</h3>
                                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ $item['description'] }}</p>
                                    </div>
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">{{ $item['status'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="tenant-card p-5 sm:p-6">
                    <h2 class="text-lg font-bold text-slate-950">Privacy and Security</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-600">
                        Keep your tenant account protected and review sensitive account actions from one place.
                    </p>

                    <div class="mt-5 divide-y divide-slate-100 rounded-2xl border border-slate-200">
                        @foreach ($securityItems as $item)
                            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-950">{{ $item['label'] }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $item['value'] }}</p>
                                    @if (! empty($item['description']))
                                        <p class="mt-1 max-w-xl text-xs leading-5 text-slate-500">{{ $item['description'] }}</p>
                                    @endif
                                </div>
                                @if (($item['action'] ?? null) === 'two_factor')
                                    <button type="button" @click="twoFactorOpen = true" class="inline-flex h-10 items-center justify-center rounded-xl border border-violet-200 px-4 text-sm font-bold text-violet-700 transition hover:bg-violet-50">
                                        Enable
                                    </button>
                                @elseif (! empty($item['href']))
                                    <a href="{{ $item['href'] }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Manage</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </article>
            </div>

            <aside class="space-y-6">
                <article class="tenant-card p-5 sm:p-6">
                    <h2 class="text-lg font-bold text-slate-950">Account Shortcuts</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Open the tenant pages most related to your settings.</p>

                    <div class="mt-5 grid gap-3">
                        @foreach ($quickLinks as $link)
                            <a href="{{ $link['href'] }}" class="group rounded-2xl border border-slate-200 p-4 transition hover:border-blue-200 hover:bg-blue-50">
                                <span class="block text-sm font-bold text-slate-950 group-hover:text-blue-700">{{ $link['label'] }}</span>
                                <span class="mt-1 block text-sm leading-6 text-slate-500">{{ $link['description'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </article>

                <article class="tenant-card p-5 sm:p-6">
                    <h2 class="text-lg font-bold text-slate-950">Appearance</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Switch between light and dark display modes for this browser.</p>

                    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-bold text-slate-950">Theme Preference</p>
                                <p class="mt-1 text-sm text-slate-500">Saved locally on this device.</p>
                            </div>
                            <x-theme-toggle class="h-10 rounded-xl bg-white px-4 text-sm shadow-sm" show-label />
                        </div>
                    </div>
                </article>

                <article class="tenant-card p-5 sm:p-6">
                    <h2 class="text-lg font-bold text-slate-950">Device Session</h2>
                    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-bold text-slate-950">Current Browser</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Active session for {{ $displayName }}.</p>
                        <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Last checked</p>
                        <p class="mt-1 text-sm font-bold text-slate-900">{{ now()->format('M d, Y h:i A') }}</p>
                    </div>
                </article>

                <article class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm sm:p-6">
                    <h2 class="text-lg font-bold text-slate-950">Need Help?</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Contact support if you need help with account access, listings, reservations, or messages.
                    </p>
                    <a href="{{ route('tenant.messages') }}" class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-xl bg-blue-700 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800">
                        Open Messages
                    </a>
                </article>
            </aside>
        </section>

        <div
            x-show="twoFactorOpen"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4"
            style="display: none;"
            role="dialog"
            aria-modal="true"
            aria-labelledby="tenant-two-factor-title"
            @click.self="twoFactorOpen = false"
        >
            <section x-transition.scale class="tenant-two-factor-modal rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
                    <div>
                        <h2 id="tenant-two-factor-title" class="text-lg font-bold text-slate-950">Two-Factor Authentication</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Protect your tenant account by requiring a verification code after entering your password.
                        </p>
                    </div>
                    <button type="button" @click="twoFactorOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100" aria-label="Close two-factor authentication dialog">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>

                <div class="tenant-two-factor-modal__body space-y-4 px-5 py-5 sm:px-6">
                    <div class="rounded-2xl bg-violet-50 p-4 text-sm leading-6 text-violet-800">
                        Two-factor authentication is ready in the settings UI. Backend code verification can be connected when a 2FA provider or Laravel Fortify is added.
                    </div>

                    <div class="grid gap-3 text-sm text-slate-700">
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="font-bold text-slate-950">1. Verify password</p>
                            <p class="mt-1 text-slate-500">Confirm the account owner before enabling 2FA.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="font-bold text-slate-950">2. Scan authenticator QR code</p>
                            <p class="mt-1 text-slate-500">Use an authenticator app to generate sign-in codes.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="font-bold text-slate-950">3. Save recovery codes</p>
                            <p class="mt-1 text-slate-500">Keep backup codes for account recovery.</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                    <button type="button" @click="twoFactorOpen = false" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                        Close
                    </button>
                    <button type="button" disabled class="inline-flex h-10 cursor-not-allowed items-center justify-center rounded-xl bg-violet-200 px-4 text-sm font-bold text-violet-800 opacity-80">
                        Backend Setup Required
                    </button>
                </div>
            </section>
        </div>
    </div>
</x-tenant.shell>
</x-layouts.caretaker>
