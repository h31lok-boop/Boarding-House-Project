<x-layouts.dashboard>
<x-admin.shell>
@php
    $hasTenantProfiles = $hasTenantProfiles ?? \Illuminate\Support\Facades\Schema::hasTable('tenant_profiles');
    $hasReservations = $hasReservations ?? \Illuminate\Support\Facades\Schema::hasTable('reservations');
    $hasBoardingHouseUserColumn = $hasBoardingHouseUserColumn ?? \Illuminate\Support\Facades\Schema::hasColumn('users', 'boarding_house_id');

    $activeLeaseStatuses = ['active', 'approved', 'checked-in', 'checked_in', 'confirmed'];
    $pendingLeaseStatuses = ['pending', 'requested', 'reserved'];

    $initialsFor = function (?string $name): string {
        $words = preg_split('/\s+/', trim((string) $name)) ?: [];
        $initials = collect($words)
            ->filter()
            ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
            ->take(2)
            ->implode('');

        return $initials ?: 'T';
    };

    $phoneFor = fn ($tenant) => $tenant->phone_number ?: ($tenant->phone ?: ($tenant->contact_number ?: 'Not set'));

    $imageFor = function ($tenant): ?string {
        $path = $tenant->profile_photo ?: $tenant->profile_image;

        if (! $path) {
            return null;
        }

        return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/'])
            ? $path
            : \Illuminate\Support\Facades\Storage::url($path);
    };

    $isActiveTenant = fn ($tenant): bool => (bool) ($tenant->is_active ?? false)
        || strtolower((string) ($tenant->status ?? '')) === 'active';

    $summaryCards = [
        [
            'label' => 'Total Tenants',
            'value' => $totalTenants ?? $tenants->total(),
            'tone' => 'bg-blue-50 text-blue-600',
            'icon' => 'tenants',
        ],
        [
            'label' => 'Active Tenants',
            'value' => $activeTenants ?? 0,
            'tone' => 'bg-emerald-50 text-emerald-600',
            'icon' => 'check',
        ],
        [
            'label' => 'Inactive Tenants',
            'value' => $inactiveTenants ?? 0,
            'tone' => 'bg-rose-50 text-rose-600',
            'icon' => 'tenant',
        ],
    ];
@endphp

<div
    x-data="{
        viewOpen: false,
        editOpen: false,
        addOpen: @json(request('add') === 'tenant'),
        selected: {},
        photoPreview: null,
        openView(tenant) {
            this.selected = tenant;
            this.viewOpen = true;
        },
        openEdit(tenant) {
            this.selected = tenant;
            this.photoPreview = null;
            this.editOpen = true;
        }
    }"
    class="space-y-6"
