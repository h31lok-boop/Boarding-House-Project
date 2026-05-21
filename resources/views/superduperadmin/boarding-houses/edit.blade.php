<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />

@php
    $roleUiLabel = fn (?string $role) => match (strtolower((string) $role)) {
        'superduperadmin', 'owner' => 'Owner',
        'admin', 'caretaker', 'manager' => 'Caretaker',
        'tenant', 'user', 'student' => 'Tenant/Student',
        'validator', 'osas' => 'OSAS',
        default => ucfirst((string) ($role ?: 'User')),
    };
@endphp

<x-admin.workspace-shell
    workspace="superduperadmin"
    title="Owner Workspace"
    subtitle="Owner editing workspace for updating boarding house details, geotagging, and listing media."
    profile-role-label="Owner"
    active="table">
    <x-slot name="actions">
        <a href="{{ route('superduperadmin.boarding-houses.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl border ui-border bg-[color:var(--surface)] px-4 text-sm font-semibold text-[color:var(--text)] no-underline transition hover:bg-[color:var(--surface-2)]">
            Back to Listing Table
        </a>
    </x-slot>

    <style>
        .owner-edit-card { border: 1px solid rgba(231, 224, 216, 0.9); background: rgba(255, 255, 255, 0.88); box-shadow: 0 18px 36px rgba(26, 18, 15, 0.08); border-radius: 1.5rem; }
        [data-theme='dark'] .owner-edit-card { border-color: rgba(42, 34, 30, 0.92); background: rgba(23, 19, 17, 0.94); }
        .owner-edit-label { display: block; margin-bottom: 0.42rem; color: var(--muted); font-size: 0.76rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; }
        .owner-edit-input, .owner-edit-select, .owner-edit-textarea { width: 100%; min-height: 2.95rem; border-radius: 1rem; border: 1px solid var(--border); background: var(--surface); color: var(--text); padding: 0.8rem 0.95rem; font: inherit; }
        .owner-edit-textarea { min-height: 6rem; resize: vertical; }
        .owner-edit-input:focus, .owner-edit-select:focus, .owner-edit-textarea:focus { outline: none; border-color: rgba(242, 105, 74, 0.42); box-shadow: 0 0 0 4px rgba(255, 126, 95, 0.14); }
        #editMap { height: 420px; border-radius: 1.25rem; overflow: hidden; }
    </style>

    <section class="owner-edit-card p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] ui-muted">Owner Workspace / Edit Listing</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-[color:var(--text)]">Edit Boarding House</h2>
                <p class="mt-1 text-sm ui-muted">{{ $house->name }}</p>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('superduperadmin.boarding-houses.update', $house) }}" enctype="multipart/form-data" class="grid gap-5 xl:grid-cols-[minmax(0,1.2fr)_minmax(340px,1fr)]">
        @csrf
        @method('PUT')

        <section class="owner-edit-card p-5">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-[color:var(--text)]">Owner Map Geotagging</h3>
                <p class="mt-1 text-sm ui-muted">Click the map or drag the marker to update the listing coordinates.</p>
            </div>
            <div class="overflow-hidden rounded-[1.35rem] border ui-border">
                <div id="editMap"></div>
            </div>
        </section>

        <section class="owner-edit-card p-5">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-[color:var(--text)]">Listing Details</h3>
                <p class="mt-1 text-sm ui-muted">Update ownership, location, pricing, media, and listing status from the Owner workspace.</p>
            </div>

            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12">
                    <label class="owner-edit-label">Assign Owner</label>
                    <select name="owner_user_id" class="owner-edit-select">
                        <option value="">Current Owner</option>
                        @foreach($ownersAndManagers as $manager)
                            <option value="{{ $manager->id }}" @selected(old('owner_user_id', $house->owner_id) == $manager->id)>
                                {{ $manager->name }} - {{ $roleUiLabel($manager->role) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-12">
                    <label class="owner-edit-label">Name</label>
                    <input type="text" name="name" class="owner-edit-input" value="{{ old('name', $house->name) }}" required>
                </div>

                <div class="col-span-12">
                    <label class="owner-edit-label">Address</label>
                    <input type="text" id="addressField" name="address" class="owner-edit-input" value="{{ old('address', $house->address) }}" required>
                </div>

                <div class="col-span-12">
                    <label class="owner-edit-label">Description</label>
                    <textarea name="description" class="owner-edit-textarea">{{ old('description', $house->description) }}</textarea>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-edit-label">Latitude</label>
                    <input type="text" id="latitudeField" name="latitude" class="owner-edit-input" value="{{ old('latitude', $house->latitude) }}" required>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-edit-label">Longitude</label>
                    <input type="text" id="longitudeField" name="longitude" class="owner-edit-input" value="{{ old('longitude', $house->longitude) }}" required>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-edit-label">Region</label>
                    <select id="regionSelect" name="region_id" class="owner-edit-select">
                        <option value="">Select Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}" @selected(old('region_id', $house->region_id) == $region->id)>{{ $region->region_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-edit-label">Province</label>
                    <select id="provinceSelect" name="province_id" class="owner-edit-select">
                        <option value="">Select Province</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province->id }}" data-region="{{ $province->region_id }}" @selected(old('province_id', $house->province_id) == $province->id)>{{ $province->province_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-edit-label">City / Municipality</label>
                    <select id="citySelect" name="city_id" class="owner-edit-select">
                        <option value="">Select City</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" data-province="{{ $city->province_id }}" @selected(old('city_id', $house->city_id) == $city->id)>{{ $city->city_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-edit-label">Barangay</label>
                    <select id="barangaySelect" name="barangay_id" class="owner-edit-select">
                        <option value="">Select Barangay</option>
                        @foreach($barangays as $barangay)
                            <option value="{{ $barangay->id }}" data-city="{{ $barangay->city_id }}" data-lat="{{ $barangay->latitude }}" data-lng="{{ $barangay->longitude }}" @selected(old('barangay_id', $house->barangay_id) == $barangay->id)>{{ $barangay->barangay_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-edit-label">Price</label>
                    <input type="number" step="0.01" name="price" class="owner-edit-input" value="{{ old('price', $house->price) }}" required>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-edit-label">Available Rooms</label>
                    <input type="number" name="available_rooms" class="owner-edit-input" min="0" value="{{ old('available_rooms', $house->available_rooms) }}" required>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-edit-label">Status</label>
                    <select name="status" class="owner-edit-select" required>
                        @foreach(['draft','pending','approved','rejected','suspended','closed'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $house->status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-edit-label">Contact Number</label>
                    <input type="text" name="contact_number" class="owner-edit-input" value="{{ old('contact_number', $house->contact_number ?: $house->contact_phone) }}">
                </div>

                <div class="col-span-12">
                    <label class="owner-edit-label">Amenities</label>
                    <input type="text" name="amenities" class="owner-edit-input" value="{{ old('amenities', $amenitiesText) }}">
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-edit-label">Primary Image</label>
                    <input type="file" name="image" class="owner-edit-input" accept="image/*">
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-edit-label">Additional Gallery Images</label>
                    <input type="file" name="images[]" class="owner-edit-input" accept="image/*" multiple>
                </div>

                <div class="col-span-12">
                    <button type="submit" class="sa-button w-full">Update Boarding House</button>
                </div>
            </div>
        </section>
    </form>

    <section class="owner-edit-card p-5">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-[color:var(--text)]">Current Images</h3>
            <p class="mt-1 text-sm ui-muted">Existing listing media visible in the Owner workspace.</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @forelse($house->images as $image)
                <div class="overflow-hidden rounded-[1.25rem] border ui-border bg-[color:var(--surface-2)]/70 p-3">
                    <img src="{{ asset('storage/'.$image->image_path) }}" alt="Boarding house image" class="h-40 w-full rounded-xl object-cover">
                    <div class="mt-3 text-sm font-medium text-[color:var(--text)]">{{ $image->is_primary ? 'Primary Image' : 'Gallery Image' }}</div>
                </div>
            @empty
                <div class="col-span-full rounded-[1.25rem] border border-dashed ui-border bg-[color:var(--surface-2)]/60 px-6 py-10 text-center text-sm ui-muted">
                    No images uploaded yet.
                </div>
            @endforelse
        </div>
    </section>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        const map = L.map('editMap').setView([{{ (float) ($house->latitude ?? 6.744) }}, {{ (float) ($house->longitude ?? 125.355) }}], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let marker = L.marker([{{ (float) ($house->latitude ?? 6.744) }}, {{ (float) ($house->longitude ?? 125.355) }}], { draggable: true }).addTo(map);
        marker.on('dragend', function (event) {
            const p = event.target.getLatLng();
            document.getElementById('latitudeField').value = p.lat.toFixed(8);
            document.getElementById('longitudeField').value = p.lng.toFixed(8);
            reverseGeocode(p.lat, p.lng);
        });
        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            document.getElementById('latitudeField').value = e.latlng.lat.toFixed(8);
            document.getElementById('longitudeField').value = e.latlng.lng.toFixed(8);
            reverseGeocode(e.latlng.lat, e.latlng.lng);
        });

        const provinceOptions = Array.from(document.querySelectorAll('#provinceSelect option[data-region]'));
        const cityOptions = Array.from(document.querySelectorAll('#citySelect option[data-province]'));
        const barangayOptions = Array.from(document.querySelectorAll('#barangaySelect option[data-city]'));

        function filterOptions(selectId, options, attr, value, placeholder) {
            const select = document.getElementById(selectId);
            const current = select.value;
            select.innerHTML = `<option value="">${placeholder}</option>`;
            options
                .filter(option => !value || option.dataset[attr] === String(value))
                .forEach(option => select.appendChild(option.cloneNode(true)));

            if ([...select.options].some(option => option.value === current)) {
                select.value = current;
            }
        }

        document.getElementById('regionSelect').addEventListener('change', (e) => {
            filterOptions('provinceSelect', provinceOptions, 'region', e.target.value, 'Select Province');
            filterOptions('citySelect', cityOptions, 'province', '', 'Select City');
            filterOptions('barangaySelect', barangayOptions, 'city', '', 'Select Barangay');
        });
        document.getElementById('provinceSelect').addEventListener('change', (e) => {
            filterOptions('citySelect', cityOptions, 'province', e.target.value, 'Select City');
            filterOptions('barangaySelect', barangayOptions, 'city', '', 'Select Barangay');
        });
        document.getElementById('citySelect').addEventListener('change', (e) => {
            filterOptions('barangaySelect', barangayOptions, 'city', e.target.value, 'Select Barangay');
        });
        document.getElementById('barangaySelect').addEventListener('change', (e) => {
            const option = e.target.selectedOptions[0];
            const lat = option?.dataset?.lat;
            const lng = option?.dataset?.lng;
            if (!lat || !lng) return;

            document.getElementById('latitudeField').value = Number(lat).toFixed(8);
            document.getElementById('longitudeField').value = Number(lng).toFixed(8);
            marker.setLatLng([Number(lat), Number(lng)]);
            map.setView([Number(lat), Number(lng)], 15);
            reverseGeocode(Number(lat), Number(lng));
        });

        function reverseGeocode(lat, lng) {
            const addressField = document.getElementById('addressField');
            if (!addressField) return;

            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                .then((response) => response.ok ? response.json() : Promise.reject())
                .then((payload) => {
                    if (!payload?.display_name) return;
                    if ((addressField.value || '').trim() === '' || addressField.dataset.autofill === '1') {
                        addressField.value = payload.display_name;
                        addressField.dataset.autofill = '1';
                    }
                })
                .catch(() => {});
        }

        if (document.getElementById('regionSelect').value) {
            document.getElementById('regionSelect').dispatchEvent(new Event('change'));
        }
        if (document.getElementById('provinceSelect').value) {
            document.getElementById('provinceSelect').dispatchEvent(new Event('change'));
        }
        if (document.getElementById('citySelect').value) {
            document.getElementById('citySelect').dispatchEvent(new Event('change'));
        }
    </script>
</x-admin.workspace-shell>
