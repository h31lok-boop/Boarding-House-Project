<x-layouts.dashboard>
<x-user.shell>
@php
    $profileImageUrl = $tenant->photo_url;

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

    $inputClasses = 'w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-100';
    $labelClasses = 'mb-1 block text-xs font-semibold text-slate-600';
    $primaryButtonClasses = 'inline-flex h-9 items-center justify-center rounded-lg bg-blue-600 px-4 text-[13px] font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100';
    $cardClasses = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/50';
    $sectionIconClasses = 'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600';
@endphp

<div x-data="settingsPage()" class="mx-auto w-full max-w-6xl space-y-4">

    <div x-show="toast" x-transition x-cloak
         class="fixed right-4 top-4 z-[9999] rounded-xl border border-blue-100 bg-white px-4 py-3 text-sm font-semibold text-blue-700 shadow-lg"
         x-text="toast"></div>

    {{-- Header --}}
    <header>
        <h1 class="text-xl font-bold tracking-tight text-slate-900">Profile Settings</h1>
        <p class="mt-0.5 text-[13px] text-slate-500">Manage your personal information, security, and contact details.</p>
    </header>

    {{-- 1. Personal Information --}}
    <form method="POST" action="{{ route('user.settings.personal.update') }}" enctype="multipart/form-data" class="{{ $cardClasses }}">
        @csrf
        @method('PUT')
        <input type="file" id="avatarPicker" name="profile_photo" accept="image/jpeg,image/png,image/gif" class="sr-only"
               x-on:change="const file = $event.target.files[0]; if (file) photoPreview = URL.createObjectURL(file);">

        <div class="grid gap-5 lg:grid-cols-[220px_minmax(0,1fr)]">
            <div>
                <div class="flex items-start gap-3">
                    <span class="{{ $sectionIconClasses }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Personal Information</h2>
                        <p class="mt-0.5 text-xs text-slate-400">Your name, photo, and basic details.</p>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <div class="relative shrink-0">
                        <div class="h-14 w-14 overflow-hidden rounded-full ring-2 ring-slate-100">
                            <template x-if="photoPreview">
                                <img :src="photoPreview" alt="{{ $displayName }}" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!photoPreview">
                                <div class="flex h-full w-full items-center justify-center bg-blue-600 text-sm font-bold text-white">
                                    <span x-text="initials">{{ $initials }}</span>
                                </div>
                            </template>
                        </div>
                        <label for="avatarPicker" class="absolute -bottom-0.5 -right-0.5 flex h-5 w-5 cursor-pointer items-center justify-center rounded-full border-2 border-white bg-slate-700 text-white transition hover:bg-slate-800" aria-label="Upload a new profile photo">
                            <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </label>
                    </div>
                    <div>
                        <label for="avatarPicker" class="inline-flex h-8 cursor-pointer items-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                            Change Photo
                        </label>
                        <p class="mt-1 text-[11px] text-slate-400">JPG, PNG or GIF. Max size 2MB.</p>
                        @error('profile_photo')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3.5">
                <div class="grid gap-3.5 sm:grid-cols-2">
                    <div>
                        <label for="first_name" class="{{ $labelClasses }}">First Name</label>
                        <input id="first_name" name="first_name" x-model="firstName" required class="{{ $inputClasses }}" placeholder="First name">
                        @error('first_name')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="last_name" class="{{ $labelClasses }}">Last Name</label>
                        <input id="last_name" name="last_name" x-model="lastName" required class="{{ $inputClasses }}" placeholder="Last name">
                        @error('last_name')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="date_of_birth" class="{{ $labelClasses }}">Date of Birth</label>
                        <input id="date_of_birth" type="date" name="date_of_birth" value="{{ $dateOfBirth }}" class="{{ $inputClasses }}">
                        @error('date_of_birth')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="gender" class="{{ $labelClasses }}">Gender</label>
                        <div class="relative">
                            <select id="gender" name="gender" class="{{ $inputClasses }} appearance-none pr-9">
                                <option value="">Select gender</option>
                                @foreach (['Male', 'Female', 'Other', 'Prefer not to say'] as $option)
                                    <option value="{{ $option }}" @selected($gender === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        @error('gender')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="{{ $primaryButtonClasses }}">Save Changes</button>
                </div>
            </div>
        </div>
    </form>

    {{-- 2. Account Security --}}
    <form method="POST" action="{{ route('user.settings.password.update') }}" class="{{ $cardClasses }}">
        @csrf
        @method('PUT')
        <div class="grid gap-5 lg:grid-cols-[220px_minmax(0,1fr)]">
            <div class="flex items-start gap-3">
                <span class="{{ $sectionIconClasses }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                </span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Account Security</h2>
                    <p class="mt-0.5 text-xs text-slate-400">Keep your account safe with a strong password.</p>
                </div>
            </div>

            <div class="flex flex-col gap-3.5">
                <div class="grid gap-3.5 sm:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <label for="settings_current_password" class="{{ $labelClasses }}">Current Password</label>
                        <div class="relative">
                            <input id="settings_current_password" name="current_password" :type="showCurPwd ? 'text' : 'password'" autocomplete="current-password"
                                   class="{{ $inputClasses }} pr-11" placeholder="Current password">
                            <button type="button" x-on:click="showCurPwd = !showCurPwd" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600" :aria-label="showCurPwd ? 'Hide password' : 'Show password'">
                                <svg x-show="!showCurPwd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <svg x-show="showCurPwd" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="settings_password" class="{{ $labelClasses }}">New Password</label>
                        <div class="relative">
                            <input id="settings_password" name="password" :type="showNewPwd ? 'text' : 'password'" x-on:input="checkStrength($event.target.value)" autocomplete="new-password"
                                   class="{{ $inputClasses }} pr-11" placeholder="New password">
                            <button type="button" x-on:click="showNewPwd = !showNewPwd" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600" :aria-label="showNewPwd ? 'Hide password' : 'Show password'">
                                <svg x-show="!showNewPwd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <svg x-show="showNewPwd" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                        <div x-show="pwdStrength" x-cloak class="mt-2 flex items-center gap-2">
                            <div class="relative h-1.5 flex-1 rounded-full bg-slate-200">
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
                        <label for="settings_password_confirmation" class="{{ $labelClasses }}">Confirm Password</label>
                        <div class="relative">
                            <input id="settings_password_confirmation" name="password_confirmation" :type="showConPwd ? 'text' : 'password'" autocomplete="new-password"
                                   class="{{ $inputClasses }} pr-11" placeholder="Confirm new password">
                            <button type="button" x-on:click="showConPwd = !showConPwd" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600" :aria-label="showConPwd ? 'Hide password' : 'Show password'">
                                <svg x-show="!showConPwd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <svg x-show="showConPwd" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="{{ $primaryButtonClasses }}">Update Password</button>
                </div>
            </div>
        </div>
    </form>

    {{-- 3. Contact Information --}}
    <form method="POST" action="{{ route('user.settings.contact.update') }}" class="{{ $cardClasses }}">
        @csrf
        @method('PUT')
        <div class="grid gap-5 lg:grid-cols-[220px_minmax(0,1fr)]">
            <div class="flex items-start gap-3">
                <span class="{{ $sectionIconClasses }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                </span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Contact Information</h2>
                    <p class="mt-0.5 text-xs text-slate-400">How owners and BoardMatch can reach you.</p>
                </div>
            </div>

            <div class="flex flex-col gap-3.5">
                <div class="grid gap-3.5 sm:grid-cols-2">
                    <div>
                        <label for="email" class="{{ $labelClasses }}">Email Address</label>
                        <input id="email" name="email" type="email" required value="{{ $email }}" class="{{ $inputClasses }}" placeholder="you@email.com">
                        @error('email')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="phone_number" class="{{ $labelClasses }}">Phone Number</label>
                        <div class="flex gap-2">
                            <span class="flex shrink-0 items-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-600">+63</span>
                            <input id="phone_number" name="phone_number" type="tel" value="{{ $phone }}" class="{{ $inputClasses }}" placeholder="912 345 6789">
                        </div>
                        @error('phone_number')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="current_address" class="{{ $labelClasses }}">Current Address</label>
                        <textarea id="current_address" name="current_address" rows="2" class="{{ $inputClasses }} resize-none" placeholder="Street, Barangay, City, Province, ZIP">{{ $currentAddress }}</textarea>
                        @error('current_address')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="{{ $primaryButtonClasses }}">Update Contact Info</button>
                </div>
            </div>
        </div>
    </form>

    {{-- 4. Additional Security --}}
    <div class="{{ $cardClasses }}">
        <div class="grid gap-5 lg:grid-cols-[220px_minmax(0,1fr)]">
            <div class="flex items-start gap-3">
                <span class="{{ $sectionIconClasses }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                </span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Additional Security</h2>
                    <p class="mt-0.5 text-xs text-slate-400">Add extra security and manage your account access.</p>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between gap-4 pb-4">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="{{ $sectionIconClasses }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-slate-900">Two-Factor Authentication</p>
                            <p class="mt-0.5 text-xs text-slate-400">Receive a code via SMS to verify your identity.</p>
                        </div>
                    </div>
                    <button type="button" x-on:click="toggleTwoFactor()"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-4 focus:ring-blue-100"
                            :class="smsTwoFactor ? 'bg-blue-600' : 'bg-slate-200'"
                            role="switch" :aria-checked="smsTwoFactor" aria-label="Toggle SMS two-factor authentication">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"
                              :class="smsTwoFactor ? 'translate-x-5' : 'translate-x-0'"></span>
                    </button>
                </div>

                <form method="POST" action="{{ route('user.settings.logout-other-devices') }}" class="flex flex-col gap-3 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    @csrf
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="{{ $sectionIconClasses }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-slate-900">Log out from other devices</p>
                            <p class="mt-0.5 text-xs text-slate-400">End active sessions on all other devices.</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <div>
                            <label for="logout_current_password" class="sr-only">Current password</label>
                            <input id="logout_current_password" name="logout_current_password" type="password" autocomplete="current-password"
                                   class="{{ $inputClasses }} sm:w-48" placeholder="Current password">
                            @error('logout_current_password')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="inline-flex h-[42px] shrink-0 items-center justify-center gap-1.5 rounded-lg border border-rose-200 bg-white px-4 text-[13px] font-semibold text-rose-600 transition hover:border-rose-300 hover:bg-rose-50">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-7.5A2.25 2.25 0 003.75 5.25v13.5A2.25 2.25 0 006 21h7.5a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                            Log out all devices
                        </button>
                    </div>
                </form>
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
            smsTwoFactor: @json((bool) ($tenant->sms_two_factor_enabled ?? false)),
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

            async toggleTwoFactor() {
                const previous = this.smsTwoFactor;
                this.smsTwoFactor = !this.smsTwoFactor;

                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token());

                try {
                    const response = await fetch(this.twoFactorUrl, {
                        method: 'PUT',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify({ sms_two_factor_enabled: this.smsTwoFactor }),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        this.smsTwoFactor = previous;
                        this.showToast(data.message || 'Unable to save setting.');
                        return;
                    }

                    this.showToast(data.message || 'Setting updated.');
                } catch (error) {
                    this.smsTwoFactor = previous;
                    this.showToast('Unable to save setting.');
                }
            },
        };
    };
</script>
</x-user.shell>
</x-layouts.dashboard>
