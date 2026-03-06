<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Boarding House</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        #editMap {
            height: 420px;
            border-radius: 0.75rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 mb-1">Edit Boarding House</h1>
                <p class="text-muted mb-0">{{ $house->name }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('superduperadmin.dashboard') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('superduperadmin.boarding-houses.update', $house) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-lg-7">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h2 class="h6 mb-3">Map Geotagging</h2>
                            <p class="text-muted small">Click map or drag marker to update coordinates.</p>
                            <div id="editMap"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label">Assign Owner / Manager</label>
                                    <select name="owner_user_id" class="form-select">
                                        <option value="">Current Owner</option>
                                        @foreach($ownersAndManagers as $manager)
                                            <option value="{{ $manager->id }}" @selected(old('owner_user_id', $house->owner_id) == $manager->id)>
                                                {{ $manager->name }} ({{ $manager->role }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $house->name) }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <input type="text" id="addressField" name="address" class="form-control" value="{{ old('address', $house->address) }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="2">{{ old('description', $house->description) }}</textarea>
                                </div>

                                <div class="col-6">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" id="latitudeField" name="latitude" class="form-control" value="{{ old('latitude', $house->latitude) }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" id="longitudeField" name="longitude" class="form-control" value="{{ old('longitude', $house->longitude) }}" required>
                                </div>

                                <div class="col-6">
                                    <label class="form-label">Region</label>
                                    <select id="regionSelect" name="region_id" class="form-select">
                                        <option value="">Select Region</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->id }}" @selected(old('region_id', $house->region_id) == $region->id)>{{ $region->region_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Province</label>
                                    <select id="provinceSelect" name="province_id" class="form-select">
                                        <option value="">Select Province</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province->id }}" data-region="{{ $province->region_id }}" @selected(old('province_id', $house->province_id) == $province->id)>
                                                {{ $province->province_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">City / Municipality</label>
                                    <select id="citySelect" name="city_id" class="form-select">
                                        <option value="">Select City</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}" data-province="{{ $city->province_id }}" @selected(old('city_id', $house->city_id) == $city->id)>
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
                                                @selected(old('barangay_id', $house->barangay_id) == $barangay->id)
                                            >
                                                {{ $barangay->barangay_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-6">
                                    <label class="form-label">Price</label>
                                    <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $house->price) }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Available Rooms</label>
                                    <input type="number" name="available_rooms" class="form-control" min="0" value="{{ old('available_rooms', $house->available_rooms) }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select" required>
                                        @foreach(['draft','pending','approved','rejected','suspended','closed'] as $status)
                                            <option value="{{ $status }}" @selected(old('status', $house->status) === $status)>{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Contact Number</label>
                                    <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $house->contact_number ?: $house->contact_phone) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Amenities (comma-separated)</label>
                                    <input type="text" name="amenities" class="form-control" value="{{ old('amenities', $amenitiesText) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Primary Image</label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Additional Gallery Images</label>
                                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100">Update Boarding House</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <h2 class="h6 mb-3">Current Images</h2>
                <div class="row g-2">
                    @forelse($house->images as $image)
                        <div class="col-6 col-md-3">
                            <img src="{{ asset('storage/'.$image->image_path) }}" alt="Boarding house image" class="img-fluid rounded border">
                            <div class="small text-muted mt-1">{{ $image->is_primary ? 'Primary' : 'Gallery' }}</div>
                        </div>
                    @empty
                        <div class="col-12 text-muted small">No images uploaded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

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
                .catch(() => {
                    // Keep manual address when reverse geocode fails.
                });
        }

        // Trigger filter setup for existing selected values
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
</body>
</html>
