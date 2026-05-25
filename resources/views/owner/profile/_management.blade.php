@php
    $showPageHeader = $showPageHeader ?? true;
    $user = auth()->user()->loadMissing('ownerProfile');
    $isSuperWorkspace = request()->routeIs('superduperadmin.profile*');
    $profileUpdateRoute = $isSuperWorkspace && Route::has('superduperadmin.profile.update')
        ? route('superduperadmin.profile.update')
        : (request()->routeIs('owner.*') && Route::has('owner.profile.update') ? route('owner.profile.update') : route('profile.update'));
    $profileDestroyRoute = $isSuperWorkspace && Route::has('superduperadmin.profile.destroy')
        ? route('superduperadmin.profile.destroy')
        : (request()->routeIs('owner.*') && Route::has('owner.profile.destroy') ? route('owner.profile.destroy') : route('profile.destroy'));
    $dashboardHref = $isSuperWorkspace && Route::has('superduperadmin.dashboard')
        ? route('superduperadmin.dashboard')
        : (request()->routeIs('owner.*') && Route::has('owner.dashboard') ? route('owner.dashboard') : route('dashboard'));
    $initials = collect(explode(' ', $user->name))->map(fn ($part) => substr($part, 0, 1))->take(2)->implode('') ?: 'OW';
@endphp

<div class="space-y-6">
    @if ($showPageHeader)
        <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Owner Profile</h1>
                <p class="mt-1 text-sm text-slate-600 sm:text-base">Manage your personal, business, and account information.</p>
            </div>
            <a href="{{ $dashboardHref }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back to Dashboard</a>
        </section>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <span class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-700 text-2xl font-bold text-white ring-4 ring-blue-100">
                    @if ($user->profile_image)
                        <img src="{{ asset('storage/'.$user->profile_image) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                    @else
                        {{ $initials }}
                    @endif
                </span>
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-2xl font-bold text-slate-950">{{ $user->name }}</h2>
                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Owner</p>
                    <p class="mt-2 max-w-2xl text-sm text-slate-600">{{ $user->email }}{{ $user->phone ? ' | '.$user->phone : '' }}</p>
                </div>
            </div>
        </div>
    </section>

    <section id="personal-information" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-bold text-slate-950">Personal and Business Information</h2>
        <form method="POST" action="{{ $profileUpdateRoute }}" enctype="multipart/form-data" class="mt-5 space-y-5">
            @csrf
            @method('PATCH')
            <div class="grid gap-4 lg:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Full Name</span>
                    <input name="name" type="text" value="{{ old('name', $user->name) }}" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Email Address</span>
                    <input name="email" type="email" value="{{ old('email', $user->email) }}" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Contact Number</span>
                    <input name="phone" type="text" value="{{ old('phone', $user->phone ?: $user->contact_number) }}" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Company Name</span>
                    <input name="company_name" type="text" value="{{ old('company_name', $user->ownerProfile?->company_name) }}" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
                <label class="block lg:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Owner Address</span>
                    <textarea name="address" rows="3" class="mt-2 w-full rounded-xl border-slate-200 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('address', $user->ownerProfile?->address) }}</textarea>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Business Permit Number</span>
                    <input name="business_permit_number" type="text" value="{{ old('business_permit_number', $user->ownerProfile?->business_permit_number) }}" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Valid ID Type</span>
                    <input name="valid_id_type" type="text" value="{{ old('valid_id_type', $user->ownerProfile?->valid_id_type) }}" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Valid ID Number</span>
                    <input name="valid_id_number" type="text" value="{{ old('valid_id_number', $user->ownerProfile?->valid_id_number) }}" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Profile Image</span>
                    <input name="profile_image" type="file" accept="image/*" class="mt-2 block w-full text-sm text-slate-600">
                </label>
            </div>
            @if ($user->profile_image)
                <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <input type="checkbox" name="profile_image_remove" value="1" class="rounded border-slate-300 text-blue-700 focus:ring-blue-500">
                    Remove current profile image
                </label>
            @else
                <input type="hidden" name="profile_image_remove" value="0">
            @endif
            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ $dashboardHref }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Cancel</a>
                <button class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-700 px-5 text-sm font-bold text-white shadow-sm shadow-blue-900/20 transition hover:bg-blue-800">Save Profile</button>
            </div>
        </form>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('password.update') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            @method('PUT')
            <h2 class="text-lg font-bold text-slate-950">Change Password</h2>
            <div class="mt-5 space-y-4">
                <input name="current_password" type="password" placeholder="Current password" class="h-11 w-full rounded-xl border-slate-200 text-sm">
                <input name="password" type="password" placeholder="New password" class="h-11 w-full rounded-xl border-slate-200 text-sm">
                <input name="password_confirmation" type="password" placeholder="Confirm new password" class="h-11 w-full rounded-xl border-slate-200 text-sm">
                <button class="rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-800">Update Password</button>
            </div>
        </form>

        <section id="delete-account" class="rounded-2xl border border-rose-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-bold text-rose-700">Delete Account</h2>
            <p class="mt-2 text-sm text-slate-600">This permanently deletes the signed-in account after password confirmation.</p>
            <form method="POST" action="{{ $profileDestroyRoute }}" class="mt-5 flex flex-col gap-3 sm:flex-row">
                @csrf
                @method('DELETE')
                <input name="password" type="password" placeholder="Current password" class="h-11 flex-1 rounded-xl border-rose-200 text-sm">
                <button class="rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-rose-700" onclick="return confirm('Delete this account permanently?')">Delete Account</button>
            </form>
        </section>
    </section>
</div>
