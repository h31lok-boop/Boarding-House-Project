@php
    $showPageHeader = $showPageHeader ?? true;

    $iconPaths = [
        'bell' => '<path d="M15 17H9m9-5a6 6 0 1 0-12 0c0 3-1.5 4-2 5h16c-.5-1-2-2-2-5Z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'question' => '<path d="M9.5 9a2.5 2.5 0 1 1 4 2c-1 .7-1.5 1.2-1.5 2.5"/><path d="M12 17h.01"/><circle cx="12" cy="12" r="9"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
        'mail' => '<path d="M4 5h16v14H4z"/><path d="m4 7 8 6 8-6"/>',
        'palette' => '<path d="M12 3a9 9 0 0 0 0 18h1.5a2 2 0 0 0 1.2-3.6l-.5-.4a2 2 0 0 1 1.2-3.6H18a3 3 0 0 0 3-3A7.5 7.5 0 0 0 12 3Z"/><path d="M7.5 10h.01M9.5 6.8h.01M14 6.5h.01M16.5 10h.01"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a15 15 0 0 1 0 18"/><path d="M12 3a15 15 0 0 0 0 18"/>',
        'shield' => '<path d="M12 3 19 7v5c0 4.5-2.7 7.9-7 9-4.3-1.1-7-4.5-7-9V7l7-4Z"/><path d="m9 12 2 2 4-4"/>',
        'key' => '<circle cx="8" cy="15" r="4"/><path d="m11 12 8-8"/><path d="M17 6h3v3"/>',
        'device' => '<rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>',
        'building' => '<path d="M4 21V7.5L12 3l8 4.5V21"/><path d="M9 21v-4h6v4"/><path d="M8 10h.01M12 10h.01M16 10h.01"/>',
        'reply' => '<path d="m10 9-5 5 5 5"/><path d="M5 14h10a5 5 0 0 0 5-5V7"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'message' => '<path d="M4 5h16a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H9l-5 4V6a1 1 0 0 1 1-1Z"/><path d="M7 9h10M7 12h7"/>',
        'trash' => '<path d="M4 7h16M9 7V5h6v2M7 7l1 13h8l1-13"/><path d="M10 11v5M14 11v5"/>',
        'check' => '<path d="m4 12 5 5L20 6"/>',
        'external' => '<path d="M14 4h6v6"/><path d="m10 14 10-10"/><path d="M20 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h5"/>',
        'edit' => '<path d="m4 20 4.2-1 10-10a2 2 0 0 0-3-3l-10 10L4 20Z"/><path d="m13.5 6.5 4 4"/>',
        'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19 19 0 0 1-8.3-3 18.7 18.7 0 0 1-5.8-5.8 19 19 0 0 1-3-8.3A2 2 0 0 1 4.7 2h3a2 2 0 0 1 2 1.7l.4 2.7a2 2 0 0 1-.6 1.8L8.2 9.5a15 15 0 0 0 6.3 6.3l1.3-1.3a2 2 0 0 1 1.8-.6l2.7.4a2 2 0 0 1 1.7 2Z"/>',
        'map' => '<path d="M12 21s7-5.4 7-11a7 7 0 0 0-14 0c0 5.6 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'moon' => '<path d="M21 13A8 8 0 0 1 11 3a9 9 0 1 0 10 10Z"/>',
        'help' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 4 2c-1 .7-1.5 1.2-1.5 2.5"/><path d="M12 17h.01"/>',
        'activity' => '<path d="M3 12h4l3-7 4 14 3-7h4"/>',
        'camera' => '<path d="M4 8h4l2-3h4l2 3h4v11H4z"/><circle cx="12" cy="13" r="3"/>',
    ];

    $uiIcon = fn ($name, $class = 'h-5 w-5') => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8">'.$iconPaths[$name].'</svg>';

    $isSuperWorkspace = request()->routeIs('superduperadmin.*');
    $profileUpdateRoute = $isSuperWorkspace && \Illuminate\Support\Facades\Route::has('superduperadmin.profile.update')
        ? route('superduperadmin.profile.update')
        : (\Illuminate\Support\Facades\Route::has('owner.profile.update') ? route('owner.profile.update') : '#');

    $navGroups = [
        'Account & Profile' => [
            ['label' => 'Account Settings', 'icon' => 'user'],
            ['label' => 'Change Password', 'icon' => 'lock'],
            ['label' => 'Email Address', 'icon' => 'mail'],
        ],
        'Preferences' => [
            ['label' => 'Notifications', 'icon' => 'bell'],
            ['label' => 'Theme & Appearance', 'icon' => 'palette'],
            ['label' => 'Language', 'icon' => 'globe'],
        ],
        'Privacy & Security' => [
            ['label' => 'Privacy Settings', 'icon' => 'shield'],
            ['label' => 'Security Settings', 'icon' => 'key'],
            ['label' => 'Two-Factor Authentication', 'icon' => 'lock', 'badge' => 'Soon'],
            ['label' => 'Connected Devices', 'icon' => 'device'],
        ],
        'Owner Settings' => [
            ['label' => 'Owner Information', 'icon' => 'building'],
            ['label' => 'Auto-reply Message', 'icon' => 'reply'],
            ['label' => 'OSAS Reminders', 'icon' => 'clock'],
            ['label' => 'Message Notifications', 'icon' => 'message'],
        ],
        'Danger Zone' => [
            ['label' => 'Deactivate or Delete Account', 'icon' => 'trash', 'danger' => true],
        ],
    ];

    $notifications = [
        ['key' => 'inquiries', 'label' => 'Inquiries', 'description' => 'Get notified when you receive new inquiries'],
        ['key' => 'messages', 'label' => 'Messages', 'description' => 'Get notified for new messages'],
        ['key' => 'bookings', 'label' => 'Bookings & Reservations', 'description' => 'Get notified for new bookings and updates'],
        ['key' => 'osas', 'label' => 'OSAS Compliance Reminders', 'description' => 'Get notified about document expiration'],
        ['key' => 'reviews', 'label' => 'Review & Ratings', 'description' => 'Get notified for new reviews'],
    ];

    $ownerSettings = [
        ['label' => 'Default Contact Number', 'value' => '0917 123 4567', 'description' => 'Used as the primary contact for your listings.', 'icon' => 'phone'],
        ['label' => 'Default Address / Location', 'value' => 'Purok 5, Zone 2, Digos City, Davao del Sur, Philippines', 'description' => 'Used as the default location for your listings.', 'icon' => 'map'],
        ['label' => 'Auto-reply for Inquiries', 'value' => 'Enabled', 'description' => 'Respond automatically to new inquiries.', 'icon' => 'reply'],
        ['label' => 'OSAS Document Reminders', 'value' => '30 days before expiration', 'description' => 'Receive reminders before OSAS documents expire.', 'icon' => 'clock'],
    ];

    $devices = [
        ['name' => 'Windows &middot; Chrome', 'meta' => 'Manila, Philippines &middot; This device', 'current' => true, 'icon' => 'device'],
        ['name' => 'iPhone 13 &middot; Safari', 'meta' => 'Manila, Philippines &middot; May 20, 2026', 'current' => false, 'icon' => 'phone'],
        ['name' => 'Android &middot; Chrome', 'meta' => 'Davao City, Philippines &middot; May 18, 2026', 'current' => false, 'icon' => 'phone'],
    ];

    $helpLinks = [
        ['label' => 'Help Center', 'description' => 'View guides and tutorials', 'icon' => 'help'],
        ['label' => 'Contact Support', 'description' => 'Get help from our team', 'icon' => 'message'],
        ['label' => 'System Status', 'description' => 'Check system status', 'icon' => 'activity'],
    ];
