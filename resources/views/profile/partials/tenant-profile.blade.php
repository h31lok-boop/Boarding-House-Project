@php
    $tenant = $user ?? auth()->user();
    $profileImageUrl = $tenant?->profile_image ? \Illuminate\Support\Facades\Storage::url($tenant->profile_image) : '';
    $displayName = filled($tenant?->name) ? $tenant->name : 'Tenant';
    $nameParts = preg_split('/\s+/', trim($displayName));
    $initials = strtoupper(substr($nameParts[0] ?? 'T', 0, 1).substr($nameParts[1] ?? '', 0, 1)) ?: 'T';
    $statusLabel = ($tenant?->is_active ?? true) ? 'Active Tenant' : 'Pending Review';
    $statusClass = ($tenant?->is_active ?? true)
        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
        : 'bg-amber-50 text-amber-700 ring-amber-200';
    $emailStatus = $tenant?->email_verified_at ? 'Verified' : 'Unverified';
    $emailStatusClass = $tenant?->email_verified_at
        ? 'bg-blue-50 text-blue-700 ring-blue-200'
        : 'bg-rose-50 text-rose-700 ring-rose-200';
    $memberSince = $tenant?->created_at ? $tenant->created_at->format('M d, Y') : 'N/A';
    $applicationsCount = $tenant ? $tenant->boardingHouseApplications()->count() : 0;
    $reservationsCount = $tenant ? $tenant->reservations()->count() : 0;
    $savedCount = $tenant ? $tenant->favorites()->count() : 0;
    $reviewsCount = $tenant ? $tenant->reviews()->count() : 0;
@endphp

