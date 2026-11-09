import {
    boardMatchMapConfig,
    createHtmlMarker,
    createOpenStreetMap,
    fitMapToLngLats,
    isInsideServiceArea,
    openStreetMapConfig,
    serviceAreaMessage,
    whenMapLoaded,
} from './openstreetmap';

const MAP_ROOT_SELECTOR = '[data-boardmatch-location-map]';
const ROUTE_STYLE = {
    active: '#4285f4',
    activeOutline: '#0b57d0',
    inactive: '#9aa0a6',
    inactiveOutline: '#ffffff',
};

const DEFAULT_MESSAGES = {
    initial: 'Open the reservation panel to request your location and preview the route.',
    requesting: 'Getting your location…',
    reset: 'Map reset. Open the reservation panel or tap Reserve Room to route again.',
    missingCoordinates: 'Exact location not provided.',
    geolocationDenied: 'Enable location services to see live routes from your current location. The map is centered on the boarding house for now.',
    routeFailed: 'Route could not be generated right now. Please try again in a moment.',
    locationUnavailable: 'Your location could not be determined right now. Please check your device settings and try again.',
    locationTimeout: 'Getting your location took too long. Please try again.',
    noRoute: 'No route was found for the selected travel mode.',
    autoLocateReady: 'Your current location is ready. Choose a travel mode to preview directions.',
    modeTransitNote: 'Transit is using the available road-routing data because live public transit feeds are not available for this map.',
    modeMotorcycleNote: 'Motorcycle is using the available road-routing data for the best available route preview.',
};

const MODE_META = {
    DRIVING: { label: 'Driving', profile: 'driving', note: '' },
    WALKING: { label: 'Walking', profile: 'walking', note: '' },
    TWO_WHEELER: { label: 'Motorcycle', profile: 'driving', note: 'modeMotorcycleNote' },
    TRANSIT: { label: 'Transit', profile: 'driving', note: 'modeTransitNote' },
};

const modeRoutingServices = (profile) => {
    const routing = boardMatchMapConfig().routing || {};
    const baseUrl = profile === 'walking'
        ? routing.walkingUrl
        : routing.drivingUrl;

    if (!baseUrl) {
        return [];
    }

    return [{
        baseUrl: `${String(baseUrl).replace(/\/$/, '')}/route/v1`,
        profile: 'driving',
    }];
};

const numberOrNull = (value) => {
    if (value === null || value === undefined || String(value).trim() === '') return null;
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : null;
};

const isValidCoordinate = (coord) => Boolean(
    coord
    && Number.isFinite(coord.lat)
    && Number.isFinite(coord.lng)
    && Math.abs(coord.lat) <= 90
    && Math.abs(coord.lng) <= 180,
);

const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

const formatDistance = (meters) => {
    if (!Number.isFinite(meters)) {
        return '--';
    }

    if (meters < 1000) {
        return `${Math.max(1, Math.round(meters))} m`;
    }

    return `${(meters / 1000).toFixed(meters < 10000 ? 1 : 0)} km`;
};

const formatRouteDistance = (meters) => {
    if (!Number.isFinite(meters)) {
        return '--';
    }

    if (meters < 1000) {
        // Google Maps presents short route totals in readable 50 m increments.
        return `${Math.max(50, Math.round(meters / 50) * 50)} m`;
    }

    return `${(meters / 1000).toFixed(meters < 10000 ? 1 : 0)} km`;
};

const formatDuration = (seconds) => {
    if (!Number.isFinite(seconds)) {
        return '--';
    }

    // Do not show a 1 minute ETA for a route that takes more than 60 seconds.
    const totalMinutes = Math.max(1, Math.ceil(seconds / 60));
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    if (hours === 0) {
        return `${totalMinutes} min`;
    }

    return minutes === 0 ? `${hours} hr` : `${hours} hr ${minutes} min`;
};

const travelModeLabel = (mode) => MODE_META[mode]?.label || 'Route';

