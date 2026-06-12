<x-layouts.dashboard>
<x-admin.shell>
@php
    $admin = auth()->user();
    $photoPath = $admin?->profile_photo ?: $admin?->profile_image;
    $photoUrl = $photoPath
        ? (\Illuminate\Support\Str::startsWith($photoPath, ['http://', 'https://', '/'])
            ? $photoPath
            : \Illuminate\Support\Facades\Storage::url($photoPath))
        : null;
    $adminName = $admin?->name ?: 'Jani';
    $adminRole = ucfirst($admin?->role ?: 'Admin');
    $initial = strtoupper(substr($adminName, 0, 1));
    $phone = $admin?->phone ?: ($admin?->phone_number ?: $admin?->contact_number);
    $twoFactorEnabled = (bool) ($admin?->sms_two_factor_enabled ?? $admin?->two_factor_enabled ?? false);
    $activeTab = in_array(request('tab'), ['profile', 'security', 'notifications', 'preferences'], true)
        ? request('tab')
        : 'profile';
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
    class="space-y-6"
>
    <section class="rounded-2xl border border-slate-200 bg-white px-6 py-6 shadow-sm shadow-slate-900/5">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-700">Settings</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Admin Settings</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">Manage your profile, security, and preferences.</p>
            </div>

            <div class="relative">
                <button
                    type="button"
                    @click="profileMenuOpen = !profileMenuOpen"
                    class="flex min-w-[210px] items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left shadow-sm transition hover:border-blue-200 hover:bg-blue-50"
                    aria-haspopup="menu"
                    :aria-expanded="profileMenuOpen"
                >
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-600 text-sm font-bold text-white shadow-sm shadow-blue-600/20">
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
                        <span class="block truncate text-sm font-bold text-slate-950">{{ $adminName }}</span>
                        <span class="block truncate text-sm font-medium text-slate-500">{{ $adminRole }}</span>
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
                        <button type="button" @click="setTab('profile'); profileMenuOpen = false" class="flex w-full items-center rounded-xl px-3 py-2.5 text-left font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700" role="menuitem">Profile Settings</button>
                        <button type="button" @click="setTab('security'); profileMenuOpen = false" class="flex w-full items-center rounded-xl px-3 py-2.5 text-left font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700" role="menuitem">Account Settings</button>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="flex w-full items-center rounded-xl px-3 py-2.5 text-left font-semibold text-rose-600 transition hover:bg-rose-50" role="menuitem">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-x-auto rounded-2xl border border-slate-200 bg-white px-6 shadow-sm shadow-slate-900/5">
        <nav class="flex min-w-max gap-10" aria-label="Settings tabs">
            @foreach ([
                'profile' => ['label' => 'Profile', 'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 21a8 8 0 0 1 16 0'],
                'security' => ['label' => 'Security', 'icon' => 'M7 10V8a5 5 0 0 1 10 0v2M6 10h12v10H6z'],
                'notifications' => ['label' => 'Notifications', 'icon' => 'M15 17H9m0 0-3-9a6 6 0 1 1 12 0l-3 9m-6 0v1a3 3 0 0 0 6 0v-1'],
                'preferences' => ['label' => 'Preferences', 'icon' => 'M4 7h6M14 7h6M4 12h10M18 12h2M4 17h8M16 17h4'],
            ] as $id => $item)
                <button
                    type="button"
                    @click="setTab('{{ $id }}')"
                    class="inline-flex items-center gap-3 border-b-2 py-4 text-sm font-bold transition"
                    :class="tab === '{{ $id }}' ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-800'"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/>
                    </svg>
                    {{ $item['label'] }}
                </button>
            @endforeach
        </nav>
    </section>

    <section x-show="tab === 'profile'" x-cloak class="space-y-6">
        <form method="POST" action="{{ route('admin.settings.profile.update') }}" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[420px_minmax(0,1fr)]">
            @csrf
            @method('PUT')

            <article class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm shadow-slate-900/5">
                <h2 class="text-lg font-bold text-slate-950">Profile Overview</h2>
                <div class="mt-8 flex flex-col items-center text-center">
                    <div class="flex h-36 w-36 items-center justify-center overflow-hidden rounded-full border-4 border-white bg-blue-600 text-5xl font-bold text-white shadow-xl shadow-blue-600/20">
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
                    <p class="mt-5 text-2xl font-bold text-slate-950">{{ $adminName }}</p>
                    <p class="mt-1 text-base font-medium text-slate-500">{{ $adminRole }}</p>
                    <label class="mt-6 inline-flex h-12 cursor-pointer items-center justify-center gap-2 rounded-xl border border-blue-200 bg-white px-5 text-sm font-bold text-blue-700 transition hover:bg-blue-50">
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
                    <p class="mt-3 text-xs leading-5 text-slate-400">JPG, PNG, or WEBP. Max size: 2MB.</p>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm shadow-slate-900/5">
                <h2 class="text-lg font-bold text-slate-950">Account Information</h2>
                <div class="mt-6 grid gap-5">
                    <label class="block text-sm font-bold text-slate-700">
                        Full Name
                        <input
                            name="name"
                            value="{{ old('name', $admin?->name) }}"
                            required
                            maxlength="100"
                            class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                    </label>
                    <label class="block text-sm font-bold text-slate-700">
                        Email Address
                        <input
                            name="email"
                            type="email"
                            value="{{ old('email', $admin?->email) }}"
                            required
                            class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                    </label>
                    <label class="block text-sm font-bold text-slate-700">
                        Phone Number
                        <input
                            name="phone"
                            value="{{ old('phone', $phone) }}"
                            maxlength="20"
                            class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                    </label>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-bold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 3h12l1 1v17H5V4l1-1Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 3v6h8V3M8 17h8"/>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </article>
        </form>

        <x-admin.settings-security-panel :two-factor-enabled="$twoFactorEnabled" />
    </section>

    <section x-show="tab === 'security'" x-cloak class="space-y-6">
        <x-admin.settings-security-panel :two-factor-enabled="$twoFactorEnabled" />

        <article class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm shadow-slate-900/5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Password</h2>
                    <p class="mt-2 text-sm text-slate-500">Use the change password action to update account credentials.</p>
                </div>
                <button type="button" @click="passwordOpen = true" class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700">Change Password</button>
            </div>
        </article>
    </section>

    <section x-show="tab === 'notifications'" x-cloak class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm shadow-slate-900/5">
        <h2 class="text-lg font-bold text-slate-950">Notifications</h2>
        <form method="POST" action="{{ route('admin.settings.action') }}" class="mt-6 grid gap-4 md:grid-cols-3">
            @csrf
            <input type="hidden" name="action" value="save_notifications">
            @foreach ([
                'Payment reminders',
                'Booking updates',
                'Tenant inquiries',
            ] as $label)
                <label class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-bold text-slate-700">
                    <span>{{ $label }}</span>
                    <input type="checkbox" checked class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                </label>
            @endforeach
            <div class="md:col-span-3 flex justify-end">
                <button class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700">Save Notifications</button>
            </div>
        </form>
    </section>

    <section x-show="tab === 'preferences'" x-cloak class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm shadow-slate-900/5">
        <h2 class="text-lg font-bold text-slate-950">Preferences</h2>
        <form method="POST" action="{{ route('admin.settings.action') }}" class="mt-6 grid gap-4 md:grid-cols-2">
            @csrf
            <input type="hidden" name="action" value="save_preferences">
            <label class="block text-sm font-bold text-slate-700">
                Default Dashboard View
                <select class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option>Overview</option>
                    <option>Reports</option>
                    <option>Reservations</option>
                </select>
            </label>
            <label class="block text-sm font-bold text-slate-700">
                Advanced
                <select class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option>Backup and restore hidden</option>
                    <option>Show advanced tools</option>
                </select>
            </label>
            <div class="md:col-span-2 flex justify-end">
                <button class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700">Save Preferences</button>
            </div>
        </form>
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
