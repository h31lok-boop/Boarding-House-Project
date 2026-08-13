<x-layouts.dashboard>
<x-admin.shell :show-header="false">
    @php
        $workspace = request()->routeIs('owner.*') ? 'owner' : 'admin';
        $route = fn (string $name, $params = []) => route($workspace.'.'.$name, $params);
        $isMineView = request('owner') === 'mine';
        $pageTitle = $isMineView ? 'My Properties' : 'Boarding Houses';
        $pageSubtitle = $isMineView
            ? 'Manage your boarding houses, rooms, tenants, and property details.'
            : '';
        $sharedRouteParams = array_filter([
            'owner' => request('owner'),
        ]);
        $createListingUrl = $route('boarding-houses.create', $sharedRouteParams);
        $resetFiltersUrl = $route('boarding-houses', $sharedRouteParams);

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

        $listingRows = $houseCollection->map(function ($house) use ($statusLabel, $statusBadge, $occupancyTone, $isMineView, $route) {
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
                'update_url' => $route('listings.update', $house),
                'destroy_url' => $route('listings.destroy', array_filter([
                    'boarding_house' => $house,
                    'return_to_my_boarding_house' => $isMineView ? 1 : null,
                ])),
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
                'destroy_url' => $route('listings.destroy', array_filter([
                    'boarding_house' => $house,
                    'return_to_my_boarding_house' => $isMineView ? 1 : null,
                ])),
            ];
        })->values();
    @endphp

    <div
        x-data="{
            addOpen: @js(request()->routeIs($workspace.'.boarding-houses.create')),
            viewOpen: false,
            editOpen: false,
            confirmOpen: false,
            confirmAction: { url: '', title: '', message: '', label: '' },
            askConfirm(action) { this.confirmAction = action; this.confirmOpen = true; },
            selected: {},
            createPhotos: [],
            editPhotos: [],
            createCoverSelection: '',
            editCoverSelection: '',
            viewPhotoCursor: 0,
            editPhotoCursor: 0,
            photoStore() {
                window.boardingHousePhotoFiles ??= { create: new Map(), edit: new Map() };
                return window.boardingHousePhotoFiles;
            },
            showDetails(house) {
                if (@js($isMineView)) {
                    this.editDetails(house);
                    return;
                }

                this.selected = house;
                this.viewPhotoCursor = 0;
                this.viewOpen = true;
                this.$nextTick(() => window.dispatchEvent(new CustomEvent('boarding-house-map:show', { detail: house })));
            },
            moveViewPhoto(direction) {
                const count = (this.selected.images || []).length;
                if (count < 2) return;
                this.viewPhotoCursor = (this.viewPhotoCursor + direction + count) % count;
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
                this.editPhotoCursor = 0;
                this.editOpen = true;
                this.$nextTick(() => window.dispatchEvent(new CustomEvent('boarding-house-map:edit')));
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
                    if (photos.length) this.editPhotoCursor = existingCount;
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
                    const existingFallback = mode === 'edit'
                        ? (this.selected.images || []).find(image => !image.removed)
                        : null;
                    const fallback = photos.length
                        ? 'new:0'
                        : (existingFallback ? `existing:${existingFallback.id}` : '');
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
                if (mode === 'edit') this.editPhotoCursor = Math.max(0, Math.min(this.editPhotoCursor, this.editorPhotos().length - 1));
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
                this.editPhotoCursor = Math.max(0, Math.min(this.editPhotoCursor, this.editorPhotos().length - 1));
            },
            editorPhotos() {
                const existing = (this.selected.images || [])
                    .map((image, sourceIndex) => ({
                        kind: 'existing',
                        uid: `existing-${image.id}`,
                        key: `existing:${image.id}`,
                        sourceIndex,
                        url: image.url,
                        image,
                    }))
                    .filter(photo => !photo.image.removed);
                const added = this.editPhotos.map((photo, sourceIndex) => ({
                    kind: 'new',
                    uid: `new-${photo.id}`,
                    key: `new:${sourceIndex}`,
                    sourceIndex,
                    url: photo.url,
                    name: photo.name,
                    photo,
                }));

                return [...existing, ...added];
            },
            currentEditorPhoto() {
                const photos = this.editorPhotos();
                return photos[Math.max(0, Math.min(this.editPhotoCursor, photos.length - 1))] || null;
            },
            showEditorPhoto(direction) {
                const count = this.editorPhotos().length;
                if (count < 2) return;
                this.editPhotoCursor = (this.editPhotoCursor + direction + count) % count;
            },
            makeCurrentEditorPhotoCover() {
                const current = this.currentEditorPhoto();
                if (current) this.editCoverSelection = current.key;
            },
            moveCurrentEditorPhoto(direction) {
                const current = this.currentEditorPhoto();
                if (!current) return;
                if (current.kind === 'existing') this.moveExistingPhoto(current.sourceIndex, direction);
                else this.moveNewPhoto('edit', current.sourceIndex, direction);
                this.$nextTick(() => {
                    const nextIndex = this.editorPhotos().findIndex(photo => photo.uid === current.uid);
                    if (nextIndex >= 0) this.editPhotoCursor = nextIndex;
                });
            },
            removeCurrentEditorPhoto() {
                const current = this.currentEditorPhoto();
                if (!current) return;
                if (current.kind === 'existing') this.toggleExistingPhoto(current.image);
                else this.removeNewPhoto('edit', current.sourceIndex);
                this.$nextTick(() => {
                    this.editPhotoCursor = Math.max(0, Math.min(this.editPhotoCursor, this.editorPhotos().length - 1));
                });
            },
            restoreRemovedEditorPhotos() {
                (this.selected.images || []).forEach(image => { image.removed = false; });
                this.editPhotoCursor = 0;
            }
        }"
        class="space-y-5 text-slate-950"
    >
        <section class="overflow-hidden rounded-[1.6rem] border border-slate-200/80 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
            <div class="border-b border-slate-200/80 bg-[radial-gradient(circle_at_top_left,_rgba(37,99,235,0.08),_transparent_30%),linear-gradient(180deg,#ffffff_0%,#f8fafc_100%)] px-5 py-5 dark:bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.16),_transparent_34%),linear-gradient(180deg,#0f172a_0%,#111c30_100%)] sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
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

        {{-- Stat Cards --}}
        @php
            $availableRoomsTotal = $houseCollection->sum('available_rooms');
            $pendingReservations = $houseCollection->sum(fn ($h) => (int) ($h['payload']['reservations_count'] ?? 0));
            $pendingInquiries = $houseCollection->sum(fn ($h) => (int) ($h['payload']['inquiries_count'] ?? 0));
            $totalTenants = $houseCollection->sum('occupied_count');
            if ($isMineView) {
                $bhStats = [
                    ['label' => 'Properties', 'value' => $totalBoardingHouses, 'tone' => 'blue', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['label' => 'Total Rooms', 'value' => $totalRooms, 'tone' => 'slate', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                    ['label' => 'Active Tenants', 'value' => $totalTenants, 'tone' => 'emerald', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'],
                    ['label' => 'Available Rooms', 'value' => $availableRoomsTotal, 'tone' => 'blue', 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'Occupancy Rate', 'value' => $occupancyRate.'%', 'tone' => $occupancyRate >= 70 ? 'emerald' : ($occupancyRate >= 45 ? 'amber' : 'blue'), 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ];
            } else {
                $bhStats = [
                    ['label' => 'Boarding Houses', 'value' => $totalBoardingHouses, 'tone' => 'blue', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['label' => 'Total Rooms', 'value' => $totalRooms, 'tone' => 'slate', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                    ['label' => 'Occupied', 'value' => $occupiedRooms, 'tone' => 'amber', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['label' => 'Available', 'value' => $availableRoomsTotal, 'tone' => 'emerald', 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'Occupancy Rate', 'value' => $occupancyRate.'%', 'tone' => $occupancyRate >= 70 ? 'emerald' : ($occupancyRate >= 45 ? 'amber' : 'blue'), 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ];
            }
            $scColor = fn($t) => match($t) {
                'amber'   => 'bg-amber-50 text-amber-600',
                'emerald' => 'bg-emerald-50 text-emerald-600',
                'slate'   => 'bg-slate-100 text-slate-600',
                default   => 'bg-blue-50 text-blue-600',
            };
        @endphp
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($bhStats as $sc)
                <div class="flex items-center gap-3 rounded-[1.1rem] border border-slate-200/80 bg-white px-4 py-3.5 shadow-[0_4px_12px_rgba(15,23,42,0.04)]">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $scColor($sc['tone']) }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $sc['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $sc['label'] }}</p>
                        <p class="text-[1.3rem] font-black tracking-tight text-slate-950">{{ $sc['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($isMineView)
            <section class="overflow-hidden rounded-[1.1rem] border border-slate-200/80 bg-white shadow-[0_4px_12px_rgba(15,23,42,0.04)]">
                <div class="border-b border-slate-200/80 bg-gradient-to-r from-blue-50/50 to-transparent px-5 py-4">
                    <h2 class="text-sm font-semibold tracking-[-0.01em] text-slate-950">Quick Actions</h2>
                </div>
                <div class="grid grid-cols-2 gap-3 px-5 py-4 sm:grid-cols-4">
                    <a href="{{ $createListingUrl }}" class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 transition group-hover:bg-blue-100">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <span>Add Property</span>
                    </a>
                    <a href="{{ $route('rooms', $isMineView && $listingRows->count() === 1 ? ($propRouteParams ?? $sharedRouteParams) : $sharedRouteParams) }}" class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-100">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <span>Manage Rooms</span>
                    </a>
                    <a href="{{ $route('reservations', $isMineView && $listingRows->count() === 1 ? ($propRouteParams ?? $sharedRouteParams) : $sharedRouteParams) }}" class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-md">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600 transition group-hover:bg-amber-100">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <span>Reservations</span>
                    </a>
                    <a href="{{ $route('payments', $isMineView && $listingRows->count() === 1 ? ($propRouteParams ?? $sharedRouteParams) : $sharedRouteParams) }}" class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:-translate-y-0.5 hover:border-purple-200 hover:shadow-md">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-50 text-purple-600 transition group-hover:bg-purple-100">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span>Payments</span>
                    </a>
                </div>
            </section>
        @endif

        @if ($isMineView && $listingRows->count() === 1)
            @php
                $property = $listingRows->first();
                $bhId = $property['payload']['id'] ?? null;
                $propRouteParams = array_filter(array_merge($sharedRouteParams, $bhId ? ['boarding_house_id' => $bhId] : []));
                $googleMapsUrl = $property['payload']['google_maps_url'] ?? null;
            @endphp
            <section class="overflow-hidden rounded-[1.45rem] border border-slate-200/80 bg-white shadow-[0_12px_30px_rgba(15,23,42,0.05)]">
                <div class="border-b border-slate-200/80 bg-gradient-to-r from-blue-50/50 to-transparent px-5 py-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="text-base font-semibold tracking-[-0.02em] text-slate-950">{{ $property['name'] }}</h2>
                            <p class="mt-0.5 text-[13px] text-slate-500">{{ $property['full_location'] }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-medium {{ $property['status_classes'] }}">
                                {{ $property['visible_status'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 px-5 py-5 sm:grid-cols-4">
                    <a href="{{ $route('rooms', $propRouteParams) }}" class="group rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3.5 transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Total Rooms</p>
                        <p class="mt-1 text-xl font-bold text-slate-950">{{ $property['total_count'] }}</p>
                    </a>
                    <a href="{{ $route('rooms', array_merge($propRouteParams, ['status' => 'available'])) }}" class="group rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3.5 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Available</p>
                        <p class="mt-1 text-xl font-bold text-emerald-600">{{ $property['available_rooms'] }}</p>
                    </a>
                    <a href="{{ $route('rooms', array_merge($propRouteParams, ['status' => 'occupied'])) }}" class="group rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3.5 transition hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-md">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Occupied</p>
                        <p class="mt-1 text-xl font-bold text-slate-950">{{ $property['occupied_count'] }}</p>
                    </a>
                    <div class="rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3.5">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Occupancy</p>
                        <p class="mt-1 text-xl font-bold {{ $property['percent_class'] }}">{{ $property['occupancy_pct'] }}%</p>
                    </div>
                </div>

                @php
                    $amenities = $property['payload']['amenities'] ?? [];
                    $hasCoords = ($property['payload']['latitude'] ?? null) && ($property['payload']['longitude'] ?? null);
                @endphp
                @if (!empty($amenities))
                <div class="border-t border-slate-200/80 px-5 py-3">
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($amenities as $amenity)
                            <span class="inline-flex rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-medium text-slate-600">{{ $amenity }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="border-t border-slate-200/80 px-5 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ $route('rooms', $propRouteParams) }}" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 text-xs font-medium text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                Rooms
                            </a>
                            <a href="{{ $route('reservations', $propRouteParams) }}" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 text-xs font-medium text-slate-700 transition hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Reservations
                            </a>
                            <a href="{{ $route('payments', $propRouteParams) }}" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 text-xs font-medium text-slate-700 transition hover:border-purple-200 hover:bg-purple-50 hover:text-purple-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Payments
                            </a>
                            <a href="{{ $route('tenants.index', $sharedRouteParams) }}" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 text-xs font-medium text-slate-700 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                                Tenants
                            </a>
                            @if ($googleMapsUrl)
                            <a href="{{ $googleMapsUrl }}" target="_blank" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 text-xs font-medium text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5" stroke-width="1.7"/></svg>
                                View on Map
                            </a>
                            @endif
                        </div>
                        <p class="text-xs font-medium text-slate-500">{{ $property['photo_note'] }}</p>
                    </div>
                </div>
            </section>
        @else
        <section class="overflow-hidden rounded-[1.45rem] border border-slate-200/80 bg-white shadow-[0_12px_30px_rgba(15,23,42,0.05)]">
            <div class="border-b border-slate-200/80 px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-base font-semibold tracking-[-0.02em] text-slate-950">{{ $isMineView ? 'Your Properties' : 'Listings' }}</h2>
                    <span class="hidden rounded-full bg-slate-100 px-3 py-1 text-[11px] font-medium text-slate-600 sm:inline-flex">{{ number_format($listingRows->count()) }} on this page</span>
                </div>
            </div>

            @if ($listingRows->isEmpty())
                <div class="px-5 py-16">
                    <div class="mx-auto max-w-sm text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                            @include('components.sidebar.partials.admin-icon', ['name' => 'boarding-house'])
                        </div>
                        <p class="mt-4 text-base font-semibold tracking-[-0.02em] text-slate-900">{{ $isMineView ? 'You haven\'t added a property yet' : 'No boarding houses yet' }}</p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">{{ $isMineView ? 'Add your boarding house to start managing rooms and tenants.' : 'Add your first boarding house to start managing rooms and availability.' }}</p>
                        <a href="{{ $createListingUrl }}" class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-medium text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">
                            {{ $isMineView ? 'Add Your Property' : 'Add Boarding House' }}
                        </a>
                    </div>
                </div>
            @else
                <div class="divide-y divide-slate-200 lg:hidden">
                    @foreach ($listingRows as $listing)
                        <article
                            class="cursor-pointer space-y-4 px-5 py-5 transition hover:bg-blue-50/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500"
                            role="button"
                            tabindex="0"
                            @click="showDetails({{ \Illuminate\Support\Js::from($listing['payload']) }})"
                            @keydown.enter.prevent="showDetails({{ \Illuminate\Support\Js::from($listing['payload']) }})"
                            @keydown.space.prevent="showDetails({{ \Illuminate\Support\Js::from($listing['payload']) }})"
                        >
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

                            <div class="hidden" aria-hidden="true">
                                <button type="button" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 transition hover:bg-slate-50" @click="showDetails({{ \Illuminate\Support\Js::from($listing['payload']) }})">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                    </svg>
                                </button>
                                <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" title="Edit" @click="editDetails({{ \Illuminate\Support\Js::from($listing['payload']) }})">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16.862 4.487a2.1 2.1 0 0 1 2.97 2.97L8.416 18.873l-4.5.5.5-4.5 12.446-10.386Z"/>
                                    </svg>
                                </button>
                                <button type="button"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-600 transition hover:-translate-y-0.5 hover:bg-rose-100"
                                    title="Delete"
                                    @click="askConfirm({ url: '{{ $listing['destroy_url'] }}', title: 'Delete this boarding house?', message: 'This will permanently remove {{ addslashes($listing['name']) }}. This cannot be undone.', label: 'Yes, Delete' })">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="hidden overflow-x-auto lg:block">
                    <table class="min-w-[1120px] w-full text-sm">
                        <thead class="bg-slate-50/80 text-[11px] uppercase tracking-[0.16em] text-slate-500">
                            <tr>
                                <th class="px-5 py-3.5 text-left font-medium">Boarding House</th>
                                @unless ($isMineView)
                                <th class="px-5 py-3.5 text-left font-medium">Owner</th>
                                @endunless
                                <th class="px-5 py-3.5 text-left font-medium">Location</th>
                                <th class="px-5 py-3.5 text-left font-medium">Rooms</th>
                                <th class="px-5 py-3.5 text-left font-medium">Occupancy</th>
                                <th class="px-5 py-3.5 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($listingRows as $listing)
                                <tr
                                    class="cursor-pointer align-top transition hover:bg-blue-50/40 focus:outline-none focus-visible:bg-blue-50/60"
                                    role="button"
                                    tabindex="0"
                                    @click="showDetails({{ \Illuminate\Support\Js::from($listing['payload']) }})"
                                    @keydown.enter.prevent="showDetails({{ \Illuminate\Support\Js::from($listing['payload']) }})"
                                    @keydown.space.prevent="showDetails({{ \Illuminate\Support\Js::from($listing['payload']) }})"
                                >
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
                                    @unless ($isMineView)
                                    <td class="px-5 py-4.5">
                                        <div class="space-y-1 text-[13px]">
                                            <p class="font-medium text-slate-950">{{ $listing['payload']['owner_name'] ?? 'N/A' }}</p>
                                            <p class="text-slate-500">{{ $listing['payload']['owner_email'] ?? '' }}</p>
                                        </div>
                                    </td>
                                    @endunless
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
                                    <td class="hidden">
                                        <div class="hidden" aria-hidden="true">
                                            <button type="button" title="View" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" @click="showDetails({{ \Illuminate\Support\Js::from($listing['payload']) }})">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                                </svg>
                                            </button>
                                            <button type="button" title="Edit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" @click="editDetails({{ \Illuminate\Support\Js::from($listing['payload']) }})">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16.862 4.487a2.1 2.1 0 0 1 2.97 2.97L8.416 18.873l-4.5.5.5-4.5 12.446-10.386Z"/>
                                                </svg>
                                            </button>
                                            <button type="button" title="Delete"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-600 transition hover:-translate-y-0.5 hover:bg-rose-100"
                                                @click="askConfirm({ url: '{{ $listing['destroy_url'] }}', title: 'Delete this boarding house?', message: 'This will permanently remove {{ addslashes($listing['name']) }}. This cannot be undone.', label: 'Yes, Delete' })">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
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
        @endif

        <template x-teleport="body">
        <div data-modal-root role="dialog" aria-modal="true" x-show="addOpen" x-cloak x-transition.opacity @keydown.escape.window="addOpen = false" class="bm-modal-overlay" style="display: none;">
            <form method="POST" action="{{ $route('listings.store') }}" enctype="multipart/form-data" class="bm-modal bm-modal--xl">
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
                            <p class="mt-1 text-xs text-slate-500">Upload up to 10 JPG, PNG, or WEBP photos. The first photo becomes the listing background.</p>
                        </div>
                        <label class="btn-secondary cursor-pointer justify-center">
                            Upload Photos
                            <input x-ref="createPhotoInput" type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" class="sr-only" @change="handlePhotoFiles($event, 'create')">
                        </label>
                    </div>
                    <div x-show="createPhotos.length" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <template x-for="(photo, index) in createPhotos" :key="photo.id">
                            <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                                <div class="relative">
                                    <img :src="photo.url" alt="New property photo" class="h-32 w-full object-cover" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}'">
                                </div>
                                <div class="space-y-2 p-3">
                                    <p class="truncate text-xs font-semibold text-slate-700" x-text="photo.name"></p>
                                    <div class="flex flex-wrap gap-1.5">
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
        </template>

        @unless ($isMineView)
        <template x-teleport="body">
        <div data-modal-root role="dialog" aria-modal="true" x-show="viewOpen" x-cloak x-transition.opacity @keydown.escape.window="viewOpen = false" class="bm-modal-overlay" style="display: none;">
            <div class="bm-modal bm-modal--xl">
                <div class="bm-modal__header">
                    <div>
                        <p class="bm-modal__eyebrow">View</p>
                        <h2 class="bm-modal__title">Boarding House Details</h2>
                        <p class="bm-modal__subtitle">A simplified view of the verified property record.</p>
                    </div>
                    <button type="button" @click="viewOpen = false" class="bm-modal__close" aria-label="Close boarding house details modal">x</button>
                </div>
                <div class="bm-modal__body">
                    <div class="grid gap-5 lg:grid-cols-[1fr_0.95fr]">
                        <div class="space-y-4">
                            <section data-admin-property-photo-carousel class="relative overflow-hidden rounded-2xl border ui-border bg-slate-100 dark:bg-slate-900">
                                <template x-if="selected.images && selected.images.length">
                                    <img :src="selected.images[viewPhotoCursor]?.url" alt="Boarding house photo" class="h-72 w-full object-cover" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}'">
                                </template>
                                <template x-if="!selected.images || !selected.images.length">
                                    <img src="{{ asset('images/boarding-house-placeholder.svg') }}" alt="Boarding house photo unavailable" class="h-72 w-full object-cover">
                                </template>
                                <template x-if="(selected.images || []).length > 1">
                                    <div>
                                        <button type="button" @click="moveViewPhoto(-1)" class="absolute left-3 top-1/2 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-slate-950/70 text-white" aria-label="Previous property photo"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg></button>
                                        <button type="button" @click="moveViewPhoto(1)" class="absolute right-3 top-1/2 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-slate-950/70 text-white" aria-label="Next property photo"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg></button>
                                        <span class="absolute bottom-3 right-3 rounded-full bg-slate-950/75 px-3 py-1 text-xs font-bold text-white" x-text="`${viewPhotoCursor + 1} / ${selected.images.length}`"></span>
                                    </div>
                                </template>
                            </section>

                            <div>
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-xl font-black" x-text="selected.name"></h3>
                                        <p class="mt-1 text-sm ui-muted" x-text="selected.full_address || selected.address || 'Location not set'"></p>
                                    </div>
                                    <span class="rounded-full border border-emerald-300/30 bg-emerald-400/10 px-3 py-1 text-xs font-bold capitalize text-emerald-600 dark:text-emerald-300" x-text="selected.active_label || selected.approval_status || 'Pending'"></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                <div class="rounded-xl border ui-border p-3"><p class="text-[10px] font-bold uppercase ui-muted">Monthly fee</p><p class="mt-1 font-black" x-text="selected.monthly_payment ? `PHP ${Number(selected.monthly_payment).toLocaleString()}` : 'Not set'"></p></div>
                                <div class="rounded-xl border ui-border p-3"><p class="text-[10px] font-bold uppercase ui-muted">Available</p><p class="mt-1 font-black" x-text="selected.available_rooms ?? 0"></p></div>
                                <div class="rounded-xl border ui-border p-3"><p class="text-[10px] font-bold uppercase ui-muted">Capacity</p><p class="mt-1 font-black" x-text="selected.capacity || 'Not set'"></p></div>
                                <div class="rounded-xl border ui-border p-3"><p class="text-[10px] font-bold uppercase ui-muted">Reservations</p><p class="mt-1 font-black" x-text="selected.reservations_count ?? 0"></p></div>
                            </div>

                            <div class="rounded-xl border ui-border p-4 text-sm">
                                <p class="font-bold">About this property</p>
                                <p class="mt-2 leading-6 ui-muted" x-text="selected.description || 'No description provided.'"></p>
                                <p class="mt-4 font-bold">House rules</p>
                                <p class="mt-2 leading-6 ui-muted" x-text="selected.house_rules || 'No house rules provided.'"></p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <section class="rounded-xl border ui-border p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div><h3 class="text-sm font-bold">Property location</h3><p class="mt-1 text-xs ui-muted" x-text="selected.location_label || selected.full_address || 'Location not set'"></p></div>
                                    <a x-show="selected.google_maps_url" :href="selected.google_maps_url" target="_blank" rel="noopener" class="shrink-0 text-xs font-bold text-[color:var(--brand-600)]">Open map</a>
                                </div>
                                <div id="boardingHouseDetailMap" class="mt-3 h-56 w-full overflow-hidden rounded-lg border ui-border"></div>
                                <p id="boardingHouseDetailMapEmpty" class="mt-2 hidden text-sm ui-muted">No geotag coordinates set for this boarding house.</p>
                            </section>

                            <section class="rounded-xl border ui-border p-4">
                                <h3 class="text-sm font-bold">Amenities</h3>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <template x-if="!selected.amenities || !selected.amenities.length"><span class="text-sm ui-muted">No amenities listed.</span></template>
                                    <template x-for="amenity in selected.amenities || []" :key="amenity"><span class="rounded-full border ui-border px-2.5 py-1 text-xs" x-text="amenity"></span></template>
                                </div>
                            </section>

                            <section class="rounded-xl border ui-border p-4">
                                <h3 class="text-sm font-bold">Owner and contact</h3>
                                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                                    <div><dt class="text-xs ui-muted">Owner</dt><dd class="mt-1 font-semibold" x-text="selected.owner_name || 'Not assigned'"></dd></div>
                                    <div><dt class="text-xs ui-muted">Company</dt><dd class="mt-1 font-semibold" x-text="selected.owner_company || selected.landlord_info || 'Not set'"></dd></div>
                                    <div><dt class="text-xs ui-muted">Email</dt><dd class="mt-1 break-all" x-text="selected.owner_email || 'Not set'"></dd></div>
                                    <div><dt class="text-xs ui-muted">Phone</dt><dd class="mt-1" x-text="selected.contact_phone || selected.owner_phone || 'Not set'"></dd></div>
                                </dl>
                            </section>

                            <section class="rounded-xl border ui-border p-4">
                                <h3 class="text-sm font-bold">Room options</h3>
                                <div class="mt-3 divide-y ui-border">
                                    <template x-if="!selected.room_categories || !selected.room_categories.length"><p class="py-2 text-sm ui-muted">No room categories listed.</p></template>
                                    <template x-for="category in selected.room_categories || []" :key="category.name">
                                        <div class="flex items-center justify-between gap-3 py-2 text-sm">
                                            <div><p class="font-semibold" x-text="category.name"></p><p class="text-xs ui-muted" x-text="`${category.available_rooms || 0} of ${category.total_rooms || 0} available`"></p></div>
                                            <p class="shrink-0 font-bold" x-text="category.monthly_rate ? `PHP ${Number(category.monthly_rate).toLocaleString()}` : 'N/A'"></p>
                                        </div>
                                    </template>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
                <div class="bm-modal__footer items-center justify-between">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="editDetails(selected); viewOpen = false" class="bm-modal__button bm-modal__button--primary">Edit</button>
                        <button
                            type="button"
                            @click="askConfirm({ url: selected.destroy_url, title: 'Delete this boarding house?', message: `This will permanently remove ${selected.name}. This cannot be undone.`, label: 'Yes, Delete' }); viewOpen = false"
                            class="bm-modal__button bm-modal__button--danger"
                        >Delete</button>
                    </div>
                    <button type="button" @click="viewOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button>
                </div>
            </div>
        </div>
        </template>
        @endunless

        <template x-teleport="body">
        <div data-modal-root role="dialog" aria-modal="true" x-show="editOpen" x-cloak x-transition.opacity @keydown.escape.window="editOpen = false" @click.self="editOpen = false" class="bm-modal-overlay" style="display: none;">
            <form method="POST" :action="selected.update_url" enctype="multipart/form-data" class="bm-modal bm-modal--property-editor" @if ($isMineView) data-owner-direct-editor @endif>
                @csrf @method('PUT')
                @if ($isMineView)
                    <input type="hidden" name="return_to_my_boarding_house" value="1">
                @endif
                <div class="bm-modal__header">
                    <div>
                        <p class="bm-modal__eyebrow">Property editor</p>
                        <h2 class="bm-modal__title" x-text="selected.name || 'Edit Boarding House'"></h2>
                        <p class="bm-modal__subtitle">Manage the photos and information tenants see on your listing.</p>
                    </div>
                    @unless ($isMineView)
                    <button type="button" @click="editOpen = false" class="bm-modal__close" aria-label="Close edit boarding house modal">x</button>
                    @endunless
                </div>
                <div class="bm-modal__body !p-0">
                    <div class="grid min-w-0 xl:grid-cols-[minmax(0,1.15fr)_minmax(24rem,0.85fr)]">
                        <section data-property-photo-workspace class="min-w-0 border-b border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-950/35 sm:p-5 xl:border-b-0 xl:border-r">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-300">Photo gallery</p>
                                    <h3 class="mt-1 text-base font-bold text-slate-950 dark:text-white">Show tenants the real property</h3>
                                    <p class="mt-1 max-w-xl text-xs leading-5 text-slate-500 dark:text-slate-400">Arrange every photo in the order tenants should see it. The first photo is automatically used as the listing background.</p>
                                </div>
                                <span class="inline-flex shrink-0 self-start rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-bold text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300" x-text="`${(selected.images || []).filter(image => !image.removed).length + editPhotos.length}/10 photos`"></span>
                            </div>

                            <label class="mt-4 flex min-h-32 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-blue-200 bg-blue-50/70 px-5 py-6 text-center transition hover:border-blue-400 hover:bg-blue-50 focus-within:ring-4 focus-within:ring-blue-100 dark:border-blue-400/30 dark:bg-blue-400/5 dark:hover:border-blue-400/60">
                                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/20">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 16.5V18a2.25 2.25 0 0 0 2.25 2.25h13.5A2.25 2.25 0 0 0 21 18v-1.5m-13.5-6L12 6m0 0 4.5 4.5M12 6v10.5"/></svg>
                                </span>
                                <span class="mt-3 text-sm font-bold text-slate-900 dark:text-white">Add property photos</span>
                                <span class="mt-1 text-xs text-slate-500 dark:text-slate-400">JPG, PNG, or WEBP · up to 5 MB each · maximum 10</span>
                                <input x-ref="editPhotoInput" type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" class="sr-only" @change="handlePhotoFiles($event, 'edit')">
                            </label>

                            <template x-for="image in (selected.images || []).filter(image => image.removed)" :key="`removed-${image.id}`">
                                <input type="hidden" name="remove_image_ids[]" :value="image.id">
                            </template>
                            <template x-for="image in (selected.images || []).filter(image => !image.removed)" :key="`order-${image.id}`">
                                <input type="hidden" name="image_order[]" :value="image.id">
                            </template>

                            <div data-property-photo-carousel class="mt-4" x-show="editorPhotos().length">
                                <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 shadow-xl dark:border-slate-700">
                                    <template x-if="currentEditorPhoto()">
                                        <img :src="currentEditorPhoto().url" alt="Property photo" class="h-[28rem] w-full object-cover sm:h-[34rem]" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}'">
                                    </template>
                                    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-36 bg-gradient-to-t from-slate-950 via-slate-950/45 to-transparent"></div>

                                    <button x-show="editorPhotos().length > 1" type="button" @click="showEditorPhoto(-1)" class="absolute left-4 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full border border-white/25 bg-slate-950/65 text-white shadow-xl backdrop-blur transition hover:scale-105 hover:bg-slate-950" aria-label="Previous property photo" title="Previous photo">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
                                    </button>
                                    <button x-show="editorPhotos().length > 1" type="button" @click="showEditorPhoto(1)" class="absolute right-4 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full border border-white/25 bg-slate-950/65 text-white shadow-xl backdrop-blur transition hover:scale-105 hover:bg-slate-950" aria-label="Next property photo" title="Next photo">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
                                    </button>

                                    <div class="absolute inset-x-4 bottom-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                        <div class="text-white">
                                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/65">Gallery position</p>
                                            <p class="mt-1 text-sm font-bold"><span x-text="editPhotoCursor + 1"></span> of <span x-text="editorPhotos().length"></span></p>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <button type="button" @click="moveCurrentEditorPhoto(-1)" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-white/20 bg-white/10 px-3 text-[11px] font-bold text-white backdrop-blur transition hover:bg-white/20" title="Move active photo earlier"><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg> Earlier</button>
                                            <button type="button" @click="moveCurrentEditorPhoto(1)" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-white/20 bg-white/10 px-3 text-[11px] font-bold text-white backdrop-blur transition hover:bg-white/20" title="Move active photo later">Later <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg></button>
                                            <button type="button" @click="removeCurrentEditorPhoto()" class="grid h-9 w-9 place-items-center rounded-xl bg-rose-600 text-white shadow transition hover:bg-rose-700" aria-label="Remove active property photo" title="Remove photo"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 7h12m-9 0V5.75A1.75 1.75 0 0 1 10.75 4h2.5A1.75 1.75 0 0 1 15 5.75V7m-7 0 .7 11.2A2 2 0 0 0 10.7 20h2.6a2 2 0 0 0 2-1.8L16 7"/></svg></button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 flex items-center justify-between gap-3">
                                    <div class="flex max-w-full items-center gap-1.5 overflow-hidden" aria-label="Photo position indicators">
                                        <template x-for="(photo, index) in editorPhotos()" :key="photo.uid">
                                            <button type="button" @click="editPhotoCursor = index" class="h-1.5 rounded-full transition-all" :class="editPhotoCursor === index ? 'w-8 bg-blue-600' : 'w-3 bg-slate-300 hover:bg-slate-400 dark:bg-slate-700'" :aria-label="`Show property photo ${index + 1}`"></button>
                                        </template>
                                    </div>
                                    <button x-show="(selected.images || []).some(image => image.removed)" type="button" @click="restoreRemovedEditorPhotos()" class="shrink-0 text-[11px] font-bold text-emerald-700 hover:text-emerald-800 dark:text-emerald-300">Undo removed photos</button>
                                </div>
                            </div>

                            <div x-show="(!selected.images || selected.images.every(image => image.removed)) && !editPhotos.length" class="mt-4 rounded-2xl border border-dashed border-amber-300 bg-amber-50 px-5 py-8 text-center dark:border-amber-400/30 dark:bg-amber-400/10">
                                <p class="text-sm font-bold text-amber-900 dark:text-amber-200">No property photos selected</p>
                                <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">Add at least one clear exterior or room photo to make the listing trustworthy.</p>
                            </div>
                            @error('photos')<p class="mt-3 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                            @error('photos.*')<p class="mt-3 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </section>

                        <section class="min-w-0 space-y-5 bg-white p-4 dark:bg-slate-900 sm:p-5">
                            <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Listing details</p>
                                    <h3 class="mt-1 text-base font-bold text-slate-950 dark:text-white">Property information</h3>
                                </div>
                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200 md:col-span-2">Property name<input name="name" required class="ui-input mt-1.5" :value="selected.name"></label>
                                    @unless ($isMineView)
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Owner account
                                        <select name="owner_id" class="ui-input mt-1.5" :value="selected.owner_id">
                                            <option value="">Use current admin</option>
                                            @foreach ($owners as $owner)
                                                <option value="{{ $owner->id }}">{{ $owner->name }} - {{ $owner->email }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Approval
                                        <select name="approval_status" class="ui-input mt-1.5" :value="selected.approval_status"><option value="approved">Approved</option><option value="pending">Pending</option><option value="rejected">Rejected</option></select>
                                    </label>
                                    @endunless
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Monthly fee<input name="monthly_payment" type="number" min="0" step="0.01" class="ui-input mt-1.5" :value="selected.monthly_payment"></label>
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Capacity<input name="capacity" type="number" min="1" class="ui-input mt-1.5" :value="selected.capacity"></label>
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Available rooms<input name="available_rooms" type="number" min="0" class="ui-input mt-1.5" :value="selected.available_rooms"></label>
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Owner / landlord<input name="landlord_info" class="ui-input mt-1.5" :value="selected.landlord_info || ''"></label>
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Contact person<input name="contact_name" class="ui-input mt-1.5" :value="selected.contact_name || ''"></label>
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Contact number<input name="contact_phone" class="ui-input mt-1.5" :value="selected.contact_phone || ''"></label>
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200 md:col-span-2">Description<textarea name="description" rows="4" class="ui-input mt-1.5" x-model="selected.description"></textarea></label>
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200 md:col-span-2">House rules<textarea name="house_rules" rows="4" class="ui-input mt-1.5" x-model="selected.house_rules"></textarea></label>
                                    <label class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 md:col-span-2">
                                        <span><span class="block">Visible to tenants</span><span class="mt-0.5 block text-[11px] font-normal text-slate-500 dark:text-slate-400">Turn off to temporarily hide this listing.</span></span>
                                        <span><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" :checked="selected.is_active" class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-blue-100 bg-blue-50/50 p-4 dark:border-blue-400/20 dark:bg-blue-400/5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-300">Location</p>
                                        <h3 class="mt-1 text-base font-bold text-slate-950 dark:text-white">Tag the property on the map</h3>
                                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Click the exact building location to place the marker. Drag the marker anytime to correct it.</p>
                                    </div>
                                    <button type="button" class="btn-secondary shrink-0 justify-center" data-location-picker="edit">Center marker</button>
                                </div>
                                <div class="mt-4 overflow-hidden rounded-2xl border border-blue-200 bg-white shadow-sm dark:border-blue-400/30 dark:bg-slate-900">
                                    <div id="edit-location-map" data-property-location-map class="h-80 w-full sm:h-96"></div>
                                    <div class="flex items-start gap-3 border-t border-blue-100 bg-white px-4 py-3 dark:border-blue-400/20 dark:bg-slate-900">
                                        <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-400/10 dark:text-blue-300"><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5" stroke-width="2"/></svg></span>
                                        <div><p class="text-xs font-bold text-slate-800 dark:text-slate-100">Property marker</p><p class="mt-0.5 text-[11px] leading-5 text-slate-500 dark:text-slate-400">The coordinates and DSSC distance below update automatically from this marker.</p></div>
                                    </div>
                                </div>
                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200 md:col-span-2">Complete address<input id="edit-address" name="address" required class="ui-input mt-1.5" :value="selected.address"></label>
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Barangay<input id="edit-barangay" name="barangay" list="dssc-location-options" class="ui-input mt-1.5" :value="selected.barangay"></label>
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Nearby landmark<input id="edit-landmark" name="nearby_landmark" class="ui-input mt-1.5" :value="selected.nearby_landmark"></label>
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Distance from DSSC (km)<input id="edit-distance" name="distance_from_dssc" type="number" min="0" max="100" step="0.01" class="ui-input mt-1.5" :value="selected.distance_from_dssc"></label>
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Location accuracy
                                        <select id="edit-location-status" name="location_status" class="ui-input mt-1.5" :value="selected.location_status || 'approximate'">
                                            <option value="exact">Exact</option>
                                            <option value="approximate">Approximate</option>
                                        </select>
                                    </label>
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Latitude<input id="edit-latitude" name="latitude" type="number" step="0.0000001" min="-90" max="90" class="ui-input mt-1.5" :value="selected.latitude"></label>
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Longitude<input id="edit-longitude" name="longitude" type="number" step="0.0000001" min="-180" max="180" class="ui-input mt-1.5" :value="selected.longitude"></label>
                                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200 md:col-span-2">
                                        <input type="hidden" name="is_near_dssc" value="0">
                                        <input id="edit-near-dssc" type="checkbox" name="is_near_dssc" value="1" :checked="selected.is_near_dssc" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        Near DSSC Main Campus
                                    </label>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
                @if ($isMineView)
                <div class="bm-modal__footer items-center justify-between gap-4">
                    <p class="hidden text-xs text-slate-500 dark:text-slate-400 sm:block">Saving updates the property information shown to tenants.</p>
                    <button class="bm-modal__button bm-modal__button--primary sm:min-w-36">Save changes</button>
                </div>
                @else
                <div class="bm-modal__footer"><button type="button" @click="editOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button><button class="bm-modal__button bm-modal__button--primary">Save changes</button></div>
                @endif
            </form>
        </div>
        </template>

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
                pickerMaps[mode].map.setView([startLat, startLng], pickerMaps[mode].map.getZoom() || 15);
                window.setTimeout(() => pickerMaps[mode].map.invalidateSize(), 100);
            };

            document.addEventListener('click', (event) => {
                const button = event.target.closest?.('[data-location-picker]');
                if (!button) return;
                openLocationPicker(button.dataset.locationPicker);
            });

            window.addEventListener('boarding-house-map:edit', () => {
                window.setTimeout(() => openLocationPicker('edit'), 120);
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

    {{-- Delete confirmation modal --}}
    <template x-teleport="body">
    <div data-modal-root role="dialog" aria-modal="true" x-show="confirmOpen" x-cloak x-transition
        class="bm-modal-overlay">
        <div class="bm-modal bm-modal--sm">
            <div class="flex items-start gap-3.5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h2 class="text-[15px] font-bold tracking-[-0.01em] text-slate-950" x-text="confirmAction.title"></h2>
                    <p class="mt-1.5 text-[13px] leading-5 text-slate-500" x-text="confirmAction.message"></p>
                </div>
            </div>
            <form method="POST" :action="confirmAction.url" class="mt-5 flex justify-end gap-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="confirmOpen = false" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Go Back</button>
                <button class="inline-flex h-9 items-center justify-center rounded-xl bg-rose-600 px-4 text-sm font-bold text-white shadow-sm shadow-rose-600/20 transition hover:bg-rose-700" x-text="confirmAction.label || 'Delete'"></button>
            </form>
        </div>
    </div>
    </template>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
