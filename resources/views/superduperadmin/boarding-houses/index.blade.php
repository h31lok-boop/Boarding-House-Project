<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />

@php
    $r = fn ($name, $params = [], $fallback = '#') => \Illuminate\Support\Facades\Route::has($name)
        ? route($name, $params, false)
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
        'pin' => '<path d="M12 21s6-5.2 6-11a6 6 0 1 0-12 0c0 5.8 6 11 6 11Z"/><circle cx="12" cy="10" r="2"/>',
        'text' => '<path d="M7 4h10M12 4v16M8 20h8"/>',
        'sparkle' => '<path d="M12 3l1.7 5.2L19 10l-5.3 1.8L12 17l-1.7-5.2L5 10l5.3-1.8L12 3Z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8L19 15Z"/>',
        'clipboard' => '<path d="M9 4h6l1 2h3v15H5V6h3l1-2Z"/><path d="M9 11h6M9 15h6"/>',
        'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19 19 0 0 1-8.3-3 18.7 18.7 0 0 1-5.8-5.8 19 19 0 0 1-3-8.3A2 2 0 0 1 4.7 2h3a2 2 0 0 1 2 1.7l.4 2.7a2 2 0 0 1-.6 1.8L8.2 9.5a15 15 0 0 0 6.3 6.3l1.3-1.3a2 2 0 0 1 1.8-.6l2.7.4a2 2 0 0 1 1.7 2Z"/>',
        'mail' => '<path d="M4 5h16v14H4z"/><path d="m4 7 8 6 8-6"/>',
        'upload' => '<path d="M12 16V5"/><path d="m8 9 4-4 4 4"/><path d="M20 16.5a4 4 0 0 0-4-4h-1a6 6 0 0 0-11.3 2A3.5 3.5 0 0 0 5.5 21H18a4 4 0 0 0 2-7.5"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'warning' => '<path d="m12 3 10 18H2L12 3Z"/><path d="M12 9v5M12 17h.01"/>',
    ];

    $uiIcon = fn ($name, $class = 'h-5 w-5') => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8">'.($iconPaths[$name] ?? $iconPaths['info']).'</svg>';

    $statusClasses = [
        'Draft' => 'bg-violet-100 text-violet-700 ring-violet-200',
        'Pending' => 'bg-amber-100 text-amber-700 ring-amber-200',
        'Approved' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'Rejected' => 'bg-rose-100 text-rose-700 ring-rose-200',
    ];

    $stats = [
        ['label' => 'Total Listings', 'value' => '12', 'description' => 'All your listings', 'icon' => 'home', 'iconClass' => 'bg-blue-100 text-blue-600 ring-blue-200'],
        ['label' => 'Approved Listings', 'value' => '8', 'description' => 'Active and approved', 'icon' => 'check', 'iconClass' => 'bg-emerald-100 text-emerald-600 ring-emerald-200'],
        ['label' => 'Pending Listings', 'value' => '2', 'description' => 'For admin review', 'icon' => 'clock', 'iconClass' => 'bg-orange-100 text-orange-600 ring-orange-200'],
        ['label' => 'Draft Listings', 'value' => '2', 'description' => 'Not yet submitted', 'icon' => 'document', 'iconClass' => 'bg-violet-100 text-violet-600 ring-violet-200'],
    ];

    $listings = [
        [
            'name' => 'MetroNest Boarding Hub',
            'address' => '123 Rizal Avenue, Davao City, Davao del Sur 8000',
            'status' => 'Draft',
            'phone' => '0917 123 4567',
            'email' => 'metronest@gmail.com',
            'photoClass' => 'from-slate-200 via-blue-100 to-slate-300 text-blue-700',
            'description' => 'A modern and safe boarding house near schools, markets, and public transport. Ideal for students and working professionals.',
        ],
        [
            'name' => 'Casa Digos Boarding Stay',
            'address' => 'Purok 3, Upper Digos, Digos City, Davao del Sur',
            'status' => 'Pending',
            'phone' => '0938 765 4321',
            'email' => 'casadigos@outlook.com',
            'photoClass' => 'from-stone-200 via-amber-100 to-stone-300 text-amber-700',
        ],
        [
            'name' => 'Sunrise Student Boarding House',
            'address' => '45-B P. Gomez Street, Buhangin, Davao City',
            'status' => 'Approved',
            'phone' => '0921 345 6789',
            'email' => 'sunriseboarding@gmail.com',
            'photoClass' => 'from-slate-200 via-emerald-100 to-slate-300 text-emerald-700',
        ],
        [
            'name' => 'Green Haven Residences',
            'address' => 'Lot 8, Block 2, Camella Homes, Cabantian, Davao City',
            'status' => 'Rejected',
            'phone' => '0906 543 2109',
            'email' => 'greenhaven.res@gmail.com',
            'photoClass' => 'from-rose-100 via-emerald-100 to-slate-300 text-emerald-700',
            'rejection' => 'Missing fire safety equipment details and emergency exit plan.',
        ],
        [
            'name' => 'Maple Corner Boarding House',
            'address' => '78 Maple Street, Panabo City, Davao del Norte',
            'status' => 'Draft',
            'phone' => '0915 222 3344',
            'email' => 'maplecorner.ph@gmail.com',
            'photoClass' => 'from-slate-200 via-lime-100 to-stone-300 text-lime-700',
        ],
    ];

    $selectedListing = $listings[0];
    $amenities = ['Wi-Fi', 'CCTV', 'Laundry Area', 'Study Area', 'Kitchen Access', 'Water Tank'];
    $rules = ['No smoking inside the rooms', 'No overnight visitors', 'Curfew at 10:00 PM', 'Keep the premises clean and quiet'];
    $photoTiles = [
        'from-slate-200 via-blue-100 to-slate-300',
        'from-stone-100 via-slate-100 to-blue-100',
        'from-emerald-100 via-slate-100 to-stone-200',
        'from-slate-200 via-stone-100 to-slate-400',
    ];
@endphp

<style>
    #addListingMap {
        height: 380px;
        min-height: 320px;
        width: 100%;
        border-radius: 1rem;
        overflow: hidden;
    }

    #addListingMap .leaflet-pane,
    #addListingMap .leaflet-top,
    #addListingMap .leaflet-bottom {
        z-index: 1;
    }

    @media (max-width: 640px) {
        #addListingMap {
            height: 280px;
            min-height: 260px;
        }
    }
