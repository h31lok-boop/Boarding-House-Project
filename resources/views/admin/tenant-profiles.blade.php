<x-layouts.dashboard>
<x-admin.shell :show-header="false">
@php
    $workspace = request()->routeIs('owner.*') ? 'owner' : 'admin';
    $route = fn (string $name, $params = []) => route($workspace.'.'.$name, $params);
    $isOwnerWorkspace = $workspace === 'owner';

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

    $formatShortDate = function ($value, string $fallback = 'Not scheduled'): string {
        if (blank($value)) {
            return $fallback;
        }

        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('M d, Y');
        }

        return \Illuminate\Support\Carbon::parse($value)->format('M d, Y');
    };

    $formatRelativeDate = function ($value, string $fallback = 'Recently'): string {
        if (blank($value)) {
            return $fallback;
        }

        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->diffForHumans();
        }

        return \Illuminate\Support\Carbon::parse($value)->diffForHumans();
    };

    $resolveLeaseDetails = function ($tenant) use ($hasReservations, $hasBoardingHouseUserColumn, $activeLeaseStatuses, $pendingLeaseStatuses, $formatShortDate) {
        $tenantReservations = $hasReservations ? collect($tenant->reservations) : collect();
        $leaseReservation = $tenantReservations
            ->filter(fn ($reservation) => in_array(strtolower((string) $reservation->status), array_merge($activeLeaseStatuses, $pendingLeaseStatuses), true))
            ->sortByDesc(fn ($reservation) => optional($reservation->created_at)->timestamp ?? 0)
            ->first();

        $leaseStatusKey = strtolower((string) ($leaseReservation?->status ?? ''));
        $status = in_array($leaseStatusKey, $activeLeaseStatuses, true)
            ? ['label' => 'Active Lease', 'dot' => 'bg-emerald-500', 'text' => 'text-emerald-700', 'tone' => 'emerald']
            : (in_array($leaseStatusKey, $pendingLeaseStatuses, true)
                ? ['label' => 'Pending Move-in', 'dot' => 'bg-amber-500', 'text' => 'text-amber-700', 'tone' => 'amber']
                : ['label' => 'No Lease', 'dot' => 'bg-slate-400', 'text' => 'text-slate-500', 'tone' => 'slate']);

        $assignedHouse = $leaseReservation?->boardingHouse?->name
            ?: ($hasBoardingHouseUserColumn ? $tenant->boardingHouse?->name : null);
        $assignedHouseId = $leaseReservation?->boarding_house_id
            ?: ($hasBoardingHouseUserColumn ? $tenant->boarding_house_id : null);
        $assignedRoom = $leaseReservation?->room?->effective_room_number ?: ($tenant->room_number ?? null);

        return [
            'reservation' => $leaseReservation,
            'status_key' => $leaseStatusKey,
            'status' => $status,
            'assigned_house' => $assignedHouse,
            'assigned_house_id' => $assignedHouseId,
            'assigned_room' => $assignedRoom,
            'check_in_label' => $formatShortDate($leaseReservation?->check_in_date, 'Not scheduled'),
        ];
    };

    $currentCollection = method_exists($tenants, 'getCollection')
        ? $tenants->getCollection()
        : collect($tenants ?? []);
    $filteredTenants = collect($insightTenants ?? $currentCollection);
    $matchingTenants = $filteredTenants->count();
    $showingFrom = method_exists($tenants, 'firstItem') ? ($tenants->firstItem() ?? 0) : ($currentCollection->isEmpty() ? 0 : 1);
    $showingTo = method_exists($tenants, 'lastItem') ? ($tenants->lastItem() ?? 0) : $currentCollection->count();

    $tenantSignals = $filteredTenants->map(function ($tenant) use ($resolveLeaseDetails, $hasTenantProfiles, $phoneFor, $isActiveTenant) {
        $profile = $hasTenantProfiles ? $tenant->tenantProfile : null;
        $lease = $resolveLeaseDetails($tenant);
        $isVerified = (bool) ($profile?->id_verified);
        $activeAccount = $isActiveTenant($tenant);
        $needsFollowUp = ! $activeAccount
            || $lease['status']['tone'] === 'amber'
            || blank($lease['assigned_house'])
            || ! $isVerified
            || $phoneFor($tenant) === 'Not set';

        return [
            'tenant' => $tenant,
            'profile' => $profile,
            'lease' => $lease,
            'active_account' => $activeAccount,
            'verified' => $isVerified,
            'needs_follow_up' => $needsFollowUp,
        ];
    })->values();

    $activeLeaseCount = $tenantSignals->filter(fn ($row) => $row['lease']['status']['tone'] === 'emerald')->count();
    $pendingLeaseCount = $tenantSignals->filter(fn ($row) => $row['lease']['status']['tone'] === 'amber')->count();
    $verifiedTenantCount = $tenantSignals->filter(fn ($row) => $row['verified'])->count();
    $unassignedTenantCount = $tenantSignals->filter(fn ($row) => blank($row['lease']['assigned_house']))->count();
    $representedHouseCount = $tenantSignals
        ->pluck('lease.assigned_house_id')
        ->filter()
        ->unique()
        ->count();
    $needsFollowUpCount = $tenantSignals->filter(fn ($row) => $row['needs_follow_up'])->count();
    $newThisMonthCount = $filteredTenants->filter(function ($tenant) {
        $createdAt = $tenant->created_at;

        return $createdAt && $createdAt->greaterThanOrEqualTo(now()->startOfMonth());
    })->count();
    $filteredActiveCount = $tenantSignals->filter(fn ($row) => $row['active_account'])->count();

    $filterChips = collect([
        'Search' => request('q'),
        'Status' => filled(request('status')) ? ucfirst((string) request('status')) : null,
        'Boarding House' => optional($boardingHouses->firstWhere('id', (int) request('boarding_house')))->name,
    ])->filter(fn ($value) => filled($value));

    $summaryCards = [
        [
            'label' => 'Matching Tenants',
            'value' => number_format($matchingTenants),
            'meta' => number_format($totalTenants ?? $matchingTenants).' total tracked',
            'icon' => 'tenants',
            'tone' => 'blue',
        ],
        [
            'label' => 'Active Stays',
            'value' => number_format($activeLeaseCount),
            'meta' => 'Checked in or confirmed',
            'icon' => 'reservations',
            'tone' => 'emerald',
        ],
        [
            'label' => 'Pending Move-ins',
            'value' => number_format($pendingLeaseCount),
            'meta' => 'Reservation follow-up needed',
            'icon' => 'notifications',
            'tone' => 'amber',
        ],
        [
            'label' => 'Verified Profiles',
            'value' => number_format($verifiedTenantCount),
            'meta' => number_format($needsFollowUpCount).' still need attention',
            'icon' => 'audit-logs',
            'tone' => 'violet',
        ],
    ];

    $insightCards = [
        [
            'label' => 'House Coverage',
            'value' => number_format($representedHouseCount),
            'detail' => 'Boarding houses represented in the current results',
            'tone' => 'blue',
        ],
        [
            'label' => 'Unassigned Tenants',
            'value' => number_format($unassignedTenantCount),
            'detail' => 'Profiles without a current room placement',
            'tone' => 'amber',
        ],
        [
            'label' => 'New This Month',
            'value' => number_format($newThisMonthCount),
            'detail' => 'Recently onboarded tenant accounts',
            'tone' => 'emerald',
        ],
    ];

    $recentActivity = $tenantSignals
        ->sortByDesc(function ($row) {
            $tenant = $row['tenant'];
            $timestamp = $tenant->updated_at ?: $tenant->created_at;

            return optional($timestamp)->timestamp ?? 0;
        })
        ->take(6)
        ->map(function ($row) use ($formatRelativeDate) {
            $tenant = $row['tenant'];
            $lease = $row['lease'];
            $profile = $row['profile'];
            $tone = $lease['status']['tone'] === 'slate'
                ? ($row['verified'] ? 'blue' : ($row['active_account'] ? 'slate' : 'rose'))
                : $lease['status']['tone'];

            if ($lease['status']['tone'] === 'emerald') {
                $detail = trim(($lease['assigned_house'] ?: 'Assigned property').' '.($lease['assigned_room'] ? 'Room '.$lease['assigned_room'] : ''));
            } elseif ($lease['status']['tone'] === 'amber') {
                $detail = 'Pending placement'.($lease['assigned_house'] ? ' at '.$lease['assigned_house'] : ' with no confirmed room yet');
            } elseif ($profile?->id_verified) {
                $detail = 'Profile verified and ready for owner coordination';
            } else {
                $detail = 'Profile updated and awaiting next action';
            }

            return [
                'title' => $tenant->name,
                'detail' => $detail,
                'meta' => $formatRelativeDate($tenant->updated_at ?: $tenant->created_at),
                'tone' => $tone,
            ];
        })
        ->values();

    $sidebarStats = [
        ['label' => 'Active Accounts', 'value' => number_format($filteredActiveCount), 'tone' => 'emerald'],
        ['label' => 'Inactive Accounts', 'value' => number_format(max($matchingTenants - $filteredActiveCount, 0)), 'tone' => 'rose'],
        ['label' => 'Needs Follow-up', 'value' => number_format($needsFollowUpCount), 'tone' => 'amber'],
    ];

    $toneClasses = fn (string $tone): string => match ($tone) {
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'violet' => 'bg-violet-50 text-violet-700 ring-violet-100',
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-100',
        'slate' => 'bg-slate-100 text-slate-600 ring-slate-200',
        default => 'bg-blue-50 text-blue-700 ring-blue-100',
    };
