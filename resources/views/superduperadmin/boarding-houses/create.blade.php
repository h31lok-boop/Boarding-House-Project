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
    title="Create Listing"
    subtitle="Open the Add Boarding House form on its own page and save it under the current owner workflow."
    profile-role-label="Owner"
    active="create">
    <x-slot name="actions">
        <a href="{{ route('superduperadmin.boarding-houses.index', [], false) }}" class="sa-button-secondary">Listing Table</a>
    </x-slot>

    <style>
        .owner-create-card { border: 1px solid rgba(231, 224, 216, 0.9); background: rgba(255, 255, 255, 0.88); box-shadow: 0 18px 36px rgba(26, 18, 15, 0.08); border-radius: 1.5rem; }
        [data-theme='dark'] .owner-create-card { border-color: rgba(42, 34, 30, 0.92); background: rgba(23, 19, 17, 0.94); }
        .owner-create-label { display: block; margin-bottom: 0.42rem; color: var(--muted); font-size: 0.76rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; }
        .owner-create-input, .owner-create-select, .owner-create-textarea { width: 100%; min-height: 2.95rem; border-radius: 1rem; border: 1px solid var(--border); background: var(--surface); color: var(--text); padding: 0.8rem 0.95rem; font: inherit; }
        .owner-create-textarea { min-height: 6rem; resize: vertical; }
        .owner-create-input:focus, .owner-create-select:focus, .owner-create-textarea:focus { outline: none; border-color: rgba(242, 105, 74, 0.42); box-shadow: 0 0 0 4px rgba(255, 126, 95, 0.14); }
        #createMap { height: 520px; border-radius: 1.25rem; overflow: hidden; }
        .leaflet-popup-content-wrapper, .leaflet-popup-tip { background: var(--surface); color: var(--text); border: 1px solid var(--border); }
    </style>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.2fr)_minmax(360px,1fr)]">
        <section class="owner-create-card p-5">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-[color:var(--text)]">Location Picker</h2>
                <p class="mt-1 text-sm ui-muted">Click the map or drag the marker to geotag the new listing before you save it.</p>
            </div>

            <div class="overflow-hidden rounded-[1.35rem] border ui-border">
                <div id="createMap"></div>
            </div>
        </section>

        <section class="owner-create-card p-5">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-[color:var(--text)]">Add Boarding House</h2>
                <p class="mt-1 text-sm ui-muted">Create a listing without leaving the dedicated Create Listing page.</p>
            </div>

            <form method="POST" action="{{ route('superduperadmin.boarding-houses.store') }}" enctype="multipart/form-data" class="grid grid-cols-12 gap-4">
                @csrf

                <div class="col-span-12">
                    <label class="owner-create-label" for="owner_user_id">Assign Owner</label>
                    <select id="owner_user_id" name="owner_user_id" class="owner-create-select">
                        <option value="">Current owner account</option>
                        @foreach($ownersAndManagers as $manager)
                            <option value="{{ $manager->id }}" @selected(old('owner_user_id') == $manager->id)>{{ $manager->name }} - {{ $roleUiLabel($manager->role) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-12">
                    <label class="owner-create-label" for="name">Boarding House Name</label>
                    <input type="text" id="name" name="name" class="owner-create-input" value="{{ old('name') }}" required>
                </div>

                <div class="col-span-12">
                    <label class="owner-create-label" for="addressField">Address</label>
                    <input type="text" id="addressField" name="address" class="owner-create-input" value="{{ old('address') }}" required>
                </div>

                <div class="col-span-12">
                    <label class="owner-create-label" for="description">Description / Specs</label>
                    <textarea id="description" name="description" class="owner-create-textarea">{{ old('description') }}</textarea>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-create-label" for="regionSelect">Region</label>
                    <select id="regionSelect" name="region_id" class="owner-create-select">
                        <option value="">Select Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}" @selected(old('region_id') == $region->id)>{{ $region->region_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-create-label" for="provinceSelect">Province</label>
                    <select id="provinceSelect" name="province_id" class="owner-create-select">
                        <option value="">Select Province</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province->id }}" data-region="{{ $province->region_id }}" @selected(old('province_id') == $province->id)>{{ $province->province_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-create-label" for="citySelect">City</label>
                    <select id="citySelect" name="city_id" class="owner-create-select">
                        <option value="">Select City</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" data-province="{{ $city->province_id }}" @selected(old('city_id') == $city->id)>{{ $city->city_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-create-label" for="barangaySelect">Barangay</label>
                    <select id="barangaySelect" name="barangay_id" class="owner-create-select">
                        <option value="">Select Barangay</option>
                        @foreach($barangays as $barangay)
                            <option value="{{ $barangay->id }}" data-city="{{ $barangay->city_id }}" data-lat="{{ $barangay->latitude }}" data-lng="{{ $barangay->longitude }}" @selected(old('barangay_id') == $barangay->id)>{{ $barangay->barangay_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-create-label" for="latitudeField">Latitude</label>
                    <input type="text" id="latitudeField" name="latitude" class="owner-create-input" value="{{ old('latitude') }}" required>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-create-label" for="longitudeField">Longitude</label>
                    <input type="text" id="longitudeField" name="longitude" class="owner-create-input" value="{{ old('longitude') }}" required>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-create-label" for="price">Price</label>
                    <input type="number" step="0.01" id="price" name="price" class="owner-create-input" value="{{ old('price') }}" required>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-create-label" for="available_rooms">Available Rooms</label>
                    <input type="number" id="available_rooms" name="available_rooms" class="owner-create-input" value="{{ old('available_rooms', 0) }}" min="0" required>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-create-label" for="contact_number">Contact Number</label>
                    <input type="text" id="contact_number" name="contact_number" class="owner-create-input" value="{{ old('contact_number') }}">
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-create-label" for="status">Status</label>
                    <select id="status" name="status" class="owner-create-select" required>
                        @foreach($statusKeys as $status)
                            <option value="{{ $status }}" @selected(old('status', 'pending') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-12">
                    <label class="owner-create-label" for="amenities">Amenities</label>
                    <input type="text" id="amenities" name="amenities" class="owner-create-input" value="{{ old('amenities') }}" placeholder="WiFi, Laundry, Parking">
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-create-label" for="image">Primary Image</label>
                    <input type="file" id="image" name="image" class="owner-create-input" accept="image/*">
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="owner-create-label" for="images">Gallery Images</label>
                    <input type="file" id="images" name="images[]" class="owner-create-input" accept="image/*" multiple>
                </div>

                <div class="col-span-12">
                    <button type="submit" class="sa-button w-full">Save Boarding House</button>
                </div>
            </form>
        </section>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        (() => {
            const listings = @json($mapHouses);
            const latitudeField = document.getElementById('latitudeField');
            const longitudeField = document.getElementById('longitudeField');
            const addressField = document.getElementById('addressField');
            const markersLayer = L.layerGroup();

            const defaultLat = parseFloat(latitudeField.value || '6.744');
            const defaultLng = parseFloat(longitudeField.value || '125.355');
            const map = L.map('createMap').setView([defaultLat, defaultLng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            markersLayer.addTo(map);

            const popupContent = (listing) => `
                <div style="min-width:220px">
                    <strong>${listing.name}</strong><br>
                    ${listing.address ?? ''}<br>
                    <small>Price: ${listing.price_range ?? 'N/A'}</small><br>
                    <small>Available Rooms: ${listing.available_rooms ?? 0}</small><br>
                    <small>Status: ${listing.status ?? 'N/A'}</small>
                </div>
            `;

            listings.forEach((listing) => {
                if (listing.latitude === null || listing.longitude === null) return;
                L.marker([listing.latitude, listing.longitude]).addTo(markersLayer).bindPopup(popupContent(listing));
            });

            let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

            const reverseGeocode = (lat, lng) => {
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
            };

            const setMarker = (lat, lng) => {
                marker.setLatLng([lat, lng]);
                latitudeField.value = Number(lat).toFixed(8);
                longitudeField.value = Number(lng).toFixed(8);
                reverseGeocode(lat, lng);
            };

            marker.on('dragend', (event) => {
                const point = event.target.getLatLng();
                setMarker(point.lat, point.lng);
            });

            map.on('click', (event) => {
                setMarker(event.latlng.lat, event.latlng.lng);
            });

            const provinceOptions = Array.from(document.querySelectorAll('#provinceSelect option[data-region]'));
            const cityOptions = Array.from(document.querySelectorAll('#citySelect option[data-province]'));
            const barangayOptions = Array.from(document.querySelectorAll('#barangaySelect option[data-city]'));

            const filterOptions = (selectId, options, attr, value, placeholder) => {
                const select = document.getElementById(selectId);
                const current = select.value;
                select.innerHTML = `<option value="">${placeholder}</option>`;
                options
                    .filter((option) => !value || option.dataset[attr] === String(value))
                    .forEach((option) => select.appendChild(option.cloneNode(true)));

                if ([...select.options].some((option) => option.value === current)) {
                    select.value = current;
                }
            };

            document.getElementById('regionSelect')?.addEventListener('change', (event) => {
                filterOptions('provinceSelect', provinceOptions, 'region', event.target.value, 'Select Province');
                filterOptions('citySelect', cityOptions, 'province', '', 'Select City');
                filterOptions('barangaySelect', barangayOptions, 'city', '', 'Select Barangay');
            });

            document.getElementById('provinceSelect')?.addEventListener('change', (event) => {
                filterOptions('citySelect', cityOptions, 'province', event.target.value, 'Select City');
                filterOptions('barangaySelect', barangayOptions, 'city', '', 'Select Barangay');
            });

            document.getElementById('citySelect')?.addEventListener('change', (event) => {
                filterOptions('barangaySelect', barangayOptions, 'city', event.target.value, 'Select Barangay');
            });

            document.getElementById('barangaySelect')?.addEventListener('change', (event) => {
                const option = event.target.selectedOptions[0];
                const lat = option?.dataset?.lat;
                const lng = option?.dataset?.lng;
                if (!lat || !lng) return;

                setMarker(Number(lat), Number(lng));
                map.setView([Number(lat), Number(lng)], 15);
            });
        })();
    </script>
</x-admin.workspace-shell>