</style>

<x-admin.workspace-shell
    workspace="superduperadmin"
    title="All Boarding Houses"
    subtitle="Manage all boarding house and property listings."
    profile-role-label="Owner"
    active="listings">

    <x-slot name="actions">
        <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Notifications">
            {!! $uiIcon('bell', 'h-5 w-5') !!}
            <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white">3</span>
        </button>
        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Help">
            {!! $uiIcon('question', 'h-5 w-5') !!}
        </button>
        <button type="button" x-data @click="$dispatch('open-add-listing')" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white shadow-sm shadow-blue-900/20 transition hover:bg-blue-800">
            {!! $uiIcon('plus', 'h-5 w-5') !!}
            <span>Add New Listing</span>
        </button>
    </x-slot>

    @if (($boardingHouses ?? collect())->isNotEmpty())
        <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Saved Boarding Houses</h2>
                    <p class="text-sm text-slate-500">Database records currently available to the listing workspace.</p>
                </div>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($boardingHouses as $house)
                    <article class="rounded-xl border border-slate-200 p-4">
                        <p class="font-bold text-slate-950">{{ $house->name }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $house->address ?: 'Address not set' }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <div
        x-data="{
            view: 'table',
            search: '',
            status: 'All',
            statusOpen: false,
            modalType: null,
            selectedListing: @js(array_merge(['amenities' => $amenities, 'rules' => $rules, 'photos' => $photoTiles, 'previewUrl' => '#'], $selectedListing)),
            editListing: {},
            addListingForm: {},
            formErrors: {},
            localListings: [],
            baseTotal: 12,
            baseApproved: 8,
            basePending: 2,
            baseDraft: 2,
            availableAmenities: ['Wi-Fi', 'CCTV', 'Laundry Area', 'Study Area', 'Kitchen Access', 'Water Tank', 'Parking Area', 'Private CR'],
            toast: '',
            toastTimer: null,
            init() {
                const params = new URLSearchParams(window.location.search);
                if (params.get('modal') === 'add') {
                    this.openAddModal();
                    params.delete('modal');

                    const query = params.toString();
                    const cleanUrl = `${window.location.pathname}${query ? `?${query}` : ''}${window.location.hash}`;
                    window.history.replaceState({}, '', cleanUrl);
                }
            },
            get totalListings() {
                return this.baseTotal + this.localListings.length;
            },
            get approvedListings() {
                return this.baseApproved + this.localCount('Approved');
            },
            get pendingListings() {
                return this.basePending + this.localCount('Pending');
            },
            get draftListings() {
                return this.baseDraft + this.localCount('Draft');
            },
            matches(name, address, status) {
                const query = this.search.toLowerCase().trim();
                const haystack = `${name} ${address}`.toLowerCase();
                return (this.status === 'All' || this.status === status) && (! query || haystack.includes(query));
            },
            matchesListing(listing) {
                const query = this.search.toLowerCase().trim();
                const haystack = `${listing.name} ${listing.address} ${listing.phone || ''} ${listing.email || ''}`.toLowerCase();
                return (this.status === 'All' || this.status === listing.status) && (! query || haystack.includes(query));
            },
            visibleLocalListings() {
                return this.localListings.filter((listing) => this.matchesListing(listing));
            },
            localCount(status) {
                return this.localListings.filter((listing) => listing.status === status).length;
            },
            cloneListing(listing) {
                return JSON.parse(JSON.stringify(listing || {}));
            },
            todayLabel() {
                return new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            },
            emptyAddForm() {
                return {
                    name: '',
                    address: '',
                    description: '',
                    contactNumber: '',
                    email: '',
                    region: '',
                    province: '',
                    city: '',
                    barangay: '',
                    latitude: '6.74400000',
                    longitude: '125.35500000',
                    amenities: ['Wi-Fi', 'CCTV'],
                    rulesText: 'No smoking inside the rooms\nNo overnight visitors\nCurfew at 10:00 PM\nKeep the premises clean and quiet',
                    status: 'Draft',
                    photoNames: [],
                    photoUrls: [],
                };
            },
            openAddModal() {
                this.closeModal();
                this.addListingForm = this.emptyAddForm();
                this.formErrors = {};
                this.modalType = 'add';
                setTimeout(() => this.initAddListingMap(), 150);
            },
            mapStore() {
                window.ownerAddListingMapState = window.ownerAddListingMapState || { map: null, marker: null };
                return window.ownerAddListingMapState;
            },
            initAddListingMap() {
                if (this.modalType !== 'add') return;
                if (! window.L) {
                    setTimeout(() => this.initAddListingMap(), 150);
                    return;
                }
                const mapElement = document.getElementById('addListingMap');
                if (! mapElement) return;

                const lat = this.validCoordinate(this.addListingForm.latitude, -90, 90) ? Number(this.addListingForm.latitude) : 6.744;
                const lng = this.validCoordinate(this.addListingForm.longitude, -180, 180) ? Number(this.addListingForm.longitude) : 125.355;
                const store = this.mapStore();

                if (! store.map) {
                    store.map = L.map(mapElement, { scrollWheelZoom: true }).setView([lat, lng], 14);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(store.map);

                    store.marker = L.marker([lat, lng], { draggable: true }).addTo(store.map);
                    store.marker.on('dragend', (event) => {
                        const point = event.target.getLatLng();
                        this.setMapLocation(point.lat, point.lng, true);
                    });
                    store.map.on('click', (event) => {
                        this.setMapLocation(event.latlng.lat, event.latlng.lng, true);
                    });
                } else {
                    store.map.setView([lat, lng], 14);
                    store.marker.setLatLng([lat, lng]);
                }

                setTimeout(() => store.map.invalidateSize(), 50);
                setTimeout(() => store.map.invalidateSize(), 300);
            },
            validCoordinate(value, min, max) {
                if (value === null || value === undefined || value === '') return false;
                const number = Number(value);
                return Number.isFinite(number) && number >= min && number <= max;
            },
            syncMapFromFields() {
                if (! this.validCoordinate(this.addListingForm.latitude, -90, 90) || ! this.validCoordinate(this.addListingForm.longitude, -180, 180)) return;
                this.setMapLocation(Number(this.addListingForm.latitude), Number(this.addListingForm.longitude), false);
            },
            setMapLocation(lat, lng, shouldReverseGeocode = false) {
                if (! Number.isFinite(Number(lat)) || ! Number.isFinite(Number(lng))) return;
                const cleanLat = Number(lat);
                const cleanLng = Number(lng);
                this.addListingForm.latitude = cleanLat.toFixed(8);
                this.addListingForm.longitude = cleanLng.toFixed(8);

                const store = this.mapStore();
                if (store.marker) store.marker.setLatLng([cleanLat, cleanLng]);
                if (store.map) {
                    store.map.setView([cleanLat, cleanLng], Math.max(store.map.getZoom(), 14));
                    setTimeout(() => store.map.invalidateSize(), 50);
                }

                if (shouldReverseGeocode) this.reverseGeocode(cleanLat, cleanLng);
            },
            reverseGeocode(lat, lng) {
                fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lng))
                    .then((response) => response.ok ? response.json() : Promise.reject())
                    .then((payload) => {
                        if (! payload) return;
                        const details = payload.address || {};
                        if (payload.display_name && ! (this.addListingForm.address || '').trim()) {
                            this.addListingForm.address = payload.display_name;
                        }
                        this.addListingForm.region = this.addListingForm.region || details.region || '';
                        this.addListingForm.province = this.addListingForm.province || details.state || details.county || '';
                        this.addListingForm.city = this.addListingForm.city || details.city || details.town || details.municipality || details.village || '';
                        this.addListingForm.barangay = this.addListingForm.barangay || details.suburb || details.neighbourhood || details.hamlet || '';
                    })
                    .catch(() => {});
            },
            prepareEditListing(listing) {
                const copy = this.cloneListing(listing);
                copy.amenitiesText = (copy.amenities || []).join(', ');
                copy.rulesText = (copy.rules || []).join('\n');
                return copy;
            },
            openListingModal(type, listing) {
                this.selectedListing = this.cloneListing(listing);
                this.editListing = this.prepareEditListing(listing);
                this.modalType = type;
            },
            closeModal() {
                this.modalType = null;
                this.formErrors = {};
            },
            confirmAction(message) {
                this.closeModal();
                this.showToast(message);
            },
            toggleAmenity(amenity) {
                const current = this.addListingForm.amenities || [];
                this.addListingForm.amenities = current.includes(amenity)
                    ? current.filter((item) => item !== amenity)
                    : [...current, amenity];
            },
            handleAddPhotos(event) {
                const files = Array.from(event.target.files || []);
                this.addListingForm.photoNames = files.map((file) => file.name);
                this.addListingForm.photoUrls = files.slice(0, 10).map((file) => URL.createObjectURL(file));
            },
            validateAddListing() {
                const errors = {};
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (! this.addListingForm.name.trim()) errors.name = 'Boarding House Name is required.';
                if (! this.addListingForm.address.trim()) errors.address = 'Address is required.';
                if (! this.addListingForm.description.trim()) errors.description = 'Description is required.';
                if (! this.addListingForm.contactNumber.trim()) errors.contactNumber = 'Contact Number is required.';
                if (! this.addListingForm.email.trim()) errors.email = 'Email Address is required.';
                if (this.addListingForm.email.trim() && ! emailPattern.test(this.addListingForm.email.trim())) errors.email = 'Enter a valid email address.';
                if (! this.validCoordinate(this.addListingForm.latitude, -90, 90)) errors.latitude = 'Enter a valid latitude.';
                if (! this.validCoordinate(this.addListingForm.longitude, -180, 180)) errors.longitude = 'Enter a valid longitude.';
                this.formErrors = errors;
                return Object.keys(errors).length === 0;
            },
            createListing(statusOverride = null, message = 'Listing created.') {
                if (! this.validateAddListing()) {
                    this.showToast('Please fix the highlighted fields.');
                    return;
                }
                const status = statusOverride || this.addListingForm.status || 'Draft';
                const listing = {
                    id: `local-${Date.now()}`,
                    name: this.addListingForm.name.trim(),
                    address: this.addListingForm.address.trim(),
                    description: this.addListingForm.description.trim(),
                    phone: this.addListingForm.contactNumber.trim(),
                    contactNumber: this.addListingForm.contactNumber.trim(),
                    email: this.addListingForm.email.trim(),
                    status,
                    amenities: this.addListingForm.amenities || [],
                    rules: (this.addListingForm.rulesText || '').split('\n').map((item) => item.trim()).filter(Boolean),
                    photos: @js($photoTiles),
                    photoUrls: this.addListingForm.photoUrls || [],
                    photoUrl: (this.addListingForm.photoUrls || [])[0] || null,
                    photoClass: 'from-slate-200 via-blue-100 to-slate-300 text-blue-700',
                    rejection: '',
                    rejectionReason: '',
                    region: this.addListingForm.region,
                    province: this.addListingForm.province,
                    city: this.addListingForm.city,
                    barangay: this.addListingForm.barangay,
                    latitude: this.addListingForm.latitude,
                    longitude: this.addListingForm.longitude,
                    createdAt: this.todayLabel(),
                    updatedAt: this.todayLabel(),
                    previewUrl: '#',
                };
                this.localListings.unshift(listing);
                this.selectedListing = this.cloneListing(listing);
                this.closeModal();
                this.addListingForm = this.emptyAddForm();
                this.showToast(message);
            },
            saveEdit(statusOverride = null, message = 'Listing changes saved locally.') {
                if (this.selectedListing?.id?.startsWith?.('local-')) {
                    const updated = this.cloneListing(this.editListing);
                    updated.status = statusOverride || updated.status || 'Draft';
                    updated.phone = updated.phone || updated.contactNumber || '';
                    updated.contactNumber = updated.phone;
                    updated.amenities = (updated.amenitiesText || '').split(',').map((item) => item.trim()).filter(Boolean);
                    updated.rules = (updated.rulesText || '').split('\n').map((item) => item.trim()).filter(Boolean);
                    updated.updatedAt = this.todayLabel();
                    delete updated.amenitiesText;
                    delete updated.rulesText;
                    this.localListings = this.localListings.map((listing) => listing.id === updated.id ? updated : listing);
                    this.selectedListing = this.cloneListing(updated);
                }
                this.confirmAction(message);
            },
            deleteSelectedListing() {
                if (this.selectedListing?.id?.startsWith?.('local-')) {
                    this.localListings = this.localListings.filter((listing) => listing.id !== this.selectedListing.id);
                }
                this.confirmAction('Listing removed locally.');
            },
            submitSelectedListing() {
                if (this.selectedListing?.id?.startsWith?.('local-')) {
                    const updated = { ...this.selectedListing, status: 'Pending', rejection: '', rejectionReason: '', updatedAt: this.todayLabel() };
                    this.localListings = this.localListings.map((listing) => listing.id === updated.id ? updated : listing);
                    this.selectedListing = this.cloneListing(updated);
                }
                this.confirmAction('Listing submitted for approval.');
            },
            showToast(message) {
                this.toast = message;
                clearTimeout(this.toastTimer);
                this.toastTimer = setTimeout(() => this.toast = '', 2500);
            },
            badgeClass(status) {
                return {
                    Draft: 'bg-violet-100 text-violet-700 ring-violet-200',
                    Pending: 'bg-amber-100 text-amber-700 ring-amber-200',
                    Approved: 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                    Rejected: 'bg-rose-100 text-rose-700 ring-rose-200',
                }[status] || 'bg-slate-100 text-slate-700 ring-slate-200';
            }
        }"
        @open-add-listing.window="openAddModal()"
        class="grid gap-6"
    >
        <div class="min-w-0 space-y-6">
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($stats as $stat)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full ring-1 {{ $stat['iconClass'] }}">
                                {!! $uiIcon($stat['icon'], 'h-7 w-7') !!}
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-600">{{ $stat['label'] }}</p>
                                <p class="mt-1 text-2xl font-bold text-slate-950">
                                    @if ($stat['label'] === 'Total Listings')
                                        <span x-text="totalListings">{{ $stat['value'] }}</span>
                                    @elseif ($stat['label'] === 'Approved Listings')
                                        <span x-text="approvedListings">{{ $stat['value'] }}</span>
                                    @elseif ($stat['label'] === 'Pending Listings')
                                        <span x-text="pendingListings">{{ $stat['value'] }}</span>
                                    @elseif ($stat['label'] === 'Draft Listings')
                                        <span x-text="draftListings">{{ $stat['value'] }}</span>
                                    @else
                                        {{ $stat['value'] }}
                                    @endif
                                </p>
                                <p class="mt-1 text-sm text-slate-500">{{ $stat['description'] }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <section class="min-w-0 rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-200 p-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="grid gap-3 sm:grid-cols-[minmax(220px,1fr)_170px] lg:min-w-[520px]">
                        <label class="relative block">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">{!! $uiIcon('search', 'h-5 w-5') !!}</span>
                            <input x-model.debounce.150ms="search" type="search" placeholder="Search by name or location" class="h-11 w-full rounded-xl border-slate-200 bg-white pl-11 pr-4 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500">
                        </label>

                        <div class="relative" @click.outside="statusOpen = false">
                            <button type="button" @click="statusOpen = ! statusOpen" class="flex h-11 w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 shadow-sm transition hover:bg-slate-50">
                                <span x-text="status === 'All' ? 'All Status' : status">All Status</span>
                                <span class="text-slate-500">{!! $uiIcon('chevron-down', 'h-4 w-4') !!}</span>
                            </button>
                            <div x-show="statusOpen" x-transition style="display: none;" class="absolute left-0 top-[calc(100%+0.35rem)] z-30 w-full overflow-hidden rounded-xl border border-slate-200 bg-white py-2 shadow-xl">
                                @foreach (['All', 'Draft', 'Pending', 'Approved', 'Rejected'] as $option)
                                    <button type="button" @click="status = @js($option); statusOpen = false" class="flex w-full items-center justify-between px-4 py-2.5 text-left text-sm text-slate-800 transition hover:bg-slate-50">
                                        <span>{{ $option }}</span>
                                        <span x-show="status === @js($option)" class="text-blue-700">{!! $uiIcon('check', 'h-4 w-4') !!}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="view = 'card'" :class="view === 'card' ? 'border-blue-600 text-blue-700 bg-blue-50' : 'border-slate-200 text-slate-700 bg-white hover:bg-slate-50'" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border px-4 text-sm font-semibold transition">
                            {!! $uiIcon('grid', 'h-4 w-4') !!}
                            <span>Card View</span>
                        </button>
                        <button type="button" @click="view = 'table'" :class="view === 'table' ? 'border-blue-600 text-blue-700 bg-blue-50' : 'border-slate-200 text-slate-700 bg-white hover:bg-slate-50'" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border px-4 text-sm font-semibold transition">
                            {!! $uiIcon('list', 'h-4 w-4') !!}
                            <span>Table View</span>
                        </button>
                    </div>
                </div>

                <div x-show="view === 'table'" class="hidden overflow-x-auto lg:block">
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
                        <tbody class="divide-y divide-slate-200">
                            <template x-for="listing in visibleLocalListings()" :key="listing.id">
                                    <tr class="transition hover:bg-slate-50/80 bg-blue-50/60 shadow-[inset_4px_0_0_#2563eb]">
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
                                        <td class="max-w-[260px] px-5 py-4 text-slate-600" x-text="listing.address"></td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex rounded-lg px-3 py-1 text-xs font-bold ring-1" :class="badgeClass(listing.status)" x-text="listing.status"></span>
                                        </td>
                                        <td class="px-5 py-4 text-slate-700">
                                            <p x-text="listing.phone"></p>
                                            <p class="text-slate-500" x-text="listing.email"></p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button" @click="openListingModal('edit', listing)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" title="Edit listing details">{!! $uiIcon('pencil', 'h-4 w-4') !!}</button>
                                                <button type="button" @click="openListingModal('delete', listing)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700" title="Delete listing">{!! $uiIcon('trash', 'h-4 w-4') !!}</button>
                                                <button type="button" @click="openListingModal('view', listing)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" title="View listing details">{!! $uiIcon('eye', 'h-4 w-4') !!}</button>
                                                <button type="button" @click="openListingModal('submit', listing)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-blue-700 transition hover:border-blue-200 hover:bg-blue-50" title="Submit listing for approval">{!! $uiIcon('send', 'h-4 w-4') !!}</button>
                                            </div>
                                        </td>
                                    </tr>
                            </template>
                            @foreach ($listings as $listing)
                                @php($listingPayload = array_merge(['amenities' => $amenities, 'rules' => $rules, 'photos' => $photoTiles, 'previewUrl' => '#'], $listing))
                                <tr x-show="matches(@js($listing['name']), @js($listing['address']), @js($listing['status']))" class="transition hover:bg-slate-50/80 {{ $loop->first ? 'bg-blue-50/60 shadow-[inset_4px_0_0_#2563eb]' : '' }}">
                                    <td class="px-5 py-4">
                                        <span class="relative block h-16 w-20 overflow-hidden rounded-xl bg-gradient-to-br {{ $listing['photoClass'] }} shadow-inner">
                                            <span class="absolute inset-x-2 bottom-2 h-9 rounded bg-white/75 shadow-sm"></span>
                                            <span class="absolute left-3 top-5 h-7 w-4 rounded-sm bg-slate-700/20"></span>
                                            <span class="absolute left-8 top-4 h-8 w-4 rounded-sm bg-slate-700/15"></span>
                                            <span class="absolute right-3 top-6 h-6 w-5 rounded-sm bg-slate-700/20"></span>
                                            <span class="absolute inset-x-3 bottom-2 h-1 rounded-full bg-slate-700/20"></span>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-950">{{ $listing['name'] }}</p>
                                    </td>
                                    <td class="max-w-[260px] px-5 py-4 text-slate-600">{{ $listing['address'] }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-lg px-3 py-1 text-xs font-bold ring-1 {{ $statusClasses[$listing['status']] }}">
                                            {{ $listing['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-slate-700">
                                        <p>{{ $listing['phone'] }}</p>
                                        <p class="text-slate-500">{{ $listing['email'] }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button" @click="openListingModal('edit', @js($listingPayload))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" title="Edit listing details">
                                                {!! $uiIcon('pencil', 'h-4 w-4') !!}
                                            </button>
                                            <button type="button" @click="openListingModal('delete', @js($listingPayload))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700" title="Delete listing">
                                                {!! $uiIcon('trash', 'h-4 w-4') !!}
                                            </button>
                                            <button type="button" @click="openListingModal('view', @js($listingPayload))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" title="Preview public listing page">
                                                {!! $uiIcon('eye', 'h-4 w-4') !!}
                                            </button>
                                            <button type="button" @click="openListingModal('submit', @js($listingPayload))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-blue-700 transition hover:border-blue-200 hover:bg-blue-50" title="Submit listing for approval">
                                                {!! $uiIcon('send', 'h-4 w-4') !!}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @if (! empty($listing['rejection']))
                                    <tr x-show="matches(@js($listing['name']), @js($listing['address']), @js($listing['status']))">
                                        <td colspan="6" class="px-5 pb-4">
                                            <div class="flex flex-col gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 sm:flex-row sm:items-center sm:justify-between">
                                                <div class="flex items-start gap-3">
                                                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-600 text-white">
                                                        {!! $uiIcon('info', 'h-3.5 w-3.5') !!}
                                                    </span>
                                                    <p><span class="font-bold">Rejection Reason:</span> {{ $listing['rejection'] }}</p>
                                                </div>
                                                <button type="button" @click="openListingModal('view', @js($listingPayload))" class="font-semibold text-blue-700 hover:text-blue-800">View Details</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div x-show="view === 'table'" class="grid gap-4 p-4 lg:hidden">
                    <template x-for="listing in visibleLocalListings()" :key="`mobile-local-${listing.id}`">
                        <article class="rounded-2xl border border-blue-200 bg-blue-50/40 p-4 shadow-sm">
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
                                    <p class="mt-2 text-sm text-slate-700"><span x-text="listing.phone"></span> | <span x-text="listing.email"></span></p>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <button type="button" @click="openListingModal('edit', listing)" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700">Edit</button>
                                <button type="button" @click="openListingModal('view', listing)" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700">Preview</button>
                                <button type="button" @click="openListingModal('submit', listing)" class="inline-flex h-9 items-center gap-2 rounded-lg border border-blue-200 bg-white px-3 text-sm font-semibold text-blue-700">Submit</button>
                            </div>
                        </article>
                    </template>
                    @foreach ($listings as $listing)
                        @php($listingPayload = array_merge(['amenities' => $amenities, 'rules' => $rules, 'photos' => $photoTiles, 'previewUrl' => '#'], $listing))
                        <article x-show="matches(@js($listing['name']), @js($listing['address']), @js($listing['status']))" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex gap-4">
                                <span class="relative block h-16 w-20 shrink-0 overflow-hidden rounded-xl bg-gradient-to-br {{ $listing['photoClass'] }} shadow-inner">
                                    <span class="absolute inset-x-2 bottom-2 h-9 rounded bg-white/75 shadow-sm"></span>
                                    <span class="absolute left-3 top-5 h-7 w-4 rounded-sm bg-slate-700/20"></span>
                                    <span class="absolute left-8 top-4 h-8 w-4 rounded-sm bg-slate-700/15"></span>
                                    <span class="absolute right-3 top-6 h-6 w-5 rounded-sm bg-slate-700/20"></span>
                                    <span class="absolute inset-x-3 bottom-2 h-1 rounded-full bg-slate-700/20"></span>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <h3 class="font-semibold text-slate-950">{{ $listing['name'] }}</h3>
                                        <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClasses[$listing['status']] }}">{{ $listing['status'] }}</span>
                                    </div>
                                    <p class="mt-2 text-sm text-slate-600">{{ $listing['address'] }}</p>
                                    <p class="mt-2 text-sm text-slate-700">{{ $listing['phone'] }} | {{ $listing['email'] }}</p>
                                </div>
                            </div>
                            @if (! empty($listing['rejection']))
                                <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                                    <span class="font-bold">Rejection Reason:</span> {{ $listing['rejection'] }}
                                </div>
                            @endif
                            <div class="mt-4 flex flex-wrap gap-2">
                                <button type="button" @click="openListingModal('edit', @js($listingPayload))" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700">Edit</button>
                                <button type="button" @click="openListingModal('view', @js($listingPayload))" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700">Preview</button>
                                <button type="button" @click="openListingModal('submit', @js($listingPayload))" class="inline-flex h-9 items-center gap-2 rounded-lg border border-blue-200 px-3 text-sm font-semibold text-blue-700">Submit</button>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div x-show="view === 'card'" class="grid gap-4 p-4 sm:grid-cols-2 xl:grid-cols-3">
                    <template x-for="listing in visibleLocalListings()" :key="`card-local-${listing.id}`">
                        <article class="rounded-2xl border border-blue-200 bg-blue-50/40 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
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
                                <p x-text="listing.phone"></p>
                                <p class="text-slate-500" x-text="listing.email"></p>
                            </div>
                            <div class="mt-4 flex items-center justify-between gap-2">
                                <button type="button" @click="openListingModal('edit', listing)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700">{!! $uiIcon('pencil', 'h-4 w-4') !!}</button>
                                <button type="button" @click="openListingModal('delete', listing)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700">{!! $uiIcon('trash', 'h-4 w-4') !!}</button>
                                <button type="button" @click="openListingModal('view', listing)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700">{!! $uiIcon('eye', 'h-4 w-4') !!}</button>
                                <button type="button" @click="openListingModal('submit', listing)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-blue-200 bg-white text-blue-700">{!! $uiIcon('send', 'h-4 w-4') !!}</button>
                            </div>
                        </article>
                    </template>
                    @foreach ($listings as $listing)
                        @php($listingPayload = array_merge(['amenities' => $amenities, 'rules' => $rules, 'photos' => $photoTiles, 'previewUrl' => '#'], $listing))
                        <article x-show="matches(@js($listing['name']), @js($listing['address']), @js($listing['status']))" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="relative h-36 overflow-hidden rounded-xl bg-gradient-to-br {{ $listing['photoClass'] }} shadow-inner">
                                <span class="absolute inset-x-6 bottom-4 h-20 rounded-xl bg-white/75 shadow-sm"></span>
                                <span class="absolute left-8 top-12 h-16 w-9 rounded bg-slate-700/20"></span>
                                <span class="absolute left-20 top-9 h-20 w-10 rounded bg-slate-700/15"></span>
                                <span class="absolute right-10 top-14 h-14 w-12 rounded bg-slate-700/20"></span>
                                <span class="absolute inset-x-8 bottom-4 h-2 rounded-full bg-slate-700/20"></span>
                            </div>
                            <div class="mt-4 flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-slate-950">{{ $listing['name'] }}</h3>
                                    <p class="mt-1 text-sm text-slate-600">{{ $listing['address'] }}</p>
                                </div>
                                <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClasses[$listing['status']] }}">{{ $listing['status'] }}</span>
                            </div>
                            <div class="mt-4 text-sm text-slate-700">
                                <p>{{ $listing['phone'] }}</p>
                                <p class="text-slate-500">{{ $listing['email'] }}</p>
                            </div>
                            @if (! empty($listing['rejection']))
                                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                                    <span class="font-bold">Rejection Reason:</span> {{ $listing['rejection'] }}
                                </div>
                            @endif
                            <div class="mt-4 flex items-center justify-between gap-2">
                                <button type="button" @click="openListingModal('edit', @js($listingPayload))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-700">{!! $uiIcon('pencil', 'h-4 w-4') !!}</button>
                                <button type="button" @click="openListingModal('delete', @js($listingPayload))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-700">{!! $uiIcon('trash', 'h-4 w-4') !!}</button>
                                <button type="button" @click="openListingModal('view', @js($listingPayload))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-700">{!! $uiIcon('eye', 'h-4 w-4') !!}</button>
                                <button type="button" @click="openListingModal('submit', @js($listingPayload))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-blue-200 text-blue-700">{!! $uiIcon('send', 'h-4 w-4') !!}</button>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="flex flex-col gap-4 border-t border-slate-200 p-4 lg:flex-row lg:items-center lg:justify-between">
                    <p class="text-sm text-slate-600">Showing 1 to 5 of 12 listings</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <nav class="flex items-center gap-2" aria-label="Pagination">
                            <button type="button" class="inline-flex h-9 items-center gap-1 rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-600 hover:bg-slate-50">
                                {!! $uiIcon('chevron-left', 'h-4 w-4') !!}
                                <span class="hidden sm:inline">Previous</span>
                            </button>
                            <button type="button" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-blue-700 px-3 text-sm font-bold text-white">1</button>
                            <button type="button" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 hover:bg-slate-50">2</button>
                            <button type="button" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 hover:bg-slate-50">3</button>
                            <button type="button" class="inline-flex h-9 items-center gap-1 rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-600 hover:bg-slate-50">
                                <span class="hidden sm:inline">Next</span>
                                {!! $uiIcon('chevron-right', 'h-4 w-4') !!}
                            </button>
                        </nav>
                        <label class="relative block">
                            <select class="h-10 appearance-none rounded-xl border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option>5 / page</option>
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">{!! $uiIcon('chevron-down', 'h-4 w-4') !!}</span>
                        </label>
                    </div>
                </div>
            </section>
        </div>

        <div x-show="toast" x-transition style="display: none;" class="fixed bottom-6 right-6 z-50 rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white shadow-2xl" x-text="toast"></div>

        <div x-show="modalType" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" @keydown.escape.window="closeModal()">
            <div @click.outside="closeModal()" class="flex max-h-[85vh] w-full flex-col overflow-hidden rounded-2xl bg-white shadow-2xl" :class="modalType === 'delete' || modalType === 'submit' ? 'max-w-lg' : (modalType === 'add' || modalType === 'edit' ? 'max-w-5xl' : 'max-w-3xl')">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-bold text-slate-950" x-text="modalType === 'add' ? 'Add New Listing' : (modalType === 'edit' ? 'Edit Listing' : (modalType === 'delete' ? 'Delete Listing?' : (modalType === 'submit' ? 'Submit for Approval?' : 'Listing Details')))"></h2>
                        <p class="mt-1 text-sm text-slate-500" x-text="modalType === 'add' ? 'Create a boarding house listing without leaving My Listings.' : selectedListing?.name"></p>
                    </div>
                    <button type="button" @click="closeModal()" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900" aria-label="Close modal">
                        {!! $uiIcon('x', 'h-5 w-5') !!}
                    </button>
                </div>

                <div class="overflow-y-auto px-6 py-5">
                    <div x-show="modalType === 'add'" class="space-y-6 text-sm">
                        <section>
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Basic Information</h3>
                            <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                <label class="block sm:col-span-2">
                                    <span class="font-semibold text-slate-700">Boarding House Name</span>
                                    <input x-model="addListingForm.name" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span x-show="formErrors.name" class="mt-1 block text-xs font-semibold text-rose-600" x-text="formErrors.name"></span>
                                </label>
                                <label class="block sm:col-span-2">
                                    <span class="font-semibold text-slate-700">Address</span>
                                    <input x-model="addListingForm.address" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span x-show="formErrors.address" class="mt-1 block text-xs font-semibold text-rose-600" x-text="formErrors.address"></span>
                                </label>
                                <label class="block sm:col-span-2">
                                    <span class="font-semibold text-slate-700">Description</span>
                                    <textarea x-model="addListingForm.description" rows="4" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                    <span x-show="formErrors.description" class="mt-1 block text-xs font-semibold text-rose-600" x-text="formErrors.description"></span>
                                </label>
                            </div>
                        </section>

                        <section>
                            <div class="flex flex-col gap-1">
                                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Location Picker</h3>
                                <p class="text-sm text-slate-500">Click the map or drag the marker to set the boarding house location.</p>
                            </div>
                            <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                <div id="addListingMap"></div>
                            </div>
                            <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                <label class="block">
                                    <span class="font-semibold text-slate-700">Latitude</span>
                                    <input x-model="addListingForm.latitude" @change="syncMapFromFields()" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span x-show="formErrors.latitude" class="mt-1 block text-xs font-semibold text-rose-600" x-text="formErrors.latitude"></span>
                                </label>
                                <label class="block">
                                    <span class="font-semibold text-slate-700">Longitude</span>
                                    <input x-model="addListingForm.longitude" @change="syncMapFromFields()" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span x-show="formErrors.longitude" class="mt-1 block text-xs font-semibold text-rose-600" x-text="formErrors.longitude"></span>
                                </label>
                            </div>
                        </section>

                        <section>
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Address Details</h3>
                            <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <label class="block">
                                    <span class="font-semibold text-slate-700">Region</span>
                                    <input x-model="addListingForm.region" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </label>
                                <label class="block">
                                    <span class="font-semibold text-slate-700">Province</span>
                                    <input x-model="addListingForm.province" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </label>
                                <label class="block">
                                    <span class="font-semibold text-slate-700">City / Municipality</span>
                                    <input x-model="addListingForm.city" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </label>
                                <label class="block">
                                    <span class="font-semibold text-slate-700">Barangay</span>
                                    <input x-model="addListingForm.barangay" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </label>
                                <label class="block sm:col-span-2 lg:col-span-4">
                                    <span class="font-semibold text-slate-700">Complete Address</span>
                                    <input x-model="addListingForm.address" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span x-show="formErrors.address" class="mt-1 block text-xs font-semibold text-rose-600" x-text="formErrors.address"></span>
                                </label>
                            </div>
                        </section>

                        <section>
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Contact Information</h3>
                            <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                <label class="block">
                                    <span class="font-semibold text-slate-700">Contact Number</span>
                                    <input x-model="addListingForm.contactNumber" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span x-show="formErrors.contactNumber" class="mt-1 block text-xs font-semibold text-rose-600" x-text="formErrors.contactNumber"></span>
                                </label>
                                <label class="block">
                                    <span class="font-semibold text-slate-700">Email Address</span>
                                    <input x-model="addListingForm.email" type="email" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span x-show="formErrors.email" class="mt-1 block text-xs font-semibold text-rose-600" x-text="formErrors.email"></span>
                                </label>
                            </div>
                        </section>

                        <section>
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Amenities</h3>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <template x-for="amenity in availableAmenities" :key="amenity">
                                    <button type="button" @click="toggleAmenity(amenity)" class="rounded-full px-3 py-1.5 text-xs font-bold ring-1 transition" :class="(addListingForm.amenities || []).includes(amenity) ? 'bg-emerald-100 text-emerald-700 ring-emerald-200' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'" x-text="amenity"></button>
                                </template>
                            </div>
                        </section>

                        <section class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_260px]">
                            <label class="block">
                                <span class="text-sm font-bold uppercase tracking-wide text-slate-500">House Rules</span>
                                <textarea x-model="addListingForm.rulesText" rows="5" class="mt-3 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </label>
                            <label class="block">
                                <span class="text-sm font-bold uppercase tracking-wide text-slate-500">Listing Status</span>
                                <select x-model="addListingForm.status" class="mt-3 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option>Draft</option>
                                    <option>Pending</option>
                                </select>
                            </label>
                        </section>

                        <section>
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Photos</h3>
                            <div class="mt-3 grid gap-3 md:grid-cols-[240px_minmax(0,1fr)]">
                                <label class="flex min-h-36 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 text-center text-sm text-slate-500 transition hover:border-blue-300 hover:bg-blue-50">
                                    <input type="file" multiple accept="image/*" class="hidden" @change="handleAddPhotos($event)">
                                    <span class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm">{!! $uiIcon('upload', 'h-6 w-6') !!}</span>
                                    <span>Drag and drop photos here</span>
                                    <span class="text-blue-700">or click to upload</span>
                                </label>
                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                    <template x-for="(url, index) in addListingForm.photoUrls || []" :key="`add-photo-${index}`">
                                        <div class="space-y-1">
                                            <img :src="url" alt="" class="h-24 w-full rounded-xl object-cover">
                                            <p class="truncate text-xs text-slate-500" x-text="addListingForm.photoNames[index]"></p>
                                        </div>
                                    </template>
                                    <p x-show="!(addListingForm.photoUrls || []).length" class="col-span-full rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Selected photo names or thumbnails will appear here.</p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div x-show="modalType === 'view'" class="space-y-5 text-sm">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-2xl font-bold text-slate-950" x-text="selectedListing?.name"></p>
                                <p class="mt-2 max-w-2xl text-slate-600" x-text="selectedListing?.address"></p>
                            </div>
                            <span class="inline-flex w-fit rounded-lg px-3 py-1 text-xs font-bold ring-1" :class="badgeClass(selectedListing?.status)" x-text="selectedListing?.status"></span>
                        </div>
                        <div class="grid gap-5 lg:grid-cols-[220px_minmax(0,1fr)]">
                            <div class="relative h-40 overflow-hidden rounded-2xl bg-gradient-to-br shadow-inner" :class="selectedListing?.photoClass">
                                <span class="absolute inset-x-8 bottom-5 h-24 rounded-xl bg-white/75 shadow-sm"></span>
                                <span class="absolute left-10 top-14 h-16 w-9 rounded bg-slate-700/20"></span>
                                <span class="absolute right-10 top-16 h-14 w-12 rounded bg-slate-700/20"></span>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <p class="font-semibold text-slate-700">Description</p>
                                    <p class="mt-1 leading-6 text-slate-700" x-text="selectedListing?.description || 'No description provided.'"></p>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <p class="font-semibold text-slate-700">Contact Number</p>
                                        <p class="mt-1 text-slate-900" x-text="selectedListing?.phone"></p>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-700">Email Address</p>
                                        <p class="mt-1 text-blue-700" x-text="selectedListing?.email"></p>
                                    </div>
                                </div>
                                <div x-show="selectedListing?.latitude || selectedListing?.longitude" class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <p class="font-semibold text-slate-700">Latitude</p>
                                        <p class="mt-1 text-slate-900" x-text="selectedListing?.latitude || 'Not set'"></p>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-700">Longitude</p>
                                        <p class="mt-1 text-slate-900" x-text="selectedListing?.longitude || 'Not set'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div x-show="selectedListing?.rejection" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
                            <span class="font-bold">Rejection Reason:</span>
                            <span x-text="selectedListing?.rejection"></span>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-700">Amenities</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <template x-for="amenity in selectedListing?.amenities || []" :key="amenity">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        <span x-text="amenity"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-700">House Rules</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5 text-slate-700">
                                <template x-for="rule in selectedListing?.rules || []" :key="rule">
                                    <li x-text="rule"></li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <div x-show="modalType === 'edit'" class="grid gap-4 text-sm sm:grid-cols-2">
                        <label class="block sm:col-span-2">
                            <span class="font-semibold text-slate-700">Boarding House Name</span>
                            <input x-model="editListing.name" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="font-semibold text-slate-700">Address</span>
                            <input x-model="editListing.address" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="font-semibold text-slate-700">Description</span>
                            <textarea x-model="editListing.description" rows="4" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </label>
                        <label class="block">
                            <span class="font-semibold text-slate-700">Contact Number</span>
                            <input x-model="editListing.phone" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </label>
                        <label class="block">
                            <span class="font-semibold text-slate-700">Email Address</span>
                            <input x-model="editListing.email" type="email" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </label>
                        <label class="block">
                            <span class="font-semibold text-slate-700">Latitude</span>
                            <input x-model="editListing.latitude" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </label>
                        <label class="block">
                            <span class="font-semibold text-slate-700">Longitude</span>
                            <input x-model="editListing.longitude" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </label>
                        <label class="block">
                            <span class="font-semibold text-slate-700">Listing Status</span>
                            <select x-model="editListing.status" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option>Draft</option>
                                <option>Pending</option>
                                <option>Approved</option>
                                <option>Rejected</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="font-semibold text-slate-700">Amenities</span>
                            <input x-model="editListing.amenitiesText" type="text" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="font-semibold text-slate-700">House Rules</span>
                            <textarea x-model="editListing.rulesText" rows="3" class="mt-2 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </label>
                    </div>

                    <div x-show="modalType === 'delete'" class="text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-100 text-rose-700">
                            {!! $uiIcon('warning', 'h-7 w-7') !!}
                        </div>
                        <p class="mt-4 text-sm leading-6 text-slate-600">Are you sure you want to delete this listing? This action cannot be undone.</p>
                        <p class="mt-3 rounded-xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950" x-text="selectedListing?.name"></p>
                    </div>

                    <div x-show="modalType === 'submit'" class="text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-700">
                            {!! $uiIcon('send', 'h-7 w-7') !!}
                        </div>
                        <p class="mt-4 text-sm leading-6 text-slate-600">Submit this listing for approval review. You can keep editing it later if the backend requires revisions.</p>
                        <p class="mt-3 rounded-xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950" x-text="selectedListing?.name"></p>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:justify-end">
                    <button type="button" @click="closeModal()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</button>
                    <button x-show="modalType === 'add'" type="button" @click="createListing('Draft', 'Listing saved as draft.')" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Save as Draft</button>
                    <button x-show="modalType === 'add'" type="button" @click="createListing(null, 'Listing created.')" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white transition hover:bg-blue-800">Create Listing</button>
                    <button x-show="modalType === 'add'" type="button" @click="createListing('Pending', 'Listing created and submitted for approval.')" class="inline-flex h-10 items-center justify-center rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white transition hover:bg-emerald-700">Submit for Approval</button>
                    <button x-show="modalType === 'view'" type="button" @click="openListingModal('edit', selectedListing)" class="inline-flex h-10 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">Edit Listing</button>
                    <button x-show="modalType === 'view'" type="button" @click="confirmAction('Preview opened as a placeholder.')" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white transition hover:bg-blue-800">Preview Public Listing</button>
                    <button x-show="modalType === 'edit'" type="button" @click="saveEdit('Draft', 'Listing saved as draft.')" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Save as Draft</button>
                    <button x-show="modalType === 'edit'" type="button" @click="saveEdit(null, 'Listing changes saved locally.')" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white transition hover:bg-blue-800">Save Changes</button>
                    <button x-show="modalType === 'edit'" type="button" @click="saveEdit('Pending', 'Listing submitted for approval.')" class="inline-flex h-10 items-center justify-center rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white transition hover:bg-emerald-700">Submit for Approval</button>
                    <button x-show="modalType === 'delete'" type="button" @click="deleteSelectedListing()" class="inline-flex h-10 items-center justify-center rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white transition hover:bg-rose-700">Delete Listing</button>
                    <button x-show="modalType === 'submit'" type="button" @click="submitSelectedListing()" class="inline-flex h-10 items-center justify-center rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white transition hover:bg-emerald-700">Submit for Approval</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
</x-admin.workspace-shell>
