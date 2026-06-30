<x-layouts.dashboard>
<x-admin.shell :show-header="false">
@php
    $admin = auth()->user();
    $photoPath = $admin?->profile_photo ?: $admin?->profile_image;
    $photoUrl = $photoPath
        ? (\Illuminate\Support\Str::startsWith($photoPath, ['http://', 'https://', '/'])
            ? $photoPath
            : \Illuminate\Support\Facades\Storage::url($photoPath))
        : null;
    $adminName = trim((string) ($admin?->name ?: 'Owner Admin'));
    $firstName = trim(explode(' ', $adminName)[0] ?? 'Owner') ?: 'Owner';
    $adminRole = match (strtolower((string) $admin?->role)) {
        'admin' => 'Owner Administrator',
        'owner' => 'Property Owner',
        default => ucfirst((string) ($admin?->role ?: 'Admin')),
    };
    $initial = strtoupper(substr($adminName, 0, 1));
    $phone = $admin?->phone ?: ($admin?->phone_number ?: $admin?->contact_number);
    $twoFactorEnabled = (bool) ($admin?->sms_two_factor_enabled ?? $admin?->two_factor_enabled ?? false);
    $activeTab = in_array(request('tab'), ['profile', 'security', 'notifications', 'preferences'], true)
        ? request('tab')
        : 'profile';

    $completionItems = collect([
        ['label' => 'Profile name added', 'complete' => filled($admin?->name)],
        ['label' => 'Verified email on file', 'complete' => filled($admin?->email_verified_at)],
        ['label' => 'Phone number added', 'complete' => filled($phone)],
        ['label' => 'Profile photo uploaded', 'complete' => filled($photoUrl)],
        ['label' => 'Two-factor enabled', 'complete' => $twoFactorEnabled],
    ]);
    $completionCount = $completionItems->where('complete', true)->count();
    $completionPercent = (int) round(($completionCount / max($completionItems->count(), 1)) * 100);
    $verifiedSignals = collect([
        filled($admin?->email_verified_at),
        filled($phone),
        $twoFactorEnabled,
    ])->filter()->count();
    $lastUpdated = $admin?->updated_at;
    $joinedAt = $admin?->created_at;

    $toneClasses = fn (string $tone): string => match ($tone) {
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'violet' => 'bg-violet-50 text-violet-700 ring-violet-100',
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-100',
        'slate' => 'bg-slate-100 text-slate-600 ring-slate-200',
        default => 'bg-blue-50 text-blue-700 ring-blue-100',
    };

    $tabItems = [
        'profile' => ['label' => 'Profile', 'icon' => 'users'],
        'security' => ['label' => 'Security', 'icon' => 'audit-logs'],
        'notifications' => ['label' => 'Notifications', 'icon' => 'notifications'],
        'preferences' => ['label' => 'Preferences', 'icon' => 'settings'],
    ];

    $overviewCards = [
        [
            'label' => 'Account Completion',
            'value' => $completionPercent.'%',
            'meta' => $completionCount.' of '.$completionItems->count().' setup signals complete',
            'icon' => 'audit-logs',
            'tone' => $completionPercent >= 80 ? 'emerald' : ($completionPercent >= 50 ? 'blue' : 'amber'),
        ],
        [
            'label' => 'Security Coverage',
            'value' => $twoFactorEnabled ? 'Strong' : 'Standard',
            'meta' => $twoFactorEnabled ? 'Two-factor protection is active' : 'Enable two-factor for stronger protection',
            'icon' => 'settings',
            'tone' => $twoFactorEnabled ? 'emerald' : 'amber',
        ],
        [
            'label' => 'Verified Signals',
            'value' => number_format($verifiedSignals),
            'meta' => 'Email, phone, and security signals on file',
            'icon' => 'notifications',
            'tone' => $verifiedSignals >= 2 ? 'blue' : 'slate',
        ],
        [
            'label' => 'Active Sessions',
            'value' => '1',
            'meta' => 'Current browser session is active',
            'icon' => 'users',
            'tone' => 'violet',
        ],
    ];

    $accountStatusItems = [
        [
            'label' => 'Email Verification',
            'value' => filled($admin?->email_verified_at) ? 'Verified' : 'Pending',
            'detail' => filled($admin?->email_verified_at)
                ? 'Confirmed on '.$admin->email_verified_at->format('M d, Y')
                : 'Verify your email to improve account trust.',
            'tone' => filled($admin?->email_verified_at) ? 'emerald' : 'amber',
        ],
        [
            'label' => 'Two-Factor Auth',
            'value' => $twoFactorEnabled ? 'Enabled' : 'Disabled',
            'detail' => $twoFactorEnabled ? 'Extra sign-in verification is active.' : 'Add an extra security layer to this account.',
            'tone' => $twoFactorEnabled ? 'emerald' : 'amber',
        ],
        [
            'label' => 'Phone Number',
            'value' => $phone ?: 'Not Added',
            'detail' => $phone ? 'Used for account recovery and outreach.' : 'Add a mobile number for recovery options.',
            'tone' => $phone ? 'blue' : 'slate',
        ],
        [
            'label' => 'Last Updated',
            'value' => $lastUpdated ? $lastUpdated->format('M d, Y') : 'No activity',
            'detail' => $lastUpdated ? $lastUpdated->diffForHumans() : 'No recent updates recorded.',
            'tone' => 'violet',
        ],
    ];

    $notificationOptions = [
        'Payment reminders',
        'Booking updates',
        'Tenant inquiries',
    ];