const createProjectedRouteOverlay = (map, coordinates, active = false) => {
    const namespace = 'http://www.w3.org/2000/svg';
    const overlay = document.createElementNS(namespace, 'svg');
    const outline = document.createElementNS(namespace, 'path');
    const route = document.createElementNS(namespace, 'path');

    overlay.dataset.routeOverlay = '';
    overlay.setAttribute('aria-hidden', 'true');
    Object.assign(overlay.style, {
        position: 'absolute',
        inset: '0',
        width: '100%',
        height: '100%',
        overflow: 'hidden',
        pointerEvents: 'none',
    });

    [outline, route].forEach((path) => {
        path.setAttribute('fill', 'none');
        path.setAttribute('stroke-linecap', 'round');
        path.setAttribute('stroke-linejoin', 'round');
        path.setAttribute('vector-effect', 'non-scaling-stroke');
    });
    overlay.append(outline, route);
    map.getContainer().append(overlay);

    const render = () => {
        const points = coordinates
            .map(([lng, lat]) => map.project([lng, lat]))
            .filter((point) => Number.isFinite(point.x) && Number.isFinite(point.y));
        const pathData = points.map((point, index) => (
            `${index === 0 ? 'M' : 'L'}${point.x.toFixed(2)},${point.y.toFixed(2)}`
        )).join(' ');

        outline.setAttribute('d', pathData);
        route.setAttribute('d', pathData);
    };

    const setActive = (isActive) => {
        outline.setAttribute('stroke', isActive ? ROUTE_STYLE.activeOutline : ROUTE_STYLE.inactiveOutline);
        outline.setAttribute('stroke-width', isActive ? '10' : '8');
        outline.setAttribute('stroke-opacity', isActive ? '0.98' : '0.55');
        route.setAttribute('stroke', isActive ? ROUTE_STYLE.active : ROUTE_STYLE.inactive);
        route.setAttribute('stroke-width', isActive ? '6.5' : '4');
        route.setAttribute('stroke-opacity', isActive ? '1' : '0.72');
        overlay.style.zIndex = isActive ? '2' : '1';
    };

    map.on('move', render);
    map.on('resize', render);
    setActive(active);
    render();

    return {
        setActive,
        remove() {
            map.off('move', render);
            map.off('resize', render);
            overlay.remove();
        },
    };
};

const stepInstruction = (step, index) => {
    const type = String(step?.maneuver?.type || '').toLowerCase();
    const modifier = String(step?.maneuver?.modifier || '').replaceAll('_', ' ').toLowerCase();
    const roadName = String(step?.name || '').trim();
    const roadSuffix = roadName ? ` onto ${roadName}` : '';

    switch (type) {
    case 'depart':
        return index === 0 ? `Start and head${roadSuffix || ' toward the boarding house'}` : `Depart${roadSuffix}`;
    case 'arrive':
        return 'Arrive at the boarding house';
    case 'turn':
        return `Turn ${modifier || 'ahead'}${roadSuffix}`;
    case 'new name':
        return `Continue${roadSuffix}`;
    case 'merge':
        return `Merge ${modifier || 'ahead'}${roadSuffix}`;
    case 'fork':
        return `Keep ${modifier || 'ahead'} at the fork${roadSuffix}`;
    case 'roundabout':
    case 'rotary':
        return roadName ? `Enter the roundabout and continue to ${roadName}` : 'Enter the roundabout';
    case 'on ramp':
        return `Take the ramp${roadSuffix}`;
    case 'off ramp':
        return `Take the exit${roadSuffix}`;
    case 'end of road':
        return `At the end of the road, turn ${modifier || 'ahead'}${roadSuffix}`;
    case 'continue':
    case 'notification':
        return `Continue${roadSuffix || ' straight'}`;
    case 'uturn':
        return `Make a U-turn${roadSuffix}`;
    default:
        return roadName ? `Continue on ${roadName}` : 'Continue on the route';
    }
};

const markerHtml = (type) => {
    if (type === 'campus') {
        return '<div class="bm-map-marker bm-map-marker-campus"><span class="bm-map-campus-core">D</span></div>';
    }

    if (type === 'user') {
        return '<div class="bm-map-marker bm-map-marker-user"><span class="bm-map-user-pulse"></span><span class="bm-map-user-dot"></span></div>';
    }

    return `
        <div class="bm-map-marker bm-map-marker-house">
            <span class="bm-map-marker-core">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 21s7-4.8 7-11a7 7 0 1 0-14 0c0 6.2 7 11 7 11Z"></path>
                    <circle cx="12" cy="10" r="2.5"></circle>
                </svg>
            </span>
        </div>
    `;
};

