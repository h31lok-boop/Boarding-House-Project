import {
    boardMatchMapConfig,
    circlePolygon,
    createHtmlMarker,
    createOpenStreetMap,
    fitMapToLngLats,
    isInsideServiceArea,
    serviceAreaMessage,
    whenMapLoaded,
} from './openstreetmap';

const ROOT_SELECTOR = '[data-boardmatch-browse-map]';
const GOOGLE_ROUTE_BLUE = '#4285f4';
const GOOGLE_ROUTE_OUTLINE = '#0b57d0';

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[character]));

const numberOrNull = (value) => {
    if (value === null || value === undefined || String(value).trim() === '') return null;
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

const addBrowseMarker = (map, { lng, lat, type, label = '', title, popupHtml }) => {
    const marker = createHtmlMarker({
        map,
        lngLat: [lng, lat],
        html: browseMarkerHtml(type, label),
        title,
        anchor: 'center',
        popupHtml,
        popupOffset: type === 'campus' ? 22 : 20,
    });
    marker.getElement().style.zIndex = '3';

    return marker;
};

const createQuickRouteOverlay = (map, coordinates) => {
    const namespace = 'http://www.w3.org/2000/svg';
    const overlay = document.createElementNS(namespace, 'svg');
    const outline = document.createElementNS(namespace, 'path');
    const route = document.createElementNS(namespace, 'path');

    overlay.dataset.quickRouteOverlay = '';
    overlay.setAttribute('aria-hidden', 'true');
    Object.assign(overlay.style, {
        position: 'absolute',
        inset: '0',
        width: '100%',
        height: '100%',
        overflow: 'hidden',
        pointerEvents: 'none',
        zIndex: '2',
    });

    [outline, route].forEach((path) => {
        path.setAttribute('fill', 'none');
        path.setAttribute('stroke-linecap', 'round');
        path.setAttribute('stroke-linejoin', 'round');
        path.setAttribute('vector-effect', 'non-scaling-stroke');
    });
    outline.setAttribute('stroke', GOOGLE_ROUTE_OUTLINE);
    outline.setAttribute('stroke-width', '7.5');
    outline.setAttribute('stroke-opacity', '0.82');
    route.setAttribute('stroke', GOOGLE_ROUTE_BLUE);
    route.setAttribute('stroke-width', '4.5');
    route.setAttribute('pathLength', '1000');
    route.style.strokeDasharray = '1000';
    route.style.strokeDashoffset = '1000';

    overlay.append(outline, route);
    map.getContainer().append(overlay);

    const render = () => {
        const points = coordinates
            .map(([lng, lat]) => map.project([lng, lat]))
            .filter((point) => Number.isFinite(point.x) && Number.isFinite(point.y));
        if (points.length < 2) return;

        let pathData = `M${points[0].x.toFixed(2)},${points[0].y.toFixed(2)}`;
        for (let index = 1; index < points.length - 1; index += 1) {
            const point = points[index];
            const next = points[index + 1];
            const midpoint = { x: (point.x + next.x) / 2, y: (point.y + next.y) / 2 };
            pathData += ` Q${point.x.toFixed(2)},${point.y.toFixed(2)} ${midpoint.x.toFixed(2)},${midpoint.y.toFixed(2)}`;
        }
        const last = points.at(-1);
        if (points.length === 2) {
            const start = points[0];
            const midpoint = { x: (start.x + last.x) / 2, y: (start.y + last.y) / 2 };
            const delta = { x: last.x - start.x, y: last.y - start.y };
            const length = Math.max(Math.hypot(delta.x, delta.y), 1);
            const bend = Math.min(52, length * .18);
            const control = {
                x: midpoint.x - (delta.y / length) * bend,
                y: midpoint.y + (delta.x / length) * bend,
            };
            pathData += ` Q${control.x.toFixed(2)},${control.y.toFixed(2)} ${last.x.toFixed(2)},${last.y.toFixed(2)}`;
        } else {
            pathData += ` Q${last.x.toFixed(2)},${last.y.toFixed(2)} ${last.x.toFixed(2)},${last.y.toFixed(2)}`;
        }

        outline.setAttribute('d', pathData);
        route.setAttribute('d', pathData);
    };

    map.on('move', render);
    map.on('resize', render);
    render();
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        window.requestAnimationFrame(() => {
            route.style.transition = 'stroke-dashoffset .9s ease-out';
            route.style.strokeDashoffset = '0';
        });
    } else {
        route.style.strokeDashoffset = '0';
    }

    return {
        remove() {
            map.off('move', render);
            map.off('resize', render);
            overlay.remove();
        },
    };
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
    }, 'service-area-mask');
    map.addLayer({
        id: 'dssc-five-kilometer-radius-line',
        type: 'line',
        source: 'dssc-five-kilometer-radius',
        paint: { 'line-color': '#2563eb', 'line-width': 1.5 },
    }, 'service-area-mask');

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
        // The backend already restricts public mapHouses to approved listings
        // inside the service area. Render every coordinate it returns so the
        // map always reflects the exact saved boarding-house location instead
        // of applying a second, potentially stale client-side boundary check.
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
let quickRouteOverlay = null;
let quickRouteRequest = 0;

