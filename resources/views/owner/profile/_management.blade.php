@php
    $showPageHeader = $showPageHeader ?? true;
    $isSuperWorkspace = request()->routeIs('superduperadmin.profile*');
    $profileUpdateRoute = $isSuperWorkspace && \Illuminate\Support\Facades\Route::has('superduperadmin.profile.update')
        ? route('superduperadmin.profile.update')
        : (\Illuminate\Support\Facades\Route::has('owner.profile.update') ? route('owner.profile.update') : route('profile.update'));
    $dashboardHref = $isSuperWorkspace && \Illuminate\Support\Facades\Route::has('superduperadmin.dashboard')
        ? route('superduperadmin.dashboard')
        : (\Illuminate\Support\Facades\Route::has('owner.dashboard') ? route('owner.dashboard') : route('dashboard'));
    $settingsHref = $isSuperWorkspace && \Illuminate\Support\Facades\Route::has('superduperadmin.settings')
        ? route('superduperadmin.settings')
        : (\Illuminate\Support\Facades\Route::has('owner.settings') ? route('owner.settings') : '#');

    $iconPaths = [
        'bell' => '<path d="M15 17H9m9-5a6 6 0 1 0-12 0c0 3-1.5 4-2 5h16c-.5-1-2-2-2-5Z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'question' => '<path d="M9.5 9a2.5 2.5 0 1 1 4 2c-1 .7-1.5 1.2-1.5 2.5"/><path d="M12 17h.01"/><circle cx="12" cy="12" r="9"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'edit' => '<path d="m4 20 4.2-1 10-10a2 2 0 0 0-3-3l-10 10L4 20Z"/><path d="m13.5 6.5 4 4"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'building' => '<path d="M4 21V7.5L12 3l8 4.5V21"/><path d="M9 21v-4h6v4"/><path d="M8 10h.01M12 10h.01M16 10h.01"/>',
        'shield' => '<path d="M12 3 19 7v5c0 4.5-2.7 7.9-7 9-4.3-1.1-7-4.5-7-9V7l7-4Z"/><path d="m9 12 2 2 4-4"/>',
        'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
        'device' => '<rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>',
        'check' => '<path d="m4 12 5 5L20 6"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
        'key' => '<circle cx="8" cy="15" r="4"/><path d="m11 12 8-8"/><path d="M17 6h3v3"/>',
        'mail' => '<path d="M4 5h16v14H4z"/><path d="m4 7 8 6 8-6"/>',
        'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19 19 0 0 1-8.3-3 18.7 18.7 0 0 1-5.8-5.8 19 19 0 0 1-3-8.3A2 2 0 0 1 4.7 2h3a2 2 0 0 1 2 1.7l.4 2.7a2 2 0 0 1-.6 1.8L8.2 9.5a15 15 0 0 0 6.3 6.3l1.3-1.3a2 2 0 0 1 1.8-.6l2.7.4a2 2 0 0 1 1.7 2Z"/>',
        'map' => '<path d="M12 21s7-5.4 7-11a7 7 0 0 0-14 0c0 5.6 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
        'calendar' => '<path d="M7 3v4M17 3v4"/><path d="M4 8h16"/><path d="M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z"/>',
        'home' => '<path d="m3 10.5 9-7 9 7"/><path d="M5 9.9V20a1 1 0 0 0 1 1h4.5v-6h3v6H18a1 1 0 0 0 1-1V9.9"/>',
    ];

    $uiIcon = fn ($name, $class = 'h-5 w-5') => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8">'.$iconPaths[$name].'</svg>';

    $personal = [
        ['label' => 'Full Name', 'value' => 'Juan Dela Cruz', 'icon' => 'user'],
        ['label' => 'Email Address', 'value' => 'juandelacruz.owner@email.com', 'icon' => 'mail'],
        ['label' => 'Contact Number', 'value' => '0917 123 4567', 'icon' => 'phone'],
        ['label' => 'Address', 'value' => 'Digos City, Davao del Sur, Philippines', 'icon' => 'map'],
        ['label' => 'Joined Date', 'value' => 'May 15, 2024', 'icon' => 'calendar'],
    ];

    $business = [
        ['label' => 'Default Listing Contact Number', 'value' => '0917 123 4567'],
        ['label' => 'Default Address / Location', 'value' => 'Purok 5, Zone 2, Digos City, Davao del Sur'],
        ['label' => 'Total Listings', 'value' => '12'],
        ['label' => 'Total Rooms', 'value' => '24'],
        ['label' => 'Compliance Status', 'value' => 'Approved'],
    ];

<<<<<<< Updated upstream
    $verification = ['Email Verified', 'Phone Verified', 'Owner Verified'];
=======
    $verification = ['Email Verified', 'Phone Verified', 'Admin Verified'];