class BoardingHouseLocationMap {
    constructor(root) {
        this.root = root;
        this.config = this.readConfig();
        this.messages = { ...DEFAULT_MESSAGES, ...(this.config.messages || {}) };
        this.elements = {
            canvas: root.querySelector('[data-map-canvas]'),
            loading: root.querySelector('[data-map-loading]'),
            unavailable: root.querySelector('[data-map-unavailable]'),
            unavailableMessage: root.querySelector('[data-map-unavailable-message]'),
            provider: root.querySelector('[data-map-provider]'),
            routeButton: root.querySelector('[data-route-current]'),
            routeDsscButton: root.querySelector('[data-route-dssc]'),
            resetButton: root.querySelector('[data-reset-map]'),
            routeStatus: root.querySelector('[data-route-status]'),
            routeDistance: root.querySelector('[data-route-distance]'),
            routeDuration: root.querySelector('[data-route-duration]'),
            routeOrigin: root.querySelector('[data-route-origin]'),
            routeDestination: root.querySelector('[data-route-destination]'),
            routeOptions: root.querySelector('[data-route-options]'),
            routeOptionsBadge: root.querySelector('[data-route-options-badge]'),
            routeSteps: root.querySelector('[data-route-steps]'),
            routeStepsBadge: root.querySelector('[data-route-steps-badge]'),
            openMap: root.querySelector('[data-open-map]'),
            largerMap: root.querySelector('[data-view-larger-map]'),
            modeButtons: [...root.querySelectorAll('[data-travel-mode]')],
        };

        this.destination = this.coordinateFromConfig(this.config.house);
        if (!isInsideServiceArea(this.destination?.lat, this.destination?.lng)) {
            this.destination = null;
        }
        this.campus = this.coordinateFromConfig(this.config.dssc);
        this.travelMode = 'DRIVING';
        this.defaultOriginLabel = 'Your current location';
        this.origin = null;
        this.originLabel = this.defaultOriginLabel;
        this.routeLayers = [];
        this.routeOptionButtons = [];
        this.availableRoutes = [];
        this.activeRouteIndex = -1;
        this.autoLocateAttempted = false;
        this.permissionDenied = false;
        this.busy = false;
        this.routeRequestId = 0;
        this.map = null;
        this.houseMarker = null;
        this.campusMarker = null;
        this.userMarker = null;
    }

    readConfig() {
        const script = this.root.querySelector('[data-map-config]');

        if (!script) {
            return {};
        }

        try {
            return JSON.parse(script.textContent || '{}');
        } catch (error) {
            console.error('Invalid boarding house map configuration.', error);
            return {};
        }
    }

    coordinateFromConfig(node = {}) {
        const latitude = numberOrNull(node?.latitude);
        const longitude = numberOrNull(node?.longitude);

        return latitude === null || longitude === null ? null : { lat: latitude, lng: longitude };
    }

    message(key) {
        return this.messages[key] || DEFAULT_MESSAGES[key] || '';
    }

    setLoading(isLoading) {
        this.elements.loading?.classList.toggle('hidden', !isLoading);
    }

    setBusy(isBusy) {
        this.busy = isBusy;

        const controls = [
            this.elements.routeButton,
            this.elements.routeDsscButton,
            this.elements.resetButton,
            ...this.elements.modeButtons,
        ];

        controls.forEach((control) => {
            if (!control) {
                return;
            }

            control.disabled = isBusy;
            control.setAttribute('aria-busy', String(isBusy));
            control.classList.toggle('opacity-60', isBusy);
            control.classList.toggle('cursor-wait', isBusy);
        });
    }

    showMetricsSkeleton() {
        if (this.elements.routeDistance) {
            this.elements.routeDistance.innerHTML = '<span class="inline-block h-4 w-12 animate-pulse rounded bg-slate-200/80 align-middle dark:bg-slate-700"></span>';
        }

        if (this.elements.routeDuration) {
            this.elements.routeDuration.innerHTML = '<span class="inline-block h-4 w-12 animate-pulse rounded bg-slate-200/80 align-middle dark:bg-slate-700"></span>';
        }
    }

    setRouteStatus(message, tone = 'neutral') {
        if (!this.elements.routeStatus) {
            return;
        }

        // Announce status changes to assistive tech.
        if (!this.elements.routeStatus.hasAttribute('aria-live')) {
            this.elements.routeStatus.setAttribute('aria-live', 'polite');
            this.elements.routeStatus.setAttribute('role', 'status');
        }

        const toneClasses = {
            neutral: ['border-slate-200', 'bg-slate-50', 'text-slate-600'],
            success: ['border-emerald-200', 'bg-emerald-50', 'text-emerald-800'],
            warning: ['border-amber-200', 'bg-amber-50', 'text-amber-800'],
            error: ['border-rose-200', 'bg-rose-50', 'text-rose-800'],
        };

        this.elements.routeStatus.textContent = message;
        Object.values(toneClasses).flat().forEach((className) => this.elements.routeStatus.classList.remove(className));
        (toneClasses[tone] || toneClasses.neutral).forEach((className) => this.elements.routeStatus.classList.add(className));
    }

    setProvider(label) {
        if (this.elements.provider) {
            this.elements.provider.textContent = label;
        }
    }

    showUnavailable(message) {
        this.elements.unavailable?.classList.remove('hidden');
        this.elements.unavailable?.classList.add('flex');
        if (this.elements.unavailableMessage) {
            this.elements.unavailableMessage.textContent = message;
        }
    }

