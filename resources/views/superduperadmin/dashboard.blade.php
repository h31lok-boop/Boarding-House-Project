<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperDuperAdmin | Boarding House Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        #boardingHouseMap {
            height: 480px;
            border-radius: 0.75rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">SuperDuperAdmin Boarding House Management</h1>
                <p class="text-muted mb-0">Full access: map, geotagging, approvals, and all listings management.</p>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary" href="{{ route('admin.users') }}">Manage Users</a>
                <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}">Main Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Validation errors:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Users</p>
                        <h2 class="h4 mb-0">{{ number_format($totalUsers) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted mb-1">Boarding Houses</p>
                        <h2 class="h4 mb-0">{{ number_format($totalBoardingHouses) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted mb-1">Pending Listings</p>
                        <h2 class="h4 mb-0">{{ number_format($pendingBoardingHouses) }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 mb-0">Leaflet Map (All Boarding Houses)</h2>
                            <small class="text-muted">Click map to geotag coordinates for new listing.</small>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <input type="number" step="0.01" id="filterMinPrice" class="form-control form-control-sm" placeholder="Min price">
                            </div>
                            <div class="col-md-3">
                                <input type="number" step="0.01" id="filterMaxPrice" class="form-control form-control-sm" placeholder="Max price">
                            </div>
                            <div class="col-md-3">
                                <input type="number" id="filterRooms" class="form-control form-control-sm" placeholder="Min rooms">
                            </div>
                            <div class="col-md-3">
                                <select id="filterStatus" class="form-select form-select-sm">
                                    <option value="">Any status</option>
                                    @foreach(['draft','pending','approved','rejected','suspended','closed'] as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <input type="text" id="filterLocation" class="form-control form-control-sm" placeholder="Filter by name or address">
                            </div>
                            <div class="col-md-12 d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-primary" id="applyFilters">Apply Filters</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="resetFilters">Reset</button>
                            </div>
                        </div>

                        <div id="boardingHouseMap"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Add Boarding House</h2>
                        <form method="POST" action="{{ route('superduperadmin.boarding-houses.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-2">
                                <label class="form-label">Assign Owner / Manager</label>
                                <select name="owner_user_id" class="form-select">
                                    <option value="">Current SuperDuperAdmin</option>
                                    @foreach($ownersAndManagers as $manager)
                                        <option value="{{ $manager->id }}" @selected(old('owner_user_id') == $manager->id)>
                                            {{ $manager->name }} ({{ $manager->role }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Boarding House Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Address</label>
                                <input type="text" id="addressField" name="address" class="form-control" value="{{ old('address') }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Description / Specs</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label">Region</label>
                                    <select id="regionSelect" name="region_id" class="form-select">
                                        <option value="">Select Region</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->id }}" @selected(old('region_id') == $region->id)>{{ $region->region_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Province</label>
                                    <select id="provinceSelect" name="province_id" class="form-select">
                                        <option value="">Select Province</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province->id }}" data-region="{{ $province->region_id }}" @selected(old('province_id') == $province->id)>
                                                {{ $province->province_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label">City</label>
                                    <select id="citySelect" name="city_id" class="form-select">
                                        <option value="">Select City</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}" data-province="{{ $city->province_id }}" @selected(old('city_id') == $city->id)>
                                                {{ $city->city_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Barangay</label>
                                    <select id="barangaySelect" name="barangay_id" class="form-select">
                                        <option value="">Select Barangay</option>
                                        @foreach($barangays as $barangay)
                                            <option
                                                value="{{ $barangay->id }}"
                                                data-city="{{ $barangay->city_id }}"
                                                data-lat="{{ $barangay->latitude }}"
                                                data-lng="{{ $barangay->longitude }}"
                                                @selected(old('barangay_id') == $barangay->id)
                                            >
                                                {{ $barangay->barangay_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" id="latitudeField" name="latitude" class="form-control" value="{{ old('latitude') }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" id="longitudeField" name="longitude" class="form-control" value="{{ old('longitude') }}" required>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label">Price</label>
                                    <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price') }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Available Rooms</label>
                                    <input type="number" name="available_rooms" class="form-control" value="{{ old('available_rooms', 0) }}" min="0" required>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number') }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Amenities (comma-separated)</label>
                                <input type="text" name="amenities" class="form-control" value="{{ old('amenities') }}" placeholder="WiFi, Laundry, Parking">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    @foreach(['draft','pending','approved','rejected','suspended','closed'] as $status)
                                        <option value="{{ $status }}" @selected(old('status', 'pending') === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Primary Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Gallery Images</label>
                                <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Save Boarding House</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <h2 class="h5 mb-3">All Boarding Houses</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Price</th>
                                <th>Avail. Rooms</th>
                                <th>Owner</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($boardingHouses as $house)
                                @php
                                    $priceFromRoomsMin = $house->rooms_min_price;
                                    $priceFromRoomsMax = $house->rooms_max_price;
                                    $priceRange = 'N/A';
                                    if ($priceFromRoomsMin !== null && $priceFromRoomsMax !== null) {
                                        $priceRange = $priceFromRoomsMin == $priceFromRoomsMax
                                            ? 'PHP '.number_format((float) $priceFromRoomsMin, 2)
                                            : 'PHP '.number_format((float) $priceFromRoomsMin, 2).' - PHP '.number_format((float) $priceFromRoomsMax, 2);
                                    } elseif ($house->price !== null) {
                                        $priceRange = 'PHP '.number_format((float) $house->price, 2);
                                    }
                                    $loc = collect([$house->barangay?->barangay_name, $house->city?->city_name, $house->province?->province_name])->filter()->implode(', ');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $house->name }}</div>
                                        <div class="small text-muted">{{ $house->address }}</div>
                                    </td>
                                    <td>{{ $loc !== '' ? $loc : 'N/A' }}</td>
                                    <td>{{ $priceRange }}</td>
                                    <td>{{ max((int)($house->available_rooms ?? 0), (int)($house->available_rooms_count ?? 0), (int)($house->room_categories_available_rooms_sum ?? 0)) }}</td>
                                    <td>{{ $house->owner?->name ?? 'N/A' }}</td>
                                    <td><span class="badge text-bg-secondary">{{ $house->status }}</span></td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('superduperadmin.boarding-houses.edit', $house) }}">Edit</a>
                                            <form method="POST" action="{{ route('superduperadmin.boarding-houses.approve', $house) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('superduperadmin.boarding-houses.reject', $house) }}">
                                                @csrf
                                                <input type="hidden" name="remarks" value="Rejected by SuperDuperAdmin dashboard action.">
                                                <button type="submit" class="btn btn-sm btn-warning">Reject</button>
                                            </form>
                                            <form method="POST" action="{{ route('superduperadmin.boarding-houses.destroy', $house) }}" onsubmit="return confirm('Delete this boarding house?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No boarding houses found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div>{{ $boardingHouses->links() }}</div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        const listings = @json($mapHouses);
        const map = L.map('boardingHouseMap').setView([6.744, 125.355], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const markersLayer = L.layerGroup().addTo(map);
        let geotagMarker = null;

        function popupContent(h) {
            return `
                <div style="min-width:220px">
                    <strong>${h.name}</strong><br>
                    ${h.address ?? ''}<br>
                    <small>Price: ${h.price_range ?? 'N/A'}</small><br>
                    <small>Available Rooms: ${h.available_rooms ?? 0}</small><br>
                    <small>Owner/Manager: ${h.owner_name ?? 'N/A'}</small><br>
                    <small>Contact: ${h.contact_number ?? 'N/A'}</small><br>
                    <small>Status: ${h.status ?? 'N/A'}</small>
                </div>
            `;
        }

        function renderMarkers(data) {
            markersLayer.clearLayers();
            const points = [];
            data.forEach((h) => {
                if (h.latitude === null || h.longitude === null) return;
                const marker = L.marker([h.latitude, h.longitude]).addTo(markersLayer);
                marker.bindPopup(popupContent(h));
                points.push([h.latitude, h.longitude]);
            });
            if (points.length > 0) {
                map.fitBounds(points, { padding: [30, 30] });
            }
        }

        function applyFilters() {
            const minPrice = parseFloat(document.getElementById('filterMinPrice').value || '0');
            const maxPriceRaw = document.getElementById('filterMaxPrice').value;
            const maxPrice = maxPriceRaw === '' ? Number.MAX_SAFE_INTEGER : parseFloat(maxPriceRaw);
            const minRooms = parseInt(document.getElementById('filterRooms').value || '0', 10);
            const status = document.getElementById('filterStatus').value.toLowerCase();
            const text = document.getElementById('filterLocation').value.trim().toLowerCase();

            const filtered = listings.filter((h) => {
                const priceText = String(h.price_range ?? '');
                const prices = priceText.match(/[0-9]+(?:,[0-9]{3})*(?:\.[0-9]+)?/g) || [];
                const numericPrices = prices.map(v => parseFloat(v.replace(/,/g, ''))).filter(v => !Number.isNaN(v));
                const basePrice = numericPrices.length > 0 ? Math.min(...numericPrices) : 0;
                const rooms = parseInt(h.available_rooms ?? 0, 10);
                const haystack = `${h.name ?? ''} ${h.address ?? ''}`.toLowerCase();

                return basePrice >= minPrice
                    && basePrice <= maxPrice
                    && rooms >= minRooms
                    && (status === '' || String(h.status).toLowerCase() === status)
                    && (text === '' || haystack.includes(text));
            });

            renderMarkers(filtered);
        }

        document.getElementById('applyFilters').addEventListener('click', applyFilters);
        document.getElementById('resetFilters').addEventListener('click', () => {
            ['filterMinPrice','filterMaxPrice','filterRooms','filterLocation'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('filterStatus').value = '';
            renderMarkers(listings);
        });

        map.on('click', function (e) {
            document.getElementById('latitudeField').value = e.latlng.lat.toFixed(8);
            document.getElementById('longitudeField').value = e.latlng.lng.toFixed(8);
            reverseGeocode(e.latlng.lat, e.latlng.lng);

            if (!geotagMarker) {
                geotagMarker = L.marker(e.latlng, { draggable: true }).addTo(map);
                geotagMarker.on('dragend', function (event) {
                    const p = event.target.getLatLng();
                    document.getElementById('latitudeField').value = p.lat.toFixed(8);
                    document.getElementById('longitudeField').value = p.lng.toFixed(8);
                    reverseGeocode(p.lat, p.lng);
                });
            } else {
                geotagMarker.setLatLng(e.latlng);
            }
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
            reverseGeocode(Number(lat), Number(lng));
            const point = [Number(lat), Number(lng)];
            if (!geotagMarker) {
                geotagMarker = L.marker(point, { draggable: true }).addTo(map);
            } else {
                geotagMarker.setLatLng(point);
            }
            map.setView(point, 15);
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
                .catch(() => {
                    // Keep manual address when reverse geocode fails.
                });
        }

        renderMarkers(listings);
    </script>
</body>
</html>