@endphp

<div
    x-data="{
        tab: @js($activeTab),
        profileMenuOpen: false,
        passwordOpen: false,
        sessionOpen: false,
        photoPreview: null,
        setTab(value) {
            this.tab = value;
            const url = new URL(window.location);
            url.searchParams.set('tab', value);
            history.replaceState(null, '', url);
        },
        closeModals() {
            this.passwordOpen = false;
            this.sessionOpen = false;
            this.profileMenuOpen = false;
        }
    }"
    @keydown.escape.window="closeModals()"
    class="space-y-2.5 text-slate-950"
>
    <header class="relative z-[60] overflow-visible rounded-[1.2rem] border border-slate-200 bg-white/95 p-3 shadow-sm shadow-slate-200/60 backdrop-blur">
        <div class="flex flex-col gap-2.5 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0">
                <h1 class="mt-0.5 text-[1.15rem] font-bold tracking-tight text-slate-950">Settings</h1>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    @click="setTab('security')"
                    class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                >
                    Security Settings
                </button>
                <button
                    type="button"
                    @click="passwordOpen = true"
                    class="inline-flex h-9 items-center justify-center rounded-xl bg-blue-600 px-3.5 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700"
                >
                    Change Password
                </button>

                <div class="relative">
                    <button
                        type="button"
                        @click="profileMenuOpen = !profileMenuOpen"
                        class="flex min-w-[190px] items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-left shadow-sm transition hover:border-blue-200 hover:bg-blue-50"
                        aria-haspopup="menu"
                        :aria-expanded="profileMenuOpen"
                    >
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-600 text-sm font-bold text-white shadow-sm shadow-blue-600/20">
                            <template x-if="photoPreview">
                                <img :src="photoPreview" alt="{{ $adminName }}" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!photoPreview">
                                @if ($photoUrl)
                                    <img src="{{ $photoUrl }}" alt="{{ $adminName }}" class="h-full w-full object-cover">
                                @else
                                    <span>{{ $initial }}</span>
                                @endif
                            </template>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-[13px] font-bold text-slate-950">{{ $adminName }}</span>
                            <span class="block truncate text-[10px] text-slate-500">{{ $adminRole }}</span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 text-slate-500 transition" :class="profileMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                        </svg>
                    </button>

                    <div
                        x-cloak
                        x-show="profileMenuOpen"
                        x-transition
                        @click.outside="profileMenuOpen = false"
                        class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/12"
                        role="menu"
                    >
                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="truncate text-sm font-bold text-slate-900">{{ $adminName }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $admin?->email }}</p>
                        </div>
                        <div class="p-1.5 text-sm">
                            <button type="button" @click="setTab('profile'); profileMenuOpen = false" class="flex w-full items-center rounded-xl px-3 py-2.5 text-left font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700" role="menuitem">Profile Management</button>
                            <button type="button" @click="setTab('security'); profileMenuOpen = false" class="flex w-full items-center rounded-xl px-3 py-2.5 text-left font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700" role="menuitem">Security Settings</button>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="flex w-full items-center rounded-xl px-3 py-2.5 text-left font-semibold text-rose-600 transition hover:bg-rose-50" role="menuitem">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="rounded-[1.2rem] border border-slate-200 bg-white p-3 shadow-sm shadow-slate-200/70">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-blue-600">Account Overview</p>
                <p class="mt-0.5 text-[11px] text-slate-500">High-signal status cards for account setup, security, verification, and current access.</p>
            </div>
            <span class="inline-flex h-8 items-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-[11px] font-semibold text-slate-600">
                {{ $completionPercent }}% setup complete
            </span>
        </div>

        <div class="mt-2.5 grid gap-2.5 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($overviewCards as $card)
                <article class="rounded-[1.2rem] border border-slate-200 bg-white p-3 shadow-sm shadow-slate-200/60">
                    <div class="flex items-start justify-between gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl ring-1 {{ $toneClasses($card['tone']) }}">
                            <span class="flex h-5 w-5 items-center justify-center">
                                @include('components.sidebar.partials.admin-icon', ['name' => $card['icon']])
                            </span>
                        </span>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500">{{ $card['label'] }}</span>
                    </div>
                    <p class="mt-3 text-[1.55rem] font-black tracking-tight text-slate-950">{{ $card['value'] }}</p>
                    <p class="mt-1 text-[11px] text-slate-500">{{ $card['meta'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="grid gap-2.5 xl:grid-cols-[minmax(0,1.35fr)_340px]">
        <div class="space-y-2.5">
            <section class="rounded-[1.2rem] border border-slate-200 bg-white p-3 shadow-sm shadow-slate-200/70">
                <div class="flex flex-col gap-2.5">
                    <div class="flex justify-end">
                        <div class="flex flex-wrap items-center gap-2 text-[10px] font-medium text-slate-400">
                            <span>Joined {{ $joinedAt ? $joinedAt->format('M Y') : 'Recently' }}</span>
                            <span class="hidden h-1 w-1 rounded-full bg-slate-300 sm:inline-flex"></span>
                            <span>Updated {{ $lastUpdated ? $lastUpdated->diffForHumans() : 'Recently' }}</span>
                        </div>
                    </div>

                    <nav class="flex flex-wrap gap-2" aria-label="Settings tabs">
                        @foreach ($tabItems as $id => $item)
                            <button
                                type="button"
                                @click="setTab('{{ $id }}')"
                                class="inline-flex h-9 items-center gap-2 rounded-xl border px-3 text-xs font-semibold transition"
                                :class="tab === '{{ $id }}' ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700'"
                            >
                                <span class="flex h-4 w-4 items-center justify-center">
                                    @include('components.sidebar.partials.admin-icon', ['name' => $item['icon']])
                                </span>
                                {{ $item['label'] }}
                            </button>
                        @endforeach
                    </nav>
                </div>
            </section>

            <section x-show="tab === 'profile'" x-cloak class="space-y-3">
                <form method="POST" action="{{ route('admin.settings.profile.update') }}" enctype="multipart/form-data" class="grid gap-2.5 lg:grid-cols-[280px_minmax(0,1fr)]">
                    @csrf
                    @method('PUT')

                    <article class="rounded-[1.2rem] border border-slate-200 bg-white p-3.5 shadow-sm shadow-slate-200/70">
                        <div class="flex items-start justify-between gap-2.5">
                            <div>
                                <h2 class="text-[13px] font-bold text-slate-950">Profile Management</h2>
                                <p class="mt-0.5 text-[11px] text-slate-500">Keep your account identity and contact details polished.</p>
                            </div>
                            <span class="inline-flex h-8 items-center rounded-lg bg-blue-50 px-2.5 text-[10px] font-semibold text-blue-700">{{ $adminRole }}</span>
                        </div>

                        <div class="mt-3.5 flex flex-col items-center text-center">
                            <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-4 border-white bg-blue-600 text-2xl font-black text-white shadow-lg shadow-blue-600/20">
                                <template x-if="photoPreview">
                                    <img :src="photoPreview" alt="{{ $adminName }}" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!photoPreview">
                                    @if ($photoUrl)
                                        <img src="{{ $photoUrl }}" alt="{{ $adminName }}" class="h-full w-full object-cover">
                                    @else
                                        <span>{{ $initial }}</span>
                                    @endif
                                </template>
                            </div>
                            <p class="mt-3 text-[15px] font-bold text-slate-950">{{ $adminName }}</p>
                            <p class="mt-0.5 text-[11px] text-slate-500">{{ $admin?->email }}</p>

                            <div class="mt-2.5 flex flex-wrap justify-center gap-2">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold ring-1 {{ $toneClasses(filled($admin?->email_verified_at) ? 'emerald' : 'amber') }}">
                                    {{ filled($admin?->email_verified_at) ? 'Email Verified' : 'Email Pending' }}
                                </span>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold ring-1 {{ $toneClasses($twoFactorEnabled ? 'emerald' : 'slate') }}">
                                    {{ $twoFactorEnabled ? '2FA Enabled' : '2FA Off' }}
                                </span>
                            </div>

                            <label class="mt-4 inline-flex h-9 cursor-pointer items-center justify-center gap-2 rounded-xl border border-blue-200 bg-white px-4 text-xs font-semibold text-blue-700 transition hover:bg-blue-50">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V4m0 0-4 4m4-4 4 4M5 20h14"/>
                                </svg>
                                Upload Photo
                                <input
                                    type="file"
                                    name="profile_photo"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="sr-only"
                                    @change="photoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                >
                            </label>
                            <p class="mt-2 text-[10px] leading-5 text-slate-400">JPG, PNG, or WEBP. Max size: 2MB.</p>
                        </div>
                    </article>

                    <article class="rounded-[1.2rem] border border-slate-200 bg-white p-3.5 shadow-sm shadow-slate-200/70">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-base font-bold text-slate-950">Account Information</h2>
                                <p class="mt-0.5 text-xs text-slate-500">Update core profile fields used across the owner workspace.</p>
                            </div>
                            <span class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-[11px] font-semibold text-slate-600">
                                {{ $completionCount }} setup items done
                            </span>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <label class="block text-sm font-bold text-slate-700">
                                Full Name
                                <input
                                    name="name"
                                    value="{{ old('name', $admin?->name) }}"
                                    required
                                    maxlength="100"
                                    class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                >
                            </label>

                            <label class="block text-sm font-bold text-slate-700">
                                Email Address
                                <input
                                    name="email"
                                    type="email"
                                    value="{{ old('email', $admin?->email) }}"
                                    required
                                    class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                >
                            </label>

                            <label class="block text-sm font-bold text-slate-700 md:col-span-2">
                                Phone Number
                                <input
                                    name="phone"
                                    value="{{ old('phone', $phone) }}"
                                    maxlength="20"
                                    class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                >
                            </label>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            @foreach ($completionItems as $item)
                                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 text-sm">
                                    <span class="font-medium text-slate-700">{{ $item['label'] }}</span>
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 {{ $toneClasses($item['complete'] ? 'emerald' : 'amber') }}">
                                        <span class="h-2 w-2 rounded-full {{ $item['complete'] ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                        {{ $item['complete'] ? 'Ready' : 'Pending' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 flex justify-end">
                            <button class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 3h12l1 1v17H5V4l1-1Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 3v6h8V3M8 17h8"/>
                                </svg>
                                Save Changes
                            </button>
                        </div>
                    </article>
                </form>
            </section>

            <section x-show="tab === 'security'" x-cloak class="space-y-3">
                <x-admin.settings-security-panel :two-factor-enabled="$twoFactorEnabled" />

                <article class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-950">Password Management</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Rotate credentials, review active access, and keep owner account security current.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" @click="sessionOpen = true" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">View Session</button>
                            <button type="button" @click="passwordOpen = true" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-3.5 text-xs font-semibold text-white transition hover:bg-blue-700">Change Password</button>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Current Access</p>
                            <p class="mt-1 text-sm font-bold text-slate-950">1 active session</p>
                            <p class="mt-1 text-[12px] text-slate-500">Current browser session for {{ $admin?->email }}.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Password Hygiene</p>
                            <p class="mt-1 text-sm font-bold text-slate-950">Manual update</p>
                            <p class="mt-1 text-[12px] text-slate-500">Change password anytime using the secure modal flow.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Recovery Signal</p>
                            <p class="mt-1 text-sm font-bold text-slate-950">{{ $phone ?: 'Add phone number' }}</p>
                            <p class="mt-1 text-[12px] text-slate-500">Keep contact details up to date for recovery support.</p>
                        </div>
                    </div>
                </article>
            </section>

            <section x-show="tab === 'notifications'" x-cloak class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">Notification Preferences</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Keep owner alerts focused on bookings, payments, and tenant activity.</p>
                    </div>
                    <span class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-[11px] font-semibold text-slate-600">
                        3 tracked categories
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.settings.action') }}" class="mt-4 grid gap-3 md:grid-cols-3">
                    @csrf
                    <input type="hidden" name="action" value="save_notifications">
                    @foreach ($notificationOptions as $label)
                        <label class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm font-semibold text-slate-700">
                            <span>{{ $label }}</span>
                            <input type="checkbox" checked class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        </label>
                    @endforeach
                    <div class="md:col-span-3 flex justify-end">
                        <button class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Save Notifications</button>
                    </div>
                </form>
            </section>

            <section x-show="tab === 'preferences'" x-cloak class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">Workspace Preferences</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Tune the default owner experience while keeping advanced tools available when needed.</p>
                    </div>
                    <span class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-[11px] font-semibold text-slate-600">
                        BoardMatch blue theme active
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.settings.action') }}" class="mt-4 grid gap-4 md:grid-cols-2">
                    @csrf
                    <input type="hidden" name="action" value="save_preferences">
                    <label class="block text-sm font-bold text-slate-700">
                        Default Dashboard View
                        <select class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option>Overview</option>
                            <option>Reports</option>
                            <option>Reservations</option>
                        </select>
                    </label>
                    <label class="block text-sm font-bold text-slate-700">
                        Advanced
                        <select class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option>Backup and restore hidden</option>
                            <option>Show advanced tools</option>
                        </select>
                    </label>
                    <div class="md:col-span-2 flex justify-end">
                        <button class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Save Preferences</button>
                    </div>
                </form>
            </section>
        </div>

        <aside class="space-y-3">
            <section class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">Account Status</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Track verification, contact readiness, and security posture at a glance.</p>
                    </div>
                    <span class="inline-flex h-8 items-center rounded-lg bg-blue-50 px-2.5 text-[11px] font-semibold text-blue-700">{{ $firstName }}</span>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach ($accountStatusItems as $item)
                        <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $item['label'] }}</p>
                                    <p class="mt-1 text-sm font-bold text-slate-950">{{ $item['value'] }}</p>
                                </div>
                                <span class="inline-flex h-7 items-center rounded-full px-2.5 text-[10px] font-bold ring-1 {{ $toneClasses($item['tone']) }}">
                                    {{ ucfirst($item['tone']) }}
                                </span>
                            </div>
                            <p class="mt-2 text-[12px] leading-5 text-slate-500">{{ $item['detail'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">Quick Actions</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Shortcut common account tasks without hunting through larger forms.</p>
                    </div>
                </div>

                <div class="mt-4 grid gap-2">
                    <button type="button" @click="setTab('profile')" class="inline-flex h-10 items-center justify-between rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        <span>Open Profile Management</span>
                        <span class="flex h-4 w-4 items-center justify-center">@include('components.sidebar.partials.admin-icon', ['name' => 'users'])</span>
                    </button>
                    <button type="button" @click="setTab('security')" class="inline-flex h-10 items-center justify-between rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        <span>Open Security Settings</span>
                        <span class="flex h-4 w-4 items-center justify-center">@include('components.sidebar.partials.admin-icon', ['name' => 'audit-logs'])</span>
                    </button>
                    <button type="button" @click="passwordOpen = true" class="inline-flex h-10 items-center justify-between rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        <span>Launch Password Modal</span>
                        <span class="flex h-4 w-4 items-center justify-center">@include('components.sidebar.partials.admin-icon', ['name' => 'settings'])</span>
                    </button>
                    <button type="button" @click="sessionOpen = true" class="inline-flex h-10 items-center justify-between rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        <span>Review Active Session</span>
                        <span class="flex h-4 w-4 items-center justify-center">@include('components.sidebar.partials.admin-icon', ['name' => 'users'])</span>
                    </button>
                    <form method="POST" action="{{ route('admin.settings.action') }}">
                        @csrf
                        <input type="hidden" name="action" value="backup">
                        <button class="inline-flex h-10 w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                            <span>Request Backup</span>
                            <span class="flex h-4 w-4 items-center justify-center">@include('components.sidebar.partials.admin-icon', ['name' => 'reports'])</span>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.settings.action') }}">
                        @csrf
                        <input type="hidden" name="action" value="restore">
                        <button class="inline-flex h-10 w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                            <span>Request Restore</span>
                            <span class="flex h-4 w-4 items-center justify-center">@include('components.sidebar.partials.admin-icon', ['name' => 'settings'])</span>
                        </button>
                    </form>
                </div>
            </section>

        </aside>
    </section>

    <div
        data-modal-root
        role="dialog"
        aria-modal="true"
        x-show="passwordOpen"
        x-cloak
        x-transition
        @click.self="passwordOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
    >
        <form method="POST" action="{{ route('admin.settings.password.update') }}" class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-950/20">
            @csrf
            @method('PUT')
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-700">Security</p>
                    <h2 class="mt-2 text-xl font-bold text-slate-950">Change Password</h2>
                </div>
                <button type="button" @click="passwordOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-400 transition hover:bg-slate-50 hover:text-slate-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mt-5 grid gap-4">
                <label class="block text-sm font-bold text-slate-700">
                    Current Password
                    <input name="current_password" type="password" required autocomplete="current-password" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                </label>
                <label class="block text-sm font-bold text-slate-700">
                    New Password
                    <input name="password" type="password" required minlength="8" autocomplete="new-password" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                </label>
                <label class="block text-sm font-bold text-slate-700">
                    Confirm Password
                    <input name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                </label>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="passwordOpen = false" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Cancel</button>
                <button class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700">Update Password</button>
            </div>
        </form>
    </div>

    <div
        data-modal-root
        role="dialog"
        aria-modal="true"
        x-show="sessionOpen"
        x-cloak
        x-transition
        @click.self="sessionOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
    >
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-950/20">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-700">Session</p>
                    <h2 class="mt-2 text-xl font-bold text-slate-950">Active Session</h2>
                </div>
                <button type="button" @click="sessionOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-400 transition hover:bg-slate-50 hover:text-slate-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                <p class="font-bold text-slate-900">1 active session</p>
                <p class="mt-2">Current browser session for {{ $admin?->email }}.</p>
                <p class="mt-2">Last confirmed: {{ now()->format('M j, Y h:i A') }}</p>
            </div>
            <div class="mt-6 flex justify-end">
                <button type="button" @click="sessionOpen = false" class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700">Done</button>
            </div>
        </div>
    </div>
</div>
</x-admin.shell>
</x-layouts.dashboard>
