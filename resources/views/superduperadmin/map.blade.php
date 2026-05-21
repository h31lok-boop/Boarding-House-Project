<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />

@php
    $statusCounts = collect($mapHouses)->countBy(fn ($row) => strtolower((string) ($row['status'] ?? 'draft')));
@endphp

<x-admin.workspace-shell
    workspace="superduperadmin"
    title="Map & Geotagging"
    subtitle="Use this page only for listing markers, map filters, and coordinate capture."
    profile-role-label="Owner"
    active="map">
    <x-slot name="actions">
        <a href="{{ route('superduperadmin.boarding-houses.index', [], false) }}" class="sa-button-secondary">Listing Table</a>
    </x-slot>

    <style>
        .super-panel { border: 1px solid rgba(231, 224, 216, 0.9); background: rgba(255, 255, 255, 0.88); box-shadow: 0 18px 36px rgba(26, 18, 15, 0.08); border-radius: 1.5rem; }
        [data-theme='dark'] .super-panel { border-color: rgba(42, 34, 30, 0.92); background: rgba(23, 19, 17, 0.94); }
        .super-label { display: block; margin-bottom: 0.42rem; color: var(--muted); font-size: 0.76rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; }
        .super-input, .super-select, .super-textarea { width: 100%; min-height: 2.95rem; border-radius: 1rem; border: 1px solid var(--border); background: var(--surface); color: var(--text); padding: 0.8rem 0.95rem; font: inherit; }
        .super-input:focus, .super-select:focus, .super-textarea:focus { outline: none; border-color: rgba(242, 105, 74, 0.42); box-shadow: 0 0 0 4px rgba(255, 126, 95, 0.14); }
        .super-badge { display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; border: 1px solid transparent; padding: 0.35rem 0.7rem; font-size: 0.77rem; font-weight: 700; white-space: nowrap; }
        .super-badge[data-tone='approved'] { background: rgba(22, 163, 74, 0.12); border-color: rgba(22, 163, 74, 0.18); color: #166534; }
        .super-badge[data-tone='pending'] { background: rgba(217, 119, 6, 0.12); border-color: rgba(217, 119, 6, 0.18); color: #b45309; }
        .super-badge[data-tone='rejected'] { background: rgba(220, 38, 38, 0.1); border-color: rgba(220, 38, 38, 0.16); color: #b42318; }
        .super-badge[data-tone='suspended'] { background: rgba(168, 85, 247, 0.12); border-color: rgba(168, 85, 247, 0.18); color: #7c2d12; }
        .super-badge[data-tone='closed'], .super-badge[data-tone='draft'] { background: rgba(100, 116, 139, 0.12); border-color: rgba(100, 116, 139, 0.18); color: #596273; }
        .leaflet-popup-content-wrapper, .leaflet-popup-tip { background: var(--surface); color: var(--text); border: 1px solid var(--border); }
    </style>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,0.9fr)]">
        <section class="super-panel min-w-0 p-5">
            <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-[color:var(--text)]">Boarding House Map</h2>
                    <p class="mt-1 text-sm ui-muted">Filter visible listings, inspect markers, and click the map to capture coordinates.</p>
                </div>
                <span class="super-badge" data-tone="draft">Mapped {{ number_format($mappedCount) }}</span>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div><label class="super-label" for="filterMinPrice">Min Price</label><input type="number" step="0.01" id="filterMinPrice" class="super-input" placeholder="Min price"></div>
                <div><label class="super-label" for="filterMaxPrice">Max Price</label><input type="number" step="0.01" id="filterMaxPrice" class="super-input" placeholder="Max price"></div>
                <div><label class="super-label" for="filterRooms">Min Rooms</label><input type="number" id="filterRooms" class="super-input" placeholder="Min rooms"></div>
                <div><label class="super-label" for="filterStatus">Status</label><select id="filterStatus" class="super-select"><option value="">Any status</option>@foreach($statusKeys as $status)<option value="{{ $status }}">{{ ucfirst($status) }}</option>@endforeach</select></div>
                <div class="md:col-span-2 xl:col-span-4"><label class="super-label" for="filterLocation">Name or Address</label><input type="text" id="filterLocation" class="super-input" placeholder="Filter by listing name or address"></div>
                <div class="md:col-span-2 xl:col-span-4 flex flex-wrap gap-2">
                    <button type="button" id="applyFilters" class="sa-button">Apply Filters</button>
                    <button type="button" id="resetFilters" class="sa-button-secondary">Reset</button>
                </div>
            </div>

            <div class="mt-4 overflow-hidden rounded-[1.4rem] border ui-border">
                <div id="boardingHouseMap" class="h-[520px] w-full"></div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <span class="super-badge" data-tone="approved">Approved {{ (int) ($statusCounts['approved'] ?? 0) }}</span>
                <span class="super-badge" data-tone="pending">Pending {{ (int) ($statusCounts['pending'] ?? 0) }}</span>
                <span class="super-badge" data-tone="rejected">Rejected {{ (int) ($statusCounts['rejected'] ?? 0) }}</span>
                <span class="super-badge" data-tone="draft">Draft {{ (int) ($statusCounts['draft'] ?? 0) }}</span>
                <span class="super-badge" data-tone="suspended">Suspended {{ (int) ($statusCounts['suspended'] ?? 0) }}</span>
                <span class="super-badge" data-tone="closed">Closed {{ (int) ($statusCounts['closed'] ?? 0) }}</span>
            </div>
        </section>

        <section class="super-panel p-5">
            <h2 class="text-lg font-semibold text-[color:var(--text)]">Geotagging Controls</h2>
            <p class="mt-1 text-sm ui-muted">Click the map or drag the marker to inspect a location without opening the create form.</p>

            <div class="mt-5 space-y-4">
                <div>
                    <label class="super-label" for="selectedLatitudeField">Latitude</label>
                    <input type="text" id="selectedLatitudeField" class="super-input" readonly>
                </div>
                <div>
                    <label class="super-label" for="selectedLongitudeField">Longitude</label>
                    <input type="text" id="selectedLongitudeField" class="super-input" readonly>
                </div>
                <div>
                    <label class="super-label" for="selectedAddressField">Reverse Geocoded Address</label>
                    <textarea id="selectedAddressField" class="super-input min-h-[8rem]" readonly></textarea>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" id="clearSelection" class="sa-button-secondary">Clear Selection</button>
                </div>
            </div>
        </section>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        (() => {
            const listings = @json($mapHouses);
            const mapElement = document.getElementById('boardingHouseMap');
            if (!mapElement || typeof L === 'undefined') return;

            const colorMap = {
                approved: '#16a34a',
                pending: '#d97706',
                rejected: '#dc2626',
                suspended: '#a855f7',
                closed: '#64748b',
                draft: '#64748b',
            };

            const latitudeField = document.getElementById('selectedLatitudeField');
            const longitudeField = document.getElementById('selectedLongitudeField');
            const addressField = document.getElementById('selectedAddressField');
            const markersLayer = L.layerGroup();
            let geotagMarker = null;

            const isDarkTheme = () => document.documentElement.getAttribute('data-theme') === 'dark';
            const applyTileTheme = () => {
                document.querySelectorAll('.leaflet-tile').forEach((tile) => {
                    tile.style.filter = isDarkTheme()
                        ? 'brightness(0.62) invert(1) contrast(2.7) hue-rotate(190deg) saturate(0.35) brightness(0.75)'
                        : 'none';
                });
            };

            const map = L.map('boardingHouseMap').setView([6.744, 125.355], 13);
            markersLayer.addTo(map);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const popupContent = (listing) => `
                <div style="min-width:220px">
                    <strong>${listing.name}</strong><br>
                    ${listing.address ?? ''}<br>
                    <small>Price: ${listing.price_range ?? 'N/A'}</small><br>
                    <small>Available Rooms: ${listing.available_rooms ?? 0}</small><br>
                    <small>Owner: ${listing.owner_name ?? 'N/A'}</small><br>
                    <small>Contact: ${listing.contact_number ?? 'N/A'}</small><br>
                    <small>Status: ${listing.status ?? 'N/A'}</small>
                </div>
            `;

            const renderMarkers = (data) => {
                markersLayer.clearLayers();
                const points = [];
                data.forEach((listing) => {
                    if (listing.latitude === null || listing.longitude === null) return;
                    const color = colorMap[String(listing.status || 'draft').toLowerCase()] || '#f2694a';
                    const icon = L.divIcon({
                        className: 'map-marker-dot',
                        html: `<span style="display:flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:999px;background:${color};color:#fff;font-size:11px;font-weight:700;border:2px solid #fff;box-shadow:0 6px 12px rgba(0,0,0,0.18);">${listing.available_rooms ?? 0}</span>`,
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    });
                    const marker = L.marker([listing.latitude, listing.longitude], { icon }).addTo(markersLayer);
                    marker.bindPopup(popupContent(listing));
                    points.push([listing.latitude, listing.longitude]);
                });
                if (points.length > 0) map.fitBounds(points, { padding: [30, 30] });
            };

            const reverseGeocode = (lat, lng) => {
                fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                    .then((response) => response.ok ? response.json() : Promise.reject())
                    .then((payload) => {
                        addressField.value = payload?.display_name || '';
                    })
                    .catch(() => {
                        addressField.value = '';
                    });
            };

            const setSelection = (lat, lng) => {
                latitudeField.value = Number(lat).toFixed(8);
                longitudeField.value = Number(lng).toFixed(8);
                reverseGeocode(lat, lng);

                if (!geotagMarker) {
                    geotagMarker = L.marker([lat, lng], { draggable: true }).addTo(map);
                    geotagMarker.on('dragend', (event) => {
                        const point = event.target.getLatLng();
                        setSelection(point.lat, point.lng);
                    });
                } else {
                    geotagMarker.setLatLng([lat, lng]);
                }
            };

            const applyFilters = () => {
                const minPrice = parseFloat(document.getElementById('filterMinPrice').value || '0');
                const maxPriceValue = document.getElementById('filterMaxPrice').value;
                const maxPrice = maxPriceValue === '' ? Number.MAX_SAFE_INTEGER : parseFloat(maxPriceValue);
                const minRooms = parseInt(document.getElementById('filterRooms').value || '0', 10);
                const status = document.getElementById('filterStatus').value.toLowerCase();
                const text = document.getElementById('filterLocation').value.trim().toLowerCase();

                const filtered = listings.filter((listing) => {
                    const prices = String(listing.price_range ?? '').match(/[0-9]+(?:,[0-9]{3})*(?:\.[0-9]+)?/g) || [];
                    const numericPrices = prices.map((value) => parseFloat(value.replace(/,/g, ''))).filter((value) => !Number.isNaN(value));
                    const basePrice = numericPrices.length ? Math.min(...numericPrices) : 0;
                    const rooms = parseInt(listing.available_rooms ?? 0, 10);
                    const haystack = `${listing.name ?? ''} ${listing.address ?? ''}`.toLowerCase();

                    return basePrice >= minPrice
                        && basePrice <= maxPrice
                        && rooms >= minRooms
                        && (status === '' || String(listing.status).toLowerCase() === status)
                        && (text === '' || haystack.includes(text));
                });

                renderMarkers(filtered);
            };

            document.getElementById('applyFilters')?.addEventListener('click', applyFilters);
            document.getElementById('resetFilters')?.addEventListener('click', () => {
                ['filterMinPrice', 'filterMaxPrice', 'filterRooms', 'filterLocation'].forEach((id) => document.getElementById(id).value = '');
                document.getElementById('filterStatus').value = '';
                renderMarkers(listings);
            });

            document.getElementById('clearSelection')?.addEventListener('click', () => {
                latitudeField.value = '';
                longitudeField.value = '';
                addressField.value = '';
                if (geotagMarker) {
                    map.removeLayer(geotagMarker);
                    geotagMarker = null;
                }
            });

            map.on('click', (event) => {
                setSelection(event.latlng.lat, event.latlng.lng);
            });

            renderMarkers(listings);
            applyTileTheme();
            new MutationObserver(() => applyTileTheme()).observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
        })();
    </script>
</x-admin.workspace-shell>
