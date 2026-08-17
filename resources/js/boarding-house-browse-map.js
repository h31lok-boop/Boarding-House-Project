import {
    boardMatchMapConfig,
    circlePolygon,
    createHtmlMarker,
    createOpenStreetMap,
    fitMapToLngLats,
    lineFeature,
    removeMapLayerAndSource,
    whenMapLoaded,
} from './openstreetmap';

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

const browseMarkerHtml = (type, label = '') => (
    `<span class="bm-browse-map-pin bm-browse-map-pin--${type}">${escapeHtml(label)}</span>`
);

const addBrowseMarker = (map, { lng, lat, type, label = '', title, popupHtml }) => createHtmlMarker({
    map,
    lngLat: [lng, lat],
    html: browseMarkerHtml(type, label),
    title,
    anchor: 'center',
    popupHtml,
    popupOffset: type === 'campus' ? 22 : 20,
});

const addLineLayer = (map, id, coordinates, paint = {}) => {
    map.addSource(id, {
        type: 'geojson',
        data: lineFeature(coordinates),
    });
    map.addLayer({
        id,
        type: 'line',
        source: id,
        layout: {
            'line-cap': 'round',
            'line-join': 'round',
        },
        paint,
    });
};

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

    const map = createOpenStreetMap(root, {
        center: [campusLng, campusLat],
        zoom: 14,
        scrollZoom: false,
        openStreetMap: settings.openStreetMap,
    });
    await whenMapLoaded(map);

    map.addSource('dssc-five-kilometer-radius', {
        type: 'geojson',
        data: circlePolygon([campusLng, campusLat], 5000),
    });
    map.addLayer({
        id: 'dssc-five-kilometer-radius-fill',
        type: 'fill',
        source: 'dssc-five-kilometer-radius',
        paint: { 'fill-color': '#60a5fa', 'fill-opacity': 0.05 },
    });
    map.addLayer({
        id: 'dssc-five-kilometer-radius-line',
        type: 'line',
        source: 'dssc-five-kilometer-radius',
        paint: { 'line-color': '#2563eb', 'line-width': 1.5 },
    });

    addBrowseMarker(map, {
        lat: campusLat,
        lng: campusLng,
        type: 'campus',
        label: 'D',
        title: settings.dssc?.name || 'DSSC Main Campus',
        popupHtml: `<strong>${escapeHtml(settings.dssc?.name || 'DSSC Main Campus')}</strong>`
            + `<br>${escapeHtml(settings.dssc?.address || 'Matti, Digos City')}`,
    });

    const points = [[campusLng, campusLat]];
    let validHouseCount = 0;

    (settings.houses || []).forEach((house) => {
        const latitude = numberOrNull(house.latitude);
        const longitude = numberOrNull(house.longitude);
        if (latitude === null || longitude === null) return;

        validHouseCount += 1;
        points.push([longitude, latitude]);
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

        addBrowseMarker(map, {
            lat: latitude,
            lng: longitude,
            type: markerType,
            label: markerLabel,
            title: house.name || 'Boarding House',
            popupHtml: popup,
        });
    });

    if (validHouseCount > 0) {
        fitMapToLngLats(map, points, { padding: 50, maxZoom: 15 });
    }
};

let quickRouteMap = null;
let quickRouteCanvas = null;
let quickRouteMarkers = [];
let quickRouteLayerIds = [];
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
    quickRouteMarkers.forEach((marker) => marker.remove());
    quickRouteMarkers = [];

    quickRouteLayerIds.slice().reverse().forEach((id) => {
        removeMapLayerAndSource(quickRouteMap, id);
    });
    quickRouteLayerIds = [];
};

const initializeQuickRouteMap = async (canvas) => {
    if (quickRouteMap && quickRouteCanvas === canvas) return quickRouteMap;

    quickRouteMap?.remove();
    quickRouteCanvas = canvas;
    quickRouteMap = createOpenStreetMap(canvas, {
        center: [125.30909, 6.75874],
        zoom: 14,
        scrollZoom: false,
    });
    await whenMapLoaded(quickRouteMap);

    return quickRouteMap;
};

const drivingRouteUrl = (coordinates) => {
    const configured = String(
        boardMatchMapConfig().routing?.drivingUrl
        || 'https://routing.openstreetmap.de/routed-car',
    ).replace(/\/$/, '');

    return `${configured}/route/v1/driving/${coordinates}?overview=full&geometries=geojson&steps=false`;
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
    const map = await initializeQuickRouteMap(canvas);
    clearQuickRouteLayers();

    const campusPoint = [campusLng, campusLat];
    const housePoint = [houseLng, houseLat];
    quickRouteMarkers.push(
        addBrowseMarker(map, {
            lat: campusLat,
            lng: campusLng,
            type: 'campus',
            label: 'D',
            title: listing.dssc_name || 'DSSC Main Campus',
            popupHtml: `<strong>${escapeHtml(listing.dssc_name || 'DSSC Main Campus')}</strong><br>${escapeHtml(listing.dssc_address || 'Matti, Digos City')}`,
        }),
        addBrowseMarker(map, {
            lat: houseLat,
            lng: houseLng,
            type: 'house',
            label: 'B',
            title: listing.name || 'Boarding House',
            popupHtml: `<strong>${escapeHtml(listing.name || 'Boarding House')}</strong><br>${escapeHtml(listing.address || '')}`,
        }),
    );

    addLineLayer(map, 'quick-route-direct', [campusPoint, housePoint], {
        'line-color': '#64748b',
        'line-width': 3,
        'line-opacity': 0.7,
        'line-dasharray': [2, 2],
    });
    quickRouteLayerIds.push('quick-route-direct');
    fitMapToLngLats(map, [campusPoint, housePoint], { padding: 54, maxZoom: 15 });
    window.setTimeout(() => map.resize(), 80);

    if (status) status.textContent = 'Calculating the road route...';
    if (durationOutput) durationOutput.textContent = 'Calculating...';

    try {
        const coordinates = `${campusLng},${campusLat};${houseLng},${houseLat}`;
        const controller = new AbortController();
        const timeout = window.setTimeout(() => controller.abort(), 12000);
        const response = await fetch(drivingRouteUrl(coordinates), {
            headers: { Accept: 'application/json' },
            signal: controller.signal,
        });
        window.clearTimeout(timeout);
        if (!response.ok) throw new Error(`Routing service responded with ${response.status}.`);

        const payload = await response.json();
        const route = payload.routes?.[0];
        if (payload.code !== 'Ok' || !route?.geometry?.coordinates?.length) {
            throw new Error('No road route was returned.');
        }
        if (requestId !== quickRouteRequest) return;

        removeMapLayerAndSource(map, 'quick-route-direct');
        quickRouteLayerIds = quickRouteLayerIds.filter((id) => id !== 'quick-route-direct');
        addLineLayer(map, 'quick-route-outline', route.geometry.coordinates, {
            'line-color': '#ffffff',
            'line-width': 8,
            'line-opacity': 0.9,
        });
        addLineLayer(map, 'quick-route-road', route.geometry.coordinates, {
            'line-color': '#2563eb',
            'line-width': 5,
            'line-opacity': 0.95,
        });
        quickRouteLayerIds.push('quick-route-outline', 'quick-route-road');
        fitMapToLngLats(map, route.geometry.coordinates, { padding: 50, maxZoom: 16 });

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