>>>>>>> Stashed changes
@endphp

<div
    x-data="{
        modalType: null,
        openProfileModal(type) { this.modalType = type; },
        closeProfileModal() { this.modalType = null; }
    }"
    @keydown.escape.window="closeProfileModal()"
    class="space-y-6"
>
    @if ($showPageHeader)
        <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
<<<<<<< Updated upstream
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Owner Profile</h1>
=======
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Admin Profile</h1>
>>>>>>> Stashed changes
                <p class="mt-1 text-sm text-slate-600 sm:text-base">Manage your personal, business, and account information.</p>
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
<<<<<<< Updated upstream
                        <span class="block text-xs text-slate-500">Owner</span>
=======
                        <span class="block text-xs text-slate-500">Admin</span>
>>>>>>> Stashed changes
                    </span>
                    <span class="text-slate-500">{!! $uiIcon('chevron-down', 'h-4 w-4') !!}</span>
                </button>
            </div>
        </section>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <span class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full bg-blue-700 text-2xl font-bold text-white ring-4 ring-blue-100">JD</span>
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-2xl font-bold text-slate-950">Juan Dela Cruz</h2>
                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">Active</span>
                    </div>
<<<<<<< Updated upstream
                    <p class="mt-1 text-sm font-semibold text-slate-500">Owner</p>
=======
                    <p class="mt-1 text-sm font-semibold text-slate-500">Admin</p>