@endphp

<div
    x-data="{
        viewOpen: false,
        editOpen: false,
        addOpen: @json(request('add') === 'tenant'),
        selected: {},
        viewTab: 'overview',
        photoPreview: null,
        openView(tenant) {
            this.selected = tenant;
            this.viewTab = 'overview';
            this.viewOpen = true;
        },
        openEdit(tenant) {
            this.selected = tenant;
            this.photoPreview = null;
            this.editOpen = true;
        }
    }"
    class="space-y-5 text-slate-950"
>
    <header class="overflow-hidden rounded-[1.4rem] border border-slate-200/80 bg-white shadow-[0_14px_32px_rgba(15,23,42,0.05)]">
        <div class="px-5 py-4 sm:px-6">
            <div class="space-y-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <h1 class="text-[1.7rem] font-semibold tracking-[-0.04em] text-slate-950">Tenant Management</h1>
                        <p class="mt-1 text-[13px] leading-6 text-slate-500">Monitor tenant profiles, active stays, and housing history across your listings.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 lg:justify-end">
                        <a href="{{ $route('messages') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-[13px] font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                            Messages
                        </a>
                        <a href="{{ $route('reservations') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-[13px] font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
                            Reservations
                        </a>
                        @unless ($isOwnerWorkspace)
                        <a
                            href="{{ route('admin.tenants.create') }}"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-[13px] font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/>
                            </svg>
                            Add Tenant
                        </a>
                        @endunless
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <form method="GET" action="{{ $route('tenants.index') }}" class="grid gap-3 xl:grid-cols-[minmax(260px,1fr)_180px_220px_auto_auto] xl:items-center">
                        <label class="relative block">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                                </svg>
                            </span>
                            <input
                                name="q"
                                value="{{ request('q') }}"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="Search tenant name, email, phone, or profile"
                            >
                        </label>

                        <select
                            name="status"
                            class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                        >
                            <option value="">All Status</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>

                        <select
                            name="boarding_house"
                            class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
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
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                            </svg>
                            Apply
                        </button>

                        <a
                            href="{{ $route('tenants.index') }}"
                            class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                        >
                            Reset
                        </a>
                    </form>

                    @if ($filterChips->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($filterChips as $label => $value)
                                <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-600">
                                    <span class="text-slate-400">{{ $label }}:</span>
                                    <span class="text-slate-700">{{ $value }}</span>
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <div>
        <main class="space-y-4">
            <section class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-white shadow-sm shadow-slate-200/70">
                <div class="flex flex-col gap-3">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-950">Tenant Directory</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Showing {{ $showingFrom }} to {{ $showingTo }} of {{ number_format($matchingTenants) }} matching results.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-[11px] font-semibold text-slate-600">
                            {{ number_format($unassignedTenantCount) }} unassigned
                        </span>
                        <span class="inline-flex h-8 items-center rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 text-[11px] font-semibold text-emerald-700">
                            {{ number_format($activeLeaseCount) }} active stays
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[1120px] w-full text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50/80">
                            <tr class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">
                                <th class="px-5 py-3.5">Tenant</th>
                                <th class="px-5 py-3.5">Assignment</th>
                                <th class="px-5 py-3.5">Profile Snapshot</th>
                                <th class="px-5 py-3.5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($tenants as $tenant)
                                @php
                                    $profile = $hasTenantProfiles ? $tenant->tenantProfile : null;
                                    $lease = $resolveLeaseDetails($tenant);
                                    $tenantPhone = $phoneFor($tenant);
                                    $tenantImage = $imageFor($tenant);
                                    $active = $isActiveTenant($tenant);
                                    $language = $profile?->preferred_language ?: 'Not set';
                                    $schoolOrCompany = $profile?->school_company ?: 'Not set';
                                    $courseOrPosition = $profile?->course_or_position ?: 'No course or position saved';

                                    $tenantReservations = $hasReservations ? collect($tenant->reservations) : collect();
                                    $reservationRows = $tenantReservations
                                        ->sortByDesc(fn ($r) => optional($r->created_at)->timestamp ?? 0)
                                        ->map(fn ($r) => [
                                            'house' => $r->boardingHouse?->name ?: 'Unassigned',
                                            'room' => $r->room?->effective_room_number ?: null,
                                            'status' => ucfirst((string) ($r->status ?: 'pending')),
                                            'check_in' => $formatShortDate($r->check_in_date, 'Not scheduled'),
                                        ])->values()->all();

                                    $paymentRecords = \Illuminate\Support\Facades\Schema::hasTable('payments')
                                        ? \App\Models\Payment::whereHas('tenant', fn ($q) => $q->where('user_id', $tenant->id))
                                            ->latest('created_at')->limit(25)->get()
                                        : collect();
                                    $paymentRows = $paymentRecords->map(fn ($p) => [
                                        'amount' => 'PHP '.number_format((float) $p->amount, 2),
                                        'status' => ucfirst((string) ($p->status ?: 'pending')),
                                        'reference' => $p->reference_no ?: '—',
                                        'date' => $formatShortDate($p->paid_at ?: $p->created_at, 'Pending'),
                                    ])->values()->all();
                                    $paidStatuses = ['paid', 'completed', 'confirmed', 'verified'];
                                    $outstanding = $paymentRecords
                                        ->reject(fn ($p) => in_array(strtolower((string) $p->status), $paidStatuses, true))
                                        ->sum(fn ($p) => (float) $p->amount);

                                    $payload = [
                                        'name' => $tenant->name,
                                        'email' => $tenant->email,
                                        'phone' => $tenantPhone,
                                        'assigned_house' => $lease['assigned_house'],
                                        'assigned_room' => $lease['assigned_room'],
                                        'lease_status' => $lease['status']['label'],
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
                                        'initials' => $initialsFor($tenant->name),
                                        'move_in_date' => $formatShortDate($lease['reservation']?->check_in_date ?? $tenant->move_in_date, 'Not scheduled'),
                                        'registered' => $formatShortDate($tenant->created_at, 'Unknown'),
                                        'total_reservations' => $tenantReservations->count(),
                                        'total_payments' => $paymentRecords->count(),
                                        'outstanding' => 'PHP '.number_format((float) $outstanding, 2),
                                        'reservations' => $reservationRows,
                                        'payments' => $paymentRows,
                                        'update_url' => $route('tenant-profiles.update', $tenant),
                                        'delete_url' => $isOwnerWorkspace ? null : route('admin.users.destroy', $tenant),
                                    ];
                                @endphp
                                <tr
                                    class="cursor-pointer align-top transition hover:bg-blue-50/40 focus:outline-none focus-visible:bg-blue-50/60"
                                    role="button"
                                    tabindex="0"
                                    @click="openView({{ \Illuminate\Support\Js::from($payload) }})"
                                    @keydown.enter.prevent="openView({{ \Illuminate\Support\Js::from($payload) }})"
                                    @keydown.space.prevent="openView({{ \Illuminate\Support\Js::from($payload) }})"
                                >
                                    <td class="px-5 py-4">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-50 text-sm font-bold text-blue-700">
                                                @if ($tenantImage)
                                                    <img src="{{ $tenantImage }}" alt="{{ $tenant->name }}" class="h-full w-full object-cover">
                                                @else
                                                    {{ $initialsFor($tenant->name) }}
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate font-bold text-slate-950">{{ $tenant->name }}</p>
                                                <p class="truncate text-[12px] text-slate-500">{{ $tenant->email }}</p>
                                                <div class="mt-2 flex flex-wrap gap-1.5">
                                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">{{ $tenantPhone }}</span>
                                                    <span class="inline-flex rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700">Joined {{ $formatShortDate($tenant->created_at, 'Recently added') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-900">{{ $lease['assigned_house'] ?: 'Not assigned yet' }}</p>
                                        <p class="mt-1 text-[12px] text-slate-500">{{ $lease['assigned_room'] ? 'Room '.$lease['assigned_room'] : 'No room assigned' }}</p>
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">Check-in {{ $lease['check_in_label'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-900">{{ $schoolOrCompany }}</p>
                                        <p class="mt-1 text-[12px] text-slate-500">{{ $courseOrPosition }}</p>
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">Language: {{ $language }}</span>
                                            @if ($profile?->id_verified)
                                                <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">ID Verified</span>
                                            @else
                                                <span class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">Needs ID review</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="space-y-2">
                                            <span class="inline-flex items-center gap-2 font-semibold {{ $lease['status']['text'] }}">
                                                <span class="h-2 w-2 rounded-full {{ $lease['status']['dot'] }}"></span>
                                                {{ $lease['status']['label'] }}
                                            </span>
                                            <div>
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold {{ $active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                                    {{ $active ? 'Active account' : 'Inactive account' }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-400">Updated {{ $formatRelativeDate($tenant->updated_at ?: $tenant->created_at) }}</p>
                                        </div>
                                    </td>
                                    <td class="hidden">
                                        <div class="hidden" aria-hidden="true">
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
                                            @unless ($isOwnerWorkspace)
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
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-16">
                                        <div class="mx-auto max-w-md text-center">
                                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                                @include('components.sidebar.partials.admin-icon', ['name' => 'tenants'])
                                            </div>
                                            <h2 class="mt-4 text-base font-bold text-slate-950">No tenants found</h2>
                                            <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">Tenants will appear here once they register or are added by the admin.</p>
                                            @unless ($isOwnerWorkspace)
                                            <a href="{{ route('admin.tenants.create') }}" class="mt-5 inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/>
                                                </svg>
                                                Add Tenant
                                            </a>
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
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
        </main>

    </div>

    <template x-teleport="body">
    <div
        data-modal-root
        role="dialog"
        aria-modal="true"
        x-show="viewOpen"
        x-cloak
        x-transition
        @keydown.escape.window="viewOpen = false"
        class="bm-modal-overlay"
    >
        <div class="bm-modal bm-modal--lg">
            <div class="bm-modal__header">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-50 text-lg font-bold text-blue-700">
                        <template x-if="selected.photo_url"><img :src="selected.photo_url" :alt="selected.name" class="h-full w-full object-cover"></template>
                        <span x-show="!selected.photo_url" x-text="selected.initials || 'T'"></span>
                    </div>
                    <div>
                        <h2 class="bm-modal__title" x-text="selected.name"></h2>
                        <p class="bm-modal__subtitle" x-text="selected.email"></p>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600" x-text="selected.phone || 'No contact'"></span>
                            <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-bold"
                                  :class="selected.status === 'Active' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                                  x-text="selected.status"></span>
                        </div>
                    </div>
                </div>
                <button type="button" @click="viewOpen = false" class="bm-modal__close" aria-label="Close tenant details modal">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex gap-1 border-b border-slate-200 px-6">
                <template x-for="tab in [['overview','Overview'],['reservations','Reservations'],['payments','Payments']]" :key="tab[0]">
                    <button type="button" @click="viewTab = tab[0]"
                            class="relative -mb-px border-b-2 px-4 py-2.5 text-[13px] font-semibold transition"
                            :class="viewTab === tab[0] ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                            x-text="tab[1]"></button>
                </template>
            </div>

            <div class="bm-modal__body bm-modal__body--compact">
                {{-- Overview --}}
                <div x-show="viewTab === 'overview'" class="space-y-4">
                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3 text-center">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Reservations</p>
                            <p class="mt-1 text-xl font-bold text-slate-900" x-text="selected.total_reservations ?? 0"></p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3 text-center">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Payments</p>
                            <p class="mt-1 text-xl font-bold text-slate-900" x-text="selected.total_payments ?? 0"></p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3 text-center">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Outstanding</p>
                            <p class="mt-1 text-xl font-bold text-rose-600" x-text="selected.outstanding || 'PHP 0.00'"></p>
                        </div>
                    </div>
                    <dl class="bm-modal__details bm-modal__details--two-col">
                        <div class="bm-modal__detail"><dt>Move-in Date</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.move_in_date || 'Not scheduled'"></dd></div>
                        <div class="bm-modal__detail"><dt>Reservation Status</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.lease_status"></dd></div>
                        <div class="bm-modal__detail"><dt>Assigned Room</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.assigned_house || 'Not assigned'"></dd><dd class="text-slate-500" x-text="selected.assigned_room ? 'Room ' + selected.assigned_room : 'No room assigned'"></dd></div>
                        <div class="bm-modal__detail"><dt>Date Registered</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.registered || 'Unknown'"></dd></div>
                        <div class="bm-modal__detail"><dt>School</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.school_company || 'Not set'"></dd></div>
                        <div class="bm-modal__detail"><dt>Student ID</dt><dd class="mt-1 font-bold text-slate-900" x-text="selected.student_id || 'Not set'"></dd></div>
                    </dl>
                </div>

                {{-- Reservations --}}
                <div x-show="viewTab === 'reservations'" class="space-y-2">
                    <template x-if="!selected.reservations || selected.reservations.length === 0">
                        <p class="rounded-xl border border-dashed border-slate-200 py-8 text-center text-sm text-slate-500">No reservations on record.</p>
                    </template>
                    <template x-for="(r, i) in (selected.reservations || [])" :key="i">
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-900" x-text="r.house"></p>
                                <p class="text-[12px] text-slate-500" x-text="(r.room ? 'Room ' + r.room + ' · ' : '') + 'Check-in ' + r.check_in"></p>
                            </div>
                            <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600" x-text="r.status"></span>
                        </div>
                    </template>
                </div>

                {{-- Payments --}}
                <div x-show="viewTab === 'payments'" class="space-y-2">
                    <template x-if="!selected.payments || selected.payments.length === 0">
                        <p class="rounded-xl border border-dashed border-slate-200 py-8 text-center text-sm text-slate-500">No payments on record.</p>
                    </template>
                    <template x-for="(p, i) in (selected.payments || [])" :key="i">
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-900" x-text="p.amount"></p>
                                <p class="text-[12px] text-slate-500" x-text="'Ref ' + p.reference + ' · ' + p.date"></p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold"
                                  :class="['Paid','Completed','Confirmed','Verified'].includes(p.status) ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                                  x-text="p.status"></span>
                        </div>
                    </template>
                </div>
            </div>
            <div class="bm-modal__footer items-center justify-between">
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="openEdit(selected); viewOpen = false" class="bm-modal__button bm-modal__button--primary">Edit</button>
                    @unless ($isOwnerWorkspace)
                    <form method="POST" :action="selected.delete_url" onsubmit="return confirm('Delete this tenant account? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button class="inline-flex h-9 items-center justify-center rounded-xl bg-rose-600 px-4 text-sm font-bold text-white shadow-sm shadow-rose-600/20 transition hover:bg-rose-700">Delete</button>
                    </form>
                    @endunless
                </div>
                <button type="button" @click="viewOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button>
            </div>
        </div>
    </div>
    </template>

    <template x-teleport="body">
    <div
        data-modal-root
        role="dialog"
        aria-modal="true"
        x-show="editOpen"
        x-cloak
        x-transition
        @keydown.escape.window="editOpen = false"
        class="bm-modal-overlay"
    >
        <form method="POST" :action="selected.update_url" enctype="multipart/form-data" class="bm-modal bm-modal--lg">
            @csrf
            @method('PATCH')
            <div class="bm-modal__header">
                <div>
                    <p class="bm-modal__eyebrow">Edit</p>
                    <h2 class="bm-modal__title" x-text="selected.name"></h2>
                    <p class="bm-modal__subtitle">Update tenant profile and compliance details.</p>
                </div>
                <button type="button" @click="editOpen = false" class="bm-modal__close" aria-label="Close edit tenant modal">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="bm-modal__body">
                <section class="bm-modal__section">
                    <div>
                        <h3 class="bm-modal__section-title">Tenant Information</h3>
                        <p class="bm-modal__section-copy">Keep core identity and verification fields up to date.</p>
                    </div>
                    <div class="bm-modal__grid bm-modal__grid--two-col mt-4">
                        <label>Full Name<input name="name" :value="selected.name"></label>
                        <label>Phone<input name="phone" :value="selected.phone"></label>
                        <label>Student ID<input name="student_id" :value="selected.student_id"></label>
                        <label>School / Company<input name="school_company" :value="selected.school_company"></label>
                        <label class="sm:col-span-2">Course / Position<input name="course_or_position" :value="selected.course_or_position"></label>
                        <label>Valid ID Type<input name="valid_id_type" :value="selected.valid_id_type"></label>
                        <label>Valid ID Number<input name="valid_id_number" :value="selected.valid_id_number"></label>
                        <label>Emergency Contact<input name="emergency_contact_name" :value="selected.emergency_contact_name"></label>
                        <label>Emergency Number<input name="emergency_contact_number" :value="selected.emergency_contact_number"></label>
                        <label class="sm:col-span-2 bm-modal__checkbox">
                            <input type="hidden" name="id_verified" value="0">
                            <input type="checkbox" name="id_verified" value="1" :checked="selected.id_verified" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>Mark ID as verified</span>
                        </label>
                    </div>
                </section>
            </div>

            <div class="bm-modal__footer">
                <button type="button" @click="editOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button>
                <button type="submit" class="bm-modal__button bm-modal__button--primary">Save Changes</button>
            </div>
        </form>
    </div>
    </template>

    @unless ($isOwnerWorkspace)
    <template x-teleport="body">
    <div
        data-modal-root
        role="dialog"
        aria-modal="true"
        x-show="addOpen"
        x-cloak
        x-transition
        @keydown.escape.window="addOpen = false"
        class="bm-modal-overlay"
    >
        <form method="POST" action="{{ route('admin.users.store') }}" class="bm-modal bm-modal--lg">
            @csrf
            <input type="hidden" name="role" value="user">

            <div class="bm-modal__header">
                <div>
                    <p class="bm-modal__eyebrow">Create</p>
                    <h2 class="bm-modal__title">Add Tenant</h2>
                    <p class="bm-modal__subtitle">Create a tenant account for BoardMatch.</p>
                </div>
                <button type="button" @click="addOpen = false" class="bm-modal__close" aria-label="Close add tenant modal">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="bm-modal__body">
                <section class="bm-modal__section">
                    <div>
                        <h3 class="bm-modal__section-title">Tenant Account</h3>
                        <p class="bm-modal__section-copy">Enter the tenant's login and contact information.</p>
                    </div>
                    <div class="bm-modal__grid bm-modal__grid--two-col mt-4">
                        <label class="sm:col-span-2">Full Name<input name="name" required placeholder="Juan Dela Cruz"></label>
                        <label>Email<input name="email" type="email" required placeholder="tenant@example.com"></label>
                        <label>Phone<input name="phone" placeholder="09XX XXX XXXX"></label>
                        <label>Password<input name="password" type="password" required placeholder="Strong password"></label>
                        <label>Confirm Password<input name="password_confirmation" type="password" required placeholder="Confirm password"></label>
                        <label class="sm:col-span-2 bm-modal__checkbox">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>Set account as active</span>
                        </label>
                    </div>
                </section>
            </div>

            <div class="bm-modal__footer">
                <button type="button" @click="addOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button>
                <button type="submit" class="bm-modal__button bm-modal__button--primary">Create Tenant</button>
            </div>
        </form>
    </div>
    </template>
    @endunless
</div>
</x-admin.shell>
</x-layouts.dashboard>
