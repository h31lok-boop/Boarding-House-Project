<x-layouts.dashboard>
<x-user.shell>
    @php
        $notifications = $notificationPreferences ?? [];
        $profileImageUrl = $tenant->profile_image ? \Illuminate\Support\Facades\Storage::url($tenant->profile_image) : asset('images/boardmatch-mark.svg');
        $nameParts = preg_split('/\s+/', trim((string) $tenant->name)) ?: [];
        $firstName = $nameParts[0] ?? 'Hazel';
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : 'Mae';
        $email = $tenant->email ?: 'hazel.mae@student.com';
        $phone = $tenant->phone ?: ($tenant->contact_number ?: '+63 912 345 6789');
    @endphp

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">Profile Settings</h1>
            <p class="mt-2 text-sm ui-muted">Manage your personal information and account settings.</p>
        </div>

        <div class="flex flex-wrap gap-8 border-b ui-border">
            @foreach (['Profile Information', 'Account & Security', 'Notifications', 'Privacy'] as $index => $tab)
                <a href="{{ $index === 0 ? '#profile-information' : '#account-security' }}" class="{{ $index === 0 ? 'border-b-2 border-indigo-600 text-indigo-700' : 'ui-muted' }} px-6 py-3 text-sm font-semibold">{{ $tab }}</a>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-[270px_1fr_380px]">
            <aside class="space-y-5">
                <section class="ui-card p-5 text-center">
                    <div class="relative mx-auto h-28 w-28 overflow-hidden rounded-full border ui-border">
                        <img src="{{ $profileImageUrl }}" alt="{{ $tenant->name }}" class="h-full w-full object-cover">
                    </div>
                    <h2 class="mt-4 text-xl font-bold">{{ $firstName }} {{ $lastName }}</h2>
                    <span class="mt-2 inline-flex rounded-lg bg-violet-50 px-3 py-1 text-xs font-semibold text-indigo-700">Student</span>
                    <div class="mt-6 space-y-3 text-left text-sm ui-muted">
                        <p>{{ $email }}</p>
                        <p>{{ $phone }}</p>
                        <p>Cagayan de Oro City, Philippines</p>
                        <p>Member since May 12, 2026</p>
                    </div>
                </section>

                <section class="ui-card p-5">
                    <h2 class="font-semibold">Profile Completion</h2>
                    <p class="mt-2 text-sm ui-muted">Complete your profile to get better matches.</p>
                    <div class="mt-4 flex items-center gap-3">
                        <div class="h-2 flex-1 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-indigo-600" style="width:85%"></div></div>
                        <span class="text-sm font-semibold">85%</span>
                    </div>
                    <ul class="mt-5 space-y-3 text-sm">
                        <li class="text-emerald-700">Personal Information</li>
                        <li class="text-emerald-700">Contact Information</li>
                        <li class="text-emerald-700">Student Information</li>
                        <li class="text-emerald-700">Preferences</li>
                        <li class="ui-muted">ID Verification <a href="#id-verification" class="float-right font-semibold text-indigo-700">Verify now</a></li>
                    </ul>
                </section>
            </aside>

            <form id="profile-information" method="POST" action="{{ route('user.settings.update') }}" enctype="multipart/form-data" class="ui-card p-6">
                @csrf
                @method('PUT')

                <section>
                    <h2 class="text-lg font-semibold">Personal Information</h2>
                    <p class="mt-1 text-sm ui-muted">Update your personal details.</p>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <label class="text-sm">First Name<input name="name" required value="{{ old('name', $tenant->name) }}" class="ui-input mt-2"></label>
                        <label class="text-sm">Last Name<input value="{{ $lastName }}" class="ui-input mt-2"></label>
                        <label class="text-sm">Date of Birth<input type="date" value="2004-05-15" class="ui-input mt-2"></label>
                        <label class="text-sm">Gender<select class="ui-input mt-2"><option>Female</option><option>Male</option></select></label>
                        <label class="text-sm">Nationality<select class="ui-input mt-2"><option>Filipino</option></select></label>
                        <label class="text-sm">Civil Status<select class="ui-input mt-2"><option>Single</option></select></label>
                        <label class="text-sm">School / University<input value="Xavier University - Ateneo de Cagayan" class="ui-input mt-2"></label>
                        <label class="text-sm">Course<input value="BS Information Technology" class="ui-input mt-2"></label>
                    </div>
                </section>

                <section class="mt-6 border-t ui-border pt-6">
                    <h2 class="text-lg font-semibold">Contact Information</h2>
                    <p class="mt-1 text-sm ui-muted">Update your contact details.</p>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <label class="text-sm">Email Address<input name="email" type="email" required value="{{ old('email', $email) }}" class="ui-input mt-2"></label>
                        <label class="text-sm">Phone Number<input name="phone" value="{{ old('phone', $phone) }}" class="ui-input mt-2"></label>
                        <label class="text-sm md:col-span-2">Current Address<textarea rows="3" class="ui-input mt-2">Cagayan de Oro City, Misamis Oriental, Philippines</textarea></label>
                    </div>
                </section>

                <section class="mt-6 border-t ui-border pt-6">
                    <h2 class="text-lg font-semibold">Profile Photo</h2>
                    <div class="mt-4">
                        <x-profile-image-uploader
                            label=""
                            name="profile_image"
                            :initial="$profileImageUrl"
                            :max-size-kb="5120"
                            :size="96"
                            :circle="true"
                        />
                    </div>
                </section>

                <section id="account-security" class="mt-6 border-t ui-border pt-6">
                    <h2 class="text-lg font-semibold">Notifications</h2>
                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        <label class="flex items-center gap-3 rounded-lg border ui-border p-3 text-sm"><input type="checkbox" name="notify_payment_reminders" value="1" @checked(old('notify_payment_reminders', $notifications['payment_reminders'] ?? true))> Payment reminders</label>
                        <label class="flex items-center gap-3 rounded-lg border ui-border p-3 text-sm"><input type="checkbox" name="notify_booking_updates" value="1" @checked(old('notify_booking_updates', $notifications['booking_updates'] ?? true))> Booking updates</label>
                        <label class="flex items-center gap-3 rounded-lg border ui-border p-3 text-sm"><input type="checkbox" name="notify_ticket_updates" value="1" @checked(old('notify_ticket_updates', $notifications['ticket_updates'] ?? true))> Message alerts</label>
                    </div>
                </section>

                <div class="mt-6 flex justify-end">
                    <button class="rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-700">Save Changes</button>
                </div>
            </form>

            <aside class="space-y-5">
                <section class="ui-card p-5">
                    <h2 class="font-semibold">Student Information</h2>
                    <p class="mt-1 text-sm ui-muted">Manage your academic details.</p>
                    <div class="mt-5 space-y-4">
                        <label class="text-sm">Student ID<input value="2026-IT-0987" class="ui-input mt-2"></label>
                        <label class="text-sm">Year Level<select class="ui-input mt-2"><option>2nd Year</option><option>3rd Year</option><option>4th Year</option></select></label>
                        <label class="text-sm">Expected Graduation<input value="May 2028" class="ui-input mt-2"></label>
                    </div>
                </section>

                <section id="id-verification" class="ui-card bg-amber-50/70 p-5 dark:bg-amber-950/20">
                    <h2 class="font-semibold text-amber-700 dark:text-amber-200">ID Verification</h2>
                    <p class="mt-3 text-sm ui-muted">Verify your identity to build trust and increase your booking credibility.</p>
                    <span class="mt-4 inline-flex rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">Verified</span>
                    <p class="mt-3 text-sm ui-muted">Verified on May 10, 2026</p>
                </section>

                <section class="ui-card bg-violet-50/70 p-5 dark:bg-violet-950/20">
                    <h2 class="font-semibold text-indigo-700 dark:text-indigo-200">Account Tips</h2>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li>Keep your information up to date</li>
                        <li>Verify your ID for more trust</li>
                        <li>Enable notifications for updates</li>
                    </ul>
                </section>
            </aside>
        </div>

        <div class="ui-card flex flex-col gap-3 bg-violet-50/50 p-5 text-sm dark:bg-violet-950/20 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-semibold">Your account is secure</p>
                <p class="mt-1 ui-muted">We take your privacy and security seriously.</p>
            </div>
            <a href="#account-security" class="rounded-lg border ui-border px-5 py-3 text-sm font-semibold text-indigo-700 hover:bg-[color:var(--surface-2)]">View Account Activity</a>
        </div>
    </div>
</x-user.shell>
</x-layouts.dashboard>
