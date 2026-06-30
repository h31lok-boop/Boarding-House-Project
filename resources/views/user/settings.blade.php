<x-layouts.dashboard>
<x-user.shell>
@php
    $notifications = array_merge([
        'email_notifications' => true,
        'sms_notifications' => true,
        'booking_reminders' => true,
        'promotions_updates' => false,
    ], $notificationPreferences ?? []);

    $privacy = array_merge([
        'show_profile_to_owners' => true,
        'allow_owner_messages' => true,
        'allow_matchmaking_data' => true,
    ], $privacyPreferences ?? []);

    $rawImg = $tenant->profile_photo ?: $tenant->profile_image;
    $hasPhoto = filled($rawImg);
    $profileImageUrl = $hasPhoto
        ? (\Illuminate\Support\Str::startsWith($rawImg, ['http://', 'https://', '/'])
            ? $rawImg
            : \Illuminate\Support\Facades\Storage::url($rawImg))
        : null;

    $displayName = trim((string) $tenant->name) ?: 'Tenant';
    $nameParts = explode(' ', $displayName, 2);
    $firstName = old('first_name', $tenant->first_name ?: ($nameParts[0] ?? ''));
    $lastName = old('last_name', $tenant->last_name ?: ($nameParts[1] ?? ''));
    $initials = collect(explode(' ', trim($displayName)))
        ->filter()
        ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
        ->take(2)
        ->implode('') ?: 'BM';

    $email = old('email', $tenant->email ?: '');
    $phone = old('phone_number', $tenant->phone_number ?: ($tenant->phone ?: ($tenant->contact_number ?: '')));
    $currentAddress = old('current_address', $tenant->current_address ?? '');
    $dateOfBirth = old('date_of_birth', $tenant->date_of_birth ? \Carbon\Carbon::parse($tenant->date_of_birth)->format('Y-m-d') : '');
    $gender = old('gender', $tenant->gender ?? '');
    $gender = match (strtolower((string) $gender)) {
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
        'prefer not to say' => 'Prefer not to say',
        default => $gender,
    };

    $memberSince = optional($tenant->created_at)->format('M d, Y') ?? 'Recently';
    $memberAgo = $tenant->created_at ? \Carbon\Carbon::parse($tenant->created_at)->diffForHumans(null, true) : '';
    $completionItems = $completionItems ?? [];
    $completionPercent = (int) ($completionPercent ?? 0);
    $circumference = 2 * M_PI * 45;
    $dashOffset = $circumference * (1 - $completionPercent / 100);
    $accountStatus = $accountStatus ?? 'Active';
    $accountType = $accountType ?? ($tenant->email_verified_at ? 'Verified Member' : 'Member');
    $statusTone = match ($accountStatus) {
        'Active' => 'bg-emerald-100 text-emerald-700',
        'Suspended' => 'bg-rose-100 text-rose-700',
        default => 'bg-amber-100 text-amber-700',
    };
    $sessionDetails = array_merge([
        'device' => 'Current Device',
        'location' => 'Current Device',
        'activity' => now()->format('M d, Y h:i A'),
    ], $sessionDetails ?? []);
@endphp

