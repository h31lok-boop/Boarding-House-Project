@php
    $showPageHeader = $showPageHeader ?? true;
    $isAdminWorkspace = request()->routeIs('admin.*');
    $routeName = fn (string $admin, string $owner, $params = []) => route($isAdminWorkspace ? $admin : $owner, $params);
    $user = $user ?? auth()->user();
    $ownerProfile = $ownerProfile ?? $user?->ownerProfile;
@endphp

<div id="settings-management" class="space-y-6">
    @if ($showPageHeader)
        <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Settings</h1>
                <p class="mt-1 text-sm text-slate-600 sm:text-base">Manage account details, password, owner profile, and notification preferences.</p>
            </div>
            <a href="{{ $routeName('admin.profile', 'owner.profile') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-blue-200 px-4 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                Open Full Profile
            </a>
        </section>
    @endif

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <main class="space-y-6">
            <form method="POST" action="{{ $routeName('admin.settings.update', 'owner.settings.update') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                @csrf
                @method('PATCH')
                <h2 class="text-lg font-bold text-slate-950">Account Settings</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Full Name</span>
                        <input name="name" value="{{ old('name', $user->name) }}" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm" required>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Email Address</span>
                        <input name="email" type="email" value="{{ old('email', $user->email) }}" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm" required>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Contact Number</span>
                        <input name="phone" value="{{ old('phone', $user->phone ?: $user->contact_number) }}" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Company / Owner Label</span>
                        <input name="company_name" value="{{ old('company_name', $ownerProfile?->company_name) }}" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm">
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Default Address / Location</span>
                        <textarea name="address" rows="3" class="mt-2 w-full rounded-xl border-slate-200 text-sm">{{ old('address', $ownerProfile?->address) }}</textarea>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Business Permit Number</span>
                        <input name="business_permit_number" value="{{ old('business_permit_number', $ownerProfile?->business_permit_number) }}" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm">
                    </label>
                </div>
                <div class="mt-5 flex justify-end">
                    <button class="rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-800">Save Account Settings</button>
                </div>
            </form>

            <form method="POST" action="{{ $routeName('admin.settings.update', 'owner.settings.update') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                @csrf
                @method('PATCH')
                <h2 class="text-lg font-bold text-slate-950">Notification Preferences</h2>
                <div class="mt-5 divide-y divide-slate-200">
                    @foreach ([
                        'notify_booking_updates' => ['label' => 'Bookings & Reservations', 'description' => 'Notify me about new booking requests and reservation updates.'],
                        'notify_ticket_updates' => ['label' => 'Messages & Maintenance', 'description' => 'Notify me about messages, inquiries, and request updates.'],
                        'notify_payment_reminders' => ['label' => 'Payment Reminders', 'description' => 'Notify me about rent and billing reminders.'],
                    ] as $field => $item)
                        <label class="flex cursor-pointer flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                            <span>
                                <span class="block font-bold text-slate-950">{{ $item['label'] }}</span>
                                <span class="mt-1 block text-sm text-slate-500">{{ $item['description'] }}</span>
                            </span>
                            <span class="inline-flex items-center gap-2 text-sm font-bold text-slate-700">
                                <input type="hidden" name="{{ $field }}" value="0">
                                <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $user->{$field})) class="rounded border-slate-300 text-blue-700 focus:ring-blue-500">
                                Enabled
                            </span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-5 flex justify-end">
                    <button class="rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-800">Save Notification Settings</button>
                </div>
            </form>

            <form method="POST" action="{{ $routeName('admin.settings.update', 'owner.settings.update') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                @csrf
                @method('PATCH')
                <h2 class="text-lg font-bold text-slate-950">Change Password</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    <input name="current_password" type="password" placeholder="Current password" class="h-11 rounded-xl border-slate-200 text-sm">
                    <input name="password" type="password" placeholder="New password" class="h-11 rounded-xl border-slate-200 text-sm">
                    <input name="password_confirmation" type="password" placeholder="Confirm password" class="h-11 rounded-xl border-slate-200 text-sm">
                </div>
                <div class="mt-5 flex justify-end">
                    <button class="rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-800">Update Password</button>
                </div>
            </form>
        </main>

        <aside class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Profile Snapshot</h2>
                <div class="mt-5 flex items-center gap-4">
                    <span class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-700 text-lg font-bold text-white">
                        @if ($user->profile_image)
                            <img src="{{ asset('storage/'.$user->profile_image) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                        @else
                            {{ collect(explode(' ', $user->name))->map(fn ($part) => substr($part, 0, 1))->take(2)->implode('') }}
                        @endif
                    </span>
                    <div>
                        <p class="font-bold text-slate-950">{{ $user->name }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $user->email }}</p>
                        <p class="mt-1 text-xs font-bold uppercase tracking-wide text-blue-700">Owner</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Help & Support</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <a href="{{ $routeName('admin.inquiries.index', 'owner.inquiries.index') }}" class="block rounded-xl border border-slate-200 p-3 font-semibold text-slate-700 hover:bg-slate-50">Inquiry support queue</a>
                    <a href="{{ $routeName('admin.compliance.index', 'owner.compliance.index') }}" class="block rounded-xl border border-slate-200 p-3 font-semibold text-slate-700 hover:bg-slate-50">Compliance requirements</a>
                    <a href="{{ $routeName('admin.reports', 'owner.reports') }}" class="block rounded-xl border border-slate-200 p-3 font-semibold text-slate-700 hover:bg-slate-50">Owner report export</a>
                </div>
            </section>

            <section class="rounded-2xl border border-rose-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-rose-700">Danger Zone</h2>
                <p class="mt-2 text-sm text-slate-600">Account deletion is available from the full profile page and requires password confirmation.</p>
                <a href="{{ $routeName('admin.profile', 'owner.profile') }}#delete-account" class="mt-4 inline-flex rounded-xl border border-rose-200 px-4 py-2 text-sm font-bold text-rose-700 hover:bg-rose-50">Manage Account Deletion</a>
            </section>
        </aside>
    </section>
</div>
