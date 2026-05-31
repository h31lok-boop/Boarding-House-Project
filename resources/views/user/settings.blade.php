<x-layouts.dashboard>
<x-user.shell>
@php
    $notifications = $notificationPreferences ?? [];

    $rawImg = $tenant->profile_image;
    $hasPhoto = filled($rawImg);
    $profileImageUrl = $hasPhoto
        ? (\Illuminate\Support\Str::startsWith($rawImg, ['http://', 'https://'])
            ? $rawImg
            : \Illuminate\Support\Facades\Storage::url($rawImg))
        : null;

    $displayName = trim((string) $tenant->name) ?: 'Tenant';
    $nameParts   = explode(' ', $displayName, 2);
    $firstName   = $nameParts[0] ?? '';
    $lastName    = $nameParts[1] ?? '';
    $initials    = collect(explode(' ', $displayName))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('');
    $email       = $tenant->email ?: '';
    $phone       = $tenant->phone ?: ($tenant->contact_number ?: '');
    $memberSince = optional($tenant->created_at)->format('M d, Y') ?? 'Recently';
    $memberAgo   = $tenant->created_at ? \Carbon\Carbon::parse($tenant->created_at)->diffForHumans(null, true) : '';

    $hasMatchProfiles = \Illuminate\Support\Facades\Schema::hasTable('tenant_match_profiles');
    $matchDone = $hasMatchProfiles
        && $tenant->relationLoaded('tenantMatchProfile')
        && (bool) $tenant->tenantMatchProfile?->completed_at;

    $completionItems = [
        ['label' => 'Personal Information',       'done' => filled($tenant->name),   'optional' => false],
        ['label' => 'Contact Information',         'done' => filled($email) && filled($phone), 'optional' => false],
        ['label' => 'Profile Photo',               'done' => $hasPhoto,               'optional' => false],
        ['label' => 'Account Security',            'done' => true,                    'optional' => false],
        ['label' => 'Government ID (Optional)',    'done' => false,                   'optional' => true],
    ];
    $completedCount    = collect($completionItems)->filter(fn($i) => $i['done'])->count();
    $completionPercent = (int) round(($completedCount / max(count($completionItems), 1)) * 100);

    $isVerified = $tenant->email_verified_at !== null;
    $isActive   = $tenant->is_active ?? true;

    $circumference = 2 * M_PI * 45;
    $dashOffset    = $circumference * (1 - $completionPercent / 100);
@endphp