<div class="mx-auto max-w-7xl space-y-6">
    <section class="tenant-card overflow-hidden">
        <div class="flex flex-col gap-5 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-center">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-emerald-600 text-lg font-bold text-white shadow-sm">
                    @if ($profileImageUrl)
                        <img src="{{ $profileImageUrl }}" alt="{{ $displayName }}" class="h-full w-full object-cover">
                    @else
                        {{ $initials }}
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Tenant Profile</h1>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base">
                        Manage your DSSC Boarding profile, contact information, account security, and tenant activity.
                    </p>
                </div>
            </div>

            <a href="{{ route('tenant.dashboard') }}" class="inline-flex h-11 w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 sm:w-auto">
                Back to Dashboard
            </a>
        </div>
    </section>

    @if (session('status') === 'profile-updated')
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700 shadow-sm">
            Profile updated successfully.
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700 shadow-sm">
            Password updated successfully.
        </div>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="tenant-card p-5">
            <p class="text-sm font-semibold text-slate-500">Applications</p>
            <p class="mt-3 text-3xl font-bold text-slate-950">{{ $applicationsCount }}</p>
            <p class="mt-1 text-sm text-slate-500">Submitted boarding house requests</p>
        </article>
        <article class="tenant-card p-5">
            <p class="text-sm font-semibold text-slate-500">Reservations</p>
            <p class="mt-3 text-3xl font-bold text-slate-950">{{ $reservationsCount }}</p>
            <p class="mt-1 text-sm text-slate-500">Active and past reservations</p>
        </article>
        <article class="tenant-card p-5">
            <p class="text-sm font-semibold text-slate-500">Saved Listings</p>
            <p class="mt-3 text-3xl font-bold text-slate-950">{{ $savedCount }}</p>
            <p class="mt-1 text-sm text-slate-500">Boarding houses saved for later</p>
        </article>
        <article class="tenant-card p-5">
            <p class="text-sm font-semibold text-slate-500">Reviews</p>
            <p class="mt-3 text-3xl font-bold text-slate-950">{{ $reviewsCount }}</p>
            <p class="mt-1 text-sm text-slate-500">Feedback you have submitted</p>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_minmax(320px,0.85fr)]">
        <div class="space-y-6">
            <article class="tenant-card p-5 sm:p-6">
                <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-slate-950">Personal Information</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Keep your name, email, phone number, and profile photo updated.</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $emailStatusClass }}">Email {{ $emailStatus }}</span>
                </div>

                <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                    @csrf
                </form>

                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('patch')

                    <x-profile-image-uploader
                        label="Profile Photo"
                        name="profile_image"
                        :initial="$profileImageUrl"
                        :fallback="asset('images/avatar-placeholder.svg')"
                        max-size-kb="5120"
                        size="112"
                        circle="true"
                    />
                    <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="tenant_profile_name" class="text-sm font-bold text-slate-700">Full Name</label>
                            <input id="tenant_profile_name" name="name" type="text" value="{{ old('name', $tenant->name) }}" required autocomplete="name" class="mt-2 h-12 w-full rounded-xl border-slate-200 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <label for="tenant_profile_phone" class="text-sm font-bold text-slate-700">Contact Number</label>
                            <input id="tenant_profile_phone" name="phone" type="tel" value="{{ old('phone', $tenant->phone ?: $tenant->contact_number) }}" autocomplete="tel" placeholder="09171234567" class="mt-2 h-12 w-full rounded-xl border-slate-200 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        </div>
                    </div>

                    <div>
                        <label for="tenant_profile_email" class="text-sm font-bold text-slate-700">Email Address</label>
                        <input id="tenant_profile_email" name="email" type="email" value="{{ old('email', $tenant->email) }}" required autocomplete="username" class="mt-2 h-12 w-full rounded-xl border-slate-200 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />

                        @if ($tenant instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $tenant->hasVerifiedEmail())
                            <p class="mt-3 text-sm text-slate-600">
                                Your email address is unverified.
                                <button form="send-verification" class="font-bold text-blue-700 underline underline-offset-4 hover:text-blue-800">
                                    Send a new verification link.
                                </button>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-2 text-sm font-semibold text-emerald-600">A new verification link has been sent to your email address.</p>
                            @endif
                        @endif
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center">
                        <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-blue-700 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200 sm:w-auto">
                            Save Profile
                        </button>
                        <p class="text-sm text-slate-500">Changes are reflected across your tenant dashboard and applications.</p>
                    </div>
                </form>
            </article>

            <article class="tenant-card p-5 sm:p-6">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-slate-950">Account Security</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Use a strong password to keep your reservations, messages, and profile secure.</p>
                </div>

                <form method="post" action="{{ route('password.update') }}" class="space-y-5">
                    @csrf
                    @method('put')

                    <div class="grid gap-5 lg:grid-cols-3">
                        <div>
                            <label for="tenant_current_password" class="text-sm font-bold text-slate-700">Current Password</label>
                            <input id="tenant_current_password" name="current_password" type="password" autocomplete="current-password" class="mt-2 h-12 w-full rounded-xl border-slate-200 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                        </div>

                        <div>
                            <label for="tenant_new_password" class="text-sm font-bold text-slate-700">New Password</label>
                            <input id="tenant_new_password" name="password" type="password" autocomplete="new-password" class="mt-2 h-12 w-full rounded-xl border-slate-200 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <label for="tenant_password_confirmation" class="text-sm font-bold text-slate-700">Confirm Password</label>
                            <input id="tenant_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="mt-2 h-12 w-full rounded-xl border-slate-200 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center">
                        <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-blue-700 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200 sm:w-auto">
                            Update Password
                        </button>
                        <p class="text-sm text-slate-500">Minimum 8 characters is recommended.</p>
                    </div>
                </form>
            </article>
        </div>

        <aside class="space-y-6">
            <article class="tenant-card p-5 sm:p-6">
                <h2 class="text-lg font-bold text-slate-950">Profile Summary</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="font-semibold text-slate-500">Full Name</dt>
                        <dd class="mt-1 break-words font-bold text-slate-950">{{ $displayName }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Email Address</dt>
                        <dd class="mt-1 break-words font-bold text-slate-950">{{ $tenant->email }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Contact Number</dt>
                        <dd class="mt-1 break-words font-bold text-slate-950">{{ $tenant->phone ?: ($tenant->contact_number ?: 'Not provided') }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Member Since</dt>
                        <dd class="mt-1 font-bold text-slate-950">{{ $memberSince }}</dd>
                    </div>
                </dl>
            </article>

            <article class="tenant-card p-5 sm:p-6">
                <h2 class="text-lg font-bold text-slate-950">Quick Links</h2>
                <div class="mt-4 grid gap-3 text-sm font-bold">
                    <a href="{{ route('tenant.applications') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">My Applications</a>
                    <a href="{{ route('tenant.reservations') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">My Reservations</a>
                    <a href="{{ route('tenant.saved-listings') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Saved Listings</a>
                    <a href="{{ route('tenant.reviews') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Reviews</a>
                </div>
            </article>

            <article class="rounded-2xl border border-rose-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-lg font-bold text-slate-950">Account Actions</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Deleting your account permanently removes access to tenant records tied to this login.</p>

                <button
                    type="button"
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-tenant-account-deletion')"
                    class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-xl bg-rose-600 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-rose-700 focus:outline-none focus:ring-4 focus:ring-rose-200"
                >
                    Delete Account
                </button>
            </article>
        </aside>
    </section>

    <x-modal name="confirm-tenant-account-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-slate-950">Delete tenant account?</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                This action cannot be undone. Enter your password to permanently delete your account.
            </p>

            <div class="mt-6">
                <label for="tenant_delete_password" class="sr-only">Password</label>
                <input id="tenant_delete_password" name="password" type="password" class="mt-1 block w-full rounded-xl border-slate-200 text-sm text-slate-900 shadow-sm focus:border-rose-500 focus:ring-rose-500" placeholder="Password">
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" x-on:click="$dispatch('close')" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-rose-600 px-5 text-sm font-bold text-white transition hover:bg-rose-700">
                    Delete Account
                </button>
            </div>
        </form>
    </x-modal>
</div>