const formatRouteDistance = (meters) => {
    if (!Number.isFinite(meters)) return 'Unavailable';

    return meters < 1000
        ? `${Math.max(50, Math.round(meters / 50) * 50)} m`
        : `${(meters / 1000).toFixed(2)} km`;
};

const formatRouteDuration = (seconds) => {
    if (!Number.isFinite(seconds)) return 'Unavailable';

    const minutes = Math.max(1, Math.ceil(seconds / 60));
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    return hours > 0
        ? `${hours} hr${remainingMinutes ? ` ${remainingMinutes} min` : ''}`
        : `${minutes} min`;
};

const clearQuickRouteLayers = () => {
    quickRouteMarkers.forEach((marker) => marker.remove());
    quickRouteMarkers = [];
    quickRouteOverlay?.remove();
    quickRouteOverlay = null;
};

const initializeQuickRouteMap = async (canvas, center) => {
    if (quickRouteMap && quickRouteCanvas === canvas) return quickRouteMap;

    quickRouteMap?.remove();
    quickRouteCanvas = canvas;
    quickRouteMap = createOpenStreetMap(canvas, {
        center,
        zoom: 13,
        scrollZoom: false,
    });
    await whenMapLoaded(quickRouteMap);

    return quickRouteMap;
};

const drivingRouteUrl = (coordinates, baseUrl) => {
    const configured = String(baseUrl || 'https://routing.openstreetmap.de/routed-car').replace(/\/$/, '');
    const routeBase = configured.endsWith('/route/v1') ? configured : `${configured}/route/v1`;

    // Stored property pins can be inside a lot rather than directly on a
    // road. Let the routing service snap both endpoints to the nearest
    // routable road instead of rejecting them with a strict radius.
    return `${routeBase}/driving/${coordinates}?overview=full&geometries=geojson&steps=false&radiuses=100;100`;
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

    const requestId = ++quickRouteRequest;
    clearQuickRouteLayers();
    if (!isInsideServiceArea(listing.latitude, listing.longitude)) {
        if (status) status.textContent = serviceAreaMessage();
        if (distanceOutput) distanceOutput.textContent = 'Unavailable';
        if (durationOutput) durationOutput.textContent = 'Unavailable';
        return;
    }

    if ([campusLat, campusLng, houseLat, houseLng].includes(null)) {
        if (status) status.textContent = 'Route unavailable because one location has no saved coordinates.';
        return;
    }

    const map = await initializeQuickRouteMap(canvas, [
        (campusLng + houseLng) / 2,
        (campusLat + houseLat) / 2,
    ]);
    if (requestId !== quickRouteRequest) return;
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

    fitMapToLngLats(map, [campusPoint, housePoint], { padding: 54, maxZoom: 15 });
    window.setTimeout(() => map.resize(), 80);

    if (status) status.textContent = 'Calculating the road route...';
    if (durationOutput) durationOutput.textContent = 'Calculating...';

    try {
        const coordinates = `${campusLng},${campusLat};${houseLng},${houseLat}`;
        const controller = new AbortController();
        const timeout = window.setTimeout(() => controller.abort(), 12000);
        const routing = boardMatchMapConfig().routing || {};
        const routeServices = [
            routing.drivingUrl,
            routing.fallbackUrl,
            'https://router.project-osrm.org/route/v1',
        ].filter((url, index, services) => url && services.indexOf(url) === index);
        let payload = null;
        let route = null;

        for (const service of routeServices) {
            const response = await fetch(drivingRouteUrl(coordinates, service), {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });
            if (!response.ok) continue;
            const candidate = await response.json();
            if (candidate.routes?.[0]?.geometry?.coordinates?.length) {
                payload = candidate;
                route = candidate.routes[0];
                break;
            }
        }
        window.clearTimeout(timeout);
        if (payload.code !== 'Ok' || !route?.geometry?.coordinates?.length) {
            throw new Error('No road route was returned.');
        }
        if (requestId !== quickRouteRequest) return;

        const highlightedCoordinates = [campusPoint, ...route.geometry.coordinates, housePoint];
        quickRouteOverlay = createQuickRouteOverlay(map, highlightedCoordinates);
        fitMapToLngLats(
            map,
            highlightedCoordinates,
            {
                padding: { top: 64, right: 56, bottom: 64, left: 56 },
                maxZoom: 15,
            },
        );

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
        if (status) status.textContent = 'Road routing is unavailable; try turn-by-turn directions.';
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
