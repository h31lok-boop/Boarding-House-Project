import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

const ROOT_SELECTOR = '[data-boardmatch-browse-map]';

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[character]));

const numberOrNull = (value) => {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : null;
};

const priceLabel = (value) => {
    const price = numberOrNull(value);

    return price !== null && price > 0
        ? `₱${price.toLocaleString()}/month`
        : 'Ask owner';
};

const markerIcon = (L, type, label = '') => L.divIcon({
    className: '',
    html: `<span class="bm-browse-map-pin bm-browse-map-pin--${type}">${escapeHtml(label)}</span>`,
    iconSize: type === 'campus' ? [34, 34] : [30, 30],
    iconAnchor: type === 'campus' ? [17, 17] : [15, 15],
    popupAnchor: [0, -18],
});

const initializeMap = async (root) => {
    const configNode = root.parentElement?.querySelector('[data-boardmatch-browse-map-config]');
    if (!configNode) return;

    let settings;
    try {
        settings = JSON.parse(configNode.textContent || '{}');
    } catch (error) {
        return;
    }

    const campusLat = numberOrNull(settings.dssc?.latitude);
    const campusLng = numberOrNull(settings.dssc?.longitude);
    if (campusLat === null || campusLng === null) return;

    const map = L.map(root, { scrollWheelZoom: false }).setView([campusLat, campusLng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    const bounds = L.latLngBounds([[campusLat, campusLng]]);
    L.circle([campusLat, campusLng], {
        radius: 5000,
        color: '#2563eb',
        fillColor: '#60a5fa',
        fillOpacity: 0.05,
        weight: 1.5,
    }).addTo(map);

    L.marker([campusLat, campusLng], {
        icon: markerIcon(L, 'campus', 'D'),
        title: settings.dssc?.name || 'DSSC Main Campus',
    }).addTo(map).bindPopup(
        `<strong>${escapeHtml(settings.dssc?.name || 'DSSC Main Campus')}</strong>`
        + `<br>${escapeHtml(settings.dssc?.address || 'Matti, Digos City')}`
    );

    (settings.houses || []).forEach((house) => {
        const latitude = numberOrNull(house.latitude);
        const longitude = numberOrNull(house.longitude);
        if (latitude === null || longitude === null) return;

        bounds.extend([latitude, longitude]);
        const matchScore = numberOrNull(house.match_score);
        const markerLabel = settings.showMatchScores && matchScore !== null
            ? `${Math.round(matchScore)}`
            : '';
        const markerType = settings.showMatchScores && matchScore !== null && matchScore >= 80
            ? 'match'
            : (numberOrNull(house.available_rooms) !== null && Number(house.available_rooms) <= 2 ? 'few' : 'house');
        const distance = house.distance_from_dssc_label
            || (numberOrNull(house.distance_from_dssc) !== null
                ? `${Number(house.distance_from_dssc).toFixed(1)} km from DSSC Main Campus`
                : 'Distance from DSSC unavailable');
        const popup = [
            `<strong>${escapeHtml(house.name || 'Boarding House')}</strong>`,
            escapeHtml(distance),
            escapeHtml(priceLabel(house.price)),
            escapeHtml(house.availability_label || 'Availability not recorded'),
            `<a class="bm-browse-map-popup-link" href="${escapeHtml(house.url || '#')}">View Details</a>`,
        ].join('<br>');

        L.marker([latitude, longitude], {
            icon: markerIcon(L, markerType, markerLabel),
            title: house.name || 'Boarding House',
        }).addTo(map).bindPopup(popup);
    });

    if (bounds.isValid() && (settings.houses || []).length > 0) {
        map.fitBounds(bounds.pad(0.12), { maxZoom: 15 });
    }
};

let quickRouteMap = null;
let quickRouteCanvas = null;
let quickRouteLayers = [];
let quickRouteRequest = 0;

const formatRouteDistance = (meters) => {
    if (!Number.isFinite(meters)) return 'Unavailable';

    return meters < 1000
        ? `${Math.max(1, Math.round(meters))} m`
        : `${(meters / 1000).toFixed(2)} km`;
};

const formatRouteDuration = (seconds) => {
    if (!Number.isFinite(seconds)) return 'Unavailable';

    const minutes = Math.max(1, Math.round(seconds / 60));
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    return hours > 0
        ? `${hours} hr${remainingMinutes ? ` ${remainingMinutes} min` : ''}`
        : `${minutes} min`;
};

const clearQuickRouteLayers = () => {
    quickRouteLayers.forEach((layer) => layer.remove());
    quickRouteLayers = [];
};

const initializeQuickRouteMap = (canvas) => {
    if (quickRouteMap && quickRouteCanvas === canvas) return quickRouteMap;

    quickRouteMap?.remove();
    quickRouteCanvas = canvas;
    quickRouteMap = L.map(canvas, { scrollWheelZoom: false, zoomControl: true });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(quickRouteMap);

    return quickRouteMap;
};

const renderQuickRoute = async (listing = {}) => {
    const canvas = document.querySelector('[data-boardmatch-quick-route-map]');
    if (!canvas) return;

    const section = canvas.closest('[data-renter-quick-location-map]');
    const status = section?.querySelector('[data-quick-route-status]');
    const distanceOutput = section?.querySelector('[data-quick-route-distance]');
    const durationOutput = section?.querySelector('[data-quick-route-duration]');
    const campusLat = numberOrNull(listing.dssc_latitude);
    const campusLng = numberOrNull(listing.dssc_longitude);
    const houseLat = numberOrNull(listing.latitude);
    const houseLng = numberOrNull(listing.longitude);

    if ([campusLat, campusLng, houseLat, houseLng].includes(null)) {
        if (status) status.textContent = 'Route unavailable because one location has no saved coordinates.';
        return;
    }

    const requestId = ++quickRouteRequest;
    const map = initializeQuickRouteMap(canvas);
    clearQuickRouteLayers();

    const campusPoint = [campusLat, campusLng];
    const housePoint = [houseLat, houseLng];
    const campusMarker = L.marker(campusPoint, {
        icon: markerIcon(L, 'campus', 'D'),
        title: listing.dssc_name || 'DSSC Main Campus',
    }).addTo(map).bindPopup(
        `<strong>${escapeHtml(listing.dssc_name || 'DSSC Main Campus')}</strong><br>${escapeHtml(listing.dssc_address || 'Matti, Digos City')}`
    );
    const houseMarker = L.marker(housePoint, {
        icon: markerIcon(L, 'house', 'B'),
        title: listing.name || 'Boarding House',
    }).addTo(map).bindPopup(
        `<strong>${escapeHtml(listing.name || 'Boarding House')}</strong><br>${escapeHtml(listing.address || '')}`
    );
    const directLine = L.polyline([campusPoint, housePoint], {
        color: '#64748b',
        weight: 3,
        opacity: 0.7,
        dashArray: '7 8',
    }).addTo(map);
    quickRouteLayers.push(campusMarker, houseMarker, directLine);
    map.fitBounds(L.latLngBounds([campusPoint, housePoint]).pad(0.2), { maxZoom: 15 });
    window.setTimeout(() => map.invalidateSize(), 80);

    if (status) status.textContent = 'Calculating the road route...';
    if (durationOutput) durationOutput.textContent = 'Calculating...';

    try {
        const coordinates = `${campusLng},${campusLat};${houseLng},${houseLat}`;
        const controller = new AbortController();
        const timeout = window.setTimeout(() => controller.abort(), 12000);
        const response = await fetch(
            `https://router.project-osrm.org/route/v1/driving/${coordinates}?overview=full&geometries=geojson&steps=false`,
            { headers: { Accept: 'application/json' }, signal: controller.signal },
        );
        window.clearTimeout(timeout);
        if (!response.ok) throw new Error(`Routing service responded with ${response.status}.`);

        const payload = await response.json();
        const route = payload.routes?.[0];
        if (payload.code !== 'Ok' || !route?.geometry?.coordinates?.length) {
            throw new Error('No road route was returned.');
        }
        if (requestId !== quickRouteRequest) return;

        directLine.remove();
        quickRouteLayers = quickRouteLayers.filter((layer) => layer !== directLine);
        const routePoints = route.geometry.coordinates.map(([lng, lat]) => [lat, lng]);
        const routeOutline = L.polyline(routePoints, { color: '#ffffff', weight: 8, opacity: 0.9 }).addTo(map);
        const routeLine = L.polyline(routePoints, { color: '#2563eb', weight: 5, opacity: 0.95 }).addTo(map);
        quickRouteLayers.push(routeOutline, routeLine);
        map.fitBounds(routeLine.getBounds().pad(0.12), { maxZoom: 16 });

        if (distanceOutput) distanceOutput.textContent = formatRouteDistance(Number(route.distance));
        if (durationOutput) durationOutput.textContent = formatRouteDuration(Number(route.duration));
        if (status) status.textContent = 'Road route loaded between DSSC and the property.';
    } catch (error) {
        if (requestId !== quickRouteRequest) return;

        const fallbackDistance = numberOrNull(listing.distance_km);
        if (distanceOutput) {
            distanceOutput.textContent = fallbackDistance !== null
                ? `${fallbackDistance.toFixed(2)} km straight-line`
                : 'Unavailable';
        }
        if (durationOutput) durationOutput.textContent = 'Route unavailable';
        if (status) status.textContent = 'Road routing is unavailable; showing the direct connection.';
    }
};

window.addEventListener('boardmatch:quick-route', (event) => {
    renderQuickRoute(event.detail || {}).catch(() => {});
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll(ROOT_SELECTOR).forEach((root) => {
        initializeMap(root).catch(() => {
            root.innerHTML = '<div class="flex h-full items-center justify-center p-5 text-center text-sm text-slate-500">Location map is temporarily unavailable.</div>';
        });
    });
});