<div x-data="settingsPage()" class="space-y-5">

    <div x-show="toast" x-transition x-cloak
         class="fixed right-4 top-4 z-[9999] rounded-xl border border-indigo-100 bg-white px-4 py-3 text-sm font-semibold text-indigo-700 shadow-lg"
         x-text="toast"></div>

    <x-user.page-header
        eyebrow="Account Center"
        title="Profile Settings"
        subtitle="Manage your account information, security, notifications, and privacy preferences."
    />

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_330px_240px]">
        <div class="space-y-4">
            <form method="POST" action="{{ route('user.settings.personal.update') }}" enctype="multipart/form-data" class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                @csrf
                @method('PUT')
                <input type="file" id="avatarPicker" name="profile_photo" accept="image/jpeg,image/png,image/gif" class="sr-only"
                       x-on:change="const file = $event.target.files[0]; if (file) photoPreview = URL.createObjectURL(file);">

                <div class="flex items-center gap-2.5 border-b border-gray-100 px-5 py-3.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                        <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-900">Personal Information</h2>
                </div>

                <div class="space-y-4 px-5 py-4">
                    <div>
                        <p class="mb-2.5 text-xs font-medium text-gray-500">Profile Photo</p>
                        <div class="flex items-end gap-3">
                            <div class="relative shrink-0">
                                <div class="h-16 w-16 overflow-hidden rounded-full border-4 border-white shadow-md ring-2 ring-gray-100">
                                    <template x-if="photoPreview">
                                        <img :src="photoPreview" alt="{{ $displayName }}" class="h-full w-full object-cover">
                                    </template>
                                    <template x-if="!photoPreview">
                                        <div class="flex h-full w-full items-center justify-center bg-blue-600 text-base font-bold text-white">
                                            <span x-text="initials">{{ $initials }}</span>
                                        </div>
                                    </template>
                                </div>
                                <label for="avatarPicker" class="absolute bottom-0 right-0 flex h-[22px] w-[22px] cursor-pointer items-center justify-center rounded-full border-2 border-white bg-gray-700 shadow">
                                    <svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </label>
                            </div>
                            <div>
                                <label for="avatarPicker" class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50">
                                    Upload Photo
                                </label>
                                <p class="mt-1.5 text-[11px] text-gray-400">JPG, PNG or GIF. Max size 2MB.</p>
                                @error('profile_photo')
                                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3.5 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="mb-1.5 block text-xs font-medium text-gray-500">First Name</label>
                            <input id="first_name" name="first_name" x-model="firstName" required
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                   placeholder="First name">
                            @error('first_name')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="last_name" class="mb-1.5 block text-xs font-medium text-gray-500">Last Name</label>
                            <input id="last_name" name="last_name" x-model="lastName" required
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                   placeholder="Last name">
                            @error('last_name')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-3.5 sm:grid-cols-2">
                        <div>
                            <label for="date_of_birth" class="mb-1.5 block text-xs font-medium text-gray-500">Date of Birth</label>
                            <input id="date_of_birth" type="date" name="date_of_birth" value="{{ $dateOfBirth }}"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            @error('date_of_birth')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="gender" class="mb-1.5 block text-xs font-medium text-gray-500">Gender</label>
                            <div class="relative">
                                <select id="gender" name="gender"
                                        class="w-full appearance-none rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                    <option value="">Select gender</option>
                                    @foreach (['Male', 'Female', 'Other', 'Prefer not to say'] as $option)
                                        <option value="{{ $option }}" @selected($gender === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                                <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                            @error('gender')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Save Personal Info
                        </button>
                    </div>
                </div>
            </form>

            <form method="POST" action="{{ route('user.settings.contact.update') }}" class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                @csrf
                @method('PUT')
                <div class="flex items-center gap-2.5 border-b border-gray-100 px-5 py-3.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                        <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-900">Contact Information</h2>
                </div>

                <div class="space-y-3.5 px-5 py-4">
                    <div>
                        <label for="email" class="mb-1.5 block text-xs font-medium text-gray-500">Email Address</label>
                        <input id="email" name="email" type="email" required value="{{ $email }}"
                               class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                               placeholder="you@email.com">
                        @error('email')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="phone_number" class="mb-1.5 block text-xs font-medium text-gray-500">Phone Number</label>
                        <div class="flex gap-2">
                            <div class="flex shrink-0 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700">
                                +63
                            </div>
                            <input id="phone_number" name="phone_number" type="tel" value="{{ $phone }}"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                   placeholder="912 345 6789">
                        </div>
                        @error('phone_number')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="current_address" class="mb-1.5 block text-xs font-medium text-gray-500">Current Address</label>
                        <textarea id="current_address" name="current_address" rows="2"
                                  class="w-full resize-none rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                  placeholder="Street, Barangay, City, Province, ZIP">{{ $currentAddress }}</textarea>
                        @error('current_address')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Save Contact Info
                        </button>
                    </div>
                </div>
            </form>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center gap-2.5 border-b border-gray-100 px-5 py-3.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                        <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Notification Preferences</h2>
                        <p class="text-xs text-gray-400">Choose how you want to be notified.</p>
                    </div>
                </div>
                <div class="grid gap-x-5 px-5 py-4 sm:grid-cols-2">
                    @foreach ([
                        ['field' => 'email_notifications', 'model' => 'emailNotify', 'label' => 'Email Notifications', 'desc' => 'Receive important updates via email.'],
                        ['field' => 'booking_reminders', 'model' => 'bookingReminders', 'label' => 'Booking Reminders', 'desc' => 'Get reminders for upcoming bookings.'],
                        ['field' => 'sms_notifications', 'model' => 'smsNotify', 'label' => 'SMS Notifications', 'desc' => 'Receive important updates via SMS.'],
                        ['field' => 'promotions_updates', 'model' => 'promoUpdates', 'label' => 'Promotions & Updates', 'desc' => 'Receive news, tips, and special offers.'],
                    ] as $index => $notif)
                        <div class="flex items-center justify-between gap-4 {{ $index >= 2 ? 'border-t border-gray-100 pt-3.5 sm:mt-3.5' : 'pb-3.5' }}">
                            <div>
                                <p class="text-[13px] font-medium text-gray-800">{{ $notif['label'] }}</p>
                                <p class="text-xs text-gray-400">{{ $notif['desc'] }}</p>
                            </div>
                            <button type="button"
                                    x-on:click="togglePreference('{{ $notif['field'] }}', '{{ $notif['model'] }}')"
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

            <form method="POST" action="{{ route('user.settings.privacy.update') }}" class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                @csrf
                @method('PUT')
                <input type="hidden" name="show_profile_to_owners" :value="showProfileToOwners ? 1 : 0">
                <input type="hidden" name="allow_owner_messages" :value="allowOwnerMessages ? 1 : 0">
                <input type="hidden" name="allow_matchmaking_data" :value="allowMatchmakingData ? 1 : 0">

                <div class="flex items-center gap-2.5 border-b border-gray-100 px-5 py-3.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                        <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-900">Privacy Settings</h2>
                </div>
                <div class="space-y-3.5 px-5 py-4">
                    @foreach ([
                        ['model' => 'showProfileToOwners', 'label' => 'Show my profile to property owners', 'desc' => 'Allow verified owners to review your basic profile for inquiries and reservations.'],
                        ['model' => 'allowOwnerMessages', 'label' => 'Allow owners to message me', 'desc' => 'Permit property owners to contact you about rooms, viewing schedules, and requests.'],
                        ['model' => 'allowMatchmakingData', 'label' => 'Use my preferences for matchmaking', 'desc' => 'Allow BoardMatch to use your preferences for better recommendations.'],
                    ] as $index => $item)
                        <div class="flex items-center justify-between gap-4 {{ $index > 0 ? 'border-t border-gray-100 pt-3.5' : '' }}">
                            <div>
                                <p class="text-[13px] font-medium text-gray-800">{{ $item['label'] }}</p>
                                <p class="text-xs text-gray-400">{{ $item['desc'] }}</p>
                            </div>
                            <button type="button"
                                    x-on:click="{{ $item['model'] }} = !{{ $item['model'] }}"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                                    :class="{{ $item['model'] }} ? 'bg-indigo-600' : 'bg-gray-200'"
                                    role="switch" :aria-checked="{{ $item['model'] }}">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"
                                      :class="{{ $item['model'] }} ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                        </div>
                    @endforeach
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Save Privacy
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center gap-2.5 border-b border-gray-100 px-5 py-3.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                        <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-900">Account Security</h2>
                </div>

                <form method="POST" action="{{ route('user.settings.password.update') }}" class="border-b border-gray-100 px-5 py-4">
                    @csrf
                    @method('PUT')
                    <h3 class="mb-0.5 text-sm font-semibold text-gray-900">Change Password</h3>
                    <p class="mb-3.5 text-xs text-gray-400">Ensure your account is using a strong password to stay secure.</p>
                    <div class="space-y-3">
                        <div>
                            <label for="settings_current_password" class="mb-1.5 block text-xs font-medium text-gray-500">Current Password</label>
                            <div class="relative">
                                <input id="settings_current_password" name="current_password" :type="showCurPwd ? 'text' : 'password'" autocomplete="current-password"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 pr-10 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                       placeholder="Current password">
                                <button type="button" x-on:click="showCurPwd = !showCurPwd" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg x-show="!showCurPwd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <svg x-show="showCurPwd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="settings_password" class="mb-1.5 block text-xs font-medium text-gray-500">New Password</label>
                            <div class="relative">
                                <input id="settings_password" name="password" :type="showNewPwd ? 'text' : 'password'" x-on:input="checkStrength($event.target.value)" autocomplete="new-password"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 pr-10 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                       placeholder="New password">
                                <button type="button" x-on:click="showNewPwd = !showNewPwd" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg x-show="!showNewPwd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <svg x-show="showNewPwd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                </button>
                            </div>
                            <div x-show="pwdStrength" x-cloak class="mt-2 flex items-center gap-2">
                                <div class="relative h-1.5 flex-1 rounded-full bg-gray-200">
                                    <div class="absolute inset-y-0 left-0 rounded-full transition-all duration-300"
                                         :style="'width:' + (pwdStrength === 'Strong' ? '100%' : pwdStrength === 'Medium' ? '60%' : '30%')"
                                         :class="pwdStrength === 'Strong' ? 'bg-emerald-400' : pwdStrength === 'Medium' ? 'bg-amber-400' : 'bg-rose-400'"></div>
                                </div>
                                <span class="shrink-0 text-xs font-medium"
                                      :class="pwdStrength === 'Strong' ? 'text-emerald-600' : pwdStrength === 'Medium' ? 'text-amber-600' : 'text-rose-500'"
                                      x-text="pwdStrength"></span>
                            </div>
                            @error('password')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="settings_password_confirmation" class="mb-1.5 block text-xs font-medium text-gray-500">Confirm New Password</label>
                            <div class="relative">
                                <input id="settings_password_confirmation" name="password_confirmation" :type="showConPwd ? 'text' : 'password'" autocomplete="new-password"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 pr-10 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                       placeholder="Confirm new password">
                                <button type="button" x-on:click="showConPwd = !showConPwd" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg x-show="!showConPwd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <svg x-show="showConPwd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="mt-1 w-full rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Update Password
                        </button>
                    </div>
                </form>

                <div class="border-b border-gray-100 px-5 py-4">
                    <h3 class="mb-0.5 text-sm font-semibold text-gray-900">Two-Factor Authentication</h3>
                    <p class="mb-3.5 text-xs text-gray-400">Add an extra layer of security to your account.</p>
                    <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 p-3.5">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-100 bg-white shadow-sm">
                            <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 3.75h3m-3 3.75h3"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900">SMS Authentication</p>
                            <p class="text-xs text-gray-400">Receive a code via SMS to verify your identity.</p>
                        </div>
                        <button type="button" x-on:click="toggleTwoFactor()"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                                :class="smsTwoFactor ? 'bg-indigo-600' : 'bg-gray-200'"
                                role="switch" :aria-checked="smsTwoFactor">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"
                                  :class="smsTwoFactor ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                </div>

                <div class="px-5 py-4">
                    <h3 class="mb-0.5 text-sm font-semibold text-gray-900">Active Sessions</h3>
                    <p class="mb-3.5 text-xs text-gray-400">Manage your active sessions on different devices.</p>
                    <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 p-3.5">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-100 bg-white shadow-sm">
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-medium text-gray-900">{{ $sessionDetails['device'] }}</p>
                                <span class="rounded-full bg-gray-800 px-2 py-0.5 text-[10px] font-semibold text-white">Current Session</span>
                            </div>
                            <p class="mt-0.5 text-xs text-gray-400">{{ $sessionDetails['location'] }} &bull; {{ $sessionDetails['activity'] }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('user.settings.logout-other-devices') }}" class="mt-3 space-y-2">
                        @csrf
                        <input name="logout_current_password" type="password" autocomplete="current-password"
                               class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                               placeholder="Current password">
                        @error('logout_current_password')
                            <p class="text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            Log out from other devices
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-gray-100 px-4 py-3.5">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h2 class="text-sm font-semibold text-gray-900">Account Status</h2>
                </div>
                <div class="px-4 py-4">
                    <div class="flex flex-col items-center">
                        <div class="relative h-24 w-24">
                            <svg class="h-24 w-24 -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="#f0fdf4" stroke-width="8"/>
                                <circle cx="50" cy="50" r="45" fill="none" stroke="#22c55e" stroke-width="8"
                                        stroke-dasharray="{{ round($circumference, 2) }}"
                                        stroke-dashoffset="{{ round($dashOffset, 2) }}"
                                        stroke-linecap="round"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-xl font-bold text-gray-900">{{ $completionPercent }}%</span>
                            </div>
                        </div>
                        <p class="mt-2.5 text-sm font-semibold text-gray-900">Profile Completeness</p>
                        <p class="mt-1 text-center text-xs leading-relaxed text-gray-400">
                            @if ($completionPercent >= 80)
                                Great job! Your profile is almost complete.
                            @elseif ($completionPercent >= 50)
                                You're halfway there! Keep it up.
                            @else
                                Complete your profile to get better matches.
                            @endif
                        </p>
                    </div>

                    <ul class="mt-4 space-y-2">
                        @foreach ($completionItems as $item)
                            <li class="flex items-center gap-2.5 text-xs">
                                @if ($item['done'])
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                                        <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                    <span class="font-medium text-gray-700">{{ $item['label'] }}</span>
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

                    <div class="my-4 border-t border-gray-100"></div>

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

                    <div class="mt-3.5 flex items-start gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100">
                            <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Account Type</p>
                            <span class="mt-1 inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">
                                {{ $accountType }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-3.5 flex items-start gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100">
                            <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Account Status</p>
                            <span class="mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusTone }}">
                                {{ $accountStatus }}
                            </span>
                        </div>
                    </div>

                    <div class="my-4 border-t border-gray-100"></div>

                    <div>
                        <p class="text-xs font-semibold text-gray-700">Need help with your account?</p>
                        <p class="mt-0.5 text-xs text-gray-400">Our support team is here to help.</p>
                        <a href="{{ route('user.help-center.index') }}" class="mt-2.5 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">
                            Contact Support
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.settingsPage = function settingsPage() {
        return {
            photoPreview: @json($profileImageUrl),
            initials: @json($initials),
            firstName: @json($firstName),
            lastName: @json($lastName),
            showCurPwd: false,
            showNewPwd: false,
            showConPwd: false,
            pwdStrength: '',
            toast: '',
            toastTimer: null,
            emailNotify: @json((bool) $notifications['email_notifications']),
            smsNotify: @json((bool) $notifications['sms_notifications']),
            bookingReminders: @json((bool) $notifications['booking_reminders']),
            promoUpdates: @json((bool) $notifications['promotions_updates']),
            smsTwoFactor: @json((bool) ($tenant->sms_two_factor_enabled ?? false)),
            showProfileToOwners: @json((bool) $privacy['show_profile_to_owners']),
            allowOwnerMessages: @json((bool) $privacy['allow_owner_messages']),
            allowMatchmakingData: @json((bool) $privacy['allow_matchmaking_data']),
            notificationUrl: @json(route('user.settings.notifications.update')),
            twoFactorUrl: @json(route('user.settings.two-factor.update')),

            checkStrength(value) {
                if (!value) {
                    this.pwdStrength = '';
                    return;
                }

                const score = [
                    /[A-Z]/.test(value),
                    /[a-z]/.test(value),
                    /[0-9]/.test(value),
                    /[^A-Za-z0-9]/.test(value),
                ].filter(Boolean).length;

                this.pwdStrength = value.length < 8 ? 'Weak' : score <= 2 ? 'Weak' : score === 3 ? 'Medium' : 'Strong';
            },

            showToast(message) {
                this.toast = message;
                clearTimeout(this.toastTimer);
                this.toastTimer = setTimeout(() => this.toast = '', 3000);
            },

            async saveSetting(url, payload, revert = null) {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token());

                try {
                    const response = await fetch(url, {
                        method: 'PUT',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        if (typeof revert === 'function') {
                            revert();
                        }

                        this.showToast(data.message || 'Unable to save setting.');
                        return;
                    }

                    this.showToast(data.message || 'Setting updated.');
                } catch (error) {
                    if (typeof revert === 'function') {
                        revert();
                    }

                    this.showToast('Unable to save setting.');
                }
            },

            togglePreference(field, modelName) {
                const previous = this[modelName];
                this[modelName] = !this[modelName];

                this.saveSetting(
                    this.notificationUrl,
                    { [field]: this[modelName] },
                    () => this[modelName] = previous
                );
            },

            toggleTwoFactor() {
                const previous = this.smsTwoFactor;
                this.smsTwoFactor = !this.smsTwoFactor;

                this.saveSetting(
                    this.twoFactorUrl,
                    { sms_two_factor_enabled: this.smsTwoFactor },
                    () => this.smsTwoFactor = previous
                );
            },
        };
    };
</script>
</x-user.shell>
</x-layouts.dashboard>
