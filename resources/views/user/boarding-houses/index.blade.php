<x-layouts.caretaker>
<x-tenant.shell>
    @php
        $r = fn ($name, $params = [], $fallback = null) => \Illuminate\Support\Facades\Route::has($name)
            ? route($name, $params)
            : ($fallback ?? url()->current());

        $browseIndexUrl = $r('tenant.boarding-houses', [], route('user.boarding-houses.index'));
        $compareUrl = $r('tenant.boarding-houses.compare', [], route('user.boarding-houses.compare'));
        $savedListingsUrl = $r('tenant.saved-listings', [], route('user.favorites.index'));
        $resultCount = method_exists($houses, 'total') ? $houses->total() : $houses->count();
    @endphp

    <div class="tenant-listings-page space-y-5">
        <section class="tenant-card overflow-hidden">
            <div class="flex flex-col gap-4 px-5 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-100">
                        Browse Listings
                    </div>
                    <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Find Boarding Houses Near You</h1>
                    <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-600">
                        Compare rooms, prices, amenities, and distance from your reference point.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ $savedListingsUrl }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">My Favorites</a>
                    <button type="button" id="useMyLocation" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">Use My Location</button>
                </div>
            </div>
        </section>

        @if(session('success'))
            <div class="px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 rounded-lg bg-rose-50 text-rose-700 text-sm">{{ session('error') }}</div>
        @endif

        @if($nearMe && $nearestHouse)
            <div class="tenant-card border-emerald-200 bg-emerald-50/70 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="font-bold text-emerald-800">Nearest match</h3>
                        <p class="mt-1 text-sm text-emerald-700">
                            <span class="font-semibold">{{ $nearestHouse->name }}</span>
                            is {{ number_format((float) $nearestHouse->distance_km, 2) }} km from your reference point.
                        </p>
                    </div>
                    <a class="inline-flex w-fit rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" href="{{ route('user.boarding-houses.show', $nearestHouse) }}">View Details</a>
                </div>
            </div>
        @endif

        <section class="tenant-card p-5">
            <form id="browseFilterForm" method="GET" action="{{ $browseIndexUrl }}" class="space-y-5">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
                    <label class="xl:col-span-2">
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Keyword</span>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Name, address, keyword" class="tenant-filter-control">
                    </label>

                    <label>
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Min Price</span>
                        <input type="number" step="0.01" min="0" name="min_price" value="{{ request('min_price') }}" placeholder="PHP" class="tenant-filter-control">
                    </label>

                    <label>
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Max Price</span>
                        <input type="number" step="0.01" min="0" name="max_price" value="{{ request('max_price') }}" placeholder="PHP" class="tenant-filter-control">
                    </label>

                    <label>
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Latitude</span>
                        <input type="number" step="0.000001" id="latField" name="lat" value="{{ request('lat', $referencePoint['lat']) }}" placeholder="Reference Lat" class="tenant-filter-control">
                    </label>

                    <label>
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Longitude</span>
                        <input type="number" step="0.000001" id="lngField" name="lng" value="{{ request('lng', $referencePoint['lng']) }}" placeholder="Reference Lng" class="tenant-filter-control">
                    </label>

                    <label>
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">City</span>
                        <select name="city_id" id="citySelect" class="tenant-filter-control">
                            <option value="">All Cities</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" @selected((int) request('city_id') === $city->id)>{{ $city->city_name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Barangay</span>
                        <select name="barangay_id" id="barangaySelect" class="tenant-filter-control">
                            <option value="">All Barangays</option>
                            @foreach($barangays as $barangay)
                                <option value="{{ $barangay->id }}" data-city="{{ $barangay->city_id }}" @selected((int) request('barangay_id') === $barangay->id)>
                                    {{ $barangay->barangay_name }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div>
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-sm font-bold text-slate-800">Amenities</h2>
                        <p class="text-xs font-medium text-slate-500">{{ count((array) request('amenities', [])) }} selected</p>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($amenities as $amenity)
                            @php $checked = in_array($amenity->id, (array) request('amenities', [])); @endphp
                            <label class="tenant-amenity-chip {{ $checked ? 'is-selected' : '' }}">
                                <input class="sr-only" type="checkbox" name="amenities[]" value="{{ $amenity->id }}" @checked($checked)>
                                <span class="tenant-amenity-box"></span>
                                <span>{{ $amenity->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-wrap gap-3">
                        <label class="tenant-toggle-chip">
                            <input type="checkbox" name="available_only" value="1" @checked(request()->boolean('available_only'))>
                            <span>Available rooms only</span>
                        </label>

                        <label class="tenant-toggle-chip">
                            <input type="checkbox" id="nearMeCheckbox" name="near_me" value="1" @checked($nearMe)>
                            <span>Sort by nearest to me</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ $browseIndexUrl }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Reset</a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-700">Apply Filters</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="space-y-5">
            <div class="tenant-card overflow-hidden">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Map View</h2>
                        <p class="mt-1 text-sm text-slate-500">Click or drag the marker to update your reference point.</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Ref: {{ $referencePoint['lat'] }}, {{ $referencePoint['lng'] }}</span>
                </div>
                <div class="p-4">
                    <div id="browseMap" class="tenant-listings-map w-full overflow-hidden rounded-xl border border-slate-200"></div>
                </div>
            </div>

            <div class="tenant-card min-w-0 overflow-hidden">
                <form id="compareListingsForm" method="GET" action="{{ $compareUrl }}"></form>
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Results</h2>
                        <p class="text-sm text-slate-500">{{ number_format($resultCount) }} boarding {{ \Illuminate\Support\Str::plural('house', $resultCount) }} found</p>
                    </div>
                    <button type="submit" form="compareListingsForm" class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600">Compare Selected</button>
                </div>

                <input form="compareListingsForm" type="hidden" name="lat" value="{{ request('lat', $referencePoint['lat']) }}">
                <input form="compareListingsForm" type="hidden" name="lng" value="{{ request('lng', $referencePoint['lng']) }}">

                <div class="p-4">
                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                        @forelse($houses as $house)
                            @php
                                $rating = $house->reviews_avg_rating ? number_format($house->reviews_avg_rating, 1) : 'N/A';
                                $image = $house->images->first()?->image_path ? asset('storage/'.$house->images->first()->image_path) : null;
                                $availableRooms = max((int)($house->available_rooms ?? 0), (int)($house->available_rooms_count ?? 0), (int)($house->room_categories_available_rooms_sum ?? 0));
                                $amenityList = $house->amenities->pluck('name')->take(3);
                            @endphp
                            <article class="tenant-listing-card">
                                <div class="tenant-listing-image">
                                    @if($image)
                                        <img src="{{ $image }}" alt="{{ $house->name }}">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-blue-600 to-emerald-500 text-white">
                                            <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 11.5 12 5l8 6.5V20H4v-8.5Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 20v-5h6v5" />
                                            </svg>
                                        </div>
                                    @endif
                                    <span class="tenant-listing-status">{{ $availableRooms > 0 ? $availableRooms.' rooms' : 'Limited' }}</span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h3 class="truncate text-base font-bold text-slate-950">{{ $house->name }}</h3>
                                            <p class="tenant-listing-address mt-1 text-sm leading-5 text-slate-500">{{ $house->address }}</p>
                                        </div>
                                        <label class="tenant-compare-check">
                                            <input form="compareListingsForm" type="checkbox" name="ids[]" value="{{ $house->id }}">
                                            <span>Compare</span>
                                        </label>
                                    </div>

                                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                        <div class="rounded-xl bg-slate-50 p-3">
                                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Price</p>
                                            <p class="mt-1 font-bold text-slate-950">{{ $house->display_price !== null ? 'PHP '.number_format((float) $house->display_price, 0) : 'N/A' }}</p>
                                        </div>
                                        <div class="rounded-xl bg-slate-50 p-3">
                                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Distance</p>
                                            <p class="mt-1 font-bold text-slate-950">{{ $house->distance_km !== null ? $house->distance_km.' km' : 'N/A' }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @forelse($amenityList as $amenityName)
                                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ $amenityName }}</span>
                                        @empty
                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">No amenities listed</span>
                                        @endforelse
                                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Rating: {{ $rating }}</span>
                                    </div>

                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <a href="{{ route('user.boarding-houses.show', $house) }}" class="inline-flex flex-1 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">View Details</a>
                                        <form method="POST" action="{{ route('user.favorites.store', $house) }}" class="flex-1">
                                            @csrf
                                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-700 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800">Save</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                                No boarding houses match the current filters.
                            </div>
                        @endforelse
                </div>
            </div>
            </div>
        </section>

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
