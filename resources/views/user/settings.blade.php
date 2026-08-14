<x-layouts.dashboard>
<x-user.shell>
@php
    $displayName = trim((string) $tenant->name) ?: 'Student';
    $nameParts = explode(' ', $displayName, 2);
    $firstName = old('first_name', $tenant->first_name ?: ($nameParts[0] ?? ''));
    $lastName = old('last_name', $tenant->last_name ?: ($nameParts[1] ?? ''));
    $initials = collect([$firstName, $lastName])
        ->filter(fn ($name) => filled($name))
        ->map(fn ($name) => strtoupper(substr(trim((string) $name), 0, 1)))
        ->take(2)
        ->implode('') ?: 'BM';

    $storedPhone = old('phone_number', $tenant->phone_number ?: ($tenant->phone ?: ($tenant->contact_number ?: '')));
    $localPhone = preg_replace('/^\+?63[\s-]?/', '', trim((string) $storedPhone));
    $dateOfBirth = old('date_of_birth', $tenant->date_of_birth?->format('Y-m-d') ?? '');
    $gender = match (strtolower((string) old('gender', $tenant->gender ?? ''))) {
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
        'prefer not to say' => 'Prefer not to say',
        default => old('gender', $tenant->gender ?? ''),
    };

    $cardClasses = 'rounded-2xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/40 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none sm:p-5';
    $labelClasses = 'mb-1.5 block text-xs font-semibold text-slate-700 dark:text-slate-300';
    $inputClasses = 'h-10 w-full rounded-xl border border-slate-200 bg-white px-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-500/10';
    $buttonClasses = 'inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto dark:focus:ring-blue-500/20';
    $iconClasses = 'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300';
    $errorClasses = 'mt-1.5 text-xs font-medium text-rose-600 dark:text-rose-400';
@endphp

<div
    x-data="{
        photoPreview: @js($tenant->photo_url),
        previewPhoto(event) {
            const file = event.target.files?.[0];
            if (!file) return;
            if (this.photoPreview?.startsWith?.('blob:')) URL.revokeObjectURL(this.photoPreview);
            this.photoPreview = URL.createObjectURL(file);
        }
    }"
    class="mx-auto w-full max-w-6xl space-y-4"
