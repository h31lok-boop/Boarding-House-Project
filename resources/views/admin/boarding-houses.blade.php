<x-layouts.dashboard>
<x-admin.shell>
    @php
        $statusLabel = fn ($house) => strtolower((string) ($house->approval_status ?: $house->status)) === 'pending'
            ? 'Pending'
            : ($house->is_active ? 'Active' : 'Inactive');

        $statusBadge = fn ($status) => match (strtolower((string) $status)) {
            'active' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
            'inactive' => 'bg-slate-100 text-slate-600 border-slate-200',
            default => 'bg-slate-50 text-slate-600 border-slate-200',
        };

        $occupancyTone = fn ($percent) => $percent >= 70
            ? 'bg-emerald-500 text-emerald-600'
            : ($percent >= 45 ? 'bg-amber-500 text-amber-600' : 'bg-blue-500 text-blue-600');

        $locationOptions = ['Davao City', 'Digos City', 'Buhangin', 'Matina', 'Talomo'];

        $summaryCards = [
            [
                'label' => 'Total Boarding Houses',
                'value' => number_format($totalBoardingHouses ?? 0),
                'sub' => null,
                'icon' => 'boarding-house',
                'tone' => 'bg-blue-50 text-blue-600',
            ],
            [
                'label' => 'Total Rooms',
                'value' => number_format($totalRooms ?? 0),
                'sub' => null,
                'icon' => 'rooms',
                'tone' => 'bg-emerald-50 text-emerald-600',
            ],
            [
                'label' => 'Occupancy Rate',
                'value' => ($occupancyRate ?? 0).'%',
                'sub' => number_format($occupiedRooms ?? 0).' / '.number_format($totalRooms ?? 0).' rooms',
                'icon' => 'analytics',
                'tone' => 'bg-violet-50 text-violet-600',
            ],
        ];
    @endphp

    <div
        x-data="{
            addOpen: @js(request()->routeIs('admin.boarding-houses.create')),
            viewOpen: false,
            editOpen: false,
            selected: {},
            showDetails(house) {
                this.selected = house;
                this.viewOpen = true;
                this.$nextTick(() => window.dispatchEvent(new CustomEvent('boarding-house-map:show', { detail: house })));
            },
            editDetails(house) {
                this.selected = house;
                this.editOpen = true;
            }
        }"
        class="space-y-6"
    >
        {{-- Page Header --}}
        <div class="ui-card rounded-2xl p-6 shadow-sm">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-blue-700">Property Management</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Boarding Houses</h1>
                    <p class="mt-2 text-sm text-slate-500">Manage registered boarding houses and room availability.</p>
                </div>
                <a href="{{ route('admin.boarding-houses.create') }}" class="btn-primary w-full justify-center sm:w-auto">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Boarding House
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" class="ui-card grid gap-3 rounded-2xl p-4 shadow-sm lg:grid-cols-[1fr_180px_200px_auto]">
            <div class="relative min-w-0">
                <svg class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input name="q" value="{{ request('q') }}" class="ui-input h-12 pl-11 text-sm" placeholder="Search by boarding house name or location...">
            </div>
            <select name="status" class="ui-input h-12 text-sm">
                <option value="">All Status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            </select>
            <select name="location" class="ui-input h-12 text-sm">
                <option value="">All Locations</option>
                @foreach ($locationOptions as $locationOption)
                    <option value="{{ $locationOption }}" @selected(request('location') === $locationOption)>{{ $locationOption }}</option>
                @endforeach
            </select>
            <button class="btn-secondary h-12 justify-center">
                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 4h18M6 8h12M9 12h6M12 16h1"/></svg>
                Filters
            </button>
        </form>

        {{-- Summary Cards --}}
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($summaryCards as $card)
                <article class="ui-card flex min-h-[116px] items-center gap-5 rounded-2xl p-5 shadow-sm">
                    <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl {{ $card['tone'] }}">
                        @include('components.sidebar.partials.admin-icon', ['name' => $card['icon']])
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold tracking-tight text-slate-950">{{ $card['value'] }}</p>
                        @if ($card['sub'])
                            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $card['sub'] }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Table --}}
        <div class="ui-card overflow-hidden rounded-2xl shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4 text-left">Boarding House</th>
                            <th class="px-5 py-4 text-left">Location</th>
                            <th class="px-5 py-4 text-left">Occupancy</th>
                            <th class="px-5 py-4 text-left">Status</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($houses as $house)
                            @php
                                $visibleStatus = $statusLabel($house);
                                $approval = $house->approval_status ?: ($house->status ?: 'pending');
                                $shortLocation = collect([
                                    $house->city?->city_name,
                                    $house->province?->province_name,
                                ])->filter()->implode(', ') ?: ($house->address ? explode(',', $house->address)[0] : 'CDO');
                                $fullLocation = collect([
                                    $house->barangay?->barangay_name,
                                    $house->city?->city_name,
                                    $house->province?->province_name,
                                ])->filter()->implode(', ') ?: ($house->full_address ?: ($house->address ?: 'Location not set'));
                                $occupiedCount = $house->rooms->filter(fn ($room) => strtolower((string) $room->status) === 'occupied')->count();
                                $totalCount = (int) ($house->rooms_count ?: $house->roomCategories->sum('total_rooms'));
                                $occupancyPct = $totalCount > 0 ? round(($occupiedCount / $totalCount) * 100) : 0;
                                $occupancyClasses = $occupancyTone($occupancyPct);
                                [$barClass, $percentClass] = explode(' ', $occupancyClasses);
                                $availableRooms = max(
                                    (int) ($house->available_rooms ?? 0),
                                    (int) $house->roomCategories->sum('available_rooms')
                                );
                                $thumbnail = $house->cover_image ?? $house->image ?? $house->photo ?? null;
                                $thumbnailUrl = $thumbnail
                                    ? (\Illuminate\Support\Str::startsWith($thumbnail, ['http://', 'https://', '/'])
                                        ? $thumbnail
                                        : asset('storage/'.$thumbnail))
                                    : null;
                                $approvalDate = $house->approval_date
                                    ? \Illuminate\Support\Carbon::parse($house->approval_date)->format('M d, Y')
                                    : null;
                                $payload = [
                                    'id' => $house->id,
                                    'name' => $house->name,
                                    'address' => $house->address ?: $house->full_address,
                                    'full_address' => $house->full_address ?: $house->address,
                                    'location_label' => collect([$house->barangay?->barangay_name, $house->city?->city_name, $house->province?->province_name, $house->region?->region_name])->filter()->implode(', '),
                                    'latitude' => $house->latitude !== null ? (float) $house->latitude : null,
                                    'longitude' => $house->longitude !== null ? (float) $house->longitude : null,
                                    'description' => $house->description,
                                    'house_rules' => $house->house_rules,
                                    'owner_id' => $house->owner_id,
                                    'owner_name' => $house->owner?->name,
                                    'owner_email' => $house->owner?->email,
                                    'owner_phone' => $house->owner?->contact_number ?: $house->owner?->phone,
                                    'owner_company' => $house->ownerProfile?->company_name,
                                    'landlord_info' => $house->landlord_info ?: ($house->contact_person ?: $house->owner?->name),
                                    'contact_name' => $house->contact_name ?: $house->contact_person,
                                    'contact_phone' => $house->contact_phone ?: $house->contact_number,
                                    'monthly_payment' => $house->monthly_payment ?: $house->price,
                                    'capacity' => $house->capacity ?: $house->max_capacity,
                                    'available_rooms' => $availableRooms,
                                    'rooms_count' => $house->rooms_count,
                                    'reservations_count' => $house->reservations_count,
                                    'inquiries_count' => $house->inquiries_count,
                                    'reviews_count' => $house->reviews_count,
                                    'approval_status' => $approval,
                                    'status' => $house->status ?: $approval,
                                    'is_active' => (bool) $house->is_active,
                                    'active_label' => $visibleStatus,
                                    'approval_date' => $approvalDate,
                                    'rejection_reason' => $house->rejection_reason,
                                    'amenities' => $house->amenities->pluck('name')->values(),
                                    'room_categories' => $house->roomCategories->map(fn ($category) => [
                                        'name' => $category->name,
                                        'monthly_rate' => $category->monthly_rate,
                                        'total_rooms' => $category->total_rooms,
                                        'available_rooms' => $category->available_rooms,
                                        'occupied_rooms' => $category->occupied_rooms,
                                        'reserved_rooms' => $category->reserved_rooms,
                                        'maintenance_rooms' => $category->maintenance_rooms,
                                        'is_available' => (bool) $category->is_available,
                                    ])->values(),
                                    'rooms' => $house->rooms->map(fn ($room) => [
                                        'name' => $room->room_no ?: ($room->room_number ?: ($room->name ?: 'Room '.$room->id)),
                                        'price' => $room->price,
                                        'capacity' => $room->capacity,
                                        'available_slots' => $room->available_slots,
                                        'status' => $room->status,
                                    ])->values(),
                                    'google_maps_url' => $house->latitude !== null && $house->longitude !== null
                                        ? 'https://www.google.com/maps/search/?api=1&query='.$house->latitude.','.$house->longitude
                                        : null,
                                    'update_url' => route('admin.listings.update', $house),
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <div class="flex min-w-[260px] items-center gap-4">
                                        <div class="flex h-16 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-blue-50 text-blue-500">
                                            @if ($thumbnailUrl)
                                                <img src="{{ $thumbnailUrl }}" class="h-full w-full object-cover" alt="{{ $house->name }}">
                                            @else
                                                @include('components.sidebar.partials.admin-icon', ['name' => 'boarding-house'])
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-bold text-slate-950">{{ $house->name }}</p>
                                            <p class="mt-1 text-xs font-semibold text-slate-500">ID: BH-{{ str_pad($house->id, 4, '0', STR_PAD_LEFT) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex min-w-[220px] items-center gap-2 text-slate-600">
                                        <svg class="h-4 w-4 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z"/>
                                            <circle cx="12" cy="10" r="2.5" stroke-width="1.8"/>
                                        </svg>
                                        <span>{{ $fullLocation }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="min-w-[190px]">
                                        <div class="flex items-center gap-1 text-sm">
                                            <span class="font-bold text-slate-950">{{ $occupiedCount }} / {{ $totalCount }}</span>
                                            <span class="text-slate-500">rooms</span>
                                        </div>
                                        <div class="mt-3 flex items-center gap-3">
                                            <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full {{ $barClass }}" style="width: {{ min(100, $occupancyPct) }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold {{ $percentClass }}">{{ $occupancyPct }}%</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ $statusBadge($visibleStatus) }}">
                                        {{ $visibleStatus }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end items-center gap-2">
                                        <button type="button" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" title="View" @click="showDetails({{ \Illuminate\Support\Js::from($payload) }})">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/></svg>
                                        </button>
                                        <button type="button" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" title="Edit" @click="editDetails({{ \Illuminate\Support\Js::from($payload) }})">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16.862 4.487a2.1 2.1 0 0 1 2.97 2.97L8.416 18.873l-4.5.5.5-4.5 12.446-10.386Z"/></svg>
                                        </button>
                                        <form method="POST" action="{{ route('admin.listings.destroy', $house) }}" onsubmit="return confirm('Delete this boarding house?')">
                                            @csrf @method('DELETE')
                                            <button class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-rose-500 transition hover:border-rose-200 hover:bg-rose-50" title="Delete">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14">
                                    <div class="mx-auto max-w-sm text-center">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                            @include('components.sidebar.partials.admin-icon', ['name' => 'boarding-house'])
                                        </div>
                                        <p class="mt-3 font-semibold text-slate-900">No boarding houses yet</p>
                                        <p class="mt-1 text-sm text-slate-500">Add your first boarding house to start managing rooms and availability.</p>
                                        <a href="{{ route('admin.boarding-houses.create') }}" class="btn-primary mt-4">Add Boarding House</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                <span>Showing {{ $houses->firstItem() ?? 0 }} to {{ $houses->lastItem() ?? 0 }} of {{ $houses->total() }} results</span>
                @if ($houses->hasPages())
                    <nav class="flex items-center gap-2" aria-label="Boarding houses pagination">
                        <a
                            href="{{ $houses->previousPageUrl() ?: '#' }}"
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 {{ $houses->onFirstPage() ? 'pointer-events-none opacity-50' : '' }}"
                            aria-label="Previous page"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg>
                        </a>

                        @foreach ($houses->getUrlRange(1, $houses->lastPage()) as $page => $url)
                            <a
                                href="{{ $url }}"
                                class="flex h-10 min-w-10 items-center justify-center rounded-xl border px-3 text-sm font-bold transition {{ $houses->currentPage() === $page ? 'border-blue-600 bg-blue-600 text-white shadow-sm shadow-blue-600/20' : 'border-slate-200 bg-white text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700' }}"
                                @if ($houses->currentPage() === $page) aria-current="page" @endif
                            >
                                {{ $page }}
                            </a>
                        @endforeach

                        <a
                            href="{{ $houses->nextPageUrl() ?: '#' }}"
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 {{ $houses->hasMorePages() ? '' : 'pointer-events-none opacity-50' }}"
                            aria-label="Next page"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                        </a>
                    </nav>
                @endif
            </div>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="addOpen" x-cloak @click.self="addOpen = false" @keydown.escape.window="addOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <form method="POST" action="{{ route('admin.listings.store') }}" class="ui-card max-h-[90vh] w-full max-w-3xl overflow-y-auto p-6">
                @csrf
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Add Boarding House</h2><button type="button" @click="addOpen = false" class="text-xl ui-muted">x</button></div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="text-sm">Name<input name="name" required class="ui-input mt-1"></label>
                    <label class="text-sm">Owner Account
                        <select name="owner_id" class="ui-input mt-1">
                            <option value="">Use current admin</option>
                            @foreach ($owners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->name }} - {{ $owner->email }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm">Monthly Fee<input name="monthly_payment" type="number" min="0" step="0.01" class="ui-input mt-1"></label>
                    <label class="text-sm">Capacity<input name="capacity" type="number" min="1" class="ui-input mt-1"></label>
                    <label class="text-sm">Available Rooms<input name="available_rooms" type="number" min="0" class="ui-input mt-1"></label>
                    <label class="text-sm">Approval<select name="approval_status" class="ui-input mt-1"><option value="approved">Approved</option><option value="pending">Pending</option><option value="rejected">Rejected</option></select></label>
                    <label class="text-sm md:col-span-2">Address<input name="address" required class="ui-input mt-1"></label>
                    <label class="text-sm">Latitude<input name="latitude" type="number" step="0.0000001" min="-90" max="90" class="ui-input mt-1" placeholder="6.7440000"></label>
                    <label class="text-sm">Longitude<input name="longitude" type="number" step="0.0000001" min="-180" max="180" class="ui-input mt-1" placeholder="125.3550000"></label>
                    <label class="text-sm">Owner / Landlord<input name="landlord_info" class="ui-input mt-1"></label>
                    <label class="text-sm">Contact Person<input name="contact_name" class="ui-input mt-1"></label>
                    <label class="text-sm">Contact Number<input name="contact_phone" class="ui-input mt-1"></label>
                    <label class="text-sm md:col-span-2">Description<textarea name="description" rows="3" class="ui-input mt-1"></textarea></label>
                    <label class="text-sm md:col-span-2">House Rules<textarea name="house_rules" rows="3" class="ui-input mt-1"></textarea></label>
                    <label class="md:col-span-2 flex items-center gap-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" checked> Active listing</label>
                </div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="addOpen = false" class="btn-secondary">Cancel</button><button class="btn-primary">Save Listing</button></div>
            </form>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="viewOpen" x-cloak @click.self="viewOpen = false" @keydown.escape.window="viewOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="ui-card max-h-[92vh] w-full max-w-5xl overflow-y-auto p-6">
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Boarding House Details</h2><button type="button" @click="viewOpen = false" class="text-xl ui-muted">x</button></div>
                <div class="mt-5 grid gap-5 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="space-y-5">
                        <dl class="grid gap-4 text-sm md:grid-cols-2">
                            <div><dt class="ui-muted">Name</dt><dd class="font-semibold" x-text="selected.name"></dd></div>
                            <div><dt class="ui-muted">Monthly Fee</dt><dd x-text="selected.monthly_payment ? `PHP ${Number(selected.monthly_payment).toLocaleString()}` : 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Capacity</dt><dd x-text="selected.capacity || 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Available Rooms</dt><dd x-text="selected.available_rooms ?? 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Listing Status</dt><dd x-text="selected.active_label || (selected.is_active ? 'Active' : 'Inactive')"></dd></div>
                            <div><dt class="ui-muted">Approval</dt><dd x-text="selected.approval_status || 'Pending'"></dd></div>
                            <div class="md:col-span-2"><dt class="ui-muted">Address</dt><dd x-text="selected.full_address || selected.address || 'Not set'"></dd></div>
                            <div x-show="selected.location_label" class="md:col-span-2"><dt class="ui-muted">Location Scope</dt><dd x-text="selected.location_label"></dd></div>
                            <div><dt class="ui-muted">Latitude</dt><dd x-text="selected.latitude ?? 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Longitude</dt><dd x-text="selected.longitude ?? 'Not set'"></dd></div>
                        </dl>

                        <dl class="grid gap-4 border-t ui-border pt-5 text-sm md:grid-cols-2">
                            <div><dt class="ui-muted">Owner Account</dt><dd class="font-semibold" x-text="selected.owner_name || 'Not assigned'"></dd></div>
                            <div><dt class="ui-muted">Owner Email</dt><dd x-text="selected.owner_email || 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Owner Company</dt><dd x-text="selected.owner_company || selected.landlord_info || 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Contact Person</dt><dd x-text="selected.contact_name || selected.landlord_info || 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Contact Number</dt><dd x-text="selected.contact_phone || selected.owner_phone || 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Approval Date</dt><dd x-text="selected.approval_date || 'Not set'"></dd></div>
                        </dl>

                        <div class="border-t ui-border pt-5 text-sm">
                            <p class="ui-muted">Description</p>
                            <p class="mt-1" x-text="selected.description || 'No description'"></p>
                            <p class="mt-4 ui-muted">House Rules</p>
                            <p class="mt-1" x-text="selected.house_rules || 'No rules specified yet.'"></p>
                            <template x-if="selected.rejection_reason">
                                <div>
                                    <p class="mt-4 ui-muted">Rejection Reason</p>
                                    <p class="mt-1 text-rose-600" x-text="selected.rejection_reason"></p>
                                </div>
                            </template>
                        </div>

                        <div class="grid gap-3 border-t ui-border pt-5 text-sm sm:grid-cols-4">
                            <div><p class="ui-muted">Rooms</p><p class="font-semibold" x-text="selected.rooms_count ?? 0"></p></div>
                            <div><p class="ui-muted">Inquiries</p><p class="font-semibold" x-text="selected.inquiries_count ?? 0"></p></div>
                            <div><p class="ui-muted">Reservations</p><p class="font-semibold" x-text="selected.reservations_count ?? 0"></p></div>
                            <div><p class="ui-muted">Reviews</p><p class="font-semibold" x-text="selected.reviews_count ?? 0"></p></div>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-sm font-semibold">Map Location</h3>
                                <a x-show="selected.google_maps_url" :href="selected.google_maps_url" target="_blank" class="text-sm text-[color:var(--brand-600)]">Open Maps</a>
                            </div>
                            <div id="boardingHouseDetailMap" class="mt-3 h-72 w-full overflow-hidden rounded-lg border ui-border"></div>
                            <p id="boardingHouseDetailMapEmpty" class="mt-2 hidden text-sm ui-muted">No geotag coordinates set for this boarding house.</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold">Amenities</h3>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <template x-if="!selected.amenities || selected.amenities.length === 0">
                                    <span class="text-sm ui-muted">No amenities listed.</span>
                                </template>
                                <template x-for="amenity in selected.amenities || []" :key="amenity">
                                    <span class="rounded-md border ui-border px-2 py-1 text-xs" x-text="amenity"></span>
                                </template>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold">Room Categories</h3>
                            <div class="mt-3 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="ui-surface-2 text-xs uppercase ui-muted">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Type</th>
                                            <th class="px-3 py-2 text-left">Rate</th>
                                            <th class="px-3 py-2 text-left">Available</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-if="!selected.room_categories || selected.room_categories.length === 0">
                                            <tr><td colspan="3" class="px-3 py-3 ui-muted">No room categories listed.</td></tr>
                                        </template>
                                        <template x-for="category in selected.room_categories || []" :key="category.name">
                                            <tr class="border-b ui-border">
                                                <td class="px-3 py-2" x-text="category.name"></td>
                                                <td class="px-3 py-2" x-text="category.monthly_rate ? `PHP ${Number(category.monthly_rate).toLocaleString()}` : 'N/A'"></td>
                                                <td class="px-3 py-2" x-text="`${category.available_rooms || 0} / ${category.total_rooms || 0}`"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold">Rooms</h3>
                            <div class="mt-3 max-h-48 overflow-y-auto">
                                <template x-if="!selected.rooms || selected.rooms.length === 0">
                                    <p class="text-sm ui-muted">No room records listed.</p>
                                </template>
                                <div class="divide-y ui-border">
                                    <template x-for="room in selected.rooms || []" :key="room.name">
                                        <div class="grid grid-cols-[1fr_auto] gap-3 py-2 text-sm">
                                            <div>
                                                <p class="font-semibold" x-text="room.name"></p>
                                                <p class="ui-muted" x-text="`${room.available_slots || 0} slots, capacity ${room.capacity || 0}`"></p>
                                            </div>
                                            <div class="text-right">
                                                <p x-text="room.price ? `PHP ${Number(room.price).toLocaleString()}` : 'N/A'"></p>
                                                <p class="ui-muted" x-text="room.status || 'No status'"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end"><button type="button" @click="viewOpen = false" class="btn-secondary">Close</button></div>
            </div>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="editOpen" x-cloak @click.self="editOpen = false" @keydown.escape.window="editOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <form method="POST" :action="selected.update_url" class="ui-card max-h-[90vh] w-full max-w-3xl overflow-y-auto p-6">
                @csrf @method('PUT')
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Edit Boarding House</h2><button type="button" @click="editOpen = false" class="text-xl ui-muted">x</button></div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="text-sm">Name<input name="name" required class="ui-input mt-1" :value="selected.name"></label>
                    <label class="text-sm">Owner Account
                        <select name="owner_id" class="ui-input mt-1" :value="selected.owner_id">
                            <option value="">Use current admin</option>
                            @foreach ($owners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->name }} - {{ $owner->email }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm">Monthly Fee<input name="monthly_payment" type="number" min="0" step="0.01" class="ui-input mt-1" :value="selected.monthly_payment"></label>
                    <label class="text-sm">Capacity<input name="capacity" type="number" min="1" class="ui-input mt-1" :value="selected.capacity"></label>
                    <label class="text-sm">Available Rooms<input name="available_rooms" type="number" min="0" class="ui-input mt-1" :value="selected.available_rooms"></label>
                    <label class="text-sm">Approval<select name="approval_status" class="ui-input mt-1" :value="selected.approval_status"><option value="approved">Approved</option><option value="pending">Pending</option><option value="rejected">Rejected</option></select></label>
                    <label class="text-sm md:col-span-2">Address<input name="address" required class="ui-input mt-1" :value="selected.address"></label>
                    <label class="text-sm">Latitude<input name="latitude" type="number" step="0.0000001" min="-90" max="90" class="ui-input mt-1" :value="selected.latitude"></label>
                    <label class="text-sm">Longitude<input name="longitude" type="number" step="0.0000001" min="-180" max="180" class="ui-input mt-1" :value="selected.longitude"></label>
                    <label class="text-sm">Owner / Landlord<input name="landlord_info" class="ui-input mt-1" :value="selected.landlord_info"></label>
                    <label class="text-sm">Contact Person<input name="contact_name" class="ui-input mt-1" :value="selected.contact_name"></label>
                    <label class="text-sm">Contact Number<input name="contact_phone" class="ui-input mt-1" :value="selected.contact_phone"></label>
                    <label class="text-sm md:col-span-2">Description<textarea name="description" rows="3" class="ui-input mt-1" x-text="selected.description"></textarea></label>
                    <label class="text-sm md:col-span-2">House Rules<textarea name="house_rules" rows="3" class="ui-input mt-1" x-text="selected.house_rules"></textarea></label>
                    <label class="md:col-span-2 flex items-center gap-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" :checked="selected.is_active"> Active listing</label>
                </div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="editOpen = false" class="btn-secondary">Cancel</button><button class="btn-primary">Save Changes</button></div>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let detailMap = null;
            let detailMarker = null;

            const setMapEmpty = (isEmpty) => {
                const mapEl = document.getElementById('boardingHouseDetailMap');
                const emptyEl = document.getElementById('boardingHouseDetailMapEmpty');

                mapEl?.classList.toggle('hidden', isEmpty);
                emptyEl?.classList.toggle('hidden', !isEmpty);
            };

            const escapeHtml = (value) => String(value || '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[char]));

            window.addEventListener('boarding-house-map:show', (event) => {
                window.setTimeout(() => {
                    const house = event.detail || {};
                    const lat = Number(house.latitude);
                    const lng = Number(house.longitude);

                    if (!window.L || !Number.isFinite(lat) || !Number.isFinite(lng)) {
                        setMapEmpty(true);
                        return;
                    }

                    setMapEmpty(false);

                    if (!detailMap) {
                        detailMap = L.map('boardingHouseDetailMap');
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(detailMap);
                    }

                    detailMap.setView([lat, lng], 16);

                    if (!detailMarker) {
                        detailMarker = L.marker([lat, lng]).addTo(detailMap);
                    } else {
                        detailMarker.setLatLng([lat, lng]);
                    }

                    detailMarker.bindPopup(`<strong>${escapeHtml(house.name || 'Boarding House')}</strong><br>${escapeHtml(house.address || '')}`).openPopup();
                    detailMap.invalidateSize();
                }, 150);
            });
        });
    </script>
</x-admin.shell>
</x-layouts.dashboard>