>>>>>>> Stashed changes
                    <p class="mt-2 max-w-2xl text-sm text-slate-600">Manage profile details, business defaults, verification status, and account security for the DSSC Boarding House System.</p>
                </div>
            </div>
            <button type="button" @click="openProfileModal('edit')" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-700 px-5 text-sm font-bold text-white shadow-sm shadow-blue-900/20 transition hover:bg-blue-800">
                {!! $uiIcon('edit', 'h-4 w-4') !!}
                Edit Profile
            </button>
        </div>
    </section>

    <form method="post" action="{{ $profileUpdateRoute }}" class="space-y-6">
        @csrf
        @method('patch')
        <input type="hidden" name="profile_image_remove" value="0">
        <input type="hidden" name="company_name" value="DSSC Boarding Admin">
        <input type="hidden" name="address" value="Digos City, Davao del Sur, Philippines">
        <input type="hidden" name="business_permit_number" value="OWNER-2024-001">
        <input type="hidden" name="valid_id_type" value="government_id">
        <input type="hidden" name="valid_id_number" value="JD-2024-001">

        <div class="space-y-6">
            <section id="personal-information" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700 ring-1 ring-blue-100">{!! $uiIcon('user', 'h-5 w-5') !!}</span>
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Personal Information</h2>
                        <p class="mt-1 text-sm text-slate-500">Core personal and contact details.</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Full Name</span>
                        <input name="name" type="text" value="Juan Dela Cruz" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Email Address</span>
                        <input name="email" type="email" value="juandelacruz.owner@email.com" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Contact Number</span>
                        <input name="phone" type="text" value="0917 123 4567" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Address</span>
                        <input type="text" value="Digos City, Davao del Sur, Philippines" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Joined Date</span>
                        <input type="text" value="May 15, 2024" disabled class="mt-2 h-11 w-full rounded-xl border-slate-200 bg-slate-100 text-sm font-semibold text-slate-500 shadow-sm">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Role</span>
                        <input type="text" value="Admin" disabled class="mt-2 h-11 w-full rounded-xl border-slate-200 bg-slate-100 text-sm font-semibold text-slate-500 shadow-sm">
                    </label>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-700 ring-1 ring-violet-100">{!! $uiIcon('building', 'h-5 w-5') !!}</span>
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Business Information</h2>
                        <p class="mt-1 text-sm text-slate-500">Boarding house ownership and operating defaults.</p>
                    </div>
                </div>
                <dl class="mt-5 grid gap-4 lg:grid-cols-2">
                    @foreach ($business as $item)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $item['label'] }}</dt>
                            <dd class="mt-2 text-sm font-bold text-slate-950">{{ $item['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">{!! $uiIcon('shield', 'h-5 w-5') !!}</span>
                    <h2 class="text-lg font-bold text-slate-950">Verification Status</h2>
                </div>
                <div class="mt-5 space-y-3">
                    @foreach ($verification as $item)
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-3">
                            <span class="text-sm font-bold text-emerald-800">{{ $item }}</span>
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-600 text-white">{!! $uiIcon('check', 'h-4 w-4') !!}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-orange-700 ring-1 ring-orange-100">{!! $uiIcon('lock', 'h-5 w-5') !!}</span>
                    <h2 class="text-lg font-bold text-slate-950">Account Security</h2>
                </div>
                <div class="mt-5 space-y-3">
                    <button type="button" @click="openProfileModal('password')" class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 p-3 text-left transition hover:bg-slate-50">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-700">{!! $uiIcon('key', 'h-4 w-4') !!}</span>
                        <span class="text-sm font-bold text-slate-950">Change Password</span>
                    </button>
                    <button type="button" @click="openProfileModal('2fa')" class="flex w-full items-center justify-between gap-3 rounded-2xl border border-slate-200 p-3 text-left transition hover:bg-slate-50">
                        <span class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-700">{!! $uiIcon('lock', 'h-4 w-4') !!}</span>
                            <span class="text-sm font-bold text-slate-950">Two-Factor Authentication</span>
                        </span>
                        <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[11px] font-bold text-violet-700">Soon</span>
                    </button>
                    <button type="button" @click="openProfileModal('devices')" class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 p-3 text-left transition hover:bg-slate-50">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-700">{!! $uiIcon('device', 'h-4 w-4') !!}</span>
                        <span class="text-sm font-bold text-slate-950">Connected Devices</span>
                    </button>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Profile Summary</h2>
                <dl class="mt-4 space-y-3">
                    @foreach ($personal as $item)
                        <div class="flex gap-3">
                            <span class="mt-0.5 text-slate-400">{!! $uiIcon($item['icon'], 'h-4 w-4') !!}</span>
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $item['label'] }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $item['value'] }}</dd>
                            </div>
                        </div>
                    @endforeach
                </dl>
            </section>
        </div>

        <div>
            <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-end">
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-700 px-5 text-sm font-bold text-white shadow-sm shadow-blue-900/20 transition hover:bg-blue-800">
                    Save Changes
                </button>
                <a href="{{ $dashboardHref }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </a>
                <button type="button" @click="openProfileModal('logout')" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-rose-200 px-5 text-sm font-bold text-rose-700 transition hover:bg-rose-50">
                    {!! $uiIcon('logout', 'h-4 w-4') !!}
                    Logout
                </button>
            </div>
        </div>
    </form>

    <form id="owner-profile-logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>

    <div x-show="modalType" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" @click.self="closeProfileModal()">
        <div class="max-h-[85vh] w-full overflow-hidden rounded-2xl bg-white shadow-2xl" :class="modalType === 'logout' ? 'max-w-lg' : 'max-w-3xl'">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-950" x-text="modalType === 'edit' ? 'Edit Profile' : modalType === 'password' ? 'Change Password' : modalType === '2fa' ? 'Two-Factor Authentication' : modalType === 'devices' ? 'Connected Devices' : 'Logout?'"></h2>
                    <p class="text-sm text-slate-500">Juan Dela Cruz</p>
                </div>
                <button type="button" @click="closeProfileModal()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900">X</button>
            </div>
            <div class="max-h-[calc(85vh-138px)] overflow-y-auto p-6">
                <div x-show="modalType === 'edit'" class="grid gap-4 sm:grid-cols-2">
                    <label><span class="text-sm font-semibold text-slate-700">Full Name</span><input type="text" value="Juan Dela Cruz" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm"></label>
                    <label><span class="text-sm font-semibold text-slate-700">Email Address</span><input type="email" value="juandelacruz.owner@email.com" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm"></label>
                    <label><span class="text-sm font-semibold text-slate-700">Contact Number</span><input type="text" value="0917 123 4567" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm"></label>
                    <label><span class="text-sm font-semibold text-slate-700">Address</span><input type="text" value="Digos City, Davao del Sur, Philippines" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm"></label>
                </div>
                <div x-show="modalType === 'password'" class="grid gap-4">
                    <label><span class="text-sm font-semibold text-slate-700">Current Password</span><input type="password" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm"></label>
                    <label><span class="text-sm font-semibold text-slate-700">New Password</span><input type="password" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm"></label>
                    <label><span class="text-sm font-semibold text-slate-700">Confirm Password</span><input type="password" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm"></label>
                </div>
                <div x-show="modalType === '2fa'" class="rounded-2xl bg-violet-50 p-4 text-sm text-violet-800">Two-factor authentication is a placeholder until backend support is available.</div>
                <div x-show="modalType === 'devices'" class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-700">Connected device management is available from Settings. This modal keeps the owner in the profile page flow.</div>
                <div x-show="modalType === 'logout'" class="rounded-2xl bg-rose-50 p-4 text-sm text-rose-800">Are you sure you want to log out?</div>
            </div>
            <div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:justify-end">
                <button type="button" @click="closeProfileModal()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                <button x-show="modalType !== 'logout'" type="button" @click="closeProfileModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Save Changes</button>
                <button x-show="modalType === 'logout'" type="submit" form="owner-profile-logout-form" class="inline-flex h-10 items-center justify-center rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white hover:bg-rose-700">Logout</button>
            </div>
        </div>
    </div>
</div>