>
    <section class="rounded-2xl border border-slate-200 bg-white px-6 py-6 shadow-sm shadow-slate-900/5">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-700">Tenant Management</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Tenants</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">Manage tenant profiles and current room assignments.</p>
            </div>
            <a
                href="{{ route('admin.tenants.create') }}"
                class="inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-bold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/>
                </svg>
                Add Tenant
            </a>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($summaryCards as $card)
            <article class="flex min-h-[112px] items-center gap-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-900/5">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full {{ $card['tone'] }}">
                    @if ($card['icon'] === 'check')
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.5 11.2 15 16 9.5"/>
                            <circle cx="12" cy="12" r="9" stroke-width="1.8"/>
                        </svg>
                    @elseif ($card['icon'] === 'tenant')
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="12" cy="8" r="3.5" stroke-width="1.8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 20a7 7 0 0 1 14 0"/>
                        </svg>
                    @else
                        @include('components.sidebar.partials.admin-icon', ['name' => 'tenants'])
                    @endif
                </div>
                <div>
                    <p class="text-3xl font-bold tracking-tight text-slate-950">{{ number_format((int) $card['value']) }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                </div>
            </article>
        @endforeach
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5">
        <form method="GET" action="{{ route('admin.tenants.index') }}" class="grid gap-3 xl:grid-cols-[minmax(260px,1fr)_220px_260px_auto]">
            <label class="relative block">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                    </svg>
                </span>
                <input
                    name="q"
                    value="{{ request('q') }}"
                    class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-12 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    placeholder="Search tenant name, email, or phone..."
                >
            </label>

            <select
                name="status"
                class="h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
            >
                <option value="">All Status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>

            <select
                name="boarding_house"
                class="h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
            >
                <option value="">All Boarding Houses</option>
                @foreach ($boardingHouses as $boardingHouse)
                    <option value="{{ $boardingHouse->id }}" @selected((string) request('boarding_house') === (string) $boardingHouse->id)>
                        {{ $boardingHouse->name }}
                    </option>
                @endforeach
            </select>

            <button
                type="submit"
                class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <circle cx="10.5" cy="10.5" r="6.5" stroke-width="2"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16 16 4 4"/>
                </svg>
                Search
            </button>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5">
        <div class="overflow-x-auto">
            <table class="min-w-[1080px] w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50/70">
                    <tr class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                        <th class="px-6 py-4">Tenant</th>
                        <th class="px-6 py-4">Contact</th>
                        <th class="px-6 py-4">Assigned Room</th>
                        <th class="px-6 py-4">Lease Status</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tenants as $tenant)
                        @php
                            $profile = $hasTenantProfiles ? $tenant->tenantProfile : null;
                            $tenantReservations = $hasReservations ? $tenant->reservations : collect();
                            $leaseReservation = $tenantReservations
                                ->filter(fn ($reservation) => in_array(strtolower((string) $reservation->status), array_merge($activeLeaseStatuses, $pendingLeaseStatuses), true))
                                ->sortByDesc(fn ($reservation) => optional($reservation->created_at)->timestamp ?? 0)
                                ->first();
                            $leaseStatusKey = strtolower((string) ($leaseReservation?->status ?? ''));
                            $leaseStatus = in_array($leaseStatusKey, $activeLeaseStatuses, true)
                                ? ['label' => 'Active Lease', 'dot' => 'bg-emerald-500', 'text' => 'text-emerald-700']
                                : (in_array($leaseStatusKey, $pendingLeaseStatuses, true)
                                    ? ['label' => 'Pending', 'dot' => 'bg-amber-500', 'text' => 'text-amber-600']
                                    : ['label' => 'No Lease', 'dot' => 'bg-slate-400', 'text' => 'text-slate-500']);

                            $assignedHouse = $leaseReservation?->boardingHouse?->name
                                ?: ($hasBoardingHouseUserColumn ? $tenant->boardingHouse?->name : null);
                            $assignedRoom = $leaseReservation?->room?->effective_room_number ?: ($tenant->room_number ?? null);
                            $tenantPhone = $phoneFor($tenant);
                            $tenantImage = $imageFor($tenant);
                            $active = $isActiveTenant($tenant);
                            $payload = [
                                'name' => $tenant->name,
                                'email' => $tenant->email,
                                'phone' => $tenantPhone,
                                'assigned_house' => $assignedHouse,
                                'assigned_room' => $assignedRoom,
                                'lease_status' => $leaseStatus['label'],
                                'status' => $active ? 'Active' : 'Inactive',
                                'student_id' => $profile?->student_id,
                                'school_company' => $profile?->school_company,
                                'course_or_position' => $profile?->course_or_position,
                                'valid_id_type' => $profile?->valid_id_type,
                                'valid_id_number' => $profile?->valid_id_number,
                                'emergency_contact_name' => $profile?->emergency_contact_name,
                                'emergency_contact_number' => $profile?->emergency_contact_number,
                                'preferred_language' => $profile?->preferred_language,
                                'id_verified' => (bool) ($profile?->id_verified),
                                'photo_url' => $tenantImage,
                                'update_url' => route('admin.tenant-profiles.update', $tenant),
                            ];
                        @endphp
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-50 text-sm font-bold text-blue-700">
                                        @if ($tenantImage)
                                            <img src="{{ $tenantImage }}" alt="{{ $tenant->name }}" class="h-full w-full object-cover">
                                        @else
                                            {{ $initialsFor($tenant->name) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-slate-950">{{ $tenant->name }}</p>
                                        <p class="truncate text-sm text-slate-500">{{ $tenant->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-700">{{ $tenantPhone }}</td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-900">{{ $assignedHouse ?: 'Not assigned' }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $assignedRoom ? 'Room '.$assignedRoom : 'No room assigned' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2 font-semibold {{ $leaseStatus['text'] }}">
                                    <span class="h-2 w-2 rounded-full {{ $leaseStatus['dot'] }}"></span>
                                    {{ $leaseStatus['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                        title="View tenant"
                                        aria-label="View {{ $tenant->name }}"
                                        @click="openView({{ \Illuminate\Support\Js::from($payload) }})"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.5 12s3.5-6.5 9.5-6.5S21.5 12 21.5 12s-3.5 6.5-9.5 6.5S2.5 12 2.5 12Z"/>
                                            <circle cx="12" cy="12" r="3" stroke-width="1.8"/>
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                        title="Edit tenant"
                                        aria-label="Edit {{ $tenant->name }}"
                                        @click="openEdit({{ \Illuminate\Support\Js::from($payload) }})"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m15.8 4.2 4 4M4 20l4.7-.8L19.3 8.6a2.8 2.8 0 0 0-4-4L4.8 15.2 4 20Z"/>
                                        </svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.users.destroy', $tenant) }}" onsubmit="return confirm('Delete this tenant account? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-rose-600 transition hover:border-rose-200 hover:bg-rose-50"
                                            title="Delete tenant"
                                            aria-label="Delete {{ $tenant->name }}"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 7h12m-9 0V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-.7 12a2 2 0 0 1-2 1.9H9.7a2 2 0 0 1-2-1.9L7 7m3 4v5m4-5v5"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16">
                                <div class="mx-auto max-w-md text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                        @include('components.sidebar.partials.admin-icon', ['name' => 'tenants'])
                                    </div>
                                    <h2 class="mt-4 text-base font-bold text-slate-950">No tenants found</h2>
                                    <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">Tenants will appear here once they register or are added by the admin.</p>
                                    <a href="{{ route('admin.tenants.create') }}" class="mt-5 inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/>
                                        </svg>
                                        Add Tenant
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>Showing {{ $tenants->firstItem() ?? 0 }} to {{ $tenants->lastItem() ?? 0 }} of {{ $tenants->total() }} results</p>
            @if ($tenants->hasPages())
                <nav class="flex flex-wrap items-center gap-2" aria-label="Tenant pagination">
                    @if ($tenants->onFirstPage())
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
                        </span>
                    @else
                        <a href="{{ $tenants->previousPageUrl() }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
                        </a>
                    @endif

                    @php($lastPage = $tenants->lastPage())
                    @php($currentPage = $tenants->currentPage())
                    @foreach ($tenants->getUrlRange(1, $lastPage) as $page => $url)
                        @if ($page === $currentPage)
                            <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-lg bg-blue-600 px-3 font-bold text-white shadow-sm">{{ $page }}</span>
                        @elseif ($lastPage <= 7 || $page <= 2 || $page === $lastPage || abs($page - $currentPage) <= 1)
                            <a href="{{ $url }}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-lg border border-slate-200 px-3 font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">{{ $page }}</a>
                        @elseif ($page === 3 || $page === $lastPage - 1)
                            <span class="px-1 text-slate-400">...</span>
                        @endif
                    @endforeach

                    @if ($tenants->hasMorePages())
                        <a href="{{ $tenants->nextPageUrl() }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
                        </a>
                    @else
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
                        </span>
                    @endif
                </nav>
            @endif
        </div>
    </section>

    <div
        data-modal-root
        role="dialog"
        aria-modal="true"
        x-show="viewOpen"
        x-cloak
        x-transition
        @click.self="viewOpen = false"
        @keydown.escape.window="viewOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
    >
        <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-700">Tenant Details</p>
                    <h2 class="mt-2 text-xl font-bold text-slate-950" x-text="selected.name"></h2>
                    <p class="mt-1 text-sm text-slate-500" x-text="selected.email"></p>
                </div>
                <button type="button" @click="viewOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4"><dt class="font-semibold text-slate-500">Contact</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.phone"></dd></div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4"><dt class="font-semibold text-slate-500">Status</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.status"></dd></div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4"><dt class="font-semibold text-slate-500">Assigned Room</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.assigned_house || 'Not assigned'"></dd><dd class="text-slate-500" x-text="selected.assigned_room ? 'Room ' + selected.assigned_room : 'No room assigned'"></dd></div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4"><dt class="font-semibold text-slate-500">Lease</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.lease_status"></dd></div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4"><dt class="font-semibold text-slate-500">School</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.school_company || 'Not set'"></dd></div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4"><dt class="font-semibold text-slate-500">Student ID</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.student_id || 'Not set'"></dd></div>
            </dl>
        </div>
    </div>

    <div
        data-modal-root
        role="dialog"
        aria-modal="true"
        x-show="editOpen"
        x-cloak
        x-transition
        @click.self="editOpen = false"
        @keydown.escape.window="editOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
    >
        <form method="POST" :action="selected.update_url" enctype="multipart/form-data" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
            @csrf
            @method('PATCH')
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-700">Edit Tenant</p>
                    <h2 class="mt-2 text-xl font-bold text-slate-950" x-text="selected.name"></h2>
                </div>
                <button type="button" @click="editOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <label class="text-sm font-semibold text-slate-700">Full Name
                    <input name="name" :value="selected.name" class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                </label>
                <label class="text-sm font-semibold text-slate-700">Phone
                    <input name="phone" :value="selected.phone" class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                </label>
                <label class="text-sm font-semibold text-slate-700">Student ID
                    <input name="student_id" :value="selected.student_id" class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                </label>
                <label class="text-sm font-semibold text-slate-700">School / Company
                    <input name="school_company" :value="selected.school_company" class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                </label>
                <label class="sm:col-span-2 text-sm font-semibold text-slate-700">Course / Position
                    <input name="course_or_position" :value="selected.course_or_position" class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                </label>
                <label class="text-sm font-semibold text-slate-700">Valid ID Type
                    <input name="valid_id_type" :value="selected.valid_id_type" class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                </label>
                <label class="text-sm font-semibold text-slate-700">Valid ID Number
                    <input name="valid_id_number" :value="selected.valid_id_number" class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                </label>
                <label class="text-sm font-semibold text-slate-700">Emergency Contact
                    <input name="emergency_contact_name" :value="selected.emergency_contact_name" class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                </label>
                <label class="text-sm font-semibold text-slate-700">Emergency Number
                    <input name="emergency_contact_number" :value="selected.emergency_contact_number" class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                </label>
                <label class="sm:col-span-2 flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <input type="hidden" name="id_verified" value="0">
                    <input type="checkbox" name="id_verified" value="1" :checked="selected.id_verified" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    Mark ID as verified
                </label>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="editOpen = false" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Cancel</button>
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700">Save Changes</button>
            </div>
        </form>
    </div>

    <div
        data-modal-root
        role="dialog"
        aria-modal="true"
        x-show="addOpen"
        x-cloak
        x-transition
        @click.self="addOpen = false"
        @keydown.escape.window="addOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
    >
        <form method="POST" action="{{ route('admin.users.store') }}" class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
            @csrf
            <input type="hidden" name="role" value="user">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-700">New Tenant</p>
                    <h2 class="mt-2 text-xl font-bold text-slate-950">Add Tenant</h2>
                    <p class="mt-1 text-sm text-slate-500">Create a tenant account for BoardMatch.</p>
                </div>
                <button type="button" @click="addOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <label class="sm:col-span-2 text-sm font-semibold text-slate-700">Full Name
                    <input name="name" required class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="Juan Dela Cruz">
                </label>
                <label class="text-sm font-semibold text-slate-700">Email
                    <input name="email" type="email" required class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="tenant@example.com">
                </label>
                <label class="text-sm font-semibold text-slate-700">Phone
                    <input name="phone" class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="09XX XXX XXXX">
                </label>
                <label class="text-sm font-semibold text-slate-700">Password
                    <input name="password" type="password" required class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="Strong password">
                </label>
                <label class="text-sm font-semibold text-slate-700">Confirm Password
                    <input name="password_confirmation" type="password" required class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="Confirm password">
                </label>
                <label class="sm:col-span-2 flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    Set account as active
                </label>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="addOpen = false" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Cancel</button>
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700">Create Tenant</button>
            </div>
        </form>
    </div>
</div>
</x-admin.shell>
</x-layouts.dashboard>
