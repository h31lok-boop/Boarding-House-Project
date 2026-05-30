<x-layouts.dashboard>
<x-admin.shell>
    @php
        $badge = fn ($status) => match (strtolower((string) $status)) {
            'active', 'verified' => 'bg-emerald-100 text-emerald-700',
            'inactive', 'needs review' => 'bg-rose-100 text-rose-700',
            default => 'bg-gray-100 text-gray-600',
        };

        $totalTenants    = $tenants->total();
        $activeTenants   = \App\Models\User::where('role', 'user')->where(fn ($q) => $q->where('is_active', true)->orWhere('status', 'active'))->count();
        $withLease       = \Illuminate\Support\Facades\Schema::hasTable('reservations')
            ? \App\Models\Reservation::whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['confirmed', 'checked-in', 'checked_in'])->distinct('user_id')->count('user_id')
            : 0;
        $inactiveTenants = \App\Models\User::where('role', 'user')->where(fn ($q) => $q->where('is_active', false)->orWhereIn('status', ['inactive', 'suspended']))->count();
    @endphp

    <div x-data="{
            viewOpen: false, editOpen: false, addOpen: false,
            selected: {}, photoPreview: null,
            openEdit(t) { this.selected = t; this.photoPreview = null; this.editOpen = true; },
            openView(t) { this.selected = t; this.viewOpen = true; }
         }" class="space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Tenants</h1>
                <p class="mt-1 text-sm text-gray-500">Manage all tenants in the system.</p>
            </div>
            <button type="button" @click="addOpen = true"
                    class="flex items-center gap-2 px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Tenant
            </button>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @php
                $summCards = [
                    ['label' => 'Total Tenants',    'value' => $totalTenants,    'sub' => '+ 4 this month', 'icon_bg' => 'bg-blue-50',    'icon_color' => 'text-blue-500',    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['label' => 'Active Tenants',   'value' => $activeTenants,   'sub' => round($totalTenants > 0 ? ($activeTenants / ($totalTenants ?: 1)) * 100 : 0).'% of total', 'icon_bg' => 'bg-emerald-50', 'icon_color' => 'text-emerald-600', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'With Active Lease','value' => $withLease,       'sub' => round($totalTenants > 0 ? ($withLease / ($totalTenants ?: 1)) * 100 : 0).'% of total', 'icon_bg' => 'bg-orange-50',  'icon_color' => 'text-orange-500',  'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'Inactive Tenants', 'value' => $inactiveTenants, 'sub' => round($totalTenants > 0 ? ($inactiveTenants / ($totalTenants ?: 1)) * 100 : 0).'% of total', 'icon_bg' => 'bg-rose-50',    'icon_color' => 'text-rose-400',    'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                ];
            @endphp
            @foreach ($summCards as $sc)
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <div class="h-10 w-10 rounded-xl {{ $sc['icon_bg'] }} flex items-center justify-center mb-3">
                        <svg class="h-5 w-5 {{ $sc['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="{{ $sc['icon'] }}"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $sc['value'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $sc['label'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $sc['sub'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Filters --}}
        <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm flex flex-wrap gap-3 items-center">
            <div class="relative flex-1 min-w-[220px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input name="q" value="{{ request('q') }}" class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" placeholder="Search by name, email, or phone...">
            </div>
            <select name="verified" class="px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300">
                <option value="">All Status</option>
                <option value="yes" @selected(request('verified') === 'yes')>Verified</option>
                <option value="no" @selected(request('verified') === 'no')>Unverified</option>
            </select>
            <select name="boarding_house" class="px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300">
                <option value="">All Boarding Houses</option>
                @foreach ($boardingHouses as $bh)
                    <option value="{{ $bh->id }}" @selected(request('boarding_house') == $bh->id)>{{ $bh->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="flex items-center gap-1.5 px-4 py-2 text-sm bg-orange-500 text-white rounded-lg hover:bg-orange-600 font-medium transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Search
            </button>
            @if (request()->hasAny(['q','verified','boarding_house']))
                <a href="{{ route('admin.tenant-profiles') }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600">Clear</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3 text-left">Tenant</th>
                            <th class="px-5 py-3 text-left">Contact</th>
                            <th class="px-5 py-3 text-left">Boarding House / Room</th>
                            <th class="px-5 py-3 text-left">Lease Period</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($tenants as $tenant)
                            @php
                                $profile = $tenant->tenantProfile;
                                $verified = (bool) ($profile?->id_verified);
                                $isActive = $tenant->is_active || $tenant->status === 'active';

                                $activeReservation = $tenant->reservations
                                    ->whereIn('status', ['confirmed', 'Confirmed', 'checked-in', 'checked_in', 'pending', 'Pending'])
                                    ->sortByDesc('created_at')
                                    ->first();

                                $payload = [
                                    'student_id' => $profile?->student_id,
                                    'school_company' => $profile?->school_company,
                                    'course_or_position' => $profile?->course_or_position,
                                    'valid_id_type' => $profile?->valid_id_type,
                                    'valid_id_number' => $profile?->valid_id_number,
                                    'emergency_contact_name' => $profile?->emergency_contact_name,
                                    'emergency_contact_number' => $profile?->emergency_contact_number,
                                    'preferred_language' => $profile?->preferred_language,
                                    'id_verified' => $verified,
                                    'tenant_name' => $tenant->name,
                                    'tenant_email' => $tenant->email,
                                    'tenant_phone' => $tenant->phone ?? $tenant->contact_number ?? '',
                                    'photo_url' => $tenant->profile_image
                                        ? (\Illuminate\Support\Str::startsWith($tenant->profile_image, ['http://', 'https://'])
                                            ? $tenant->profile_image
                                            : \Illuminate\Support\Facades\Storage::url($tenant->profile_image))
                                        : null,
                                    'update_url' => route('admin.tenant-profiles.update', $tenant),
                                    'delete_url' => $profile ? route('admin.tenant-profiles.destroy', $profile) : null,
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-full bg-gray-100 flex items-center justify-center text-sm font-semibold text-gray-600 shrink-0 overflow-hidden">
                                            @if ($tenant->profile_image)
                                                <img src="{{ \Illuminate\Support\Facades\Storage::url($tenant->profile_image) }}" class="h-full w-full object-cover" alt="">
                                            @else
                                                {{ strtoupper(substr($tenant->name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800">{{ $tenant->name }}</p>
                                            <p class="text-xs text-gray-400">{{ $tenant->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-gray-600 text-sm">{{ $tenant->contact_number ?? $tenant->phone ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    @if ($activeReservation)
                                        <p class="text-sm text-gray-700">{{ $activeReservation->boardingHouse?->name ?? '—' }}</p>
                                        <p class="text-xs text-gray-400">Room {{ $activeReservation->room?->effective_room_number ?? '—' }}</p>
                                    @else
                                        <span class="text-xs text-gray-400">Not assigned</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @if ($activeReservation)
                                        <p class="text-xs text-gray-600">{{ $activeReservation->check_in_date?->format('M d, Y') ?? '—' }}</p>
                                        <p class="text-xs text-gray-400">– {{ $activeReservation->check_out_date?->format('M d, Y') ?? '—' }}</p>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600' }}">
                                        {{ $isActive ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-1.5">
                                        <button type="button"
                                                class="h-7 w-7 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-500"
                                                title="View"
                                                @click="openView({{ \Illuminate\Support\Js::from($payload) }})">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/></svg>
                                        </button>
                                        <button type="button"
                                                class="h-7 w-7 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-orange-50 text-gray-500 hover:text-orange-500"
                                                title="Edit"
                                                @click="openEdit({{ \Illuminate\Support\Js::from($payload) }})">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16.862 4.487a2.1 2.1 0 0 1 2.97 2.97L8.416 18.873l-4.5.5.5-4.5 12.446-10.386Z"/></svg>
                                        </button>
                                        @if ($profile)
                                            <form method="POST" action="{{ route('admin.tenant-profiles.destroy', $profile) }}"
                                                  onsubmit="return confirm('Delete this tenant profile? The user account will remain.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Delete profile" class="h-7 w-7 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-rose-50 text-gray-400 hover:text-rose-500">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No tenants found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-5 py-4 flex items-center justify-between text-sm text-gray-500">
                <span>Showing {{ $tenants->firstItem() ?? 0 }} to {{ $tenants->lastItem() ?? 0 }} of {{ $tenants->total() }} results</span>
                {{ $tenants->links() }}
            </div>
        </div>

        {{-- View Modal --}}
        <div data-modal-root role="dialog" aria-modal="true" x-show="viewOpen" x-cloak @click.self="viewOpen = false" @keydown.escape.window="viewOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="bg-white rounded-2xl w-full max-w-2xl p-6 shadow-xl">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-semibold text-gray-900">Tenant Profile</h2>
                    <button type="button" @click="viewOpen = false" class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <dl class="grid gap-3 text-sm md:grid-cols-2">
                    <div class="py-2 border-b border-gray-100"><dt class="text-xs text-gray-400 uppercase">Tenant</dt><dd class="font-semibold text-gray-800 mt-1" x-text="selected.tenant_name"></dd></div>
                    <div class="py-2 border-b border-gray-100"><dt class="text-xs text-gray-400 uppercase">Email</dt><dd class="text-gray-700 mt-1" x-text="selected.tenant_email"></dd></div>
                    <div class="py-2 border-b border-gray-100"><dt class="text-xs text-gray-400 uppercase">Student ID</dt><dd class="text-gray-700 mt-1" x-text="selected.student_id || 'Not set'"></dd></div>
                    <div class="py-2 border-b border-gray-100"><dt class="text-xs text-gray-400 uppercase">School</dt><dd class="text-gray-700 mt-1" x-text="selected.school_company || 'Not set'"></dd></div>
                    <div class="py-2 border-b border-gray-100"><dt class="text-xs text-gray-400 uppercase">Course / Position</dt><dd class="text-gray-700 mt-1" x-text="selected.course_or_position || 'Not set'"></dd></div>
                    <div class="py-2 border-b border-gray-100"><dt class="text-xs text-gray-400 uppercase">Valid ID</dt><dd class="text-gray-700 mt-1" x-text="`${selected.valid_id_type || 'ID'} ${selected.valid_id_number || ''}`"></dd></div>
                    <div class="py-2 border-b border-gray-100"><dt class="text-xs text-gray-400 uppercase">Emergency Contact</dt><dd class="text-gray-700 mt-1" x-text="`${selected.emergency_contact_name || 'Not set'} ${selected.emergency_contact_number || ''}`"></dd></div>
                    <div class="py-2 border-b border-gray-100"><dt class="text-xs text-gray-400 uppercase">Verification</dt><dd class="mt-1"><span :class="selected.id_verified ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'" class="px-2 py-0.5 rounded-full text-xs font-medium" x-text="selected.id_verified ? 'Verified' : 'Needs Review'"></span></dd></div>
                </dl>
                <div class="mt-6 flex justify-end">
                    <button type="button" @click="viewOpen = false" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50">Close</button>
                </div>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div data-modal-root role="dialog" aria-modal="true" x-show="editOpen" x-cloak
             @click.self="editOpen = false" @keydown.escape.window="editOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <form method="POST" :action="selected.update_url"
                  enctype="multipart/form-data"
                  class="bg-white rounded-2xl max-h-[90vh] w-full max-w-2xl overflow-y-auto p-6 shadow-xl">
                @csrf @method('PATCH')
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-semibold text-gray-900">Edit Tenant</h2>
                    <button type="button" @click="editOpen = false"
                            class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Photo upload --}}
                <div class="flex flex-col items-center gap-3 mb-5">
                    <div class="relative">
                        <div class="h-20 w-20 rounded-full bg-gray-100 border-2 border-gray-200 overflow-hidden flex items-center justify-center text-2xl font-bold text-gray-400">
                            <template x-if="photoPreview || selected.photo_url">
                                <img :src="photoPreview || selected.photo_url" class="h-full w-full object-cover" alt="">
                            </template>
                            <template x-if="!photoPreview && !selected.photo_url">
                                <span x-text="(selected.tenant_name || 'T').charAt(0).toUpperCase()"></span>
                            </template>
                        </div>
                    </div>
                    <label class="cursor-pointer">
                        <span class="text-xs text-orange-600 font-semibold border border-orange-200 bg-orange-50 px-3 py-1.5 rounded-lg hover:bg-orange-100 transition-colors">
                            Change Photo
                        </span>
                        <input type="file" name="profile_image" accept="image/*" class="sr-only"
                               @change="photoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                    </label>
                    <p class="text-xs text-gray-400">JPG, PNG or WEBP · max 2 MB</p>
                </div>

                <div class="border-t border-gray-100 pt-4 mb-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Account Info</p>
                    <div class="grid gap-3 md:grid-cols-2">
                        <label class="text-sm font-medium text-gray-700">Full Name
                            <input name="name" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" :value="selected.tenant_name">
                        </label>
                        <label class="text-sm font-medium text-gray-700">Phone Number
                            <input name="phone" type="tel" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" :value="selected.tenant_phone" placeholder="09XX XXX XXXX">
                        </label>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Profile Details</p>
                    <div class="grid gap-3 md:grid-cols-2">
                        <label class="text-sm font-medium text-gray-700">Student ID
                            <input name="student_id" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" :value="selected.student_id">
                        </label>
                        <label class="text-sm font-medium text-gray-700">School / Company
                            <input name="school_company" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" :value="selected.school_company">
                        </label>
                        <label class="text-sm font-medium text-gray-700 md:col-span-2">Course / Position
                            <input name="course_or_position" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" :value="selected.course_or_position">
                        </label>
                        <label class="text-sm font-medium text-gray-700">Valid ID Type
                            <input name="valid_id_type" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" :value="selected.valid_id_type">
                        </label>
                        <label class="text-sm font-medium text-gray-700">Valid ID Number
                            <input name="valid_id_number" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" :value="selected.valid_id_number">
                        </label>
                        <label class="text-sm font-medium text-gray-700">Emergency Contact
                            <input name="emergency_contact_name" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" :value="selected.emergency_contact_name">
                        </label>
                        <label class="text-sm font-medium text-gray-700">Emergency Number
                            <input name="emergency_contact_number" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" :value="selected.emergency_contact_number">
                        </label>
                        <label class="text-sm font-medium text-gray-700 md:col-span-2">Preferred Language
                            <input name="preferred_language" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" :value="selected.preferred_language">
                        </label>
                        <label class="md:col-span-2 flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="hidden" name="id_verified" value="0">
                            <input type="checkbox" name="id_verified" value="1" :checked="selected.id_verified"
                                   class="rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                            <span>Mark ID as <strong>Verified</strong></span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="editOpen = false"
                            class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-orange-500 text-white text-sm font-medium hover:bg-orange-600">Save Changes</button>
                </div>
            </form>
        </div>

        {{-- Add Tenant Modal --}}
        <div data-modal-root role="dialog" aria-modal="true" x-show="addOpen" x-cloak
             @click.self="addOpen = false" @keydown.escape.window="addOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <form method="POST" action="{{ route('admin.users.store') }}"
                  class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                @csrf
                <input type="hidden" name="role" value="user">

                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Add Tenant</h2>
                        <p class="text-sm text-gray-400 mt-0.5">Create a new tenant user account</p>
                    </div>
                    <button type="button" @click="addOpen = false"
                            class="h-8 w-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="md:col-span-2 text-sm font-medium text-gray-700">Full Name <span class="text-rose-400">*</span>
                        <input name="name" required class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" placeholder="Juan dela Cruz">
                    </label>
                    <label class="text-sm font-medium text-gray-700">Email Address <span class="text-rose-400">*</span>
                        <input name="email" type="email" required class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" placeholder="juan@email.com">
                    </label>
                    <label class="text-sm font-medium text-gray-700">Phone Number
                        <input name="phone" type="tel" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" placeholder="09XX XXX XXXX">
                    </label>
                    <label class="text-sm font-medium text-gray-700">Password <span class="text-rose-400">*</span>
                        <input name="password" type="password" required minlength="8" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" placeholder="Min. 8 characters">
                    </label>
                    <label class="text-sm font-medium text-gray-700">Confirm Password <span class="text-rose-400">*</span>
                        <input name="password_confirmation" type="password" required minlength="8" class="mt-1 block w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-300" placeholder="Re-enter password">
                    </label>
                    <label class="md:col-span-2 flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                        <span>Set account as <strong>Active</strong> immediately</span>
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="addOpen = false"
                            class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-orange-500 text-white text-sm font-medium hover:bg-orange-600">Create Tenant</button>
                </div>
            </form>
        </div>

    </div>
</x-admin.shell>
</x-layouts.dashboard>