    hideUnavailable() {
        this.elements.unavailable?.classList.add('hidden');
        this.elements.unavailable?.classList.remove('flex');
    }

    updateRouteOriginLabel() {
        if (this.elements.routeOrigin) {
            this.elements.routeOrigin.textContent = this.originLabel;
        }
    }

    updateRouteDestinationLabel() {
        if (this.elements.routeDestination) {
            this.elements.routeDestination.textContent = this.config.house?.name || 'Boarding House';
        }
    }

    updateRouteMetrics(distance, duration) {
        if (this.elements.routeDistance) {
            this.elements.routeDistance.textContent = formatRouteDistance(Number(distance));
        }

        if (this.elements.routeDuration) {
            this.elements.routeDuration.textContent = formatDuration(Number(duration));
        }
    }

    createPopupHtml(title, lines = []) {
        const paragraphs = lines
            .filter(Boolean)
            .map((line) => `<p>${escapeHtml(line)}</p>`)
            .join('');

        return `<div class="bm-map-info-window"><h3>${escapeHtml(title)}</h3>${paragraphs}</div>`;
    }

    async initMap() {
        const initialCenter = this.campus
            ? [
                (this.destination.lng + this.campus.lng) / 2,
                (this.destination.lat + this.campus.lat) / 2,
            ]
            : [this.destination.lng, this.destination.lat];

        this.map = createOpenStreetMap(this.elements.canvas, {
            center: initialCenter,
            zoom: this.campus ? 13 : 15,
            scrollZoom: false,
            openStreetMap: this.config.openStreetMap,
        });
        await whenMapLoaded(this.map);

        this.houseMarker = createHtmlMarker({
            map: this.map,
            lngLat: [this.destination.lng, this.destination.lat],
            html: markerHtml('house'),
            title: this.config.house?.name || 'Boarding House',
            anchor: 'bottom',
            popupOffset: 30,
            popupHtml: this.createPopupHtml(this.config.house?.name || 'Boarding House', [
                this.config.house?.address,
                this.config.house?.availabilityLabel,
                this.config.house?.priceLabel,
            ]),
        });
        this.houseMarker.getElement().style.zIndex = '3';

        if (this.campus) {
            this.campusMarker = createHtmlMarker({
                map: this.map,
                lngLat: [this.campus.lng, this.campus.lat],
                html: markerHtml('campus'),
                title: this.config.dssc?.name || 'DSSC Main Campus',
                anchor: 'center',
                popupOffset: 22,
                popupHtml: this.createPopupHtml(this.config.dssc?.name || 'DSSC Main Campus', [
                    this.config.dssc?.address,
                ]),
            });
            this.campusMarker.getElement().style.zIndex = '3';
        }

        this.fitInitialView();
    }

    fitInitialView() {
        if (!this.map || !this.destination) {
            return;
        }

        const points = [[this.destination.lng, this.destination.lat]];
        if (this.campus) {
            points.push([this.campus.lng, this.campus.lat]);
        }

        if (this.origin) {
            points.push([this.origin.lng, this.origin.lat]);
        }

        fitMapToLngLats(this.map, points, {
            padding: { top: 72, right: 64, bottom: 72, left: 64 },
            maxZoom: this.origin ? 15 : 14,
        });
    }

    ensureUserMarker() {
        if (!this.origin || !this.map) {
            return;
        }

        if (!this.userMarker) {
            this.userMarker = createHtmlMarker({
                map: this.map,
                lngLat: [this.origin.lng, this.origin.lat],
                html: markerHtml('user'),
                title: 'Your current location',
                anchor: 'center',
                popupOffset: 22,
                popupHtml: this.createPopupHtml('Your current location'),
            });
            this.userMarker.getElement().style.zIndex = '3';
            return;
        }

        this.userMarker.setLngLat([this.origin.lng, this.origin.lat]);
    }

    removeUserMarker() {
        if (!this.userMarker) {
            return;
        }

        this.userMarker.remove();
        this.userMarker = null;
    }