@endphp

<div
    id="settings-management"
    x-data="{
        activeSection: 'Account Settings',
        theme: 'Light',
        notifications: { inquiries: true, messages: true, bookings: true, osas: true, reviews: false },
        loginAlerts: true,
        modalType: null,
        selectedSetting: '',
        toggle(name) {
            this.notifications[name] = ! this.notifications[name];
        },
        openSettingsModal(type, label = '') {
            this.modalType = type;
            this.selectedSetting = label;
        },
        closeSettingsModal() {
            this.modalType = null;
        }
    }"
    @keydown.escape.window="closeSettingsModal()"
    class="space-y-6"
>
    @if ($showPageHeader)
        <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Settings</h1>
                <p class="mt-1 text-sm text-slate-600 sm:text-base">Manage your account and system preferences.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Notifications">
                    {!! $uiIcon('bell', 'h-5 w-5') !!}
                    <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white">3</span>
                </button>
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Help">
                    {!! $uiIcon('question', 'h-5 w-5') !!}
                </button>
                <button type="button" class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-left shadow-sm">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-700 text-xs font-bold text-white">JD</span>
                    <span class="leading-tight">
                        <span class="block text-sm font-semibold text-slate-950">Juan Dela Cruz</span>
                        <span class="block text-xs text-slate-500">Owner</span>
                    </span>
                    <span class="text-slate-500">{!! $uiIcon('chevron-down', 'h-4 w-4') !!}</span>
                </button>
            </div>
        </section>
    @endif

    <section class="grid gap-6 xl:grid-cols-[260px_minmax(0,1fr)]">
        <aside class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm xl:sticky xl:top-6 xl:self-start">
            <h2 class="px-2 text-sm font-bold uppercase tracking-wide text-slate-500">Settings Menu</h2>
            <nav class="mt-4 space-y-5">
                @foreach ($navGroups as $group => $items)
                    <div>
                        <p class="px-2 text-xs font-bold uppercase tracking-wide text-slate-400">{{ $group }}</p>
                        <div class="mt-2 space-y-1">
                            @foreach ($items as $item)
                                <button
                                    type="button"
                                    @click="activeSection = @js($item['label'])"
                                    :class="activeSection === @js($item['label']) ? '{{ ! empty($item['danger']) ? 'bg-rose-50 text-rose-700 ring-1 ring-rose-100' : 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' }}' : '{{ ! empty($item['danger']) ? 'text-rose-700 hover:bg-rose-50' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}'"
                                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold transition"
                                >
                                    <span class="shrink-0">{!! $uiIcon($item['icon'], 'h-4 w-4') !!}</span>
                                    <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                                    @if (! empty($item['badge']))
                                        <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold text-violet-700">{{ $item['badge'] }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
        </aside>

        <main class="min-w-0 space-y-6">
            <form method="post" action="{{ $profileUpdateRoute }}" enctype="multipart/form-data" @if($profileUpdateRoute === '#') @submit.prevent @endif class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                @if ($profileUpdateRoute !== '#')
                    @csrf
                    @method('patch')
                @endif
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Account Settings</h2>
                    <p class="mt-1 text-sm text-slate-500">Update your personal information and account details.</p>
                </div>

                <div class="mt-6 grid gap-5 lg:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Full Name</span>
                        <input name="name" type="text" value="Juan Dela Cruz" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </label>
                    <label class="block">
                        <span class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            Email Address
                            <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-200">Verified</span>
                        </span>
                        <input name="email" type="email" value="juandelacruz.owner@email.com" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Contact Number</span>
                        <input name="phone" type="text" value="0917 123 4567" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Role</span>
                        <input type="text" value="Owner" disabled class="mt-2 h-11 w-full rounded-xl border-slate-200 bg-slate-100 text-sm font-semibold text-slate-500 shadow-sm">
                    </label>
                    <div class="lg:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Profile Picture</span>
                        <div class="mt-2 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center">
                            <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-blue-700 text-lg font-bold text-white">JD</span>
                            <div class="min-w-0 flex-1">
                                <button type="button" @click="openSettingsModal('photo')" class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                    {!! $uiIcon('camera', 'h-4 w-4') !!}
                                    Change Photo
                                </button>
                                <p class="mt-2 text-sm text-slate-500">JPG, PNG or GIF. Max size 2MB.</p>
                            </div>
                        </div>
                    </div>
                    <label class="block lg:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Member Since</span>
                        <input type="text" value="May 15, 2024" disabled class="mt-2 h-11 w-full rounded-xl border-slate-200 bg-slate-100 text-sm font-semibold text-slate-500 shadow-sm">
                    </label>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-700 px-5 text-sm font-bold text-white shadow-sm shadow-blue-900/20 transition hover:bg-blue-800">
                        Save Changes
                    </button>
                </div>
            </form>

            <section id="notification-preferences" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Notification Preferences</h2>
                    <p class="mt-1 text-sm text-slate-500">Manage how you receive notifications.</p>
                </div>
                <div class="mt-5 divide-y divide-slate-200">
                    @foreach ($notifications as $item)
                        <div class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="font-bold text-slate-950">{{ $item['label'] }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $item['description'] }}</p>
                            </div>
                            <button type="button" @click="toggle(@js($item['key']))" :class="notifications[@js($item['key'])] ? 'bg-emerald-500' : 'bg-slate-300'" class="relative h-7 w-12 rounded-full transition">
                                <span :class="notifications[@js($item['key'])] ? 'translate-x-6' : 'translate-x-1'" class="absolute left-0 top-1 h-5 w-5 rounded-full bg-white shadow transition"></span>
                            </button>
                        </div>
                    @endforeach
                </div>
                <a href="#message-notifications" class="mt-5 inline-flex text-sm font-bold text-blue-700 hover:text-blue-800">Manage message notification settings</a>
            </section>

            <section id="security-settings" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Security Settings</h2>
                    <p class="mt-1 text-sm text-slate-500">Keep your account secure.</p>
                </div>
                <div class="mt-5 divide-y divide-slate-200">
                    <div class="flex flex-col gap-3 py-4 first:pt-0 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="font-bold text-slate-950">Password</h3>
                            <p class="mt-1 text-sm text-slate-500">Last changed on May 10, 2025</p>
                        </div>
                        <button type="button" @click="openSettingsModal('password')" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Change Password</button>
                    </div>
                    <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-950">Two-Factor Authentication</h3>
                                <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[11px] font-bold text-violet-700 ring-1 ring-violet-200">Soon</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500">Add an extra layer of security</p>
                        </div>
                        <button type="button" @click="openSettingsModal('2fa')" class="inline-flex h-10 items-center justify-center rounded-xl border border-violet-200 px-4 text-sm font-bold text-violet-700 transition hover:bg-violet-50">Enable</button>
                    </div>
                    <div class="flex flex-col gap-3 py-4 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="font-bold text-slate-950">Login Alerts</h3>
                            <p class="mt-1 text-sm text-slate-500">Get notified of new login attempts</p>
                        </div>
                        <button type="button" @click="loginAlerts = ! loginAlerts" :class="loginAlerts ? 'bg-emerald-500' : 'bg-slate-300'" class="relative h-7 w-12 rounded-full transition">
                            <span :class="loginAlerts ? 'translate-x-6' : 'translate-x-1'" class="absolute left-0 top-1 h-5 w-5 rounded-full bg-white shadow transition"></span>
                        </button>
                    </div>
                </div>
            </section>

            <section id="connected-devices" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Owner Settings</h2>
                    <p class="mt-1 text-sm text-slate-500">Manage your boarding house and inquiry preferences.</p>
                </div>
                <div class="mt-5 divide-y divide-slate-200">
                    @foreach ($ownerSettings as $item)
                        <div class="flex flex-col gap-4 py-4 first:pt-0 last:pb-0 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700 ring-1 ring-blue-100">{!! $uiIcon($item['icon'], 'h-5 w-5') !!}</span>
                                <div>
                                    <h3 class="font-bold text-slate-950">{{ $item['label'] }}</h3>
                                    <p class="mt-1 font-semibold text-slate-700">{{ $item['value'] }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $item['description'] }}</p>
                                </div>
                            </div>
                            <button type="button" @click="openSettingsModal('owner-setting', @js($item['label']))" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                                Edit
                            </button>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-rose-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-700 ring-1 ring-rose-200">{!! $uiIcon('trash', 'h-5 w-5') !!}</span>
                        <div>
                            <h2 class="text-lg font-bold text-rose-700">Deactivate or Delete Account</h2>
                            <p class="mt-1 text-sm text-slate-600">Deactivate your account temporarily or permanently delete your account.</p>
                        </div>
                    </div>
                    <button type="button" @click="openSettingsModal('danger')" class="inline-flex h-10 items-center justify-center rounded-xl border border-rose-200 px-4 text-sm font-bold text-rose-700 transition hover:bg-rose-50">
                        Manage Account Deletion
                    </button>
                </div>
            </section>
        </main>

        <aside class="space-y-6 xl:col-start-2">
            <section id="help-support" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Quick Preferences</h2>
                <div class="mt-5 space-y-5">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">Theme preference</p>
                        <div class="mt-2 grid grid-cols-2 gap-2 rounded-xl bg-slate-100 p-1">
                            <button type="button" @click="theme = 'Light'" :class="theme === 'Light' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-600'" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg text-sm font-bold transition">{!! $uiIcon('sun', 'h-4 w-4') !!} Light</button>
                            <button type="button" @click="theme = 'Dark'" :class="theme === 'Dark' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-600'" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg text-sm font-bold transition">{!! $uiIcon('moon', 'h-4 w-4') !!} Dark</button>
                        </div>
                    </div>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Language</span>
                        <select class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option>English</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Time Zone</span>
                        <select class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option>(GMT+8) Asia/Manila</option>
                        </select>
                    </label>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Connected Devices</h2>
                    <p class="mt-1 text-sm text-slate-500">Manage devices connected to your account.</p>
                </div>
                <div class="mt-5 space-y-4">
                    @foreach ($devices as $device)
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 p-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700">{!! $uiIcon($device['icon'], 'h-5 w-5') !!}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-slate-950">{!! $device['name'] !!}</p>
                                <p class="mt-1 truncate text-xs text-slate-500">{!! $device['meta'] !!}</p>
                            </div>
                            @if ($device['current'])
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">Current</span>
                            @else
                                <button type="button" @click="openSettingsModal('remove-device', @js(strip_tags($device['name'])))" class="text-xs font-bold text-rose-700 hover:text-rose-800">Remove</button>
                            @endif
                        </div>
                    @endforeach
                </div>
                <a href="#connected-devices" class="mt-4 inline-flex text-sm font-bold text-blue-700 hover:text-blue-800">View all devices</a>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Help &amp; Support</h2>
                <p class="mt-1 text-sm text-slate-500">Need help? We're here for you.</p>
                <div class="mt-5 space-y-3">
                    @foreach ($helpLinks as $link)
                        <a href="#" class="flex items-center gap-3 rounded-2xl border border-slate-200 p-3 transition hover:bg-slate-50">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700 ring-1 ring-blue-100">{!! $uiIcon($link['icon'], 'h-5 w-5') !!}</span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-bold text-slate-950">{{ $link['label'] }}</span>
                                <span class="mt-1 block text-xs text-slate-500">{{ $link['description'] }}</span>
                            </span>
                            <span class="text-slate-400">{!! $uiIcon('external', 'h-4 w-4') !!}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        </aside>
    </section>

    <div x-show="modalType" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" @click.self="closeSettingsModal()">
        <div class="max-h-[85vh] w-full overflow-hidden rounded-2xl bg-white shadow-2xl" :class="modalType === 'danger' || modalType === 'remove-device' ? 'max-w-lg' : 'max-w-3xl'">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-950" x-text="modalType === 'password' ? 'Change Password' : modalType === 'photo' ? 'Change Profile Photo' : modalType === '2fa' ? 'Two-Factor Authentication' : modalType === 'owner-setting' ? selectedSetting : modalType === 'remove-device' ? 'Remove Device?' : 'Deactivate or Delete Account'"></h2>
                    <p class="text-sm text-slate-500">Account settings</p>
                </div>
                <button type="button" @click="closeSettingsModal()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900">X</button>
            </div>
            <div class="max-h-[calc(85vh-138px)] overflow-y-auto p-6">
                <div x-show="modalType === 'password'" class="grid gap-4">
                    <label><span class="text-sm font-semibold text-slate-700">Current Password</span><input type="password" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm"></label>
                    <label><span class="text-sm font-semibold text-slate-700">New Password</span><input type="password" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm"></label>
                    <label><span class="text-sm font-semibold text-slate-700">Confirm Password</span><input type="password" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm"></label>
                </div>
                <div x-show="modalType === 'photo'" class="space-y-4">
                    <button type="button" class="flex min-h-40 w-full flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 text-center text-sm text-slate-500 hover:border-blue-300 hover:bg-blue-50">{!! $uiIcon('camera', 'h-6 w-6') !!}<span class="mt-2">Upload a new profile photo</span></button>
                    <p class="text-sm text-slate-500">JPG, PNG or GIF. Max size 2MB.</p>
                </div>
                <div x-show="modalType === '2fa'" class="rounded-2xl bg-violet-50 p-4 text-sm text-violet-800">Two-factor authentication is marked as Soon and can be connected when backend support is available.</div>
                <div x-show="modalType === 'owner-setting'" class="grid gap-4">
                    <label><span class="text-sm font-semibold text-slate-700" x-text="selectedSetting"></span><input type="text" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm" placeholder="Enter updated value"></label>
                </div>
                <div x-show="modalType === 'remove-device'" class="rounded-2xl bg-rose-50 p-4 text-sm text-rose-800">Remove <span class="font-bold" x-text="selectedSetting"></span> from connected devices?</div>
                <div x-show="modalType === 'danger'" class="rounded-2xl bg-rose-50 p-4 text-sm text-rose-800">Deactivate your account temporarily or permanently delete your account. This requires confirmation before any destructive action.</div>
            </div>
            <div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:justify-end">
                <button type="button" @click="closeSettingsModal()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                <button x-show="modalType !== 'danger' && modalType !== 'remove-device'" type="button" @click="closeSettingsModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Save Changes</button>
                <button x-show="modalType === 'remove-device'" type="button" @click="closeSettingsModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white hover:bg-rose-700">Remove Device</button>
                <button x-show="modalType === 'danger'" type="button" @click="closeSettingsModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white hover:bg-rose-700">Continue</button>
            </div>
        </div>
    </div>
</div>
