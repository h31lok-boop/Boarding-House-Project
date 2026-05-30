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

    $displayName  = trim((string) $tenant->name) ?: 'Tenant';
    $initials     = collect(explode(' ', $displayName))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('');
    $email        = $tenant->email ?: '—';
    $phone        = $tenant->phone ?: ($tenant->contact_number ?: '—');
    $memberSince  = optional($tenant->created_at)->format('M d, Y') ?? 'Recently';

    $hasMatchProfiles = \Illuminate\Support\Facades\Schema::hasTable('tenant_match_profiles');
    $matchDone = $hasMatchProfiles
        && $tenant->relationLoaded('tenantMatchProfile')
        && (bool) $tenant->tenantMatchProfile?->completed_at;

    $completionItems = [
        'profile_photo'       => ['label'=>'Profile Photo',       'done'=> $hasPhoto],
        'personal_info'       => ['label'=>'Personal Information', 'done'=> filled($tenant->name)],
        'contact_info'        => ['label'=>'Contact Info',         'done'=> filled($email) && filled($phone)],
        'match_preferences'   => ['label'=>'Match Preferences',    'done'=> $matchDone],
    ];
    $completedCount    = collect($completionItems)->filter(fn($i)=>$i['done'])->count();
    $completionPercent = (int) round(($completedCount / max(count($completionItems),1)) * 100);
@endphp