    bindControls() {
        this.elements.routeButton?.addEventListener('click', () => this.routeFromCurrentLocation());
        this.elements.routeDsscButton?.addEventListener('click', () => this.routeFromDssc());
        this.elements.resetButton?.addEventListener('click', () => this.resetMap());

        this.elements.modeButtons.forEach((button) => {
            button.addEventListener('click', async () => {
                if (this.busy) {
                    return;
                }

                const nextMode = String(button.dataset.travelMode || 'DRIVING');
                if (nextMode === this.travelMode && this.availableRoutes.length) {
                    return;
                }

                this.travelMode = nextMode;
                this.syncTravelModeButtons();

                if (this.origin) {
                    await this.renderRoute();
                    return;
                }

                if (this.permissionDenied) {
                    // Don't re-trigger the permission prompt after an explicit denial.
                    this.setRouteStatus(this.message('geolocationDenied'), 'warning');
                    return;
                }

                await this.routeFromCurrentLocation();
            });
        });

        document.querySelectorAll('[data-reservation-trigger]').forEach((button) => {
            button.addEventListener('click', () => {
                window.setTimeout(() => this.autoLocateFromReservationFlow(), 180);
            });
        });

        const usesDsscByDefault = this.root.dataset.defaultRouteOrigin === 'dssc';
        const reservationPanel = document.getElementById('reservation-panel');
        if (!usesDsscByDefault && reservationPanel && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        this.autoLocateFromReservationFlow();
                    }
                });
            }, { threshold: 0.55 });

            observer.observe(reservationPanel);
        }

        window.addEventListener('hashchange', () => {
            if (usesDsscByDefault) return;
            if (window.location.hash === '#reservation-panel') {
                this.autoLocateFromReservationFlow();
            }
        });
    }

    syncTravelModeButtons() {
        this.elements.modeButtons.forEach((button) => {
            const active = button.dataset.travelMode === this.travelMode;
            button.setAttribute('aria-pressed', String(active));
            button.classList.remove('border-blue-600', 'bg-blue-600', 'text-white', 'border-slate-200', 'bg-white', 'text-slate-700');

            if (active) {
                button.classList.add('border-blue-600', 'bg-blue-600', 'text-white');
            } else {
                button.classList.add('border-slate-200', 'bg-white', 'text-slate-700');
            }
        });
    }

    autoLocateFromReservationFlow() {
        if (this.autoLocateAttempted || !this.destination) {
            return;
        }

        this.autoLocateAttempted = true;
        this.routeFromCurrentLocation({ automatic: true }).catch((error) => {
            console.error('Automatic route preview failed.', error);
        });
    }

    getCurrentPosition(options) {
        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, options);
        });
    }

    async routeFromCurrentLocation() {
        if (this.busy) {
            return;
        }

        if (!isValidCoordinate(this.destination)) {
            this.setRouteStatus(this.message('missingCoordinates'), 'warning');
            return;
        }

        if (!navigator.geolocation) {
            this.handleLocationFailure(this.message('locationUnavailable'));
            return;
        }

        this.setBusy(true);
        this.setLoading(true);
        this.showMetricsSkeleton();
        this.setRouteStatus(this.message('requesting'));

        try {
            let position;

            try {
                position = await this.getCurrentPosition({
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 30000,
                });
            } catch (error) {
                // Permission denials are final; timeouts and position errors get
                // one lower-accuracy retry, which succeeds on many indoor devices.
                if (error?.code === 1) {
                    throw error;
                }

                position = await this.getCurrentPosition({
                    enableHighAccuracy: false,
                    timeout: 10000,
                    maximumAge: 120000,
                });
            }

            const origin = {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
            };

            if (!isValidCoordinate(origin)) {
                this.handleLocationFailure(this.message('locationUnavailable'));
                return;
            }

            this.permissionDenied = false;
            this.origin = origin;
            this.originLabel = this.defaultOriginLabel;
            this.updateRouteOriginLabel();
            this.ensureUserMarker();
            await this.renderRoute();
        } catch (error) {
            if (error?.code === 1) {
                this.permissionDenied = true;
                this.handleLocationFailure(this.message('geolocationDenied'));
            } else if (error?.code === 3) {
                this.handleLocationFailure(this.message('locationTimeout'));
            } else {
                this.handleLocationFailure(this.message('locationUnavailable'));
            }
        } finally {
            this.setLoading(false);
            this.setBusy(false);
        }
    }

    async routeFromDssc() {
        if (this.busy) {
            return;
        }

        if (!isValidCoordinate(this.destination)) {
            this.setRouteStatus(this.message('missingCoordinates'), 'warning');
            return;
        }

        if (!isValidCoordinate(this.campus)) {
            this.setRouteStatus('DSSC Main Campus coordinates are unavailable.', 'error');
            return;
        }

        this.origin = { ...this.campus };
        this.originLabel = this.config.dssc?.name || 'DSSC Main Campus';
        this.updateRouteOriginLabel();
        this.removeUserMarker();
        await this.renderRoute();
    }

    handleLocationFailure(message) {
        this.origin = null;
        this.originLabel = this.defaultOriginLabel;
        this.updateRouteOriginLabel();
        this.removeUserMarker();
        this.clearRoute();
        this.clearRouteOptions('Enable location services to see route options from your current location.');
        this.renderStepsPlaceholder('Enable location services to show turn-by-turn directions.');
        this.updateRouteMetrics(null, null);
        this.fitInitialView();
        this.setRouteStatus(message, 'warning');
    }

    async fetchRoutes() {
        const modeProfile = this.config.routing?.profiles?.[this.travelMode] || MODE_META[this.travelMode]?.profile || 'driving';
        const configuredBase = String(
            this.config.routing?.serviceUrl
            || boardMatchMapConfig().routing?.fallbackUrl
            || 'https://router.project-osrm.org/route/v1',
        ).replace(/\/$/, '');

        // Try mode-specific services first (real walking/driving profiles), then
        // the configured generic OSRM endpoint as a fallback.
        const services = [
            ...modeRoutingServices(modeProfile),
            { baseUrl: configuredBase, profile: modeProfile },
        ];

        const coordinates = `${this.origin.lng},${this.origin.lat};${this.destination.lng},${this.destination.lat}`;
        let lastError = null;

        for (const service of services) {
            const url = `${service.baseUrl.replace(/\/$/, '')}/${service.profile}/${coordinates}?alternatives=true&overview=full&steps=true&geometries=geojson&radiuses=100;100`;

            try {
                const controller = new AbortController();
                const timeoutId = window.setTimeout(() => controller.abort(), 12000);
                const response = await fetch(url, {
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                });
                window.clearTimeout(timeoutId);

                if (!response.ok) {
                    throw new Error(`Routing service responded with ${response.status}.`);
                }

                const payload = await response.json();
                if (payload.code !== 'Ok' || !(payload.routes || []).length) {
                    const failure = new Error(`OSRM route lookup failed (${payload.code || 'empty'}).`);
                    // OSRM reports "NoRoute"/"NoSegment" when the network genuinely
                    // has no path — that's a user-facing "no route", not an outage.
                    failure.isNoRoute = ['NoRoute', 'NoSegment', 'Ok'].includes(payload.code);
                    throw failure;
                }

                return payload.routes.map((route, index) => ({
                    id: `route-${index}`,
                    summary: this.routeSummary(route, index),
                    note: index === 0 ? 'Primary route' : 'Alternate route',
                    distance: Number(route.distance || 0),
                    duration: this.adjustedDuration(route),
                    coordinates: (route.geometry?.coordinates || []).map(([lng, lat]) => [lat, lng]),
                    steps: (route.legs || []).flatMap((leg) => leg.steps || []),
                }));
            } catch (error) {
                lastError = error;
            }
        }

        throw lastError || new Error('All routing services failed.');
    }

    // Guard against OSRM instances that only host a car profile: they return
    // driving speeds even when a walking route was requested. If the implied
    // speed is far above walking pace, recompute the duration at ~5 km/h.
    adjustedDuration(route) {
        const distance = Number(route.distance || 0);
        const duration = Number(route.duration || 0);

        if (this.travelMode !== 'WALKING' || distance <= 0 || duration <= 0) {
            return duration;
        }

        const speedKmh = (distance / duration) * 3.6;
        return speedKmh > 8 ? distance / (5000 / 3600) : duration;
    }

    routeSummary(route, index) {
        const names = (route.legs || [])
            .flatMap((leg) => leg.steps || [])
            .map((step) => String(step.name || '').trim())
            .filter(Boolean)
            .filter((name, nameIndex, all) => all.indexOf(name) === nameIndex)
            .slice(0, 2);

        const prefix = index === 0 ? travelModeLabel(this.travelMode) : 'Alternate';
        return names.length ? `${prefix} via ${names.join(' / ')}` : `${prefix} route`;
    }

    drawRoutes(routes) {
        this.clearRoute();

        this.routeLayers = routes.map((route, index) => {
            const lngLatCoordinates = route.coordinates.map(([lat, lng]) => [lng, lat]);
            const highlightedCoordinates = [
                [this.origin.lng, this.origin.lat],
                ...lngLatCoordinates,
                [this.destination.lng, this.destination.lat],
            ];

            return createProjectedRouteOverlay(this.map, highlightedCoordinates, index === 0);
        });
    }

    selectRoute(index, { fit = true } = {}) {
        const route = this.availableRoutes[index];
        if (!route) {
            return;
        }

        this.activeRouteIndex = index;
        this.routeLayers.forEach((layer, routeIndex) => {
            layer.setActive(routeIndex === index);
        });

        this.routeOptionButtons.forEach((button, routeIndex) => {
            const active = routeIndex === index;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });

        if (this.elements.routeOptionsBadge) {
            this.elements.routeOptionsBadge.textContent = this.availableRoutes.length > 1
                ? `${index + 1} of ${this.availableRoutes.length}`
                : 'Best route';
        }

        this.updateRouteMetrics(route.distance, route.duration);
        this.renderSteps(route.steps);

        if (fit && route.coordinates.length) {
            const routePoints = route.coordinates.map(([lat, lng]) => [lng, lat]);
            if (this.origin) {
                routePoints.unshift([this.origin.lng, this.origin.lat]);
            }
            routePoints.push([this.destination.lng, this.destination.lat]);

            fitMapToLngLats(
                this.map,
                routePoints,
                {
                    padding: { top: 72, right: 64, bottom: 72, left: 64 },
                    maxZoom: 15,
                },
            );
        }
    }

    setRouteOptions(routes) {
        if (!this.elements.routeOptions) {
            return;
        }

        this.routeOptionButtons = [];
        this.elements.routeOptions.innerHTML = '';

        routes.forEach((route, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'bm-route-option';
            button.setAttribute('aria-pressed', 'false');
            button.innerHTML = `
                <span class="bm-route-option__eyebrow">${escapeHtml(route.note)}</span>
                <span class="bm-route-option__summary">${escapeHtml(route.summary)}</span>
                <span class="bm-route-option__meta">
                    <span>${escapeHtml(formatDuration(route.duration))}</span>
                    <span>${escapeHtml(formatRouteDistance(route.distance))}</span>
                </span>
            `;
            button.addEventListener('click', () => this.selectRoute(index));
            this.elements.routeOptions.appendChild(button);
            this.routeOptionButtons.push(button);
        });

        if (this.elements.routeOptionsBadge) {
            this.elements.routeOptionsBadge.textContent = routes.length > 1
                ? `${routes.length} options`
                : 'Best route';
        }
    }

    clearRouteOptions(message = null) {
        if (!this.elements.routeOptions) {
            return;
        }

        this.routeOptionButtons = [];
        this.availableRoutes = [];
        this.activeRouteIndex = -1;
        this.elements.routeOptions.innerHTML = `
            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-3 text-xs font-medium text-slate-500">
                ${escapeHtml(message || 'Route options will appear here after you choose a starting point.')}
            </div>
        `;

        if (this.elements.routeOptionsBadge) {
            this.elements.routeOptionsBadge.textContent = 'Waiting';
        }
    }

    renderStepsPlaceholder(message) {
        if (!this.elements.routeSteps) {
            return;
        }

        this.elements.routeSteps.innerHTML = `
            <div class="rounded-xl border border-dashed border-slate-200 bg-white px-4 py-4 text-sm text-slate-500">
                ${escapeHtml(message)}
            </div>
        `;

        if (this.elements.routeStepsBadge) {
            this.elements.routeStepsBadge.textContent = 'Waiting for route';
        }
    }

    renderSteps(steps = []) {
        if (!this.elements.routeSteps) {
            return;
        }

        if (!steps.length) {
            this.renderStepsPlaceholder('Turn-by-turn directions will appear here after the route is loaded.');
            return;
        }

        this.elements.routeSteps.innerHTML = steps.map((step, index) => `
            <div class="bm-route-step">
                <div class="bm-route-step__count">${index + 1}</div>
                <div class="bm-route-step__body">
                    <p class="bm-route-step__instruction">${escapeHtml(stepInstruction(step, index))}</p>
                    <div class="bm-route-step__meta">
                        <span>${escapeHtml(formatDistance(Number(step.distance || 0)))}</span>
                        <span>${escapeHtml(formatDuration(Number(step.duration || 0)))}</span>
                    </div>
                </div>
            </div>
        `).join('');

        if (this.elements.routeStepsBadge) {
            this.elements.routeStepsBadge.textContent = `${steps.length} steps`;
        }
    }

    clearRoute() {
        this.routeLayers.forEach((layer) => layer.remove());
        this.routeLayers = [];
    }

    async renderRoute() {
        if (!isValidCoordinate(this.origin) || !isValidCoordinate(this.destination)) {
            this.setRouteStatus(this.message('routeFailed'), 'error');
            return;
        }

        const requestId = ++this.routeRequestId;
        const modeLabel = travelModeLabel(this.travelMode).toLowerCase();
        this.setBusy(true);
        this.setLoading(true);
        this.showMetricsSkeleton();
        this.renderStepsPlaceholder(`Calculating ${modeLabel} directions…`);
        this.setRouteStatus(`Calculating ${modeLabel} route…`);

        try {
            const routes = await this.fetchRoutes();

            // A newer request (e.g. rapid mode switch) superseded this one.
            if (requestId !== this.routeRequestId) {
                return;
            }

            if (!routes.length) {
                const noRoute = new Error('No routes returned from OSRM.');
                noRoute.isNoRoute = true;
                throw noRoute;
            }

            this.availableRoutes = routes;
            this.drawRoutes(routes);
            this.setRouteOptions(routes);
            this.selectRoute(0);
            this.ensureUserMarker();

            const modeNote = MODE_META[this.travelMode]?.note
                ? ` ${this.message(MODE_META[this.travelMode].note)}`
                : '';
            this.setRouteStatus(`${travelModeLabel(this.travelMode)} route updated.${modeNote}`.trim(), modeNote ? 'warning' : 'success');
        } catch (error) {
            if (requestId !== this.routeRequestId) {
                return;
            }

            console.error('Route generation failed.', error);
            this.clearRoute();
            this.availableRoutes = [];
            this.updateRouteMetrics(null, null);

            const isNoRoute = error?.isNoRoute || /NoRoute|No route/i.test(String(error?.message || ''));
            const failureMessage = isNoRoute
                ? `No ${modeLabel} route is available from this location.`
                : this.message('routeFailed');

            this.clearRouteOptions(failureMessage);
            this.renderStepsPlaceholder(failureMessage);
            this.setRouteStatus(isNoRoute ? this.message('noRoute') : this.message('routeFailed'), 'error');

            // Keep both markers visible and frame them so the map is never blank.
            this.ensureUserMarker();
            this.fitInitialView();
        } finally {
            if (requestId === this.routeRequestId) {
                this.setLoading(false);
                this.setBusy(false);
            }
        }
    }

    resetMap() {
        if (this.busy) {
            return;
        }

        this.routeRequestId += 1; // invalidate any in-flight request
        this.origin = null;
        this.originLabel = this.defaultOriginLabel;
        this.permissionDenied = false;
        this.updateRouteOriginLabel();
        this.updateRouteMetrics(null, null);
        this.clearRoute();
        this.clearRouteOptions();
        this.renderStepsPlaceholder('Turn-by-turn directions will appear here after the route is loaded.');
        this.removeUserMarker();
        this.fitInitialView();
        this.setRouteStatus(this.message('reset'));
        this.autoLocateAttempted = false;
    }

    async init() {
        this.bindControls();
        this.syncTravelModeButtons();
        this.updateRouteOriginLabel();
        this.updateRouteDestinationLabel();
        this.clearRouteOptions();
        this.renderStepsPlaceholder('Turn-by-turn directions will appear here after the route is loaded.');
        this.updateRouteMetrics(null, null);
        const basemap = String(openStreetMapConfig(this.config.openStreetMap).basemap).toLowerCase();
        this.setProvider(basemap === 'hybrid' ? 'MapLibre Hybrid + OSRM' : 'OpenStreetMap + OSRM');

        if (!this.destination) {
            const configuredHouseCoordinates = this.coordinateFromConfig(this.config.house);
            const unavailableMessage = configuredHouseCoordinates
                ? serviceAreaMessage()
                : this.message('missingCoordinates');
            this.setLoading(false);
            this.showUnavailable(unavailableMessage);
            this.setRouteStatus(unavailableMessage, 'warning');
            return;
        }

        this.hideUnavailable();
        await this.initMap();
        this.setRouteStatus(this.message('initial'));
        const resizeMap = () => this.map?.resize();
        window.setTimeout(resizeMap, 140);
        window.addEventListener('resize', resizeMap);
        if ('ResizeObserver' in window) {
            this.resizeObserver = new ResizeObserver(resizeMap);
            this.resizeObserver.observe(this.root);
        }

        if (this.root.dataset.autoRouteOnLoad === 'true') {
            window.setTimeout(() => this.autoLocateFromReservationFlow(), 220);
        }

        if (this.root.dataset.defaultRouteOrigin === 'dssc') {
            window.setTimeout(() => this.routeFromDssc(), 220);
        } else if (window.location.hash === '#reservation-panel') {
            window.setTimeout(() => this.autoLocateFromReservationFlow(), 240);
        }
    }
}

const initializeBoardingHouseMaps = () => {
    document.querySelectorAll(MAP_ROOT_SELECTOR).forEach((root) => {
        if (root.dataset.mapInitialized === 'true') {
            return;
        }

        root.dataset.mapInitialized = 'true';
        const locationMap = new BoardingHouseLocationMap(root);
        locationMap.init().catch((error) => {
            console.error('Boarding house map failed to initialize.', error);
            locationMap.setLoading(false);
            locationMap.showUnavailable(locationMap.message('routeFailed'));
        });
    });
};

document.addEventListener('DOMContentLoaded', initializeBoardingHouseMaps);
