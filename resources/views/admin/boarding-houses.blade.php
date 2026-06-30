<x-layouts.dashboard>
<x-admin.shell :show-header="false">
    @php
        $isMineView = request('owner') === 'mine';
        $pageTitle = $isMineView ? 'My Boarding Houses' : 'Boarding Houses';
        $pageSubtitle = $isMineView
            ? 'Manage listing details, photos, room availability, and status from one streamlined workspace.'
            : 'Review property details, room inventory, and listing status in a cleaner owner workspace.';
        $sharedRouteParams = array_filter([
            'owner' => request('owner'),
        ]);
        $createListingUrl = route('admin.boarding-houses.create', $sharedRouteParams);
        $resetFiltersUrl = route('admin.boarding-houses', $sharedRouteParams);

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

        $locationOptions = collect(config('dssc.areas', []))
            ->push('Other nearby Digos City area')
            ->unique()
            ->values();

        $activeFilters = collect([
            'Search' => request('q'),
            'Status' => filled(request('status')) ? ucfirst((string) request('status')) : null,
            'Location' => request('location'),
        ])->filter(fn ($value) => filled($value));
        $houseCollection = method_exists($houses, 'getCollection')
            ? $houses->getCollection()
            : collect($houses ?? []);
        $totalListings = method_exists($houses, 'total') ? $houses->total() : $houseCollection->count();
        $showingFrom = method_exists($houses, 'firstItem') ? ($houses->firstItem() ?? 0) : ($houseCollection->isEmpty() ? 0 : 1);
        $showingTo = method_exists($houses, 'lastItem') ? ($houses->lastItem() ?? 0) : $houseCollection->count();
        $hasPagination = method_exists($houses, 'hasPages') && $houses->hasPages();

        $listingRows = $houseCollection->map(function ($house) use ($statusLabel, $statusBadge, $occupancyTone, $isMineView) {
            $asString = fn ($value): ?string => is_scalar($value) || $value === null ? ($value !== null ? (string) $value : null) : null;
            $visibleStatus = $statusLabel($house);
            $approval = $house->approval_status ?: ($house->status ?: 'pending');
            $fullLocation = collect([
                $house->display_barangay,
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
            $thumbnailUrl = $house->cover_image_url ?: asset('images/boarding-house-placeholder.svg');
            $approvalDate = $house->approval_date
                ? \Illuminate\Support\Carbon::parse($house->approval_date)->format('M d, Y')
                : null;
            $photoCount = $house->images->count();
            $payload = [
                'id' => $house->id,
                'name' => $house->name,
                'address' => $house->address ?: $house->full_address,
                'full_address' => $house->full_address ?: $house->address,
                'barangay' => $house->barangay ?: $house->display_barangay,
                'nearby_landmark' => $house->nearby_landmark,
                'distance_from_dssc' => $house->distance_from_dssc !== null ? (float) $house->distance_from_dssc : null,
                'is_near_dssc' => (bool) $house->is_near_dssc,
                'location_status' => $house->location_status ?: 'approximate',
                'location_label' => collect([$house->display_barangay, $house->city?->city_name, $house->province?->province_name, $house->region?->region_name])->filter()->implode(', '),
                'latitude' => $house->latitude !== null ? (float) $house->latitude : null,
                'longitude' => $house->longitude !== null ? (float) $house->longitude : null,
                'description' => $house->description,
                'house_rules' => $house->house_rules,
                'owner_id' => $house->owner_id,
                'owner_name' => $house->owner?->name,
                'owner_email' => $house->owner?->email,
                'owner_phone' => $house->owner?->contact_number ?: $house->owner?->phone,
                'owner_company' => $house->ownerProfile?->company_name,
                'landlord_info' => $asString($house->landlord_info ?: ($house->contact_person ?: $house->owner?->name)),
                'contact_name' => $asString($house->contact_name ?: $house->contact_person),
                'contact_phone' => $asString($house->contact_phone ?: $house->contact_number),
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
                'cover_image_url' => $house->cover_image_url,
                'images' => $house->images->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => $image->url,
                    'is_cover' => (bool) $image->is_primary,
                    'sort_order' => (int) $image->sort_order,
                ])->values(),
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

            return [
                'id' => $house->id,
                'display_id' => 'BH-'.str_pad($house->id, 4, '0', STR_PAD_LEFT),
                'name' => $house->name,
                'thumbnail_url' => $thumbnailUrl,
                'full_location' => $fullLocation,
                'available_rooms' => $availableRooms,
                'occupied_count' => $occupiedCount,
                'total_count' => $totalCount,
                'occupancy_pct' => $occupancyPct,
                'bar_class' => $barClass,
                'percent_class' => $percentClass,
                'visible_status' => $visibleStatus,
                'status_classes' => $statusBadge($visibleStatus),
                'photo_note' => $photoCount === 0
                    ? 'No photos yet'
                    : $photoCount.' '.\Illuminate\Support\Str::plural('photo', $photoCount).' uploaded',
                'photo_note_classes' => $photoCount === 0 ? 'text-amber-700' : 'text-blue-600',
                'payload' => $payload,
                'destroy_url' => route('admin.listings.destroy', array_filter([
                    'boarding_house' => $house,
                    'return_to_my_boarding_house' => $isMineView ? 1 : null,
                ])),
            ];
        })->values();
    @endphp

    <div
        x-data="{
            addOpen: @js(request()->routeIs('admin.boarding-houses.create')),
            viewOpen: false,
            editOpen: false,
            selected: {},
            createPhotos: [],
            editPhotos: [],
            createCoverSelection: '',
            editCoverSelection: '',
            photoStore() {
                window.boardingHousePhotoFiles ??= { create: new Map(), edit: new Map() };
                return window.boardingHousePhotoFiles;
            },
            showDetails(house) {
                this.selected = house;
                this.viewOpen = true;
                this.$nextTick(() => window.dispatchEvent(new CustomEvent('boarding-house-map:show', { detail: house })));
            },
            editDetails(house) {
                this.selected = JSON.parse(JSON.stringify(house));
                this.selected.images = (this.selected.images || []).map(image => ({ ...image, removed: false }));
                this.editPhotos.forEach(photo => URL.revokeObjectURL(photo.url));
                this.photoStore().edit.clear();
                this.editPhotos = [];
                this.editCoverSelection = this.selected.images.find(image => image.is_cover)
                    ? `existing:${this.selected.images.find(image => image.is_cover).id}`
                    : '';
                this.editOpen = true;
            },
            handlePhotoFiles(event, mode) {
                const existingCount = mode === 'edit'
                    ? (this.selected.images || []).filter(image => !image.removed).length
                    : 0;
                const files = Array.from(event.target.files || []);
                const allowedCount = Math.max(0, 10 - existingCount);

                if (files.length > allowedCount) {
                    alert(`You may upload up to ${allowedCount} more photo${allowedCount === 1 ? '' : 's'}.`);
                }

                const accepted = files.slice(0, allowedCount);
                const store = this.photoStore()[mode];
                store.clear();
                const photos = accepted.map((file, index) => {
                    const id = `${Date.now()}-${index}-${file.name}`;
                    store.set(id, file);

                    return {
                        id,
                        name: file.name,
                        url: URL.createObjectURL(file),
                    };
                });

                if (mode === 'create') {
                    this.createPhotos.forEach(photo => URL.revokeObjectURL(photo.url));
                    this.createPhotos = photos;
                    this.createCoverSelection = photos.length ? 'new:0' : '';
                    this.syncPhotoInput('create');
                } else {
                    this.editPhotos.forEach(photo => URL.revokeObjectURL(photo.url));
                    this.editPhotos = photos;
                    if (!this.editCoverSelection && photos.length) this.editCoverSelection = 'new:0';
                    this.syncPhotoInput('edit');
                }
            },
            removeNewPhoto(mode, index) {
                const photos = mode === 'create' ? this.createPhotos : this.editPhotos;
                const [removed] = photos.splice(index, 1);

                if (removed) {
                    URL.revokeObjectURL(removed.url);
                    this.photoStore()[mode].delete(removed.id);
                }

                const current = mode === 'create' ? this.createCoverSelection : this.editCoverSelection;
                if (current === `new:${index}`) {
                    const fallback = photos.length ? 'new:0' : '';
                    if (mode === 'create') this.createCoverSelection = fallback;
                    else this.editCoverSelection = fallback;
                } else if (current.startsWith('new:')) {
                    const currentIndex = Number(current.split(':')[1]);
                    if (currentIndex > index) {
                        if (mode === 'create') this.createCoverSelection = `new:${currentIndex - 1}`;
                        else this.editCoverSelection = `new:${currentIndex - 1}`;
                    }
                }
                this.syncPhotoInput(mode);
            },
            moveNewPhoto(mode, index, direction) {
                const photos = mode === 'create' ? this.createPhotos : this.editPhotos;
                const target = index + direction;
                if (target < 0 || target >= photos.length) return;
                [photos[index], photos[target]] = [photos[target], photos[index]];
                const current = mode === 'create' ? this.createCoverSelection : this.editCoverSelection;
                if (current === `new:${index}`) {
                    if (mode === 'create') this.createCoverSelection = `new:${target}`;
                    else this.editCoverSelection = `new:${target}`;
                } else if (current === `new:${target}`) {
                    if (mode === 'create') this.createCoverSelection = `new:${index}`;
                    else this.editCoverSelection = `new:${index}`;
                }
                this.syncPhotoInput(mode);
            },
            syncPhotoInput(mode) {
                const input = mode === 'create' ? this.$refs.createPhotoInput : this.$refs.editPhotoInput;
                const photos = mode === 'create' ? this.createPhotos : this.editPhotos;
                if (!input || typeof DataTransfer === 'undefined') return;
                const transfer = new DataTransfer();
                const store = this.photoStore()[mode];
                photos.forEach(photo => {
                    const file = store.get(photo.id);
                    if (file) transfer.items.add(file);
                });
                input.files = transfer.files;
            },
            moveExistingPhoto(index, direction) {
                const target = index + direction;
                if (target < 0 || target >= this.selected.images.length) return;
                [this.selected.images[index], this.selected.images[target]] = [this.selected.images[target], this.selected.images[index]];
            },
            toggleExistingPhoto(image) {
                image.removed = !image.removed;
                if (image.removed && this.editCoverSelection === `existing:${image.id}`) {
                    const fallback = (this.selected.images || []).find(candidate => !candidate.removed);
                    this.editCoverSelection = fallback ? `existing:${fallback.id}` : (this.editPhotos.length ? 'new:0' : '');
                }
            }
        }"
        class="space-y-5 text-slate-950"
    >
        <section class="overflow-hidden rounded-[1.6rem] border border-slate-200/80 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
            <div class="border-b border-slate-200/80 bg-[radial-gradient(circle_at_top_left,_rgba(37,99,235,0.08),_transparent_30%),linear-gradient(180deg,#ffffff_0%,#f8fafc_100%)] px-5 py-5 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-700">Property Management</p>
                        <h1 class="mt-2 text-[1.95rem] font-semibold tracking-[-0.04em] text-slate-950">{{ $pageTitle }}</h1>
                        <p class="mt-2 max-w-3xl text-[15px] leading-6 text-slate-600">{{ $pageSubtitle }}</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-[13px] font-medium text-slate-600 shadow-sm">
                            {{ number_format($totalListings) }} listings
                        </div>
                        <a href="{{ $createListingUrl }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Boarding House
                        </a>
                    </div>
                </div>
            </div>

            <div class="space-y-4 px-5 py-5 sm:px-6">
                <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm shadow-slate-200/50">
                    @if (filled(request('owner')))
                        <input type="hidden" name="owner" value="{{ request('owner') }}">
                    @endif

                    <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
                        <label class="relative block min-w-0 flex-1">
                            <span class="sr-only">Search listings</span>
                            <svg class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input name="q" value="{{ request('q') }}" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-10 pr-4 text-sm text-slate-700 shadow-none focus:border-slate-400 focus:bg-white focus:ring-0" placeholder="Search boarding houses or locations">
                        </label>

                        <label class="block xl:w-44">
                            <span class="sr-only">Status</span>
                            <select name="status" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-700 shadow-none focus:border-slate-400 focus:bg-white focus:ring-0">
                                <option value="">All Status</option>
                                <option value="active" @selected(request('status') === 'active')>Active</option>
                                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                            </select>
                        </label>

                        <label class="block xl:w-56">
                            <span class="sr-only">Location</span>
                            <select name="location" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-700 shadow-none focus:border-slate-400 focus:bg-white focus:ring-0">
                                <option value="">All Locations</option>
                                @foreach ($locationOptions as $locationOption)
                                    <option value="{{ $locationOption }}" @selected(request('location') === $locationOption)>{{ $locationOption }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div class="flex gap-2 xl:shrink-0">
                            <button class="inline-flex h-11 items-center justify-center rounded-xl border border-blue-600 bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">
                                Apply
                            </button>
                            @if ($activeFilters->isNotEmpty())
                                <a href="{{ $resetFiltersUrl }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                @if ($activeFilters->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach ($activeFilters as $label => $value)
                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-medium text-slate-600">
                                {{ $label }}: {{ $value }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section class="overflow-hidden rounded-[1.45rem] border border-slate-200/80 bg-white shadow-[0_12px_30px_rgba(15,23,42,0.05)]">
            <div class="border-b border-slate-200/80 px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-base font-semibold tracking-[-0.02em] text-slate-950">Listings</h2>
                    <span class="hidden rounded-full bg-slate-100 px-3 py-1 text-[11px] font-medium text-slate-600 sm:inline-flex">{{ number_format($listingRows->count()) }} on this page</span>
                </div>
            </div>

            @if ($listingRows->isEmpty())
                <div class="px-5 py-16">
                    <div class="mx-auto max-w-sm text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                            @include('components.sidebar.partials.admin-icon', ['name' => 'boarding-house'])
                        </div>
                        <p class="mt-4 text-base font-semibold tracking-[-0.02em] text-slate-900">No boarding houses yet</p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Add your first boarding house to start managing rooms and availability.</p>
                        <a href="{{ $createListingUrl }}" class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-medium text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">
                            Add Boarding House
                        </a>
                    </div>
                </div>
            @else
                <div class="divide-y divide-slate-200 lg:hidden">
                    @foreach ($listingRows as $listing)
                        <article class="space-y-4 px-5 py-5">
                            <div class="flex items-start gap-3">
                                <div class="flex h-20 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                    <img src="{{ $listing['thumbnail_url'] }}" class="h-full w-full object-cover" alt="{{ $listing['name'] }}" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}'">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <h3 class="text-sm font-semibold leading-5 tracking-[-0.01em] text-slate-950">{{ $listing['name'] }}</h3>
                                            <p class="mt-1 text-[11px] font-medium uppercase tracking-[0.16em] text-slate-400">{{ $listing['display_id'] }}</p>
                                        </div>
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-medium {{ $listing['status_classes'] }}">
                                            {{ $listing['visible_status'] }}
                                        </span>
                                    </div>

                                    <div class="mt-2 flex items-start gap-2 text-xs leading-5 text-slate-600">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z"/>
                                            <circle cx="12" cy="10" r="2.5" stroke-width="1.8"/>
                                        </svg>
                                        <p>{{ $listing['full_location'] }}</p>
                                    </div>

                                    <p class="mt-2 text-xs font-medium {{ $listing['photo_note_classes'] }}">{{ $listing['photo_note'] }}</p>
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-3">
                                    <p class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Rooms</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-950">{{ $listing['total_count'] }} total</p>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-3">
                                    <p class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Available</p>
                                    <p class="mt-2 text-sm font-semibold text-emerald-600">{{ $listing['available_rooms'] }}</p>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-3">
                                    <p class="text-[11px] uppercase tracking-[0.14em] text-slate-500">Occupied</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-950">{{ $listing['occupied_count'] }}</p>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between gap-3 text-[13px]">
                                    <span class="font-medium text-slate-950">Occupancy</span>
                                    <span class="text-xs font-medium {{ $listing['percent_class'] }}">{{ $listing['occupancy_pct'] }}%</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $listing['bar_class'] }}" style="width: {{ min(100, $listing['occupancy_pct']) }}%"></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 transition hover:bg-slate-50" @click="showDetails({{ \Illuminate\Support\Js::from($listing['payload']) }})">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                    </svg>
                                    View
                                </button>
                                <button type="button" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 transition hover:bg-slate-50" @click="editDetails({{ \Illuminate\Support\Js::from($listing['payload']) }})">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16.862 4.487a2.1 2.1 0 0 1 2.97 2.97L8.416 18.873l-4.5.5.5-4.5 12.446-10.386Z"/>
                                    </svg>
                                    Edit
                                </button>
                                <form method="POST" action="{{ $listing['destroy_url'] }}" onsubmit="return confirm('Delete this boarding house?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="inline-flex h-9 w-full items-center justify-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 text-xs font-medium text-rose-600 transition hover:bg-rose-100">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="hidden overflow-x-auto lg:block">
                    <table class="min-w-[1120px] w-full text-sm">
                        <thead class="bg-slate-50/80 text-[11px] uppercase tracking-[0.16em] text-slate-500">
                            <tr>
                                <th class="px-5 py-3.5 text-left font-medium">Boarding House</th>
                                <th class="px-5 py-3.5 text-left font-medium">Location</th>
                                <th class="px-5 py-3.5 text-left font-medium">Rooms</th>
                                <th class="px-5 py-3.5 text-left font-medium">Occupancy</th>
                                <th class="px-5 py-3.5 text-left font-medium">Status</th>
                                <th class="px-5 py-3.5 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($listingRows as $listing)
                                <tr class="align-top transition hover:bg-slate-50/70">
                                    <td class="px-5 py-4.5">
                                        <div class="flex items-start gap-4">
                                            <div class="flex h-16 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                                <img src="{{ $listing['thumbnail_url'] }}" class="h-full w-full object-cover" alt="{{ $listing['name'] }}" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}'">
                                            </div>
                                            <div class="min-w-0 space-y-1.5">
                                                <p class="text-[15px] font-semibold leading-6 tracking-[-0.02em] text-slate-950">{{ $listing['name'] }}</p>
                                                <p class="text-[11px] font-medium uppercase tracking-[0.16em] text-slate-400">{{ $listing['display_id'] }}</p>
                                                <p class="text-[12px] {{ $listing['photo_note_classes'] }}">{{ $listing['photo_note'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4.5 text-[13px] leading-6 text-slate-600">
                                        <div class="max-w-xs">
                                            {{ $listing['full_location'] }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-4.5">
                                        <div class="space-y-1 text-[13px] text-slate-600">
                                            <p><span class="font-semibold text-slate-950">{{ $listing['total_count'] }}</span> total</p>
                                            <p><span class="font-semibold text-emerald-600">{{ $listing['available_rooms'] }}</span> available</p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4.5">
                                        <div class="min-w-[220px]">
                                            <div class="flex items-center justify-between gap-3 text-[13px]">
                                                <span class="font-medium text-slate-950">{{ $listing['occupied_count'] }} / {{ $listing['total_count'] }} occupied</span>
                                                <span class="text-xs font-medium {{ $listing['percent_class'] }}">{{ $listing['occupancy_pct'] }}%</span>
                                            </div>
                                            <div class="mt-2 h-2 rounded-full bg-slate-100">
                                                <div class="h-full rounded-full {{ $listing['bar_class'] }}" style="width: {{ min(100, $listing['occupancy_pct']) }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4.5">
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-medium {{ $listing['status_classes'] }}">
                                            {{ $listing['visible_status'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4.5">
                                        <div class="flex justify-end gap-2">
                                            <button type="button" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 transition hover:bg-slate-50" @click="showDetails({{ \Illuminate\Support\Js::from($listing['payload']) }})">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                                </svg>
                                                View
                                            </button>
                                            <button type="button" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 transition hover:bg-slate-50" @click="editDetails({{ \Illuminate\Support\Js::from($listing['payload']) }})">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16.862 4.487a2.1 2.1 0 0 1 2.97 2.97L8.416 18.873l-4.5.5.5-4.5 12.446-10.386Z"/>
                                                </svg>
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ $listing['destroy_url'] }}" onsubmit="return confirm('Delete this boarding house?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 text-xs font-medium text-rose-600 transition hover:bg-rose-100">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                <span class="text-sm text-slate-500">Showing {{ $showingFrom }} to {{ $showingTo }} of {{ $totalListings }} results</span>
                @if ($hasPagination)
                    <nav class="flex items-center gap-2" aria-label="Boarding houses pagination">
                        <a
                            href="{{ $houses->previousPageUrl() ?: '#' }}"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 {{ $houses->onFirstPage() ? 'pointer-events-none opacity-50' : '' }}"
                            aria-label="Previous page"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/>
                            </svg>
                        </a>

                        @foreach ($houses->getUrlRange(1, $houses->lastPage()) as $page => $url)
                            <a
                                href="{{ $url }}"
                                class="flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-xs font-medium transition {{ $houses->currentPage() === $page ? 'border-blue-600 bg-blue-600 text-white shadow-sm shadow-blue-600/20' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}"
                                @if ($houses->currentPage() === $page) aria-current="page" @endif
                            >
                                {{ $page }}
                            </a>
                        @endforeach

                        <a
                            href="{{ $houses->nextPageUrl() ?: '#' }}"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 {{ $houses->hasMorePages() ? '' : 'pointer-events-none opacity-50' }}"
                            aria-label="Next page"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/>
                            </svg>
                        </a>
                    </nav>
                @endif
            </div>
        </section>

        <div data-modal-skip role="dialog" aria-modal="true" x-show="addOpen" x-cloak x-transition.opacity @click.self="addOpen = false" @keydown.escape.window="addOpen = false" class="bm-modal-overlay" style="display: none;">
            <form method="POST" action="{{ route('admin.listings.store') }}" enctype="multipart/form-data" class="bm-modal bm-modal--xl">
                @csrf
                @if ($isMineView)
                    <input type="hidden" name="return_to_my_boarding_house" value="1">
                @endif
                <div class="bm-modal__header">
                    <div>
                        <p class="bm-modal__eyebrow">Create</p>
                        <h2 class="bm-modal__title">Add Boarding House</h2>
                        <p class="bm-modal__subtitle">Create a complete listing with location, pricing, and photo details in one place.</p>
                    </div>
                    <button type="button" @click="addOpen = false" class="bm-modal__close" aria-label="Close add boarding house modal">x</button>
                </div>
                <div class="bm-modal__body">
                <div class="grid gap-4 md:grid-cols-2">
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
                    <label class="text-sm">Owner / Landlord<input name="landlord_info" class="ui-input mt-1"></label>
                    <label class="text-sm">Contact Person<input name="contact_name" class="ui-input mt-1"></label>
                    <label class="text-sm">Contact Number<input name="contact_phone" class="ui-input mt-1"></label>
                    <label class="text-sm md:col-span-2">Description<textarea name="description" rows="3" class="ui-input mt-1"></textarea></label>
                    <label class="text-sm md:col-span-2">House Rules<textarea name="house_rules" rows="3" class="ui-input mt-1"></textarea></label>
                    <label class="md:col-span-2 flex items-center gap-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" checked> Active listing</label>
                </div>
                <section class="mt-6 rounded-2xl border border-blue-100 bg-blue-50/50 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-950">Location</h3>
                            <p class="mt-1 text-xs text-slate-500">Use DSSC Main Campus in Matti as the distance reference.</p>
                        </div>
                        <button type="button" class="btn-secondary justify-center" data-location-picker="create">Pick Location on Map</button>
                    </div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="text-sm md:col-span-2">Complete Address<input id="create-address" name="address" required class="ui-input mt-1" placeholder="Near DSSC Main Campus, Matti, Digos City"></label>
                        <label class="text-sm">Barangay
                            <input id="create-barangay" name="barangay" list="dssc-location-options" class="ui-input mt-1" placeholder="Matti">
                        </label>
                        <label class="text-sm">Nearby Landmark<input id="create-landmark" name="nearby_landmark" class="ui-input mt-1" value="{{ config('dssc.landmark') }}"></label>
                        <label class="text-sm">Distance from DSSC Main Campus (km)<input id="create-distance" name="distance_from_dssc" type="number" min="0" max="100" step="0.01" class="ui-input mt-1"></label>
                        <label class="text-sm">Location Status
                            <select id="create-location-status" name="location_status" class="ui-input mt-1">
                                <option value="exact">Exact</option>
                                <option value="approximate" selected>Approximate</option>
                            </select>
                        </label>
                        <label class="text-sm">Latitude<input id="create-latitude" name="latitude" type="number" step="0.0000001" min="-90" max="90" class="ui-input mt-1" placeholder="{{ config('dssc.latitude') }}"></label>
                        <label class="text-sm">Longitude<input id="create-longitude" name="longitude" type="number" step="0.0000001" min="-180" max="180" class="ui-input mt-1" placeholder="{{ config('dssc.longitude') }}"></label>
                        <label class="md:col-span-2 flex items-center gap-2 text-sm">
                            <input type="hidden" name="is_near_dssc" value="0">
                            <input id="create-near-dssc" type="checkbox" name="is_near_dssc" value="1">
                            Is this near DSSC?
                        </label>
                    </div>
                    <div id="create-location-map" class="mt-4 hidden h-80 w-full overflow-hidden rounded-xl border border-blue-200 bg-white"></div>
                </section>
                <datalist id="dssc-location-options">
                    @foreach ($locationOptions as $locationOption)
                        <option value="{{ $locationOption }}"></option>
                    @endforeach
                </datalist>
                <section class="mt-6 rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-950">Boarding House Photos</h3>
                            <p class="mt-1 text-xs text-slate-500">Upload up to 10 JPG, PNG, or WEBP photos. Maximum 5 MB each.</p>
                        </div>
                        <label class="btn-secondary cursor-pointer justify-center">
                            Upload Photos
                            <input x-ref="createPhotoInput" type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" class="sr-only" @change="handlePhotoFiles($event, 'create')">
                        </label>
                    </div>
                    <input type="hidden" name="cover_selection" :value="createCoverSelection">
                    <div x-show="createPhotos.length" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <template x-for="(photo, index) in createPhotos" :key="photo.id">
                            <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                                <div class="relative">
                                    <img :src="photo.url" :alt="photo.name" class="h-32 w-full object-cover">
                                    <span x-show="createCoverSelection === `new:${index}`" class="absolute left-2 top-2 rounded-full bg-blue-600 px-2.5 py-1 text-[10px] font-bold text-white">Cover Photo</span>
                                </div>
                                <div class="space-y-2 p-3">
                                    <p class="truncate text-xs font-semibold text-slate-700" x-text="photo.name"></p>
                                    <div class="flex flex-wrap gap-1.5">
                                        <button type="button" class="rounded-lg bg-blue-50 px-2 py-1 text-[11px] font-bold text-blue-700" @click="createCoverSelection = `new:${index}`">Set as Cover</button>
                                        <button type="button" class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] font-bold text-slate-600" @click="moveNewPhoto('create', index, -1)" :disabled="index === 0">Up</button>
                                        <button type="button" class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] font-bold text-slate-600" @click="moveNewPhoto('create', index, 1)" :disabled="index === createPhotos.length - 1">Down</button>
                                        <button type="button" class="rounded-lg bg-rose-50 px-2 py-1 text-[11px] font-bold text-rose-600" @click="removeNewPhoto('create', index)">Remove</button>
                                    </div>
                                </div>
                            </article>
                        </template>
                    </div>
                    <p x-show="!createPhotos.length" class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white px-4 py-6 text-center text-xs text-slate-500">No photos selected. A placeholder will be shown until photos are uploaded.</p>
                    @error('photos')<p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    @error('photos.*')<p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </section>
                </div>
                <div class="bm-modal__footer"><button type="button" @click="addOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button><button class="bm-modal__button bm-modal__button--primary">Save Listing</button></div>
            </form>
        </div>

        <div data-modal-skip role="dialog" aria-modal="true" x-show="viewOpen" x-cloak x-transition.opacity @click.self="viewOpen = false" @keydown.escape.window="viewOpen = false" class="bm-modal-overlay" style="display: none;">
            <div class="bm-modal bm-modal--xl">
                <div class="bm-modal__header">
                    <div>
                        <p class="bm-modal__eyebrow">View</p>
                        <h2 class="bm-modal__title">Boarding House Details</h2>
                        <p class="bm-modal__subtitle">Review listing information, location, amenities, photos, and room inventory.</p>
                    </div>
                    <button type="button" @click="viewOpen = false" class="bm-modal__close" aria-label="Close boarding house details modal">x</button>
                </div>
                <div class="bm-modal__body">
                <div class="grid gap-5 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="space-y-5">
                        <dl class="grid gap-4 text-sm md:grid-cols-2">
                            <div><dt class="ui-muted">Name</dt><dd class="font-semibold" x-text="selected.name"></dd></div>
                            <div><dt class="ui-muted">Monthly Fee</dt><dd x-text="selected.monthly_payment ? `PHP ${Number(selected.monthly_payment).toLocaleString()}` : 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Capacity</dt><dd x-text="selected.capacity || 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Available Rooms</dt><dd x-text="selected.available_rooms ?? 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Listing Status</dt><dd x-text="selected.active_label || (selected.is_active ? 'Active' : 'Inactive')"></dd></div>
                            <div><dt class="ui-muted">Approval</dt><dd x-text="selected.approval_status || 'Pending'"></dd></div>
                            <div class="md:col-span-2"><dt class="ui-muted">Address</dt><dd x-text="selected.full_address || selected.address || 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Barangay</dt><dd x-text="selected.barangay || 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Nearby Landmark</dt><dd x-text="selected.nearby_landmark || 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Distance from DSSC</dt><dd x-text="selected.distance_from_dssc !== null ? `${selected.distance_from_dssc} km` : 'Not set'"></dd></div>
                            <div><dt class="ui-muted">Location Status</dt><dd class="capitalize" x-text="selected.location_status || 'Approximate'"></dd></div>
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
                            <h3 class="text-sm font-semibold">Photos</h3>
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <template x-for="image in selected.images || []" :key="image.id">
                                    <img :src="image.url" :alt="selected.name" class="h-32 w-full rounded-xl border border-slate-200 object-cover">
                                </template>
                                <template x-if="!selected.images || selected.images.length === 0">
                                    <img src="{{ asset('images/boarding-house-placeholder.svg') }}" alt="No Photo Available" class="col-span-2 h-48 w-full rounded-xl border border-slate-200 object-cover">
                                </template>
                            </div>
                        </div>
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
                </div>
                <div class="bm-modal__footer"><button type="button" @click="viewOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button></div>
            </div>
        </div>

        <div data-modal-skip role="dialog" aria-modal="true" x-show="editOpen" x-cloak x-transition.opacity @click.self="editOpen = false" @keydown.escape.window="editOpen = false" class="bm-modal-overlay" style="display: none;">
            <form method="POST" :action="selected.update_url" enctype="multipart/form-data" class="bm-modal bm-modal--xl">
                @csrf @method('PUT')
                @if ($isMineView)
                    <input type="hidden" name="return_to_my_boarding_house" value="1">
                @endif
                <div class="bm-modal__header">
                    <div>
                        <p class="bm-modal__eyebrow">Edit</p>
                        <h2 class="bm-modal__title">Edit Boarding House</h2>
                        <p class="bm-modal__subtitle">Update the listing while preserving all existing backend behavior and file handling.</p>
                    </div>
                    <button type="button" @click="editOpen = false" class="bm-modal__close" aria-label="Close edit boarding house modal">x</button>
                </div>
                <div class="bm-modal__body">
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
                    <label class="text-sm">Owner / Landlord<input name="landlord_info" class="ui-input mt-1" :value="selected.landlord_info || ''"></label>
                    <label class="text-sm">Contact Person<input name="contact_name" class="ui-input mt-1" :value="selected.contact_name || ''"></label>
                    <label class="text-sm">Contact Number<input name="contact_phone" class="ui-input mt-1" :value="selected.contact_phone || ''"></label>
                    <label class="text-sm md:col-span-2">Description<textarea name="description" rows="3" class="ui-input mt-1" x-text="selected.description"></textarea></label>
                    <label class="text-sm md:col-span-2">House Rules<textarea name="house_rules" rows="3" class="ui-input mt-1" x-text="selected.house_rules"></textarea></label>
                    <label class="md:col-span-2 flex items-center gap-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" :checked="selected.is_active"> Active listing</label>
                </div>
                <section class="mt-6 rounded-2xl border border-blue-100 bg-blue-50/50 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-950">Location</h3>
                            <p class="mt-1 text-xs text-slate-500">Selecting a map point recalculates the DSSC distance automatically.</p>
                        </div>
                        <button type="button" class="btn-secondary justify-center" data-location-picker="edit">Pick Location on Map</button>
                    </div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="text-sm md:col-span-2">Complete Address<input id="edit-address" name="address" required class="ui-input mt-1" :value="selected.address"></label>
                        <label class="text-sm">Barangay<input id="edit-barangay" name="barangay" list="dssc-location-options" class="ui-input mt-1" :value="selected.barangay"></label>
                        <label class="text-sm">Nearby Landmark<input id="edit-landmark" name="nearby_landmark" class="ui-input mt-1" :value="selected.nearby_landmark"></label>
                        <label class="text-sm">Distance from DSSC Main Campus (km)<input id="edit-distance" name="distance_from_dssc" type="number" min="0" max="100" step="0.01" class="ui-input mt-1" :value="selected.distance_from_dssc"></label>
                        <label class="text-sm">Location Status
                            <select id="edit-location-status" name="location_status" class="ui-input mt-1" :value="selected.location_status || 'approximate'">
                                <option value="exact">Exact</option>
                                <option value="approximate">Approximate</option>
                            </select>
                        </label>
                        <label class="text-sm">Latitude<input id="edit-latitude" name="latitude" type="number" step="0.0000001" min="-90" max="90" class="ui-input mt-1" :value="selected.latitude"></label>
                        <label class="text-sm">Longitude<input id="edit-longitude" name="longitude" type="number" step="0.0000001" min="-180" max="180" class="ui-input mt-1" :value="selected.longitude"></label>
                        <label class="md:col-span-2 flex items-center gap-2 text-sm">
                            <input type="hidden" name="is_near_dssc" value="0">
                            <input id="edit-near-dssc" type="checkbox" name="is_near_dssc" value="1" :checked="selected.is_near_dssc">
                            Is this near DSSC?
                        </label>
                    </div>
                    <div id="edit-location-map" class="mt-4 hidden h-80 w-full overflow-hidden rounded-xl border border-blue-200 bg-white"></div>
                </section>
                <section class="mt-6 rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-950">Boarding House Photos</h3>
                            <p class="mt-1 text-xs text-slate-500">Set a cover, reorder photos, remove old images, or upload replacements.</p>
                        </div>
                        <label class="btn-secondary cursor-pointer justify-center">
                            Upload Photos
                            <input x-ref="editPhotoInput" type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" class="sr-only" @change="handlePhotoFiles($event, 'edit')">
                        </label>
                    </div>

                    <input type="hidden" name="cover_selection" :value="editCoverSelection">
                    <template x-for="image in (selected.images || []).filter(image => image.removed)" :key="`removed-${image.id}`">
                        <input type="hidden" name="remove_image_ids[]" :value="image.id">
                    </template>
                    <template x-for="image in (selected.images || []).filter(image => !image.removed)" :key="`order-${image.id}`">
                        <input type="hidden" name="image_order[]" :value="image.id">
                    </template>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <template x-for="(image, index) in selected.images || []" :key="image.id">
                            <article class="overflow-hidden rounded-xl border bg-white shadow-sm" :class="image.removed ? 'border-rose-200 opacity-60' : 'border-slate-200'">
                                <div class="relative">
                                    <img :src="image.url" :alt="selected.name" class="h-32 w-full object-cover">
                                    <span x-show="!image.removed && editCoverSelection === `existing:${image.id}`" class="absolute left-2 top-2 rounded-full bg-blue-600 px-2.5 py-1 text-[10px] font-bold text-white">Cover Photo</span>
                                    <span x-show="image.removed" class="absolute inset-0 flex items-center justify-center bg-white/75 text-xs font-bold text-rose-700">Will be removed</span>
                                </div>
                                <div class="flex flex-wrap gap-1.5 p-3">
                                    <button x-show="!image.removed" type="button" class="rounded-lg bg-blue-50 px-2 py-1 text-[11px] font-bold text-blue-700" @click="editCoverSelection = `existing:${image.id}`">Set as Cover</button>
                                    <button x-show="!image.removed" type="button" class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] font-bold text-slate-600" @click="moveExistingPhoto(index, -1)" :disabled="index === 0">Up</button>
                                    <button x-show="!image.removed" type="button" class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] font-bold text-slate-600" @click="moveExistingPhoto(index, 1)" :disabled="index === selected.images.length - 1">Down</button>
                                    <button type="button" class="rounded-lg px-2 py-1 text-[11px] font-bold" :class="image.removed ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-600'" @click="toggleExistingPhoto(image)" x-text="image.removed ? 'Undo' : 'Remove'"></button>
                                </div>
                            </article>
                        </template>

                        <template x-for="(photo, index) in editPhotos" :key="photo.id">
                            <article class="overflow-hidden rounded-xl border border-blue-200 bg-white shadow-sm">
                                <div class="relative">
                                    <img :src="photo.url" :alt="photo.name" class="h-32 w-full object-cover">
                                    <span class="absolute right-2 top-2 rounded-full bg-white/90 px-2 py-1 text-[10px] font-bold text-blue-700">New</span>
                                    <span x-show="editCoverSelection === `new:${index}`" class="absolute left-2 top-2 rounded-full bg-blue-600 px-2.5 py-1 text-[10px] font-bold text-white">Cover Photo</span>
                                </div>
                                <div class="space-y-2 p-3">
                                    <p class="truncate text-xs font-semibold text-slate-700" x-text="photo.name"></p>
                                    <div class="flex flex-wrap gap-1.5">
                                        <button type="button" class="rounded-lg bg-blue-50 px-2 py-1 text-[11px] font-bold text-blue-700" @click="editCoverSelection = `new:${index}`">Set as Cover</button>
                                        <button type="button" class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] font-bold text-slate-600" @click="moveNewPhoto('edit', index, -1)" :disabled="index === 0">Up</button>
                                        <button type="button" class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] font-bold text-slate-600" @click="moveNewPhoto('edit', index, 1)" :disabled="index === editPhotos.length - 1">Down</button>
                                        <button type="button" class="rounded-lg bg-rose-50 px-2 py-1 text-[11px] font-bold text-rose-600" @click="removeNewPhoto('edit', index)">Remove</button>
                                    </div>
                                </div>
                            </article>
                        </template>
                    </div>
                    <p x-show="(!selected.images || selected.images.every(image => image.removed)) && !editPhotos.length" class="mt-4 rounded-xl border border-dashed border-amber-300 bg-amber-50 px-4 py-4 text-center text-xs font-semibold text-amber-800">This listing will use the “No Photo Available” placeholder. Upload a real photo to improve visibility.</p>
                    @error('photos')<p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    @error('photos.*')<p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </section>
                </div>
                <div class="bm-modal__footer"><button type="button" @click="editOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button><button class="bm-modal__button bm-modal__button--primary">Save Changes</button></div>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let detailMap = null;
            let detailMarker = null;
            const dssc = {
                lat: {{ (float) config('dssc.latitude') }},
                lng: {{ (float) config('dssc.longitude') }},
                name: @js(config('dssc.landmark')),
            };
            const pickerMaps = {};

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

            const pickerFields = (mode) => ({
                address: document.getElementById(`${mode}-address`),
                barangay: document.getElementById(`${mode}-barangay`),
                landmark: document.getElementById(`${mode}-landmark`),
                distance: document.getElementById(`${mode}-distance`),
                latitude: document.getElementById(`${mode}-latitude`),
                longitude: document.getElementById(`${mode}-longitude`),
                nearDssc: document.getElementById(`${mode}-near-dssc`),
                locationStatus: document.getElementById(`${mode}-location-status`),
                map: document.getElementById(`${mode}-location-map`),
            });

            const distanceFromDssc = (lat, lng) => {
                const earthRadius = 6371;
                const toRadians = (value) => value * Math.PI / 180;
                const latDelta = toRadians(lat - dssc.lat);
                const lngDelta = toRadians(lng - dssc.lng);
                const startLat = toRadians(dssc.lat);
                const endLat = toRadians(lat);
                const angle = 2 * Math.asin(Math.sqrt(
                    Math.sin(latDelta / 2) ** 2
                    + Math.cos(startLat) * Math.cos(endLat) * Math.sin(lngDelta / 2) ** 2
                ));

                return earthRadius * angle;
            };

            const updateLocationFields = (mode, lat, lng, markExact = false) => {
                const fields = pickerFields(mode);
                const distance = distanceFromDssc(lat, lng);

                fields.latitude.value = Number(lat).toFixed(7);
                fields.longitude.value = Number(lng).toFixed(7);
                fields.distance.value = distance.toFixed(2);
                fields.nearDssc.checked = distance <= 5;
                if (fields.nearDssc.checked && !fields.landmark.value.trim()) {
                    fields.landmark.value = dssc.name;
                }
                if (markExact) {
                    fields.locationStatus.value = 'exact';
                }
            };

            const reverseGeocode = async (mode, lat, lng) => {
                const fields = pickerFields(mode);
                if (fields.address.value.trim()) return;

                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`, {
                        headers: { Accept: 'application/json' },
                    });
                    if (!response.ok) return;
                    const result = await response.json();
                    fields.address.value = result.display_name || fields.address.value;
                    fields.barangay.value = result.address?.village
                        || result.address?.suburb
                        || result.address?.quarter
                        || fields.barangay.value;
                } catch (error) {
                    // Coordinates and distance remain usable when reverse geocoding is unavailable.
                }
            };

            const placePickerMarker = (mode, lat, lng, reverseAddress = false, markExact = true) => {
                const picker = pickerMaps[mode];
                if (!picker) return;

                if (!picker.marker) {
                    picker.marker = L.marker([lat, lng], { draggable: true }).addTo(picker.map);
                    picker.marker.on('dragend', (event) => {
                        const point = event.target.getLatLng();
                        updateLocationFields(mode, point.lat, point.lng, true);
                        reverseGeocode(mode, point.lat, point.lng);
                    });
                } else {
                    picker.marker.setLatLng([lat, lng]);
                }

                picker.marker.bindPopup('<strong>Boarding house location</strong><br>Drag or click the map to adjust.').openPopup();
                updateLocationFields(mode, lat, lng, markExact);
                if (reverseAddress) reverseGeocode(mode, lat, lng);
            };

            const openLocationPicker = (mode) => {
                const fields = pickerFields(mode);
                if (!fields.map || !window.L) return;

                fields.map.classList.remove('hidden');
                const fieldLat = Number(fields.latitude.value);
                const fieldLng = Number(fields.longitude.value);
                const startLat = Number.isFinite(fieldLat) && fields.latitude.value !== '' ? fieldLat : dssc.lat;
                const startLng = Number.isFinite(fieldLng) && fields.longitude.value !== '' ? fieldLng : dssc.lng;

                if (!pickerMaps[mode]) {
                    const map = L.map(fields.map).setView([startLat, startLng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors',
                    }).addTo(map);
                    L.circle([dssc.lat, dssc.lng], {
                        radius: 5000,
                        color: '#2563eb',
                        fillColor: '#60a5fa',
                        fillOpacity: 0.07,
                        weight: 2,
                    }).addTo(map);
                    L.circleMarker([dssc.lat, dssc.lng], {
                        radius: 9,
                        color: '#991b1b',
                        fillColor: '#dc2626',
                        fillOpacity: 1,
                        weight: 3,
                    }).addTo(map).bindPopup(`<strong>${escapeHtml(dssc.name)}</strong><br>Matti, Digos City`);
                    map.on('click', (event) => {
                        placePickerMarker(mode, event.latlng.lat, event.latlng.lng, true);
                    });
                    pickerMaps[mode] = { map, marker: null };
                }

                placePickerMarker(mode, startLat, startLng, false, false);
                window.setTimeout(() => pickerMaps[mode].map.invalidateSize(), 100);
            };

            document.querySelectorAll('[data-location-picker]').forEach((button) => {
                button.addEventListener('click', () => openLocationPicker(button.dataset.locationPicker));
            });

            ['create', 'edit'].forEach((mode) => {
                const fields = pickerFields(mode);
                [fields.latitude, fields.longitude].forEach((input) => input?.addEventListener('change', () => {
                    const lat = Number(fields.latitude.value);
                    const lng = Number(fields.longitude.value);
                    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
                    updateLocationFields(mode, lat, lng);
                    if (pickerMaps[mode]) placePickerMarker(mode, lat, lng);
                }));
            });

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