<div
    x-data="{
        photoPreview: {{ $hasPhoto ? "'".e($profileImageUrl)."'" : 'null' }},
        initials: '{{ $initials }}',
        tab: 'profile',
    }"
    class="space-y-6">

    {{-- ── Breadcrumb ── --}}
    <nav class="flex items-center gap-1.5 text-xs text-gray-400">
        <a href="{{ route('user.dashboard') }}" class="hover:text-gray-600 transition-colors">Home</a>
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="font-medium text-gray-600">Profile Settings</span>
    </nav>

    {{-- Flash --}}
    @if (session('status') === 'tenant-account-updated')
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Profile updated successfully.
        </div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            @foreach ($errors->all() as $err)<p>{{ $err }}</p>@endforeach
        </div>
    @endif

    {{-- Page header --}}
    <div class="ui-card p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest" style="color:var(--brand-600)">Account</p>
                <h1 class="mt-1 text-2xl font-bold">Profile Settings</h1>
                <p class="mt-0.5 text-sm ui-muted">Manage your personal information and profile photo.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-700">{{ $completionPercent }}% complete</p>
                    <p class="text-xs ui-muted">{{ $completedCount }}/{{ count($completionItems) }} items</p>
                </div>
                <div class="relative h-12 w-12">
                    <svg class="h-12 w-12 -rotate-90" viewBox="0 0 36 36">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--border)" stroke-width="3"/>
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--brand-500)" stroke-width="3"
                                stroke-dasharray="{{ round($completionPercent * 1) }} 100"
                                stroke-linecap="round"/>
                    </svg>
                    <span class="absolute inset-0 flex items-center justify-center text-[10px] font-bold text-gray-700">{{ $completionPercent }}%</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[300px_1fr]">

        {{-- ── Left: profile card ── --}}
        <div class="space-y-5">

            {{-- Profile card --}}
            <div class="ui-card p-6 text-center">

                {{-- Avatar with upload overlay --}}
                <div class="relative mx-auto w-fit">
                    <div class="h-28 w-28 rounded-full overflow-hidden border-4 border-white shadow-lg ring-2 ring-[color:var(--brand-500)]/30 mx-auto">
                        <template x-if="photoPreview">
                            <img :src="photoPreview" alt="{{ $displayName }}" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!photoPreview">
                            <div class="h-full w-full flex items-center justify-center text-2xl font-bold text-white"
                                 style="background:linear-gradient(135deg,var(--brand-500),var(--accent-500))">
                                <span x-text="initials">{{ $initials }}</span>
                            </div>
                        </template>
                    </div>
                    {{-- Camera button --}}
                    <label for="avatarPickerSidebar"
                           class="absolute bottom-0 right-0 h-8 w-8 rounded-full flex items-center justify-center cursor-pointer shadow-md border-2 border-white"
                           style="background:var(--brand-500)" title="Change photo">
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </label>
                </div>

                <h2 class="mt-4 text-lg font-bold text-gray-900">{{ $displayName }}</h2>
                <span class="mt-1 inline-flex items-center rounded-full px-3 py-0.5 text-xs font-semibold"
                      style="background:rgba(255,126,95,.1);color:var(--brand-600)">
                    {{ ucfirst($tenant->role ?? 'Student') }}
                </span>

                <div class="mt-5 space-y-2.5 text-left text-sm">
                    <div class="flex items-center gap-2.5 ui-muted">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        <span class="truncate">{{ $email }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 ui-muted">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.338c0-.933.779-1.712 1.713-1.713h2.42c.379 0 .713.268.793.64l.927 4.167a.802.802 0 01-.228.77l-1.323 1.196a.802.802 0 00-.228.77c.43 1.852 1.677 3.403 3.45 4.56.228.143.519.115.72-.065l1.373-1.237a.802.802 0 01.77-.228l4.167.927c.372.08.64.414.64.793v2.42c0 .934-.78 1.713-1.713 1.713H6.337C4.047 21 2.25 19.204 2.25 16.913V6.338Z"/></svg>
                        <span>{{ $phone }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 ui-muted">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/></svg>
                        <span>Member since {{ $memberSince }}</span>
                    </div>
                </div>

                @if (!$hasPhoto)
                    <label for="avatarPickerSidebar"
                           class="mt-5 flex cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-dashed py-3 text-sm font-semibold transition-colors hover:border-orange-400 hover:bg-orange-50"
                           style="border-color:var(--border);color:var(--brand-600)">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Upload Photo
                    </label>
                @endif
            </div>

            {{-- Profile completion --}}
            <div class="ui-card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-800">Profile Completion</h3>
                    <span class="text-sm font-bold" style="color:var(--brand-600)">{{ $completionPercent }}%</span>
                </div>
                <div class="h-2 w-full rounded-full bg-gray-100 mb-4">
                    <div class="h-2 rounded-full transition-all duration-500"
                         style="width:{{ $completionPercent }}%;background:linear-gradient(90deg,var(--brand-500),var(--accent-500))"></div>
                </div>
                <ul class="space-y-2">
                    @foreach ($completionItems as $item)
                        <li class="flex items-center gap-2.5 text-sm">
                            @if ($item['done'])
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                                    <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span class="text-emerald-700 font-medium">{{ $item['label'] }}</span>
                            @else
                                <span class="h-5 w-5 shrink-0 rounded-full border-2 border-gray-200"></span>
                                <span class="ui-muted">{{ $item['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

        </div>

        {{-- ── Right: main form ── --}}
        <form method="POST" action="{{ route('user.settings.update') }}"
              enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Hidden file input linked to sidebar camera button --}}
            <input type="file" id="avatarPickerSidebar" name="profile_image"
                   accept="image/*" class="sr-only"
                   x-on:change="
                       const f=$event.target.files[0];
                       if(f) { photoPreview=URL.createObjectURL(f); }
                   ">
            <input type="hidden" name="profile_image_remove" value="0" id="removePhotoFlag">

            {{-- Profile Photo card --}}
            <div class="ui-card p-6">
                <div class="flex items-start justify-between mb-5">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Profile Photo</h2>
                        <p class="text-sm ui-muted mt-0.5">JPG, PNG or WEBP · max 5 MB</p>
                    </div>
                    <template x-if="photoPreview">
                        <button type="button"
                                onclick="document.getElementById('removePhotoFlag').value='1'; photoPreview=null; document.getElementById('avatarPickerSidebar').value='';"
                                class="text-xs text-rose-500 font-semibold hover:underline">
                            Remove photo
                        </button>
                    </template>
                </div>

                <div class="flex items-center gap-5">
                    {{-- Preview --}}
                    <div class="h-20 w-20 shrink-0 rounded-full overflow-hidden border-2 shadow-sm"
                         style="border-color:var(--border)">
                        <template x-if="photoPreview">
                            <img :src="photoPreview" alt="Preview" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!photoPreview">
                            <div class="h-full w-full flex items-center justify-center text-xl font-bold text-white"
                                 style="background:linear-gradient(135deg,var(--brand-500),var(--accent-500))">
                                <span x-text="initials">{{ $initials }}</span>
                            </div>
                        </template>
                    </div>

                    <div class="flex-1 space-y-2">
                        <label for="avatarPickerSidebar"
                               class="inline-flex cursor-pointer items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition-colors"
                               style="background:var(--brand-500)">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span x-text="photoPreview ? 'Change Photo' : 'Upload Photo'">{{ $hasPhoto ? 'Change Photo' : 'Upload Photo' }}</span>
                        </label>
                        <p class="text-xs ui-muted">Click your avatar or this button to upload a new photo.</p>
                    </div>
                </div>
            </div>

            {{-- Personal info --}}
            <div class="ui-card p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Personal Information</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="md:col-span-2 text-sm font-medium text-gray-700">
                        Full Name <span class="text-rose-400">*</span>
                        <input name="name" required value="{{ old('name', $tenant->name) }}"
                               class="ui-input mt-1.5" placeholder="Your full name">
                    </label>
                    <label class="text-sm font-medium text-gray-700">
                        Email Address <span class="text-rose-400">*</span>
                        <input name="email" type="email" required value="{{ old('email', $tenant->email) }}"
                               class="ui-input mt-1.5" placeholder="you@email.com">
                    </label>
                    <label class="text-sm font-medium text-gray-700">
                        Phone Number
                        <input name="phone" type="tel" value="{{ old('phone', $tenant->phone ?: $tenant->contact_number) }}"
                               class="ui-input mt-1.5" placeholder="09XX XXX XXXX">
                    </label>
                </div>
            </div>

            {{-- Notifications --}}
            <div class="ui-card p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-1">Notifications</h2>
                <p class="text-sm ui-muted mb-4">Choose what you'd like to be notified about.</p>
                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach ([
                        'notify_payment_reminders' => ['label'=>'Payment reminders','icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z','key'=>'payment_reminders'],
                        'notify_booking_updates'   => ['label'=>'Booking updates','icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z','key'=>'booking_updates'],
                        'notify_ticket_updates'    => ['label'=>'Message alerts','icon'=>'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z','key'=>'ticket_updates'],
                    ] as $inputName => $meta)
                        <label class="flex items-center gap-3 rounded-xl border p-3.5 cursor-pointer transition-colors hover:border-orange-200 ui-border">
                            <div class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0"
                                 style="background:rgba(255,126,95,.1)">
                                <svg class="h-4 w-4" style="color:var(--brand-600)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $meta['icon'] }}"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-700">{{ $meta['label'] }}</p>
                            </div>
                            <input type="checkbox" name="{{ $inputName }}" value="1"
                                   @checked(old($inputName, $notifications[$meta['key']] ?? true))
                                   class="rounded border-gray-300"
                                   style="accent-color:var(--brand-500)">
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Save --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('user.profile') }}"
                   class="text-sm font-semibold hover:underline" style="color:var(--brand-600)">
                    Update Match Preferences →
                </a>
                <button type="submit"
                        class="btn-primary px-8 py-2.5 text-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- ── Bottom Banner ── --}}
    <div class="ui-card p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 shrink-0 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s-8-4-8-10V5l8-3 8 3v7c0 6-8 10-8 10Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900">Your account information is kept private and secure.</p>
                <p class="text-xs text-gray-400">We use industry-standard encryption to protect your personal data.</p>
            </div>
        </div>
        <a href="{{ route('user.messages') }}" class="text-sm font-semibold text-indigo-600 hover:underline shrink-0">Contact Support →</a>
    </div>

</div>
</x-user.shell>
</x-layouts.dashboard>
