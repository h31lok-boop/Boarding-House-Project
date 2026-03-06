<x-layouts.caretaker>
<x-tenant.shell>
    <div class="space-y-6">
        <div class="ui-card p-4">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="text-xl font-semibold">Find Boarding Houses Near You</h2>
                    <p class="text-sm ui-muted">Map, filters, prices, specs, and nearest prediction.</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('user.favorites.index') }}" class="px-4 py-2 rounded-lg border ui-border text-sm hover:bg-[color:var(--surface-2)]">My Favorites</a>
                    <button type="button" id="useMyLocation" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700">Use My Location</button>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 rounded-lg bg-rose-50 text-rose-700 text-sm">{{ session('error') }}</div>
        @endif

        @if($nearMe && $nearestHouse)
            <div class="ui-card p-4 border border-emerald-300">
                <h3 class="font-semibold text-emerald-700">Nearest Boarding House Prediction</h3>
                <p class="text-sm mt-1">
                    <span class="font-semibold">{{ $nearestHouse->name }}</span>
                    is currently the nearest result at
                    <span class="font-semibold">{{ number_format((float) $nearestHouse->distance_km, 2) }} km</span>.
                </p>
                <a class="inline-block mt-2 text-sm text-indigo-600" href="{{ route('user.boarding-houses.show', $nearestHouse) }}">View Details</a>
            </div>
        @endif

        <form id="browseFilterForm" method="GET" action="{{ route('user.boarding-houses.index') }}" class="ui-card p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Name, address, keyword" class="ui-input text-sm lg:col-span-2">
            <input type="number" step="0.01" min="0" name="min_price" value="{{ request('min_price') }}" placeholder="Min Price" class="ui-input text-sm">
            <input type="number" step="0.01" min="0" name="max_price" value="{{ request('max_price') }}" placeholder="Max Price" class="ui-input text-sm">
            <input type="number" step="0.000001" id="latField" name="lat" value="{{ request('lat', $referencePoint['lat']) }}" placeholder="Reference Lat" class="ui-input text-sm">
            <input type="number" step="0.000001" id="lngField" name="lng" value="{{ request('lng', $referencePoint['lng']) }}" placeholder="Reference Lng" class="ui-input text-sm">

            <select name="city_id" id="citySelect" class="ui-input text-sm">
                <option value="">All Cities</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" @selected((int) request('city_id') === $city->id)>{{ $city->city_name }}</option>
                @endforeach
            </select>

            <select name="barangay_id" id="barangaySelect" class="ui-input text-sm">
                <option value="">All Barangays</option>
                @foreach($barangays as $barangay)
                    <option value="{{ $barangay->id }}" data-city="{{ $barangay->city_id }}" @selected((int) request('barangay_id') === $barangay->id)>
                        {{ $barangay->barangay_name }}
                    </option>
                @endforeach
            </select>

            <div class="lg:col-span-6">
                <label class="text-xs ui-muted">Amenities</label>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($amenities as $amenity)
                        <label class="inline-flex items-center gap-2 text-xs px-2 py-1 rounded-md border ui-border">
                            <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}" @checked(in_array($amenity->id, (array) request('amenities', [])))>
                            <span>{{ $amenity->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="available_only" value="1" @checked(request()->boolean('available_only'))>
                <span>Available rooms only</span>
            </label>

            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" id="nearMeCheckbox" name="near_me" value="1" @checked($nearMe)>
                <span>Sort by nearest to me</span>
            </label>

            <div class="lg:col-span-4 flex items-center justify-end gap-2">
                <a href="{{ route('user.boarding-houses.index') }}" class="px-3 py-2 rounded-lg border ui-border text-sm">Reset</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">Apply Filters</button>
            </div>
        </form>

        <div class="ui-card p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold">Map View</h3>
                <p class="text-xs ui-muted">Reference: {{ $referencePoint['lat'] }}, {{ $referencePoint['lng'] }}</p>
            </div>
            <p class="text-xs ui-muted mb-2">Click the map or drag the reference marker to geotag your location for nearest results.</p>
            <div id="browseMap" class="w-full rounded-lg border ui-border" style="height: 380px;"></div>
        </div>

        <form method="GET" action="{{ route('user.boarding-houses.compare') }}" class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold">Results</h3>
                <button type="submit" class="px-4 py-2 rounded-lg bg-amber-500 text-white text-sm hover:bg-amber-600">Compare Selected</button>
            </div>

            <input type="hidden" name="lat" value="{{ request('lat', $referencePoint['lat']) }}">
            <input type="hidden" name="lng" value="{{ request('lng', $referencePoint['lng']) }}">

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse($houses as $house)
                    @php
                        $rating = $house->reviews_avg_rating ? number_format($house->reviews_avg_rating, 1) : 'N/A';
                        $image = $house->images->first()?->image_path ? asset('storage/'.$house->images->first()->image_path) : null;
                    @endphp
                    <div class="ui-card p-4 space-y-3">
                        @if($image)
                            <img src="{{ $image }}" alt="{{ $house->name }}" class="w-full h-36 object-cover rounded-lg border ui-border">
                        @endif

                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="font-semibold">{{ $house->name }}</h4>
                                <p class="text-xs ui-muted">{{ $house->address }}</p>
                            </div>
                            <label class="inline-flex items-center gap-2 text-xs">
                                <input type="checkbox" name="ids[]" value="{{ $house->id }}">
                                Compare
                            </label>
                        </div>

                        <div class="text-sm space-y-1">
                            <p>Price: <span class="font-semibold">{{ $house->display_price !== null ? 'PHP '.number_format((float) $house->display_price, 2) : 'N/A' }}</span></p>
                            <p>Distance: <span class="font-semibold">{{ $house->distance_km !== null ? $house->distance_km.' km' : 'N/A' }}</span></p>
                            <p>Amenities: <span class="font-semibold">{{ $house->amenities->pluck('name')->take(3)->join(', ') ?: 'None' }}</span></p>
                            <p>Available Rooms: <span class="font-semibold">{{ max((int)($house->available_rooms ?? 0), (int)($house->available_rooms_count ?? 0), (int)($house->room_categories_available_rooms_sum ?? 0)) }}</span></p>
                            <p>Rating: <span class="font-semibold">{{ $rating }}</span></p>
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('user.boarding-houses.show', $house) }}" class="px-3 py-2 rounded-lg border ui-border text-xs">View Details</a>
                            <form method="POST" action="{{ route('user.favorites.store', $house) }}">
                                @csrf
                                <button type="submit" class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs">Favorite</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="ui-card p-5 md:col-span-2 xl:col-span-3 text-sm ui-muted">
                        No boarding houses match the current filters.
                    </div>
                @endforelse
            </div>
        </form>

        <div>
            {{ $houses->links() }}
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fallbackMarkers = @json($mapHouses);
            const center = [{{ $referencePoint['lat'] }}, {{ $referencePoint['lng'] }}];
            const map = L.map('browseMap').setView(center, 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const referenceMarker = L.marker(center, { draggable: true }).addTo(map).bindPopup('Reference Point / Your Location');

            function setReferencePoint(lat, lng, autoSubmit = false) {
                const latFixed = Number(lat).toFixed(7);
                const lngFixed = Number(lng).toFixed(7);
                document.getElementById('latField').value = latFixed;
                document.getElementById('lngField').value = lngFixed;
                referenceMarker.setLatLng([Number(latFixed), Number(lngFixed)]);
                if (autoSubmit) {
                    document.getElementById('nearMeCheckbox').checked = true;
                    document.getElementById('browseFilterForm').submit();
                }
            }

            referenceMarker.on('dragend', (event) => {
                const point = event.target.getLatLng();
                setReferencePoint(point.lat, point.lng);
            });

            map.on('click', (event) => {
                setReferencePoint(event.latlng.lat, event.latlng.lng);
            });

            const customIcon = L.icon({
                iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
            });

            const listingLayer = L.layerGroup().addTo(map);
            const renderListings = (records) => {
                listingLayer.clearLayers();
                const points = [];

                records.forEach((item) => {
                    const lat = Number(item.latitude);
                    const lng = Number(item.longitude);
                    if (Number.isNaN(lat) || Number.isNaN(lng)) {
                        return;
                    }

                    const marker = L.marker([lat, lng], { icon: customIcon }).addTo(listingLayer);
                    marker.bindPopup(`
                        <div style="min-width:230px">
                            <strong>${item.name}</strong><br>
                            ${item.address ?? ''}<br>
                            <small>Price: ${item.price ? 'PHP ' + Number(item.price).toLocaleString() : 'N/A'}</small><br>
                            <small>Available Rooms: ${item.available_rooms ?? 0}</small><br>
                            <small>Distance: ${item.distance_km ?? 'N/A'} km</small><br>
                            ${item.image_url ? `<img src="${item.image_url}" alt="${item.name}" style="width:100%;height:80px;object-fit:cover;border-radius:6px;margin-top:4px;">` : ''}
                            ${item.url ? `<a href="${item.url}" style="display:inline-block;margin-top:4px;">View Details</a>` : ''}
                        </div>
                    `);
                    points.push([lat, lng]);
                });

                if (points.length > 0) {
                    map.fitBounds(points, { padding: [30, 30] });
                }
            };

            renderListings(fallbackMarkers);

            const mapApiUrl = @json(route('map.user.boarding-houses'));
            const queryParams = new URLSearchParams(window.location.search);
            fetch(`${mapApiUrl}?${queryParams.toString()}`)
                .then((response) => response.ok ? response.json() : Promise.reject(new Error('Map API failed')))
                .then((payload) => {
                    const records = Array.isArray(payload.data) && payload.data.length > 0
                        ? payload.data
                        : fallbackMarkers;
                    renderListings(records);
                })
                .catch(() => {
                    // Keep fallback markers already rendered.
                });

            const citySelect = document.getElementById('citySelect');
            const barangaySelect = document.getElementById('barangaySelect');
            const originalBarangays = Array.from(barangaySelect.querySelectorAll('option[data-city]'));

            function filterBarangays(cityId) {
                const currentValue = barangaySelect.value;
                barangaySelect.innerHTML = '<option value="">All Barangays</option>';
                originalBarangays
                    .filter(option => !cityId || option.dataset.city === String(cityId))
                    .forEach(option => barangaySelect.appendChild(option.cloneNode(true)));
                if ([...barangaySelect.options].some(option => option.value === currentValue)) {
                    barangaySelect.value = currentValue;
                }
            }

            citySelect.addEventListener('change', (e) => {
                filterBarangays(e.target.value);
            });

            if (citySelect.value) {
                filterBarangays(citySelect.value);
            }

            document.getElementById('useMyLocation').addEventListener('click', () => {
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by this browser.');
                    return;
                }

                navigator.geolocation.getCurrentPosition((position) => {
                    setReferencePoint(position.coords.latitude, position.coords.longitude, true);
                }, () => {
                    alert('Unable to retrieve your location. Please allow location permission.');
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                });
            });
        });
    </script>
</x-tenant.shell>
</x-layouts.caretaker>
