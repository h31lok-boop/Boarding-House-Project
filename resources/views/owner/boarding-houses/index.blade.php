<x-layouts.caretaker>
    <x-owner.shell :show-header="false">
        @php
            $r = fn ($name, $params = [], $fallback = '#') => \Illuminate\Support\Facades\Route::has($name)
                ? route($name, $params)
                : $fallback;

            $iconPaths = [
                'bell' => '<path d="M15 17H9m9-5a6 6 0 1 0-12 0c0 3-1.5 4-2 5h16c-.5-1-2-2-2-5Z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
                'question' => '<path d="M9.5 9a2.5 2.5 0 1 1 4 2c-1 .7-1.5 1.2-1.5 2.5"/><path d="M12 17h.01"/><circle cx="12" cy="12" r="9"/>',
                'plus' => '<path d="M12 5v14M5 12h14"/>',
                'home' => '<path d="m3 11.5 9-7.5 9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-5h6v5"/>',
                'check' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.2 2.3 2.3 4.7-5"/>',
                'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
                'document' => '<path d="M7 3h7l4 4v14H7z"/><path d="M14 3v5h5M10 12h4M10 16h6"/>',
                'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
                'grid' => '<path d="M5 5h5v5H5zM14 5h5v5h-5zM5 14h5v5H5zM14 14h5v5h-5z"/>',
                'list' => '<path d="M8 6h12M8 12h12M8 18h12"/><path d="M4 6h.01M4 12h.01M4 18h.01"/>',
                'pencil' => '<path d="m4 20 4.2-1 10-10a2 2 0 0 0-3-3l-10 10L4 20Z"/><path d="m13.5 6.5 4 4"/>',
                'trash' => '<path d="M4 7h16M9 7V5h6v2M7 7l1 13h8l1-13"/><path d="M10 11v5M14 11v5"/>',
                'eye' => '<path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/>',
                'send' => '<path d="m4 12 16-8-5 16-3-7-8-1Z"/><path d="m12 13 8-9"/>',
                'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
                'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
                'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
                'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
                'external' => '<path d="M14 4h6v6"/><path d="m10 14 10-10"/><path d="M20 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h5"/>',
                'upload' => '<path d="M12 16V5"/><path d="m8 9 4-4 4 4"/><path d="M20 16.5a4 4 0 0 0-4-4h-1a6 6 0 0 0-11.3 2A3.5 3.5 0 0 0 5.5 21H18a4 4 0 0 0 2-7.5"/>',
                'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
                'warning' => '<path d="m12 3 10 18H2L12 3Z"/><path d="M12 9v5M12 17h.01"/>',
                'reset' => '<path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v6h6"/>',
            ];

            $icon = fn ($name, $class = 'h-5 w-5') => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8">'.($iconPaths[$name] ?? $iconPaths['info']).'</svg>';

            $amenities = ['Wi-Fi', 'CCTV', 'Laundry Area', 'Study Area', 'Kitchen Access', 'Water Tank'];
            $rules = ['No smoking inside the rooms', 'No overnight visitors', 'Curfew at 10:00 PM', 'Keep the premises clean and quiet'];
            $photoTiles = [
                'from-slate-200 via-blue-100 to-slate-300 text-blue-700',
                'from-stone-100 via-slate-100 to-blue-100 text-blue-700',
                'from-emerald-100 via-slate-100 to-stone-200 text-emerald-700',
                'from-slate-200 via-stone-100 to-slate-400 text-slate-700',
            ];

            $normalizeStatus = function ($status) {
                $status = strtolower(trim((string) $status));

                return match ($status) {
                    'approved', 'active', 'published' => 'Approved',
                    'rejected', 'denied' => 'Rejected',
                    'pending', 'submitted', 'for review', 'under review' => 'Pending',
                    default => 'Draft',
                };
            };

            $fallbackListings = [
                [
                    'id' => 'metronest',
                    'name' => 'MetroNest Boarding Hub',
                    'address' => '123 Rizal Avenue, Davao City, Davao del Sur 8000',
                    'description' => 'A modern and safe boarding house near schools, markets, and public transport.',
                    'contactNumber' => '0917 123 4567',
                    'email' => 'metronest@gmail.com',
                    'status' => 'Draft',
                    'amenities' => $amenities,
                    'rules' => $rules,
                    'rejectionReason' => '',
                ],
                [
                    'id' => 'casa-digos',
                    'name' => 'Casa Digos Boarding Stay',
                    'address' => 'Purok 3, Upper Digos, Digos City, Davao del Sur',
                    'description' => 'A practical boarding stay with access to transport, schools, and daily essentials.',
                    'contactNumber' => '0938 765 4321',
                    'email' => 'casadigos@outlook.com',
                    'status' => 'Pending',
                    'amenities' => $amenities,
                    'rules' => $rules,
                    'rejectionReason' => '',
                ],
                [
                    'id' => 'sunrise',
                    'name' => 'Sunrise Student Boarding House',
                    'address' => '45-B P. Gomez Street, Buhangin, Davao City',
                    'description' => 'A peaceful student boarding house with clean shared facilities and study-friendly spaces.',
                    'contactNumber' => '0921 345 6789',
                    'email' => 'sunriseboarding@gmail.com',
                    'status' => 'Approved',
                    'amenities' => $amenities,
                    'rules' => $rules,
                    'rejectionReason' => '',
                ],
                [
                    'id' => 'green-haven',
                    'name' => 'Green Haven Residences',
                    'address' => 'Lot 8, Block 2, Camella Homes, Cabantian, Davao City',
                    'description' => 'A residential-style boarding house with quiet surroundings and easy access to nearby shops.',
                    'contactNumber' => '0906 543 2109',
                    'email' => 'greenhaven.res@gmail.com',
                    'status' => 'Rejected',
                    'amenities' => $amenities,
                    'rules' => $rules,
                    'rejectionReason' => 'Missing fire safety equipment details and emergency exit plan.',
                ],
                [
                    'id' => 'maple-corner',
                    'name' => 'Maple Corner Boarding House',
                    'address' => '78 Maple Street, Panabo City, Davao del Norte',
                    'description' => 'A compact boarding house option for students and workers near Panabo City center.',
                    'contactNumber' => '0915 222 3344',
                    'email' => 'maplecorner.ph@gmail.com',
                    'status' => 'Draft',
                    'amenities' => $amenities,
                    'rules' => $rules,
                    'rejectionReason' => '',
                ],
            ];

            $sourceHouses = isset($houses)
                ? collect(method_exists($houses, 'items') ? $houses->items() : $houses)
                : collect();

            $dbListings = $sourceHouses->map(function ($house, $index) use ($amenities, $rules, $photoTiles, $normalizeStatus) {
                $status = $normalizeStatus($house->approval_status ?? $house->status ?? 'draft');
                $houseRules = trim((string) ($house->house_rules ?? ''));
                $houseAmenities = method_exists($house, 'relationLoaded') && $house->relationLoaded('amenities')
                    ? $house->amenities->pluck('name')->filter()->values()->all()
                    : [];
                $imageUrls = method_exists($house, 'relationLoaded') && $house->relationLoaded('images')
                    ? $house->images->pluck('image_path')->filter()->map(fn ($path) => \Illuminate\Support\Facades\Storage::url($path))->values()->all()
                    : [];
                $featured = $house->featured_image ?? data_get($house, 'images.0.image_path');
                $previewUrl = \Illuminate\Support\Facades\Route::has('user.boarding-houses.show')
                    ? route('user.boarding-houses.show', $house)
                    : '#';

                return [
                    'id' => 'db-'.$house->id,
                    'name' => $house->name ?? 'Boarding House',
                    'address' => $house->address ?? $house->full_address ?? 'No address provided',
                    'description' => $house->description ?? 'No description provided.',
                    'contactNumber' => $house->contact_phone ?? $house->contact_number ?? $house->phone ?? '',
                    'email' => $house->contact_email ?? data_get($house, 'owner.email') ?? 'owner@example.com',
                    'status' => $status,
                    'amenities' => $houseAmenities ?: $amenities,
                    'rules' => $houseRules !== '' ? preg_split('/\r\n|\r|\n/', $houseRules) : $rules,
                    'photos' => $photoTiles,
                    'photoUrls' => $imageUrls,
                    'photoUrl' => $featured ? \Illuminate\Support\Facades\Storage::url($featured) : ($imageUrls[0] ?? null),
                    'photoClass' => $photoTiles[$index % count($photoTiles)],
                    'rejectionReason' => $status === 'Rejected' ? (data_get($house, 'approvals.0.remarks') ?: data_get($house, 'accreditation.decision_log') ?: 'Missing fire safety equipment details and emergency exit plan.') : '',
                    'createdAt' => optional($house->created_at)->format('M d, Y') ?: 'May 13, 2026',
                    'updatedAt' => optional($house->updated_at)->format('M d, Y') ?: 'May 13, 2026',
                    'previewUrl' => $previewUrl,
                ];
            })->values()->all();

            $listings = count($dbListings)
                ? $dbListings
                : collect($fallbackListings)->map(function ($listing, $index) use ($photoTiles) {
                    return array_merge([
                        'photos' => $photoTiles,
                        'photoUrls' => [],
                        'photoUrl' => null,
                        'photoClass' => $photoTiles[$index % count($photoTiles)],
                        'createdAt' => 'May 13, 2026',
                        'updatedAt' => 'May 13, 2026',
                        'previewUrl' => '#',
                    ], $listing);
                })->values()->all();
        @endphp

        <script>
            window.ownerListingsPage = function (initialListings) {
                return {
                    listings: initialListings,
                    selectedListing: null,
                    formListing: {},
                    errors: {},
                    searchQuery: '',
                    statusFilter: 'All',
                    statusOpen: false,
                    viewMode: 'table',
                    currentPage: 1,
                    perPage: 5,
                    activeModal: null,
                    toast: '',
                    toastTimer: null,
                    defaultPhotos: @js($photoTiles),
                    init() {
                        this.selectedListing = this.listings.length ? this.clone(this.listings[0]) : null;

                        const params = new URLSearchParams(window.location.search);
                        if (params.get('modal') === 'add') {
                            this.openAddModal();
                            params.delete('modal');

                            const query = params.toString();
                            const cleanUrl = `${window.location.pathname}${query ? `?${query}` : ''}${window.location.hash}`;
                            window.history.replaceState({}, '', cleanUrl);
                        }
                    },
                    get filteredListings() {
                        const query = this.searchQuery.toLowerCase().trim();

                        return this.listings.filter((listing) => {
                            const haystack = [
                                listing.name,
                                listing.address,
                                listing.contactNumber,
                                listing.email,
                            ].join(' ').toLowerCase();

                            return (this.statusFilter === 'All' || listing.status === this.statusFilter)
                                && (! query || haystack.includes(query));
                        });
                    },
                    get totalPages() {
                        return Math.max(1, Math.ceil(this.filteredListings.length / Number(this.perPage || 5)));
                    },
                    get pageStart() {
                        return this.filteredListings.length === 0 ? 0 : ((this.safeCurrentPage() - 1) * Number(this.perPage)) + 1;
                    },
                    get pageEnd() {
                        return Math.min(this.safeCurrentPage() * Number(this.perPage), this.filteredListings.length);
                    },
                    get totalCount() {
                        return this.listings.length;
                    },
                    get approvedCount() {
                        return this.countByStatus('Approved');
                    },
                    get pendingCount() {
                        return this.countByStatus('Pending');
                    },
                    get draftCount() {
                        return this.countByStatus('Draft');
                    },
                    get activeListing() {
                        return this.selectedListing || this.emptyListing();
                    },
                    emptyListing() {
                        return {
                            id: null,
                            name: '',
                            address: '',
                            description: '',
                            contactNumber: '',
                            email: '',
                            status: 'Draft',
                            amenities: [],
                            rules: [],
                            photos: this.defaultPhotos,
                            photoUrls: [],
                            photoUrl: null,
                            photoClass: this.defaultPhotos[0],
                            rejectionReason: '',
                            createdAt: '',
                            updatedAt: '',
                            previewUrl: '#',
                        };
                    },
                    newListing() {
                        return {
                            ...this.emptyListing(),
                            status: 'Draft',
                            amenities: ['Wi-Fi'],
                            rules: [],
                            createdAt: this.todayLabel(),
                            updatedAt: this.todayLabel(),
                        };
                    },
                    clone(value) {
                        return JSON.parse(JSON.stringify(value || this.emptyListing()));
                    },
                    countByStatus(status) {
                        return this.listings.filter((listing) => listing.status === status).length;
                    },
                    safeCurrentPage() {
                        if (this.currentPage > this.totalPages) {
                            this.currentPage = this.totalPages;
                        }

                        if (this.currentPage < 1) {
                            this.currentPage = 1;
                        }

                        return this.currentPage;
                    },
                    paginatedListings() {
                        const start = (this.safeCurrentPage() - 1) * Number(this.perPage);

                        return this.filteredListings.slice(start, start + Number(this.perPage));
                    },
                    pageNumbers() {
                        return Array.from({ length: this.totalPages }, (_, index) => index + 1);
                    },
                    goToPage(page) {
                        this.currentPage = Math.min(Math.max(Number(page), 1), this.totalPages);
                    },
                    resetFilters() {
                        this.searchQuery = '';
                        this.statusFilter = 'All';
                        this.currentPage = 1;
                    },
                    selectListing(listing) {
                        this.selectedListing = this.clone(listing);
                    },
                    openAddModal() {
                        this.formListing = this.prepareForm(this.newListing());
                        this.errors = {};
                        this.activeModal = 'add';
                    },
                    openViewModal(listing) {
                        this.selectListing(listing);
                        this.activeModal = 'view';
                    },
                    openEditModal(listing) {
                        this.selectListing(listing);
                        this.formListing = this.prepareForm(listing);
                        this.errors = {};
                        this.activeModal = 'edit';
                    },
                    openDeleteModal(listing) {
                        this.selectListing(listing);
                        this.activeModal = 'delete';
                    },
                    openSubmitModal(listing) {
                        this.selectListing(listing);
                        this.activeModal = 'submit';
                    },
                    closeModal() {
                        this.activeModal = null;
                        this.errors = {};
                    },
                    prepareForm(listing) {
                        const copy = this.clone(listing);
                        copy.amenitiesText = (copy.amenities || []).join(', ');
                        copy.rulesText = (copy.rules || []).join('\n');

                        return copy;
                    },
                    normalizeForm(statusOverride = null) {
                        const copy = this.clone(this.formListing);
                        copy.status = statusOverride || copy.status || 'Draft';
                        copy.amenities = (copy.amenitiesText || '').split(',').map((item) => item.trim()).filter(Boolean);
                        copy.rules = (copy.rulesText || '').split('\n').map((item) => item.trim()).filter(Boolean);
                        copy.photos = copy.photos && copy.photos.length ? copy.photos : this.defaultPhotos;
                        copy.photoClass = copy.photoClass || this.defaultPhotos[0];
                        copy.rejectionReason = copy.status === 'Rejected' ? (copy.rejectionReason || 'Missing fire safety equipment details and emergency exit plan.') : '';
                        delete copy.amenitiesText;
                        delete copy.rulesText;

                        return copy;
                    },
                    validateForm() {
                        const errors = {};
                        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        const phonePattern = /^[0-9+()\-\s]{7,30}$/;

                        if (! (this.formListing.name || '').trim()) errors.name = 'Boarding House Name is required.';
                        if (! (this.formListing.address || '').trim()) errors.address = 'Address is required.';
                        if (! (this.formListing.description || '').trim()) errors.description = 'Description is required.';
                        if (! (this.formListing.contactNumber || '').trim()) errors.contactNumber = 'Contact Number is required.';
                        if ((this.formListing.contactNumber || '').trim() && ! phonePattern.test(this.formListing.contactNumber.trim())) errors.contactNumber = 'Enter a valid phone number.';
                        if (! (this.formListing.email || '').trim()) errors.email = 'Email Address is required.';
                        if ((this.formListing.email || '').trim() && ! emailPattern.test(this.formListing.email.trim())) errors.email = 'Enter a valid email address.';

                        this.errors = errors;

                        return Object.keys(errors).length === 0;
                    },
                    saveListing(statusOverride = null, message = 'Listing saved.') {
                        if (! this.validateForm()) {
                            this.showToast('Please fix the highlighted fields.');
                            return;
                        }

                        const now = this.todayLabel();
                        const listing = this.normalizeForm(statusOverride);
                        listing.updatedAt = now;

                        if (this.activeModal === 'add') {
                            listing.id = `local-${Date.now()}`;
                            listing.createdAt = now;
                            this.listings.unshift(listing);
                            this.selectedListing = this.clone(listing);
                        } else {
                            const index = this.listings.findIndex((item) => item.id === listing.id);
                            if (index !== -1) {
                                this.listings.splice(index, 1, listing);
                            }
                            this.selectedListing = this.clone(listing);
                        }

                        this.currentPage = 1;
                        this.closeModal();
                        this.showToast(message);
                    },
                    deleteListing() {
                        if (! this.selectedListing) return;

                        const deletedName = this.selectedListing.name;
                        this.listings = this.listings.filter((listing) => listing.id !== this.selectedListing.id);
                        this.selectedListing = this.listings.length ? this.clone(this.listings[0]) : null;
                        this.closeModal();
                        this.showToast(`${deletedName} deleted.`);
                    },
                    confirmSubmitListing() {
                        if (! this.selectedListing) return;

                        const index = this.listings.findIndex((listing) => listing.id === this.selectedListing.id);
                        if (index !== -1) {
                            const updated = {
                                ...this.listings[index],
                                status: 'Pending',
                                rejectionReason: '',
                                updatedAt: this.todayLabel(),
                            };
                            this.listings.splice(index, 1, updated);
                            this.selectedListing = this.clone(updated);
                        }

                        this.closeModal();
                        this.showToast('Listing submitted for approval.');
                    },
                    previewListing(listing) {
                        if (listing && listing.previewUrl && listing.previewUrl !== '#') {
                            window.open(listing.previewUrl, '_blank', 'noopener');
                            return;
                        }

                        this.selectListing(listing || this.activeListing);
                        this.activeModal = 'preview';
                    },
                    handlePhotoInput(event) {
                        const files = Array.from(event.target.files || []);
                        if (! files.length) return;

                        const urls = files.slice(0, 10).map((file) => URL.createObjectURL(file));
                        this.formListing.photoUrls = [...(this.formListing.photoUrls || []), ...urls].slice(0, 10);
                        this.formListing.photoUrl = this.formListing.photoUrls[0] || this.formListing.photoUrl;
                    },
                    badgeClass(status) {
                        return {
                            Draft: 'bg-violet-100 text-violet-700 ring-violet-200',
                            Pending: 'bg-amber-100 text-amber-700 ring-amber-200',
                            Approved: 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                            Rejected: 'bg-rose-100 text-rose-700 ring-rose-200',
                        }[status] || 'bg-slate-100 text-slate-700 ring-slate-200';
                    },
                    todayLabel() {
                        return new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    },
                    showToast(message) {
                        this.toast = message;
                        clearTimeout(this.toastTimer);
                        this.toastTimer = setTimeout(() => this.toast = '', 2600);
                    },
                };
            };
        </script>

        <div x-data="ownerListingsPage(@js($listings))" @keydown.escape.window="closeModal()" class="space-y-6">
            <div x-show="toast" x-transition style="display: none;" class="fixed right-6 top-6 z-[70] rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm font-semibold text-emerald-700 shadow-xl">
                <span x-text="toast"></span>
            </div>

            <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">My Listings</h1>
                    <p class="mt-1 text-sm text-slate-600 sm:text-base">Manage all boarding house and property listings.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Notifications">
                        {!! $icon('bell', 'h-5 w-5') !!}
                        <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white">3</span>
                    </button>
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Help">
                        {!! $icon('question', 'h-5 w-5') !!}
                    </button>
                    <button type="button" @click="openAddModal()" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white shadow-sm shadow-blue-900/20 transition hover:bg-blue-800">
                        {!! $icon('plus', 'h-5 w-5') !!}
                        <span>Add New Listing</span>
                    </button>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 ring-1 ring-blue-200">{!! $icon('home', 'h-7 w-7') !!}</span>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-600">Total Listings</p>
                            <p class="mt-1 text-2xl font-bold text-slate-950" x-text="totalCount"></p>
                            <p class="mt-1 text-sm text-slate-500">All your listings</p>
                        </div>
                    </div>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 ring-1 ring-emerald-200">{!! $icon('check', 'h-7 w-7') !!}</span>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-600">Approved Listings</p>
                            <p class="mt-1 text-2xl font-bold text-slate-950" x-text="approvedCount"></p>
                            <p class="mt-1 text-sm text-slate-500">Active and approved</p>
                        </div>
                    </div>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-orange-100 text-orange-600 ring-1 ring-orange-200">{!! $icon('clock', 'h-7 w-7') !!}</span>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-600">Pending Listings</p>
                            <p class="mt-1 text-2xl font-bold text-slate-950" x-text="pendingCount"></p>
                            <p class="mt-1 text-sm text-slate-500">For admin review</p>
                        </div>
                    </div>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-600 ring-1 ring-violet-200">{!! $icon('document', 'h-7 w-7') !!}</span>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-600">Draft Listings</p>
                            <p class="mt-1 text-2xl font-bold text-slate-950" x-text="draftCount"></p>
                            <p class="mt-1 text-sm text-slate-500">Not yet submitted</p>
                        </div>
                    </div>
                </article>
            </section>

            <section class="min-w-0 rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-200 p-4 xl:flex-row xl:items-center xl:justify-between">
                    <div class="grid gap-3 sm:grid-cols-[minmax(220px,1fr)_170px_auto] xl:min-w-[680px]">
                        <label class="relative block">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">{!! $icon('search', 'h-5 w-5') !!}</span>
                            <input x-model.debounce.150ms="searchQuery" @input="currentPage = 1" type="search" placeholder="Search by name or location" class="h-11 w-full rounded-xl border-slate-200 bg-white pl-11 pr-4 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500">
                        </label>

                        <div class="relative" @click.outside="statusOpen = false">
                            <button type="button" @click="statusOpen = ! statusOpen" class="flex h-11 w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 shadow-sm transition hover:bg-slate-50">
                                <span x-text="statusFilter === 'All' ? 'All Status' : statusFilter">All Status</span>
                                <span class="text-slate-500">{!! $icon('chevron-down', 'h-4 w-4') !!}</span>
                            </button>
                            <div x-show="statusOpen" x-transition style="display: none;" class="absolute left-0 top-[calc(100%+0.35rem)] z-30 w-full overflow-hidden rounded-xl border border-slate-200 bg-white py-2 shadow-xl">
                                @foreach (['All', 'Draft', 'Pending', 'Approved', 'Rejected'] as $option)
                                    <button type="button" @click="statusFilter = @js($option); currentPage = 1; statusOpen = false" class="flex w-full items-center justify-between px-4 py-2.5 text-left text-sm text-slate-800 transition hover:bg-slate-50">
                                        <span>{{ $option }}</span>
                                        <span x-show="statusFilter === @js($option)" class="text-blue-700">{!! $icon('check', 'h-4 w-4') !!}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <button type="button" @click="resetFilters()" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            {!! $icon('reset', 'h-4 w-4') !!}
                            <span>Reset</span>
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="viewMode = 'card'" :class="viewMode === 'card' ? 'border-blue-600 text-blue-700 bg-blue-50' : 'border-slate-200 text-slate-700 bg-white hover:bg-slate-50'" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border px-4 text-sm font-semibold transition">
                            {!! $icon('grid', 'h-4 w-4') !!}
                            <span>Card View</span>
                        </button>
                        <button type="button" @click="viewMode = 'table'" :class="viewMode === 'table' ? 'border-blue-600 text-blue-700 bg-blue-50' : 'border-slate-200 text-slate-700 bg-white hover:bg-slate-50'" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border px-4 text-sm font-semibold transition">
                            {!! $icon('list', 'h-4 w-4') !!}
                            <span>Table View</span>
                        </button>
                    </div>
                </div>

                <div x-show="viewMode === 'table'" class="hidden overflow-x-auto lg:block">
                    <table class="min-w-[980px] w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-4">Photo</th>
                                <th class="px-5 py-4">Boarding House Name</th>
                                <th class="px-5 py-4">Address</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Contact</th>
                                <th class="px-5 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <template x-for="listing in paginatedListings()" :key="listing.id">
                            <tbody class="divide-y divide-slate-200">
                                <tr @click="selectListing(listing)" class="cursor-pointer transition hover:bg-slate-50/80" :class="activeListing.id === listing.id ? 'bg-blue-50/60 shadow-[inset_4px_0_0_#2563eb]' : ''">
                                    <td class="px-5 py-4">
                                        <span class="relative block h-16 w-20 overflow-hidden rounded-xl bg-gradient-to-br shadow-inner" :class="listing.photoClass">
                                            <template x-if="listing.photoUrl">
                                                <img :src="listing.photoUrl" :alt="listing.name" class="absolute inset-0 h-full w-full object-cover">
                                            </template>
                                            <span x-show="! listing.photoUrl" class="absolute inset-x-2 bottom-2 h-9 rounded bg-white/75 shadow-sm"></span>
                                            <span x-show="! listing.photoUrl" class="absolute left-3 top-5 h-7 w-4 rounded-sm bg-slate-700/20"></span>
                                            <span x-show="! listing.photoUrl" class="absolute left-8 top-4 h-8 w-4 rounded-sm bg-slate-700/15"></span>
                                            <span x-show="! listing.photoUrl" class="absolute right-3 top-6 h-6 w-5 rounded-sm bg-slate-700/20"></span>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-950" x-text="listing.name"></p>
                                    </td>
                                    <td class="max-w-[280px] px-5 py-4 text-slate-600" x-text="listing.address"></td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-lg px-3 py-1 text-xs font-bold ring-1" :class="badgeClass(listing.status)" x-text="listing.status"></span>
                                    </td>
                                    <td class="px-5 py-4 text-slate-700">
                                        <p x-text="listing.contactNumber"></p>
                                        <p class="text-slate-500" x-text="listing.email"></p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button" @click.stop="openEditModal(listing)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" title="Edit listing details">{!! $icon('pencil', 'h-4 w-4') !!}</button>
                                            <button type="button" @click.stop="openDeleteModal(listing)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700" title="Delete listing">{!! $icon('trash', 'h-4 w-4') !!}</button>
                                            <button type="button" @click.stop="openViewModal(listing)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" title="View listing details">{!! $icon('eye', 'h-4 w-4') !!}</button>
                                            <button type="button" @click.stop="openSubmitModal(listing)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-blue-200 bg-white text-blue-700 transition hover:border-blue-200 hover:bg-blue-50" title="Submit listing for approval">{!! $icon('send', 'h-4 w-4') !!}</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr x-show="listing.rejectionReason">
                                    <td colspan="6" class="px-5 pb-4">
                                        <div class="flex flex-col gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="flex items-start gap-3">
                                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-600 text-white">{!! $icon('info', 'h-3.5 w-3.5') !!}</span>
                                                <p><span class="font-bold">Rejection Reason:</span> <span x-text="listing.rejectionReason"></span></p>
                                            </div>
                                            <button type="button" @click="openViewModal(listing)" class="font-semibold text-blue-700 hover:text-blue-800">View Details</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </template>
                        <tbody x-show="filteredListings.length === 0">
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No listings match your search or filter.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div x-show="viewMode === 'table'" class="grid gap-4 p-4 lg:hidden">
                    <template x-for="listing in paginatedListings()" :key="`mobile-${listing.id}`">
                        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex gap-4">
                                <span class="relative block h-16 w-20 shrink-0 overflow-hidden rounded-xl bg-gradient-to-br shadow-inner" :class="listing.photoClass">
                                    <template x-if="listing.photoUrl">
                                        <img :src="listing.photoUrl" :alt="listing.name" class="absolute inset-0 h-full w-full object-cover">
                                    </template>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <h3 class="font-semibold text-slate-950" x-text="listing.name"></h3>
                                        <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold ring-1" :class="badgeClass(listing.status)" x-text="listing.status"></span>
                                    </div>
                                    <p class="mt-2 text-sm text-slate-600" x-text="listing.address"></p>
                                    <p class="mt-2 text-sm text-slate-700"><span x-text="listing.contactNumber"></span> | <span x-text="listing.email"></span></p>
                                </div>
                            </div>
                            <div x-show="listing.rejectionReason" class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                                <span class="font-bold">Rejection Reason:</span> <span x-text="listing.rejectionReason"></span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <button type="button" @click="openEditModal(listing)" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700">Edit</button>
                                <button type="button" @click="openViewModal(listing)" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700">View</button>
                                <button type="button" @click="openDeleteModal(listing)" class="inline-flex h-9 items-center gap-2 rounded-lg border border-rose-200 px-3 text-sm font-semibold text-rose-700">Delete</button>
                                <button type="button" @click="openSubmitModal(listing)" class="inline-flex h-9 items-center gap-2 rounded-lg border border-blue-200 px-3 text-sm font-semibold text-blue-700">Submit</button>
                            </div>
                        </article>
                    </template>
                    <p x-show="filteredListings.length === 0" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">No listings match your search or filter.</p>
                </div>

                <div x-show="viewMode === 'card'" class="grid gap-4 p-4 sm:grid-cols-2 xl:grid-cols-3">
                    <template x-for="listing in paginatedListings()" :key="`card-${listing.id}`">
                        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="relative h-36 overflow-hidden rounded-xl bg-gradient-to-br shadow-inner" :class="listing.photoClass">
                                <template x-if="listing.photoUrl">
                                    <img :src="listing.photoUrl" :alt="listing.name" class="absolute inset-0 h-full w-full object-cover">
                                </template>
                                <span x-show="! listing.photoUrl" class="absolute inset-x-6 bottom-4 h-20 rounded-xl bg-white/75 shadow-sm"></span>
                                <span x-show="! listing.photoUrl" class="absolute left-8 top-12 h-16 w-9 rounded bg-slate-700/20"></span>
                                <span x-show="! listing.photoUrl" class="absolute right-10 top-14 h-14 w-12 rounded bg-slate-700/20"></span>
                            </div>
                            <div class="mt-4 flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-slate-950" x-text="listing.name"></h3>
                                    <p class="mt-1 text-sm text-slate-600" x-text="listing.address"></p>
                                </div>
                                <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold ring-1" :class="badgeClass(listing.status)" x-text="listing.status"></span>
                            </div>
                            <div class="mt-4 text-sm text-slate-700">
                                <p x-text="listing.contactNumber"></p>
                                <p class="text-slate-500" x-text="listing.email"></p>
                            </div>
                            <div x-show="listing.rejectionReason" class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                                <span class="font-bold">Rejection Reason:</span> <span x-text="listing.rejectionReason"></span>
                            </div>
                            <div class="mt-4 flex items-center justify-between gap-2">
                                <button type="button" @click="openEditModal(listing)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-700">{!! $icon('pencil', 'h-4 w-4') !!}</button>
                                <button type="button" @click="openDeleteModal(listing)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-700">{!! $icon('trash', 'h-4 w-4') !!}</button>
                                <button type="button" @click="openViewModal(listing)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-700">{!! $icon('eye', 'h-4 w-4') !!}</button>
                                <button type="button" @click="openSubmitModal(listing)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-blue-200 text-blue-700">{!! $icon('send', 'h-4 w-4') !!}</button>
                            </div>
                        </article>
                    </template>
                    <p x-show="filteredListings.length === 0" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500 sm:col-span-2 xl:col-span-3">No listings match your search or filter.</p>
                </div>

                <div class="flex flex-col gap-4 border-t border-slate-200 p-4 lg:flex-row lg:items-center lg:justify-between">
                    <p class="text-sm text-slate-600">Showing <span x-text="pageStart"></span> to <span x-text="pageEnd"></span> of <span x-text="filteredListings.length"></span> listings</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <nav class="flex items-center gap-2" aria-label="Pagination">
                            <button type="button" @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" :class="currentPage === 1 ? 'opacity-50' : 'hover:bg-slate-50'" class="inline-flex h-9 items-center gap-1 rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-600">
                                {!! $icon('chevron-left', 'h-4 w-4') !!}
                                <span class="hidden sm:inline">Previous</span>
                            </button>
                            <template x-for="page in pageNumbers()" :key="page">
                                <button type="button" @click="goToPage(page)" :class="currentPage === page ? 'bg-blue-700 text-white' : 'border border-slate-200 text-slate-700 hover:bg-slate-50'" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-bold" x-text="page"></button>
                            </template>
                            <button type="button" @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" :class="currentPage === totalPages ? 'opacity-50' : 'hover:bg-slate-50'" class="inline-flex h-9 items-center gap-1 rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-600">
                                <span class="hidden sm:inline">Next</span>
                                {!! $icon('chevron-right', 'h-4 w-4') !!}
                            </button>
                        </nav>
                        <label class="relative block">
                            <select x-model.number="perPage" @change="currentPage = 1" class="h-10 appearance-none rounded-xl border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="5">5 / page</option>
                                <option value="10">10 / page</option>
                                <option value="25">25 / page</option>
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">{!! $icon('chevron-down', 'h-4 w-4') !!}</span>
                        </label>
                    </div>
                </div>
            </section>

            <div x-show="activeModal === 'add' || activeModal === 'edit'" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm" @click.self="closeModal()">
                <div class="flex max-h-[85vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-950" x-text="activeModal === 'add' ? 'Add New Listing' : 'Edit Listing'"></h2>
                            <p class="text-sm text-slate-500" x-text="formListing.name || 'Create and manage listing information.'"></p>
                        </div>
                        <button type="button" @click="closeModal()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900">{!! $icon('x', 'h-5 w-5') !!}</button>
                    </div>

                    <div class="overflow-y-auto px-6 py-5">
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="md:col-span-2">
                                <span class="text-sm font-semibold text-slate-700">Boarding House Name</span>
                                <input x-model="formListing.name" type="text" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <span x-show="errors.name" class="mt-1 block text-xs font-semibold text-rose-600" x-text="errors.name"></span>
                            </label>
                            <label class="md:col-span-2">
                                <span class="text-sm font-semibold text-slate-700">Address</span>
                                <input x-model="formListing.address" type="text" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <span x-show="errors.address" class="mt-1 block text-xs font-semibold text-rose-600" x-text="errors.address"></span>
                            </label>
                            <label class="md:col-span-2">
                                <span class="text-sm font-semibold text-slate-700">Description</span>
                                <textarea x-model="formListing.description" rows="4" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                <span x-show="errors.description" class="mt-1 block text-xs font-semibold text-rose-600" x-text="errors.description"></span>
                            </label>
                            <label>
                                <span class="text-sm font-semibold text-slate-700">Contact Number</span>
                                <input x-model="formListing.contactNumber" type="text" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <span x-show="errors.contactNumber" class="mt-1 block text-xs font-semibold text-rose-600" x-text="errors.contactNumber"></span>
                            </label>
                            <label>
                                <span class="text-sm font-semibold text-slate-700">Email Address</span>
                                <input x-model="formListing.email" type="email" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <span x-show="errors.email" class="mt-1 block text-xs font-semibold text-rose-600" x-text="errors.email"></span>
                            </label>
                            <label>
                                <span class="text-sm font-semibold text-slate-700">Listing Status</span>
                                <select x-model="formListing.status" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option>Draft</option>
                                    <option>Pending</option>
                                    <option>Approved</option>
                                    <option>Rejected</option>
                                </select>
                            </label>
                            <label>
                                <span class="text-sm font-semibold text-slate-700">Amenities</span>
                                <input x-model="formListing.amenitiesText" type="text" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Wi-Fi, CCTV, Laundry Area">
                            </label>
                            <label class="md:col-span-2">
                                <span class="text-sm font-semibold text-slate-700">House Rules</span>
                                <textarea x-model="formListing.rulesText" rows="4" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter one rule per line"></textarea>
                            </label>
                            <div class="md:col-span-2">
                                <p class="text-sm font-semibold text-slate-700">Photos</p>
                                <div class="mt-2 grid gap-3 md:grid-cols-[220px_minmax(0,1fr)]">
                                    <label class="flex min-h-32 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 text-center text-sm text-slate-500 hover:border-blue-300 hover:bg-blue-50">
                                        <input type="file" multiple accept="image/*" class="hidden" @change="handlePhotoInput($event)">
                                        <span class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm">{!! $icon('upload', 'h-6 w-6') !!}</span>
                                        <span>Drag and drop photos here</span>
                                        <span class="text-blue-700">or click to upload</span>
                                    </label>
                                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                        <template x-for="(url, index) in formListing.photoUrls || []" :key="`form-url-${index}`">
                                            <img :src="url" alt="" class="h-24 w-full rounded-xl object-cover">
                                        </template>
                                        <template x-for="(tile, index) in (formListing.photos || []).slice(0, Math.max(0, 4 - (formListing.photoUrls || []).length))" :key="`form-tile-${index}`">
                                            <div class="relative min-h-24 rounded-xl bg-gradient-to-br shadow-inner" :class="tile">
                                                <span x-show="index === 3" class="absolute inset-0 flex items-center justify-center rounded-xl bg-slate-950/55 text-lg font-bold text-white">+2</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-end">
                        <button type="button" @click="closeModal()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                        <button type="button" @click="saveListing('Draft', activeModal === 'add' ? 'Listing saved as draft.' : 'Listing saved as draft.')" class="inline-flex h-10 items-center justify-center rounded-xl border border-violet-200 px-4 text-sm font-semibold text-violet-700 hover:bg-violet-50">Save as Draft</button>
                        <button x-show="activeModal === 'add'" type="button" @click="saveListing(null, 'Listing created.')" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Create Listing</button>
                        <button x-show="activeModal === 'edit'" type="button" @click="saveListing(null, 'Listing changes saved.')" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Save Changes</button>
                        <button type="button" @click="saveListing('Pending', activeModal === 'add' ? 'Listing created and submitted for approval.' : 'Listing submitted for approval.')" class="inline-flex h-10 items-center justify-center rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700">Submit for Approval</button>
                    </div>
                </div>
            </div>

            <div x-show="activeModal === 'delete'" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm" @click.self="closeModal()">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="flex items-start gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-700">{!! $icon('warning', 'h-6 w-6') !!}</span>
                        <div class="min-w-0">
                            <h2 class="text-lg font-bold text-slate-950">Delete Listing?</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Are you sure you want to delete this listing? This action cannot be undone.</p>
                            <p class="mt-3 rounded-xl bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-900" x-text="activeListing.name"></p>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <button type="button" @click="closeModal()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                        <button type="button" @click="deleteListing()" class="inline-flex h-10 items-center justify-center rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white hover:bg-rose-700">Delete Listing</button>
                    </div>
                </div>
            </div>

            <div x-show="activeModal === 'submit'" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm" @click.self="closeModal()">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="flex items-start gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700">{!! $icon('send', 'h-6 w-6') !!}</span>
                        <div class="min-w-0">
                            <h2 class="text-lg font-bold text-slate-950">Submit Listing for Approval?</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">This listing will be sent for admin review.</p>
                            <p class="mt-3 rounded-xl bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-900" x-text="activeListing.name"></p>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <button type="button" @click="closeModal()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                        <button type="button" @click="confirmSubmitListing()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Submit</button>
                    </div>
                </div>
            </div>

            <div x-show="activeModal === 'view'" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm" @click.self="closeModal()">
                <div class="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-950">Listing Details</h2>
                            <p class="text-sm text-slate-500" x-text="activeListing.name"></p>
                        </div>
                        <button type="button" @click="closeModal()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900">{!! $icon('x', 'h-5 w-5') !!}</button>
                    </div>

                    <div class="overflow-y-auto p-6">
                        <div class="grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
                            <div>
                                <div class="relative h-44 overflow-hidden rounded-2xl bg-gradient-to-br shadow-inner" :class="activeListing.photoClass">
                                    <template x-if="activeListing.photoUrl">
                                        <img :src="activeListing.photoUrl" :alt="activeListing.name" class="absolute inset-0 h-full w-full object-cover">
                                    </template>
                                </div>
                                <span class="mt-4 inline-flex rounded-lg px-3 py-1 text-xs font-bold ring-1" :class="badgeClass(activeListing.status)" x-text="activeListing.status"></span>
                            </div>
                            <div class="space-y-5 text-sm">
                                <div>
                                    <h3 class="text-xl font-bold text-slate-950" x-text="activeListing.name"></h3>
                                    <p class="mt-2 text-slate-600" x-text="activeListing.address"></p>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-700">Description</p>
                                    <p class="mt-1 leading-6 text-slate-700" x-text="activeListing.description"></p>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Contact Number</p>
                                        <p class="mt-1 font-semibold text-slate-900" x-text="activeListing.contactNumber"></p>
                                    </div>
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email Address</p>
                                        <p class="mt-1 font-semibold text-blue-700" x-text="activeListing.email"></p>
                                    </div>
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Created Date</p>
                                        <p class="mt-1 font-semibold text-slate-900" x-text="activeListing.createdAt"></p>
                                    </div>
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Updated Date</p>
                                        <p class="mt-1 font-semibold text-slate-900" x-text="activeListing.updatedAt"></p>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-700">Amenities</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <template x-for="amenity in activeListing.amenities" :key="`view-${amenity}`">
                                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100" x-text="amenity"></span>
                                        </template>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-700">House Rules</p>
                                    <ul class="mt-2 list-disc space-y-1 pl-5 text-slate-700">
                                        <template x-for="rule in activeListing.rules" :key="`view-${rule}`">
                                            <li x-text="rule"></li>
                                        </template>
                                    </ul>
                                </div>
                                <div x-show="activeListing.rejectionReason" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                    <span class="font-bold">Rejection Reason:</span> <span x-text="activeListing.rejectionReason"></span>
                                </div>
                                <div x-show="activeListing.status === 'Draft' || activeListing.status === 'Pending'" class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                                    <span class="font-bold">Submission Status:</span>
                                    <span x-text="activeListing.status === 'Draft' ? 'This listing is saved as a draft and has not been submitted yet.' : 'This listing is pending admin review.'"></span>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-700">Photos</p>
                                    <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                                        <template x-for="(url, index) in activeListing.photoUrls || []" :key="`view-url-${index}`">
                                            <img :src="url" alt="" class="h-20 w-full rounded-xl object-cover">
                                        </template>
                                        <template x-for="(tile, index) in (activeListing.photos || []).slice(0, Math.max(0, 4 - (activeListing.photoUrls || []).length))" :key="`view-photo-${index}`">
                                            <div class="relative min-h-20 rounded-xl bg-gradient-to-br shadow-inner" :class="tile">
                                                <span x-show="index === 3" class="absolute inset-0 flex items-center justify-center rounded-xl bg-slate-950/55 text-lg font-bold text-white">+2</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-end">
                        <button type="button" @click="closeModal()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Close</button>
                        <button type="button" @click="openEditModal(activeListing)" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Edit Listing</button>
                        <button type="button" @click="previewListing(activeListing)" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-blue-200 px-4 text-sm font-semibold text-blue-700 hover:bg-blue-50">Preview Public Listing {!! $icon('external', 'h-4 w-4') !!}</button>
                    </div>
                </div>
            </div>

            <div x-show="activeModal === 'preview'" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm" @click.self="closeModal()">
                <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-950">Public Listing Preview</h2>
                            <p class="text-sm text-slate-500" x-text="activeListing.name"></p>
                        </div>
                        <button type="button" @click="closeModal()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900">{!! $icon('x', 'h-5 w-5') !!}</button>
                    </div>
                    <div class="p-6">
                        <div class="overflow-hidden rounded-2xl border border-slate-200">
                            <div class="h-44 bg-gradient-to-br" :class="activeListing.photoClass">
                                <template x-if="activeListing.photoUrl">
                                    <img :src="activeListing.photoUrl" :alt="activeListing.name" class="h-full w-full object-cover">
                                </template>
                            </div>
                            <div class="p-5">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-950" x-text="activeListing.name"></h3>
                                        <p class="mt-1 text-sm text-slate-600" x-text="activeListing.address"></p>
                                    </div>
                                    <span class="inline-flex rounded-lg px-3 py-1 text-xs font-bold ring-1" :class="badgeClass(activeListing.status)" x-text="activeListing.status"></span>
                                </div>
                                <p class="mt-4 text-sm leading-6 text-slate-700" x-text="activeListing.description"></p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <template x-for="amenity in activeListing.amenities" :key="`preview-${amenity}`">
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700" x-text="amenity"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end border-t border-slate-200 px-6 py-4">
                        <button type="button" @click="closeModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Close Preview</button>
                    </div>
                </div>
            </div>
        </div>
    </x-owner.shell>
</x-layouts.caretaker>