<div
    x-data="{
        photoPreview: {{ $hasPhoto ? "'".e($profileImageUrl)."'" : 'null' }},
        initials: '{{ $initials }}',
        firstName: '{{ e($firstName) }}',
        lastName: '{{ e($lastName) }}',
        showCurPwd: false,
        showNewPwd: false,
        showConPwd: false,
        pwdStrength: '',
        checkStrength(v) {
            if (!v) { this.pwdStrength = ''; return; }
            const s = [/[A-Z]/.test(v), /[a-z]/.test(v), /[0-9]/.test(v), /[^A-Za-z0-9]/.test(v)].filter(Boolean).length;
            this.pwdStrength = v.length < 6 ? 'Weak' : s <= 2 ? 'Weak' : s === 3 ? 'Fair' : 'Strong';
        },
        get fullName() { return (this.firstName + ' ' + this.lastName).trim(); },
        emailNotify: {{ ($notifications['booking_updates'] ?? true) ? 'true' : 'false' }},
        smsNotify: {{ ($notifications['payment_reminders'] ?? true) ? 'true' : 'false' }},
        bookingReminders: {{ ($notifications['booking_updates'] ?? true) ? 'true' : 'false' }},
        promoUpdates: false,
        showOnline: true,
    }"
    class="space-y-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs text-gray-400">
        <a href="{{ route('user.dashboard') }}" class="hover:text-gray-600 transition-colors">Dashboard</a>
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="font-medium text-gray-700">Profile Settings</span>
    </nav>


    {{-- Page header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>
        <p class="mt-1 text-sm text-gray-500">Manage your account information, security, notifications, and privacy preferences.</p>
    </div>

    {{-- 3-column grid --}}
    <form method="POST" action="{{ route('user.settings.update') }}" enctype="multipart/form-data"
          x-on:submit="$event.target.querySelector('[name=name]').value = fullName">
        @csrf
        @method('PUT')
        <input type="hidden" name="name" value="{{ old('name', $displayName) }}">
        <input type="file" id="avatarPicker" name="profile_image" accept="image/*" class="sr-only"
               x-on:change="const f=$event.target.files[0]; if(f) photoPreview=URL.createObjectURL(f);">
        <input type="hidden" name="profile_image_remove" value="0" id="removePhotoFlag">
        <input type="hidden" name="notify_payment_reminders" :value="smsNotify ? 1 : 0">
        <input type="hidden" name="notify_booking_updates" :value="bookingReminders ? 1 : 0">
        <input type="hidden" name="notify_ticket_updates" :value="emailNotify ? 1 : 0">

        <div class="grid gap-5 xl:grid-cols-[1fr_350px_252px]">

            {{-- ═══════════ LEFT COLUMN ═══════════ --}}
            <div class="space-y-5">

                {{-- Personal Information --}}
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center gap-2.5 border-b border-gray-100 px-6 py-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        </div>
                        <h2 class="text-sm font-semibold text-gray-900">Personal Information</h2>
                    </div>
                    <div class="px-6 py-5 space-y-5">

                        {{-- Profile photo --}}
                        <div>
                            <p class="mb-3 text-xs font-medium text-gray-500">Profile Photo</p>
                            <div class="flex items-end gap-4">
                                <div class="relative shrink-0">
                                    <div class="h-[72px] w-[72px] overflow-hidden rounded-full border-4 border-white shadow-md ring-2 ring-gray-100">
                                        <template x-if="photoPreview">
                                            <img :src="photoPreview" alt="{{ $displayName }}" class="h-full w-full object-cover">
                                        </template>
                                        <template x-if="!photoPreview">
                                            <div class="flex h-full w-full items-center justify-center text-lg font-bold text-white"
                                                 style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                                                <span x-text="initials">{{ $initials }}</span>
                                            </div>
                                        </template>
                                    </div>
                                    <label for="avatarPicker"
                                           class="absolute bottom-0 right-0 flex h-[22px] w-[22px] cursor-pointer items-center justify-center rounded-full border-2 border-white bg-gray-700 shadow">
                                        <svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </label>
                                </div>
                                <div>
                                    <label for="avatarPicker"
                                           class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                                        Upload Photo
                                    </label>
                                    <p class="mt-1.5 text-[11px] text-gray-400">JPG, PNG or GIF. Max size 2MB.</p>
                                </div>
                            </div>
                        </div>

                        {{-- First / Last Name --}}
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500">First Name</label>
                                <input x-model="firstName"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                       placeholder="First name">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500">Last Name</label>
                                <input x-model="lastName"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                       placeholder="Last name">
                            </div>
                        </div>

                        {{-- DOB / Gender --}}
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500">Date of Birth</label>
                                <div class="relative">
                                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $tenant->date_of_birth ?? '') }}"
                                           class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500">Gender</label>
                                <div class="relative">
                                    <select name="gender"
                                            class="w-full appearance-none rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                        <option value="">Select gender</option>
                                        <option value="male"   {{ old('gender', $tenant->gender ?? '') === 'male'   ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $tenant->gender ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other"  {{ old('gender', $tenant->gender ?? '') === 'other'  ? 'selected' : '' }}>Other</option>
                                    </select>
                                    <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Contact Information --}}
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center gap-2.5 border-b border-gray-100 px-6 py-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        </div>
                        <h2 class="text-sm font-semibold text-gray-900">Contact Information</h2>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-500">Email Address</label>
                            <input name="email" type="email" required value="{{ old('email', $email) }}"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                   placeholder="you@email.com">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-500">Phone Number</label>
                            <div class="flex gap-2">
                                <div class="flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700 shrink-0">
                                    <span class="text-base leading-none">🇵🇭</span>
                                    <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                                <input name="phone" type="tel" value="{{ old('phone', $phone) }}"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                       placeholder="+63 912 345 6789">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-500">Current Address</label>
                            <textarea name="address" rows="2"
                                      class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 resize-none"
                                      placeholder="Street, Barangay, City, Province, ZIP">{{ old('address', $tenant->address ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Notification Preferences --}}
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center gap-2.5 border-b border-gray-100 px-6 py-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">Notification Preferences</h2>
                            <p class="text-xs text-gray-400">Choose how you want to be notified.</p>
                        </div>
                    </div>
                    <div class="px-6 py-5 grid grid-cols-2 gap-x-6">
                        @foreach([
                            ['model' => 'emailNotify',      'label' => 'Email Notifications',  'desc' => 'Receive important updates via email.'],
                            ['model' => 'bookingReminders', 'label' => 'Booking Reminders',     'desc' => 'Get reminders for upcoming bookings.'],
                            ['model' => 'smsNotify',        'label' => 'SMS Notifications',     'desc' => 'Receive important updates via SMS.'],
                            ['model' => 'promoUpdates',     'label' => 'Promotions & Updates',  'desc' => 'Receive news, tips, and special offers.'],
                        ] as $i => $notif)
                        <div class="flex items-center justify-between {{ $i >= 2 ? 'border-t border-gray-100 pt-4 mt-4' : 'pb-4' }}">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $notif['label'] }}</p>
                                <p class="text-xs text-gray-400">{{ $notif['desc'] }}</p>
                            </div>
                            <button type="button"
                                    x-on:click="{{ $notif['model'] }} = !{{ $notif['model'] }}"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                                    :class="{{ $notif['model'] }} ? 'bg-indigo-600' : 'bg-gray-200'"
                                    role="switch" :aria-checked="{{ $notif['model'] }}">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"
                                      :class="{{ $notif['model'] }} ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Privacy Settings --}}
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center gap-2.5 border-b border-gray-100 px-6 py-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        </div>
                        <h2 class="text-sm font-semibold text-gray-900">Privacy Settings</h2>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Profile Visibility</p>
                                    <p class="text-xs text-gray-400">Choose who can see your profile and contact information.</p>
                                </div>
                                <div class="relative shrink-0">
                                    <select name="profile_visibility"
                                            class="appearance-none rounded-lg border border-gray-200 bg-gray-50 py-2 pl-3 pr-8 text-xs font-medium text-gray-700 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                        <option value="owners">Boarding House Owners Only</option>
                                        <option value="public">Public</option>
                                        <option value="private">Private</option>
                                    </select>
                                    <svg class="pointer-events-none absolute right-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                            <div>
                                <p class="text-sm font-medium text-gray-800">Show Online Status</p>
                                <p class="text-xs text-gray-400">Allow others to see when you are online.</p>
                            </div>
                            <button type="button"
                                    x-on:click="showOnline = !showOnline"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                                    :class="showOnline ? 'bg-indigo-600' : 'bg-gray-200'"
                                    role="switch" :aria-checked="showOnline">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"
                                      :class="showOnline ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════ MIDDLE COLUMN — Account Security ═══════════ --}}
            <div class="space-y-5">
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center gap-2.5 border-b border-gray-100 px-6 py-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                        </div>
                        <h2 class="text-sm font-semibold text-gray-900">Account Security</h2>
                    </div>

                    {{-- Change Password --}}
                    <div class="border-b border-gray-100 px-6 py-5">
                        <h3 class="mb-0.5 text-sm font-semibold text-gray-900">Change Password</h3>
                        <p class="mb-4 text-xs text-gray-400">Ensure your account is using a long, random password to stay secure.</p>
                        <div class="space-y-3">
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500">Current Password</label>
                                <div class="relative">
                                    <input name="current_password"
                                           :type="showCurPwd ? 'text' : 'password'"
                                           class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 pr-10 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                           placeholder="Current password">
                                    <button type="button" x-on:click="showCurPwd = !showCurPwd"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg x-show="!showCurPwd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <svg x-show="showCurPwd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500">New Password</label>
                                <div class="relative">
                                    <input name="password"
                                           :type="showNewPwd ? 'text' : 'password'"
                                           x-on:input="checkStrength($event.target.value)"
                                           class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 pr-10 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                           placeholder="New password">
                                    <button type="button" x-on:click="showNewPwd = !showNewPwd"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg x-show="!showNewPwd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <svg x-show="showNewPwd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                    </button>
                                </div>
                                {{-- Strength bar --}}
                                <div x-show="pwdStrength" x-cloak class="mt-2 flex items-center gap-2">
                                    <div class="relative h-1.5 flex-1 rounded-full bg-gray-200">
                                        <div class="absolute inset-y-0 left-0 rounded-full transition-all duration-300"
                                             :style="'width:' + (pwdStrength === 'Strong' ? '100%' : pwdStrength === 'Fair' ? '60%' : '30%')"
                                             :class="pwdStrength === 'Strong' ? 'bg-emerald-400' : pwdStrength === 'Fair' ? 'bg-amber-400' : 'bg-rose-400'"></div>
                                    </div>
                                    <span class="shrink-0 text-xs font-medium"
                                          :class="pwdStrength === 'Strong' ? 'text-emerald-600' : pwdStrength === 'Fair' ? 'text-amber-600' : 'text-rose-500'"
                                          x-text="pwdStrength"></span>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500">Confirm New Password</label>
                                <div class="relative">
                                    <input name="password_confirmation"
                                           :type="showConPwd ? 'text' : 'password'"
                                           class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 pr-10 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                           placeholder="Confirm new password">
                                    <button type="button" x-on:click="showConPwd = !showConPwd"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg x-show="!showConPwd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <svg x-show="showConPwd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                    </button>
                                </div>
                            </div>
                            <button type="button"
                                    class="mt-1 w-full rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                Update Password
                            </button>
                        </div>
                    </div>

                    {{-- Two-Factor Auth --}}
                    <div class="border-b border-gray-100 px-6 py-5">
                        <h3 class="mb-0.5 text-sm font-semibold text-gray-900">Two-Factor Authentication</h3>
                        <p class="mb-4 text-xs text-gray-400">Add an extra layer of security to your account.</p>
                        <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm border border-gray-100">
                                <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 3.75h3m-3 3.75h3"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">SMS Authentication</p>
                                <p class="text-xs text-gray-400">Receive a code via SMS to verify your identity.</p>
                            </div>
                            <div x-data="{ on: true }">
                                <button type="button" x-on:click="on = !on"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                                        :class="on ? 'bg-indigo-600' : 'bg-gray-200'"
                                        role="switch">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"
                                          :class="on ? 'translate-x-5' : 'translate-x-0'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Active Sessions --}}
                    <div class="px-6 py-5">
                        <h3 class="mb-0.5 text-sm font-semibold text-gray-900">Active Sessions</h3>
                        <p class="mb-4 text-xs text-gray-400">Manage your active sessions on different devices.</p>
                        <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm border border-gray-100">
                                <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="text-sm font-medium text-gray-900">Windows &bull; Chrome</p>
                                    <span class="rounded-full bg-gray-800 px-2 py-0.5 text-[10px] font-semibold text-white">Current Session</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5">Cebu City, Philippines &bull; Today, 9:15 AM</p>
                            </div>
                            <button type="button" class="shrink-0 text-gray-400 hover:text-gray-600 p-1">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                            </button>
                        </div>
                        <button type="button"
                                class="mt-3 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            Log out from other devices
                        </button>
                    </div>
                </div>
            </div>

            {{-- ═══════════ RIGHT COLUMN — Account Status ═══════════ --}}
            <div class="space-y-5">
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-4">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h2 class="text-sm font-semibold text-gray-900">Account Status</h2>
                    </div>
                    <div class="px-5 py-5">

                        {{-- Progress ring --}}
                        <div class="flex flex-col items-center">
                            <div class="relative h-28 w-28">
                                <svg class="h-28 w-28 -rotate-90" viewBox="0 0 100 100">
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="#f0fdf4" stroke-width="8"/>
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="#22c55e" stroke-width="8"
                                            stroke-dasharray="{{ round($circumference, 2) }}"
                                            stroke-dashoffset="{{ round($dashOffset, 2) }}"
                                            stroke-linecap="round"/>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-2xl font-bold text-gray-900">{{ $completionPercent }}%</span>
                                </div>
                            </div>
                            <p class="mt-3 text-sm font-semibold text-gray-900">Profile Completeness</p>
                            <p class="mt-1 text-center text-xs text-gray-400 leading-relaxed">
                                @if ($completionPercent >= 80)
                                    Great job! Your profile is almost complete.
                                @elseif ($completionPercent >= 50)
                                    You're halfway there! Keep it up.
                                @else
                                    Complete your profile to get better matches.
                                @endif
                            </p>
                        </div>

                        {{-- Checklist --}}
                        <ul class="mt-5 space-y-2.5">
                            @foreach ($completionItems as $item)
                                <li class="flex items-center gap-2.5 text-xs">
                                    @if ($item['done'])
                                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                                            <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                        <span class="text-gray-700 font-medium">{{ $item['label'] }}</span>
                                    @elseif ($item['optional'])
                                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-100">
                                            <svg class="h-3 w-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                                        </span>
                                        <span class="text-gray-500">{{ $item['label'] }}</span>
                                    @else
                                        <span class="h-5 w-5 shrink-0 rounded-full border-2 border-gray-200"></span>
                                        <span class="text-gray-400">{{ $item['label'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        {{-- Divider --}}
                        <div class="my-5 border-t border-gray-100"></div>

                        {{-- Member Since --}}
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100">
                                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Member Since</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $memberSince }}</p>
                                @if ($memberAgo)
                                    <p class="text-xs text-gray-400">{{ $memberAgo }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 flex items-start gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100">
                                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Account Type</p>
                                <span class="mt-1 inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">
                                    {{ $isVerified ? 'Verified Member' : 'Member' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 flex items-start gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100">
                                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Account Status</p>
                                <span class="mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $isActive ? 'Active' : 'Pending' }}
                                </span>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="my-5 border-t border-gray-100"></div>

                        <div>
                            <p class="text-xs font-semibold text-gray-700">Need help with your account?</p>
                            <p class="mt-0.5 text-xs text-gray-400">Our support team is here to help.</p>
                            <a href="{{ route('user.messages') }}"
                               class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">
                                Contact Support
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom actions --}}
        <div class="mt-5 flex items-center justify-end gap-3">
            <a href="{{ route('user.dashboard') }}"
               class="rounded-lg border border-gray-200 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit"
                    class="rounded-lg bg-indigo-700 px-8 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                Save Changes
            </button>
        </div>
    </form>

</div>
</x-user.shell>
</x-layouts.dashboard>