>
    <header class="pb-1">
        <h1 class="text-xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-2xl">Profile Settings</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage your personal information, security, and contact details.</p>
    </header>

    <form
        method="POST"
        action="{{ route('user.settings.personal.update') }}"
        enctype="multipart/form-data"
        class="{{ $cardClasses }}"
        x-data="{ saving: false }"
        x-on:submit="saving = true"
    >
        @csrf
        @method('PUT')

        <div class="flex items-center gap-3 border-b border-slate-100 pb-4 dark:border-slate-800">
            <span class="{{ $iconClasses }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.12a7.5 7.5 0 0115 0A17.9 17.9 0 0112 21.75a17.9 17.9 0 01-7.5-1.63z"/></svg>
            </span>
            <div>
                <h2 class="text-sm font-bold text-slate-950 dark:text-white">Personal Information</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Your photo, name, and basic details.</p>
            </div>
        </div>

        <div class="grid gap-6 pt-5 lg:grid-cols-[220px_minmax(0,1fr)]">
            <div>
                <p class="{{ $labelClasses }}">Profile Photo</p>
                <div class="flex items-center gap-4 lg:flex-col lg:items-start">
                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-full border-4 border-white bg-blue-600 shadow-sm ring-1 ring-slate-200 dark:border-slate-900 dark:ring-slate-700">
                        <img x-show="photoPreview" x-cloak :src="photoPreview" alt="{{ $displayName }} profile photo" class="h-full w-full object-cover">
                        <div x-show="!photoPreview" class="flex h-full w-full items-center justify-center text-xl font-bold text-white" aria-label="{{ $initials }} initials">{{ $initials }}</div>
                    </div>
                    <div>
                        <label for="profile_photo" class="inline-flex h-9 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-300 hover:text-blue-700 focus-within:ring-4 focus-within:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                            Change Photo
                            <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/gif" class="sr-only" x-on:change="previewPhoto($event)">
                        </label>
                        <p class="mt-2 text-[11px] leading-4 text-slate-500 dark:text-slate-400">JPG, PNG or GIF. Max size 2MB.</p>
                        @error('profile_photo')<p class="{{ $errorClasses }}" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="first_name" class="{{ $labelClasses }}">First Name</label>
                        <input id="first_name" name="first_name" value="{{ $firstName }}" required autocomplete="given-name" class="{{ $inputClasses }} @error('first_name') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror">
                        @error('first_name')<p class="{{ $errorClasses }}" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="last_name" class="{{ $labelClasses }}">Last Name</label>
                        <input id="last_name" name="last_name" value="{{ $lastName }}" required autocomplete="family-name" class="{{ $inputClasses }} @error('last_name') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror">
                        @error('last_name')<p class="{{ $errorClasses }}" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="date_of_birth" class="{{ $labelClasses }}">Date of Birth</label>
                        <input id="date_of_birth" type="date" name="date_of_birth" value="{{ $dateOfBirth }}" autocomplete="bday" class="{{ $inputClasses }} @error('date_of_birth') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror">
                        @error('date_of_birth')<p class="{{ $errorClasses }}" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="gender" class="{{ $labelClasses }}">Gender</label>
                        <select id="gender" name="gender" class="{{ $inputClasses }} @error('gender') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror">
                            <option value="">Select gender</option>
                            @foreach (['Male', 'Female', 'Other', 'Prefer not to say'] as $option)
                                <option value="{{ $option }}" @selected($gender === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        @error('gender')<p class="{{ $errorClasses }}" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="flex justify-end pt-1">
                    <button type="submit" class="{{ $buttonClasses }}" x-bind:disabled="saving">
                        <svg x-show="saving" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                        <span x-text="saving ? 'Saving...' : 'Save Changes'">Save Changes</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="grid items-start gap-4 lg:grid-cols-2">
        <form method="POST" action="{{ route('user.settings.password.update') }}" class="{{ $cardClasses }} h-full" x-data="{ saving: false, showCurrent: false, showNew: false, showConfirmation: false }" x-on:submit="saving = true">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-3 border-b border-slate-100 pb-4 dark:border-slate-800">
                <span class="{{ $iconClasses }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.04A12 12 0 013.6 6 12 12 0 003 9.75c0 5.59 3.82 10.29 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15A11.96 11.96 0 0112 2.71z"/></svg>
                </span>
                <div>
                    <h2 class="text-sm font-bold text-slate-950 dark:text-white">Account Security</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Use a strong, unique password.</p>
                </div>
            </div>

            <div class="space-y-4 pt-5">
                @foreach ([
                    ['name' => 'current_password', 'label' => 'Current Password', 'state' => 'showCurrent', 'autocomplete' => 'current-password', 'placeholder' => 'Enter current password'],
                    ['name' => 'password', 'label' => 'New Password', 'state' => 'showNew', 'autocomplete' => 'new-password', 'placeholder' => 'Enter new password'],
                    ['name' => 'password_confirmation', 'label' => 'Confirm Password', 'state' => 'showConfirmation', 'autocomplete' => 'new-password', 'placeholder' => 'Confirm new password'],
                ] as $field)
                    <div>
                        <label for="{{ $field['name'] }}" class="{{ $labelClasses }}">{{ $field['label'] }}</label>
                        <div class="relative">
                            <input id="{{ $field['name'] }}" name="{{ $field['name'] }}" :type="{{ $field['state'] }} ? 'text' : 'password'" autocomplete="{{ $field['autocomplete'] }}" placeholder="{{ $field['placeholder'] }}" class="{{ $inputClasses }} pr-11 @error($field['name']) border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror">
                            <button type="button" x-on:click="{{ $field['state'] }} = !{{ $field['state'] }}" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-xl text-slate-400 transition hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 dark:hover:text-slate-200" :aria-label="{{ $field['state'] }} ? 'Hide {{ strtolower($field['label']) }}' : 'Show {{ strtolower($field['label']) }}'">
                                <svg x-show="!{{ $field['state'] }}" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.04 12.32a1 1 0 010-.64C3.42 7.51 7.36 4.5 12 4.5s8.57 3.01 9.96 7.18a1 1 0 010 .64C20.58 16.49 16.64 19.5 12 19.5s-8.57-3.01-9.96-7.18z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <svg x-show="{{ $field['state'] }}" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6A2 2 0 0013.4 13.4M9.9 4.75A10.8 10.8 0 0112 4.5c4.64 0 8.57 3.01 9.96 7.18a1 1 0 010 .64 10.8 10.8 0 01-2.05 3.77M6.23 6.23a10.45 10.45 0 00-4.2 5.45 1 1 0 000 .64C3.42 16.49 7.36 19.5 12 19.5c1.56 0 3.04-.34 4.37-.95"/></svg>
                            </button>
                        </div>
                        @error($field['name'])<p class="{{ $errorClasses }}" role="alert">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>

            <p class="mt-3 text-[11px] leading-4 text-slate-500 dark:text-slate-400">Use at least 8 characters with uppercase, lowercase, a number, and a special character.</p>
            <div class="mt-5 flex justify-end">
                <button type="submit" class="{{ $buttonClasses }}" x-bind:disabled="saving">
                    <svg x-show="saving" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    <span x-text="saving ? 'Updating...' : 'Update Password'">Update Password</span>
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('user.settings.contact.update') }}" class="{{ $cardClasses }} h-full" x-data="{ saving: false }" x-on:submit="saving = true">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-3 border-b border-slate-100 pb-4 dark:border-slate-800">
                <span class="{{ $iconClasses }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.24a2.25 2.25 0 01-1.07 1.92l-7.5 4.61a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.92v-.24"/></svg>
                </span>
                <div>
                    <h2 class="text-sm font-bold text-slate-950 dark:text-white">Contact Information</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">How BoardMatch and property owners reach you.</p>
                </div>
            </div>

            <div class="space-y-4 pt-5">
                <div>
                    <label for="email" class="{{ $labelClasses }}">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $tenant->email) }}" required autocomplete="email" placeholder="you@example.com" class="{{ $inputClasses }} @error('email') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror">
                    @error('email')<p class="{{ $errorClasses }}" role="alert">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone_number" class="{{ $labelClasses }}">Phone Number</label>
                    <div class="grid grid-cols-[92px_minmax(0,1fr)] gap-2">
                        <div>
                            <label for="country_code" class="sr-only">Country Code</label>
                            <select id="country_code" name="country_code" class="{{ $inputClasses }} px-2.5" aria-label="Country Code"><option value="+63" selected>+63</option></select>
                        </div>
                        <input id="phone_number" name="phone_number" type="tel" value="{{ $localPhone }}" autocomplete="tel-national" inputmode="tel" placeholder="912 345 6789" class="{{ $inputClasses }} @error('phone_number') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror">
                    </div>
                    @error('country_code')<p class="{{ $errorClasses }}" role="alert">{{ $message }}</p>@enderror
                    @error('phone_number')<p class="{{ $errorClasses }}" role="alert">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="current_address" class="{{ $labelClasses }}">Current Address</label>
                    <textarea id="current_address" name="current_address" rows="4" autocomplete="street-address" placeholder="Street, barangay, city, province, ZIP" class="{{ $inputClasses }} h-24 resize-none py-2.5 @error('current_address') border-rose-400 focus:border-rose-500 focus:ring-rose-100 @enderror">{{ old('current_address', $tenant->current_address) }}</textarea>
                    @error('current_address')<p class="{{ $errorClasses }}" role="alert">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-5 flex justify-end">
                <button type="submit" class="{{ $buttonClasses }}" x-bind:disabled="saving">
                    <svg x-show="saving" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    <span x-text="saving ? 'Saving...' : 'Save Contact Information'">Save Contact Information</span>
                </button>
            </div>
        </form>
    </div>
</div>
</x-user.shell>
</x-layouts.dashboard>
