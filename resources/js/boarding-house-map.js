const MAP_ROOT_SELECTOR = '[data-boardmatch-location-map]';
const GOOGLE_SCRIPT_ID = 'boardmatch-google-maps-api';

const DEFAULT_MESSAGES = {
    initial: 'Choose a travel mode, then select where to start your route.',
    reset: 'Map reset. Choose an origin to preview directions again.',
    missingCoordinates: 'Map route is unavailable because this boarding house has no saved coordinates.',
    geolocationDenied: 'Unable to access your current location. You can still route from DSSC Main Campus or open this location in Google Maps.',
    routeFailed: 'Route could not be generated for this location. Please try opening it in Google Maps.',
    googleMapsFailed: 'Map failed to load. Please check your internet connection or Google Maps API key.',
    streetViewUnavailable: 'Street View is not available for this location.',
    motorcycleUnavailable: 'Motorcycle directions are not available in this map setup.',
    transitUnavailable: 'Transit directions are not available for this route right now.',
};

let googleMapsLoader;

const numberOrNull = (value) => {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : null;
};

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

const formatDuration = (milliseconds) => {
    if (!Number.isFinite(milliseconds)) {
        return '--';
    }

    const totalMinutes = Math.max(1, Math.round(milliseconds / 60000));
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    if (hours === 0) {
        return `${totalMinutes} min`;
    }

    return minutes === 0 ? `${hours} hr` : `${hours} hr ${minutes} min`;
};

const haversineMeters = (origin, destination) => {
    const earthRadius = 6371000;
    const toRadians = (degrees) => degrees * (Math.PI / 180);
    const latitudeDelta = toRadians(destination.lat - origin.lat);
    const longitudeDelta = toRadians(destination.lng - origin.lng);
    const originLatitude = toRadians(origin.lat);
    const destinationLatitude = toRadians(destination.lat);
    const a = Math.sin(latitudeDelta / 2) ** 2
        + Math.cos(originLatitude)
        * Math.cos(destinationLatitude)
        * Math.sin(longitudeDelta / 2) ** 2;

    return earthRadius * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
};

const estimateDuration = (meters, mode) => {
    const speeds = {
        DRIVING: 8.33,
        WALKING: 1.34,
        TRANSIT: 6.2,
        TWO_WHEELER: 9.2,
    };

    return (meters / (speeds[mode] || speeds.WALKING)) * 1000;
};

const routeNote = (index, estimated = false) => {
    if (estimated) {
        return 'Estimated route';
    }

    return index === 0 ? 'Fastest route' : 'Alternative route';
};

const travelModeLabel = (mode) => ({
    DRIVING: 'Driving',
    WALKING: 'Walking',
    TRANSIT: 'Transit',
    TWO_WHEELER: 'Motorcycle',
}[mode] || 'Route');

const googleTravelMode = (mode) => {
    if (mode === 'TRANSIT') return 'TRANSIT';
    if (mode === 'WALKING') return 'WALKING';
    return 'DRIVING';
};

const googleUrlTravelMode = (mode) => {
    if (mode === 'TRANSIT') return 'transit';
    if (mode === 'WALKING') return 'walking';
    return 'driving';
};

const summaryLabel = (summary) => {
    const trimmed = String(summary || '').trim();

    return trimmed ? `via ${trimmed}` : 'via local roads';
};

const osrmSummary = (route) => {
    const names = (route.legs || [])
        .flatMap((leg) => leg.steps || [])
        .map((step) => String(step.name || '').trim())
        .filter(Boolean)
        .filter((name, index, all) => all.indexOf(name) === index);

    return summaryLabel(names.slice(0, 2).join(' / '));
};

const sumRouteDistance = (route) => (route.legs || [])
    .reduce((sum, leg) => sum + Number(leg.distance?.value || 0), 0);

const sumRouteDuration = (route) => (route.legs || [])
    .reduce((sum, leg) => sum + (Number(leg.duration?.value || 0) * 1000), 0);

const loadGoogleMaps = (settings) => {
    if (window.google?.maps?.importLibrary) {
        return Promise.resolve(window.google.maps);
    }

    if (googleMapsLoader) {
        return googleMapsLoader;
    }

    googleMapsLoader = new Promise((resolve, reject) => {
        const existing = document.getElementById(GOOGLE_SCRIPT_ID);
        if (existing) {
            existing.addEventListener('load', () => resolve(window.google?.maps), { once: true });
            existing.addEventListener('error', reject, { once: true });
            return;
        }

        const callbackName = '__boardMatchGoogleMapsReady';
        const parameters = new URLSearchParams({
            key: settings.apiKey,
            loading: 'async',
            callback: callbackName,
            v: 'weekly',
            language: settings.language || 'en',
            region: settings.region || 'PH',
            auth_referrer_policy: 'origin',
        });
        const script = document.createElement('script');

        window[callbackName] = () => {
            delete window[callbackName];
            resolve(window.google.maps);
        };

        script.id = GOOGLE_SCRIPT_ID;
        script.async = true;
        script.src = `https://maps.googleapis.com/maps/api/js?${parameters.toString()}`;
        script.onerror = () => {
            delete window[callbackName];
            reject(new Error('Google Maps JavaScript API could not be loaded.'));
        };
        document.head.append(script);
    });

    return googleMapsLoader;
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
            openMaps: root.querySelector('[data-open-google-maps]'),
            largerMap: root.querySelector('[data-view-larger-map]'),
            streetViewLink: root.querySelector('[data-open-street-view]'),
            streetViewCanvas: root.querySelector('[data-street-view-canvas]'),
            streetViewStatus: root.querySelector('[data-street-view-status]'),
            modeButtons: [...root.querySelectorAll('[data-travel-mode]')],
        };

        this.supports = {
            twoWheeler: Boolean(this.config.supports?.twoWheeler),
            transit: this.config.supports?.transit !== false,
        };

        this.destination = this.coordinateFromConfig();
        this.campus = this.coordinateFromDsscConfig();
        this.defaultOriginLabel = 'Your current location';
        this.origin = null;
        this.originLabel = this.defaultOriginLabel;
        this.originType = null;
        this.travelMode = 'WALKING';
        this.provider = null;
        this.map = null;
        this.houseMarker = null;
        this.campusMarker = null;
        this.userMarker = null;
        this.userInfoWindow = null;
        this.googleInfoWindow = null;
        this.routeLayers = [];
        this.availableRoutes = [];
        this.activeRouteIndex = -1;
        this.routeOptionButtons = [];
        this.streetView = null;
        this.googleInitFailed = false;
        this.usedFallbackRouting = false;
    }

    message(key) {
        return this.messages[key] || DEFAULT_MESSAGES[key] || '';
    }

    readConfig() {
        const script = this.root.querySelector('[data-map-config]');

        if (!script) {
            return {};
        }

        try {
            return JSON.parse(script.textContent);
        } catch (error) {
            console.error('Invalid boarding house map configuration.', error);
            return {};
        }
    }

    coordinateFromConfig() {
        const latitude = numberOrNull(this.config.house?.latitude);
        const longitude = numberOrNull(this.config.house?.longitude);

        if (latitude === null || longitude === null) {
            return null;
        }

        return { lat: latitude, lng: longitude };
    }

    coordinateFromDsscConfig() {
        const latitude = numberOrNull(this.config.dssc?.latitude);
        const longitude = numberOrNull(this.config.dssc?.longitude);

        return latitude === null || longitude === null ? null : { lat: latitude, lng: longitude };
    }

    async init() {
        this.bindControls();
        this.updateModeAvailability();
        this.updateRouteOriginLabel();
        this.updateRouteDestinationLabel();
        this.clearRouteOptions();
        this.updateRouteMetrics(null, null);
        this.syncExternalLinks();

        if (!this.destination) {
            this.setProvider('Address only');
            this.setRouteStatus(this.message('missingCoordinates'), 'warning');
            this.showUnavailable(this.message('missingCoordinates'));
            if (this.elements.streetViewStatus) {
                this.elements.streetViewStatus.textContent = this.message('streetViewUnavailable');
            }
            return;
        }

        if (this.config.googleMaps?.apiKey) {
            try {
                await this.initGoogleMap();
                return;
            } catch (error) {
                this.googleInitFailed = true;
                console.error('Google Maps initialization failed; using the fallback map.', error);
            }
        }

        try {
            await this.initLeafletMap();
        } catch (error) {
            console.error('Fallback map initialization failed.', error);
            this.showUnavailable(this.googleInitFailed ? this.message('googleMapsFailed') : this.message('routeFailed'));
            return;
        }

        if (this.googleInitFailed) {
            this.setRouteStatus(`${this.message('googleMapsFailed')} Showing a fallback map instead.`, 'warning');
        }
    }

    bindControls() {
        this.elements.routeButton?.addEventListener('click', () => this.routeFromCurrentLocation());
        this.elements.routeDsscButton?.addEventListener('click', () => this.routeFromDssc());
        this.elements.resetButton?.addEventListener('click', () => this.resetMap());

        this.elements.modeButtons.forEach((button) => {
            button.addEventListener('click', async () => {
                if (button.disabled) {
                    const reason = button.dataset.disabledReason;
                    if (reason) {
                        this.setRouteStatus(reason, 'warning');
                    }
                    return;
                }

                this.travelMode = String(button.dataset.travelMode || 'WALKING');
                this.syncTravelModeButtons();
                this.syncExternalLinks();

                if (this.origin) {
                    await this.renderRoute();
                } else {
                    this.setRouteStatus(this.message('initial'));
                }
            });
        });
    }

    updateModeAvailability() {
        this.elements.modeButtons.forEach((button) => {
            const mode = String(button.dataset.travelMode || '');
            button.disabled = false;
            delete button.dataset.disabledReason;

            if (mode === 'TWO_WHEELER' && !this.supports.twoWheeler) {
                button.disabled = true;
                button.dataset.disabledReason = this.message('motorcycleUnavailable');
            }

            if (mode === 'TRANSIT' && (!this.supports.transit || this.provider === 'leaflet' || !this.config.googleMaps?.apiKey)) {
                button.disabled = true;
                button.dataset.disabledReason = this.message('transitUnavailable');
            }
        });

        if (this.elements.modeButtons.some((button) => button.dataset.travelMode === this.travelMode && button.disabled)) {
            this.travelMode = 'WALKING';
        }

        this.syncTravelModeButtons();
    }

    syncTravelModeButtons() {
        this.elements.modeButtons.forEach((button) => {
            const active = button.dataset.travelMode === this.travelMode && !button.disabled;
            button.setAttribute('aria-pressed', String(active));

            button.classList.remove(
                'border-blue-600',
                'bg-blue-600',
                'text-white',
                'border-slate-200',
                'bg-white',
                'text-slate-700',
                'bg-slate-100',
                'text-slate-400',
                'cursor-not-allowed',
                'opacity-50',
            );

            if (button.disabled) {
                button.classList.add('border-slate-200', 'bg-slate-100', 'text-slate-400', 'cursor-not-allowed', 'opacity-50');
            } else if (active) {
                button.classList.add('border-blue-600', 'bg-blue-600', 'text-white');
            } else {
                button.classList.add('border-slate-200', 'bg-white', 'text-slate-700');
            }
        });
    }

    setLoading(loading) {
        this.elements.loading?.classList.toggle('hidden', !loading);

        [
            this.elements.routeButton,
            this.elements.routeDsscButton,
            this.elements.resetButton,
        ].forEach((button) => {
            if (!button) return;

            if (loading) {
                button.setAttribute('disabled', 'disabled');
            } else {
                button.removeAttribute('disabled');
            }
            button.classList.toggle('opacity-60', loading);
            button.classList.toggle('cursor-wait', loading);
        });
    }

    setRouteStatus(message, tone = 'neutral') {
        if (!this.elements.routeStatus) {
            return;
        }

        const toneClasses = {
            neutral: ['border-slate-200', 'bg-slate-50', 'text-slate-600'],
            success: ['border-emerald-200', 'bg-emerald-50', 'text-emerald-800'],
            warning: ['border-amber-200', 'bg-amber-50', 'text-amber-800'],
            error: ['border-rose-200', 'bg-rose-50', 'text-rose-800'],
        };

        Object.values(toneClasses).flat().forEach((className) => this.elements.routeStatus.classList.remove(className));
        toneClasses[tone].forEach((className) => this.elements.routeStatus.classList.add(className));
        this.elements.routeStatus.textContent = message;
    }

    setProvider(label) {
        if (this.elements.provider) {
            this.elements.provider.textContent = label;
        }
    }

    hideUnavailable() {
        this.elements.canvas?.classList.remove('hidden');
        if (this.elements.unavailable) {
            this.elements.unavailable.classList.add('hidden');
            this.elements.unavailable.classList.remove('flex');
        }
    }

    showUnavailable(message) {
        this.setLoading(false);
        this.clearRoute();
        this.clearRouteOptions(message);
        this.elements.canvas?.classList.add('hidden');

        if (this.elements.unavailable) {
            this.elements.unavailable.classList.remove('hidden');
            this.elements.unavailable.classList.add('flex');
            const messageElement = this.elements.unavailable.querySelector('[data-map-unavailable-message]');
            if (messageElement) {
                messageElement.textContent = message;
            }
        }

        [this.elements.routeButton, this.elements.routeDsscButton, this.elements.resetButton]
            .forEach((button) => {
                if (!button) return;
                button.setAttribute('disabled', 'disabled');
                button.classList.add('cursor-not-allowed', 'opacity-50');
            });
    }

    renderHouseInfoHtml() {
        const lines = [
            this.config.house?.address,
            this.config.house?.monthlyRentLabel ? `${this.config.house.monthlyRentLabel}/month` : this.config.house?.priceLabel,
            this.config.house?.availabilityLabel,
            this.config.house?.distanceLabel,
            'View Details',
        ].filter(Boolean);

        return `
            <div class="bm-map-info-window">
                <h3>${escapeHtml(this.config.house?.name || 'Boarding House')}</h3>
                ${lines.map((line) => `<p>${escapeHtml(line)}</p>`).join('')}
            </div>
        `;
    }

    renderSimpleInfoHtml(title, lines = []) {
        return `
            <div class="bm-map-info-window">
                <h3>${escapeHtml(title)}</h3>
                ${lines.filter(Boolean).map((line) => `<p>${escapeHtml(line)}</p>`).join('')}
            </div>
        `;
    }

    createGoogleMarker(position, type, title, infoHtml = null) {
        const content = document.createElement('div');
        content.className = `bm-map-marker bm-map-marker-${type}`;
        content.setAttribute('aria-label', title);
        content.innerHTML = type === 'house'
            ? '<span class="bm-map-marker-core"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18M5 21V9l7-5 7 5v12M9 21v-6h6v6M8 11h2M14 11h2"/></svg></span>'
            : (type === 'campus'
                ? '<span class="bm-map-campus-core">D</span>'
                : '<span class="bm-map-user-pulse"></span><span class="bm-map-user-dot"></span>');

        const marker = new this.google.AdvancedMarkerElement({
            map: this.map,
            position,
            title,
            content,
            gmpClickable: true,
        });

        if (infoHtml) {
            marker.addListener('click', () => {
                this.googleInfoWindow.setContent(infoHtml);
                this.googleInfoWindow.open({
                    anchor: marker,
                    map: this.map,
                });
            });
        }

        return marker;
    }

    async initGoogleMap() {
        this.setLoading(true);
        this.hideUnavailable();
        await loadGoogleMaps(this.config.googleMaps);
        const { Map, InfoWindow } = await google.maps.importLibrary('maps');
        const { AdvancedMarkerElement } = await google.maps.importLibrary('marker');

        this.provider = 'google';
        this.google = { Map, InfoWindow, AdvancedMarkerElement };
        this.googleInfoWindow = new InfoWindow();
        this.map = new Map(this.elements.canvas, {
            center: this.destination,
            zoom: 16,
            mapId: this.config.googleMaps.mapId || 'DEMO_MAP_ID',
            mapTypeId: 'hybrid',
            clickableIcons: true,
            fullscreenControl: true,
            gestureHandling: 'cooperative',
            mapTypeControl: true,
            mapTypeControlOptions: {
                mapTypeIds: ['hybrid', 'satellite', 'roadmap'],
                style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
            },
            streetViewControl: true,
            zoomControl: true,
        });

        this.houseMarker = this.createGoogleMarker(
            this.destination,
            'house',
            this.config.house?.name || 'Boarding House',
            this.renderHouseInfoHtml()
        );

        if (this.campus) {
            this.campusMarker = this.createGoogleMarker(
                this.campus,
                'campus',
                this.config.dssc?.name || 'DSSC Main Campus',
                this.renderSimpleInfoHtml(
                    this.config.dssc?.name || 'DSSC Main Campus',
                    [this.config.dssc?.address || 'Matti, Digos City']
                )
            );
        }

        this.fitInitialView();
        this.setProvider('Google Maps hybrid');
        this.updateModeAvailability();
        this.setRouteStatus(this.message('initial'));
        this.syncExternalLinks();
        this.setLoading(false);
        await this.loadStreetView();
    }

    async loadStreetView() {
        const canvas = this.elements.streetViewCanvas;
        const statusElement = this.elements.streetViewStatus;

        if (!canvas || !statusElement || !this.destination || this.provider !== 'google') {
            return;
        }

        try {
            const { StreetViewPanorama, StreetViewService, StreetViewStatus } = await google.maps.importLibrary('streetView');
            const service = new StreetViewService();

            service.getPanorama({
                location: this.destination,
                radius: 200,
                preference: google.maps.StreetViewPreference.NEAREST,
            }, (data, status) => {
                if (status !== StreetViewStatus.OK || !data?.location?.latLng) {
                    statusElement.textContent = this.message('streetViewUnavailable');
                    return;
                }

                canvas.classList.remove('hidden');
                statusElement.classList.add('hidden');
                this.streetView = new StreetViewPanorama(canvas, {
                    position: data.location.latLng,
                    pov: {
                        heading: this.bearingBetween(data.location.latLng.toJSON(), this.destination),
                        pitch: 0,
                    },
                    addressControl: true,
                    fullscreenControl: true,
                    motionTracking: false,
                    linksControl: true,
                    panControl: true,
                    zoomControl: true,
                });
            });
        } catch (error) {
            console.error('Street View initialization failed.', error);
            statusElement.textContent = this.message('streetViewUnavailable');
        }
    }

    bearingBetween(origin, destination) {
        const toRadians = (degrees) => degrees * (Math.PI / 180);
        const toDegrees = (radians) => radians * (180 / Math.PI);
        const longitudeDelta = toRadians(destination.lng - origin.lng);
        const originLatitude = toRadians(origin.lat);
        const destinationLatitude = toRadians(destination.lat);
        const y = Math.sin(longitudeDelta) * Math.cos(destinationLatitude);
        const x = Math.cos(originLatitude) * Math.sin(destinationLatitude)
            - Math.sin(originLatitude) * Math.cos(destinationLatitude) * Math.cos(longitudeDelta);

        return (toDegrees(Math.atan2(y, x)) + 360) % 360;
    }

    async initLeafletMap() {
        this.setLoading(true);
        this.hideUnavailable();
        await import('leaflet/dist/leaflet.css');
        const leaflet = await import('leaflet');
        const L = leaflet.default;

        this.provider = 'leaflet';
        this.L = L;
        this.map = L.map(this.elements.canvas, {
            attributionControl: true,
            scrollWheelZoom: false,
            zoomControl: false,
        }).setView([this.destination.lat, this.destination.lng], 16);

        L.control.zoom({ position: 'topright' }).addTo(this.map);
        L.control.scale({ imperial: false, position: 'bottomleft' }).addTo(this.map);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(this.map);

        this.houseMarker = L.marker([this.destination.lat, this.destination.lng], {
            icon: L.divIcon({
                className: '',
                html: '<div class="bm-map-marker bm-map-marker-house"><span class="bm-map-marker-core"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18M5 21V9l7-5 7 5v12M9 21v-6h6v6M8 11h2M14 11h2"/></svg></span></div>',
                iconAnchor: [23, 44],
                iconSize: [46, 46],
                popupAnchor: [0, -42],
            }),
            title: this.config.house?.name || 'Boarding House',
        }).addTo(this.map).bindPopup(this.renderHouseInfoHtml());

        if (this.campus) {
            this.campusMarker = L.marker([this.campus.lat, this.campus.lng], {
                icon: L.divIcon({
                    className: '',
                    html: '<div class="bm-map-marker bm-map-marker-campus"><span class="bm-map-campus-core">D</span></div>',
                    iconAnchor: [19, 19],
                    iconSize: [38, 38],
                    popupAnchor: [0, -20],
                }),
                title: this.config.dssc?.name || 'DSSC Main Campus',
            }).addTo(this.map).bindPopup(
                this.renderSimpleInfoHtml(
                    this.config.dssc?.name || 'DSSC Main Campus',
                    [this.config.dssc?.address || 'Matti, Digos City']
                )
            );
        }

        this.fitInitialView();
        this.setProvider(this.googleInitFailed ? 'OpenStreetMap fallback' : 'OpenStreetMap');
        this.updateModeAvailability();
        this.syncExternalLinks();
        this.setLoading(false);

        if (this.elements.streetViewStatus) {
            this.elements.streetViewStatus.textContent = this.message('streetViewUnavailable');
        }

        if (!this.googleInitFailed) {
            this.setRouteStatus(this.message('initial'));
        }
    }

    fitInitialView() {
        if (!this.destination || !this.map) {
            return;
        }

        if (this.provider === 'google') {
            if (this.origin) {
                this.fitGoogleBounds(this.availableRoutes[this.activeRouteIndex]?.path || []);
                return;
            }

            this.map.setCenter(this.destination);
            this.map.setZoom(16);
            return;
        }

        if (this.origin) {
            const path = this.availableRoutes[this.activeRouteIndex]?.path || [];
            const points = path.length
                ? path.map((point) => [point.lat, point.lng])
                : [
                    [this.origin.lat, this.origin.lng],
                    [this.destination.lat, this.destination.lng],
                ];
            this.map.fitBounds(points, { padding: [40, 40], maxZoom: 16 });
            return;
        }

        this.map.setView([this.destination.lat, this.destination.lng], 16);
    }

    updateRouteOriginLabel() {
        if (this.elements.routeOrigin) {
            this.elements.routeOrigin.textContent = this.originLabel;
        }
    }

    updateRouteDestinationLabel() {
        if (this.elements.routeDestination) {
            this.elements.routeDestination.textContent = this.config.house?.name || this.config.house?.address || 'Boarding House';
        }
    }

    setOrigin(position, label, type) {
        this.origin = { ...position };
        this.originLabel = label;
        this.originType = type;
        this.updateRouteOriginLabel();
        this.addOrUpdateOriginMarker();
        this.syncExternalLinks();
    }

    addOrUpdateOriginMarker() {
        if (this.originType !== 'user' || !this.origin) {
            this.removeUserMarker();
            return;
        }

        if (this.provider === 'google') {
            if (!this.userMarker) {
                this.userMarker = this.createGoogleMarker(
                    this.origin,
                    'user',
                    'You are here',
                    this.renderSimpleInfoHtml('You are here')
                );
                return;
            }

            this.userMarker.position = this.origin;
            return;
        }

        if (this.userMarker) {
            this.userMarker.setLatLng([this.origin.lat, this.origin.lng]);
            return;
        }

        this.userMarker = this.L.marker([this.origin.lat, this.origin.lng], {
            icon: this.L.divIcon({
                className: '',
                html: '<div class="bm-map-marker bm-map-marker-user"><span class="bm-map-user-pulse"></span><span class="bm-map-user-dot"></span></div>',
                iconAnchor: [18, 18],
                iconSize: [36, 36],
            }),
            title: 'You are here',
        }).addTo(this.map).bindPopup(this.renderSimpleInfoHtml('You are here'));
    }

    removeUserMarker() {
        if (!this.userMarker) {
            return;
        }

        if (this.provider === 'google') {
            this.userMarker.map = null;
        } else {
            this.userMarker.remove();
        }

        this.userMarker = null;
    }

    async routeFromCurrentLocation() {
        if (!this.destination) {
            this.setRouteStatus(this.message('missingCoordinates'), 'warning');
            return;
        }

        if (!navigator.geolocation) {
            this.setRouteStatus(this.message('geolocationDenied'), 'error');
            return;
        }

        this.setLoading(true);
        this.setRouteStatus('Requesting permission to use your current location...');

        try {
            const position = await new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 60000,
                });
            });

            this.setOrigin({
                lat: position.coords.latitude,
                lng: position.coords.longitude,
            }, this.defaultOriginLabel, 'user');

            await this.renderRoute();
        } catch (error) {
            console.error('Browser geolocation failed.', error);
            this.setRouteStatus(this.message('geolocationDenied'), 'error');
        } finally {
            this.setLoading(false);
        }
    }

    async routeFromDssc() {
        if (!this.destination) {
            this.setRouteStatus(this.message('missingCoordinates'), 'warning');
            return;
        }

        if (!this.campus) {
            this.setRouteStatus('DSSC Main Campus coordinates are unavailable.', 'error');
            return;
        }

        this.setOrigin(this.campus, this.config.dssc?.name || 'DSSC Main Campus', 'campus');
        await this.renderRoute();
    }

    async renderRoute() {
        if (!this.origin || !this.destination) {
            this.setRouteStatus(this.message('routeFailed'), 'error');
            return;
        }

        if (this.travelMode === 'TWO_WHEELER') {
            this.setRouteStatus(this.message('motorcycleUnavailable'), 'warning');
            return;
        }

        if (this.travelMode === 'TRANSIT' && this.provider !== 'google') {
            this.clearRoute();
            this.clearRouteOptions(this.message('transitUnavailable'));
            this.updateRouteMetrics(null, null);
            this.setRouteStatus(this.message('transitUnavailable'), 'warning');
            return;
        }

        this.setLoading(true);
        this.clearRoute();
        this.clearRouteOptions();
        this.usedFallbackRouting = false;

        try {
            const routes = await this.fetchRoutes();
            if (!routes.length) {
                throw new Error('No routes returned.');
            }

            this.availableRoutes = routes;

            if (this.provider === 'google') {
                this.renderGoogleRoutes(routes);
            } else {
                this.renderLeafletRoutes(routes);
            }

            this.setRouteOptions(routes);
            this.selectRoute(0, { fit: true });

            const selected = routes[0];
            const warningText = selected.warnings?.length ? ` ${selected.warnings[0]}` : '';
            const fallbackText = this.usedFallbackRouting ? ' using the fallback routing service' : '';
            this.setRouteStatus(`${travelModeLabel(this.travelMode)} route generated${fallbackText}.${warningText}`.trim(), selected.warnings?.length ? 'warning' : 'success');
        } catch (error) {
            console.error('Route generation failed.', error);
            this.renderDirectEstimate();
            this.setRouteStatus(this.message('routeFailed'), 'error');
        } finally {
            this.setLoading(false);
        }
    }

    async fetchRoutes() {
        if (this.provider === 'google') {
            try {
                return await this.fetchGoogleRoutes();
            } catch (error) {
                if (this.travelMode === 'DRIVING' || this.travelMode === 'WALKING') {
                    const fallbackRoutes = await this.fetchFallbackRoutes();
                    if (fallbackRoutes.length) {
                        this.usedFallbackRouting = true;
                        return fallbackRoutes;
                    }
                }

                throw error;
            }
        }

        return this.fetchFallbackRoutes();
    }

    async fetchGoogleRoutes() {
        const directionsService = new google.maps.DirectionsService();
        const request = {
            origin: this.origin,
            destination: this.destination,
            travelMode: google.maps.TravelMode[googleTravelMode(this.travelMode)],
            provideRouteAlternatives: true,
            unitSystem: google.maps.UnitSystem.METRIC,
        };

        if (this.travelMode === 'DRIVING') {
            request.drivingOptions = {
                departureTime: new Date(),
                trafficModel: google.maps.TrafficModel.BEST_GUESS,
            };
        }

        if (this.travelMode === 'TRANSIT') {
            request.transitOptions = {
                departureTime: new Date(),
            };
        }

        const response = await directionsService.route(request);

        return (response.routes || []).map((route, index) => ({
            id: `google-${index}`,
            summary: summaryLabel(route.summary),
            rawSummary: route.summary || '',
            note: routeNote(index),
            distance: sumRouteDistance(route),
            duration: sumRouteDuration(route),
            path: (route.overview_path || []).map((point) => (
                typeof point.toJSON === 'function'
                    ? point.toJSON()
                    : { lat: point.lat(), lng: point.lng() }
            )),
            bounds: route.bounds || null,
            warnings: route.warnings || [],
            estimated: false,
        }));
    }

    async fetchFallbackRoutes() {
        if (!this.origin || !this.destination) {
            return [];
        }

        if (this.travelMode !== 'DRIVING' && this.travelMode !== 'WALKING') {
            return [];
        }

        const routeBase = this.travelMode === 'DRIVING'
            ? this.config.routing?.drivingUrl
            : this.config.routing?.walkingUrl;
        const baseUrl = String(routeBase || 'https://routing.openstreetmap.de/routed-car').replace(/\/$/, '');
        const coordinates = `${this.origin.lng},${this.origin.lat};${this.destination.lng},${this.destination.lat}`;
        const url = `${baseUrl}/route/v1/driving/${coordinates}?alternatives=true&steps=true&geometries=geojson&overview=full`;
        const response = await fetch(url, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            return [];
        }

        const payload = await response.json();

        return payload.code === 'Ok'
            ? (payload.routes || []).map((route, index) => ({
                id: `fallback-${index}`,
                summary: osrmSummary(route),
                rawSummary: '',
                note: routeNote(index),
                distance: Number(route.distance || 0),
                duration: Number(route.duration || 0) * 1000,
                path: (route.geometry?.coordinates || []).map(([lng, lat]) => ({ lat, lng })),
                bounds: null,
                warnings: [],
                estimated: false,
            }))
            : [];
    }

    renderGoogleRoutes(routes) {
        this.routeLayers = routes.map((route, index) => {
            const outline = new google.maps.Polyline({
                path: route.path,
                map: this.map,
                clickable: true,
                strokeColor: '#ffffff',
                strokeOpacity: 0.75,
                strokeWeight: 9,
                zIndex: 10 + index,
            });
            const line = new google.maps.Polyline({
                path: route.path,
                map: this.map,
                clickable: true,
                strokeColor: '#94a3b8',
                strokeOpacity: 0.85,
                strokeWeight: 4,
                zIndex: 20 + index,
            });

            [outline, line].forEach((polyline) => {
                polyline.addListener('click', () => this.selectRoute(index, { fit: false }));
            });

            return { outline, line, route };
        });
    }

    renderLeafletRoutes(routes) {
        this.routeLayers = routes.map((route, index) => {
            const points = route.path.map((point) => [point.lat, point.lng]);
            const outline = this.L.polyline(points, {
                color: '#ffffff',
                opacity: 0.82,
                weight: 9,
            }).addTo(this.map);
            const line = this.L.polyline(points, {
                color: '#94a3b8',
                opacity: 0.88,
                weight: 4,
            }).addTo(this.map);

            [outline, line].forEach((polyline) => {
                polyline.on('click', () => this.selectRoute(index, { fit: false }));
            });

            return { outline, line, route };
        });
    }

    selectRoute(index, { fit = true } = {}) {
        const route = this.availableRoutes[index];
        if (!route) {
            return;
        }

        this.activeRouteIndex = index;
        this.updateRouteMetrics(route.distance, route.duration);
        this.updateRenderedRouteState();
        this.updateRouteOptionsState();

        if (fit) {
            this.fitSelectedRoute(route);
        }
    }

    updateRenderedRouteState() {
        if (this.provider === 'google') {
            this.routeLayers.forEach((layer, index) => {
                const active = index === this.activeRouteIndex;
                layer.outline?.setOptions({
                    strokeOpacity: active ? 0.95 : 0.5,
                    strokeWeight: active ? 10 : 8,
                    zIndex: active ? 40 : 12 + index,
                });
                layer.line?.setOptions({
                    strokeColor: active ? '#2563eb' : '#94a3b8',
                    strokeOpacity: active ? 0.98 : 0.7,
                    strokeWeight: active ? 6 : 4,
                    zIndex: active ? 50 : 22 + index,
                });
            });

            return;
        }

        this.routeLayers.forEach((layer, index) => {
            const active = index === this.activeRouteIndex;
            layer.outline?.setStyle({
                opacity: active ? 0.95 : 0.55,
                weight: active ? 10 : 8,
            });
            layer.line?.setStyle({
                color: active ? '#2563eb' : '#94a3b8',
                opacity: active ? 0.98 : 0.72,
                weight: active ? 6 : 4,
            });
        });
    }

    fitSelectedRoute(route) {
        if (!route) {
            return;
        }

        if (this.provider === 'google') {
            if (route.bounds) {
                this.map.fitBounds(route.bounds, 72);
                return;
            }

            this.fitGoogleBounds(route.path);
            return;
        }

        const points = route.path.map((point) => [point.lat, point.lng]);
        this.map.fitBounds(points, {
            padding: [42, 42],
            maxZoom: 17,
        });
    }

    fitGoogleBounds(path = []) {
        const bounds = new google.maps.LatLngBounds();
        if (this.origin) bounds.extend(this.origin);
        if (this.destination) bounds.extend(this.destination);
        path.forEach((point) => bounds.extend(point));
        this.map.fitBounds(bounds, 72);
    }

    setRouteOptions(routes = []) {
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
                    <span>${escapeHtml(formatDistance(route.distance))}</span>
                </span>
            `;
            button.addEventListener('click', () => this.selectRoute(index));
            this.elements.routeOptions.appendChild(button);
            this.routeOptionButtons.push(button);
        });

        if (this.elements.routeOptionsBadge) {
            const label = routes.length === 1 ? '1 option' : `${routes.length} options`;
            this.elements.routeOptionsBadge.textContent = routes.length ? label : 'Waiting';
        }
    }

    updateRouteOptionsState() {
        this.routeOptionButtons.forEach((button, index) => {
            const active = index === this.activeRouteIndex;
            button.setAttribute('aria-pressed', String(active));
            button.classList.toggle('is-active', active);
        });

        if (this.elements.routeOptionsBadge && this.activeRouteIndex >= 0) {
            this.elements.routeOptionsBadge.textContent = `${this.activeRouteIndex + 1} selected`;
        }
    }

    clearRouteOptions(message = null) {
        if (!this.elements.routeOptions) {
            return;
        }

        this.routeOptionButtons = [];
        this.activeRouteIndex = -1;
        this.availableRoutes = [];
        this.elements.routeOptions.innerHTML = `
            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-3 text-xs font-medium text-slate-500">
                ${escapeHtml(message || 'Route options will appear here after you choose a starting point.')}
            </div>
        `;

        if (this.elements.routeOptionsBadge) {
            this.elements.routeOptionsBadge.textContent = 'Waiting';
        }
    }

    renderDirectEstimate() {
        if (!this.origin || !this.destination) {
            return;
        }

        const route = {
            id: 'estimate',
            summary: 'Direct estimate',
            rawSummary: '',
            note: routeNote(0, true),
            distance: haversineMeters(this.origin, this.destination),
            duration: estimateDuration(haversineMeters(this.origin, this.destination), this.travelMode),
            path: [this.origin, this.destination],
            bounds: null,
            warnings: [],
            estimated: true,
        };

        this.availableRoutes = [route];

        if (this.provider === 'google') {
            const line = new google.maps.Polyline({
                path: route.path,
                map: this.map,
                strokeColor: '#64748b',
                strokeOpacity: 0.82,
                strokeWeight: 4,
                icons: [{
                    icon: { path: 'M 0,-1 0,1', strokeOpacity: 1, scale: 3 },
                    offset: '0',
                    repeat: '16px',
                }],
            });
            line.addListener('click', () => this.selectRoute(0, { fit: false }));
            this.routeLayers = [{ outline: null, line, route }];
            this.fitGoogleBounds(route.path);
        } else {
            const line = this.L.polyline(route.path.map((point) => [point.lat, point.lng]), {
                color: '#64748b',
                dashArray: '8 10',
                opacity: 0.82,
                weight: 4,
            }).addTo(this.map);
            line.on('click', () => this.selectRoute(0, { fit: false }));
            this.routeLayers = [{ outline: null, line, route }];
            this.map.fitBounds(line.getBounds(), { padding: [42, 42] });
        }

        this.setRouteOptions([route]);
        this.selectRoute(0, { fit: false });
    }

    updateRouteMetrics(distance, duration) {
        if (this.elements.routeDistance) {
            this.elements.routeDistance.textContent = formatDistance(Number(distance));
        }

        if (this.elements.routeDuration) {
            this.elements.routeDuration.textContent = formatDuration(Number(duration));
        }
    }

    clearRoute() {
        this.routeLayers.forEach((layer) => {
            if (this.provider === 'google') {
                layer.outline?.setMap(null);
                layer.line?.setMap(null);
            } else {
                layer.outline?.remove();
                layer.line?.remove();
            }
        });

        this.routeLayers = [];
    }

    resetMap() {
        this.origin = null;
        this.originType = null;
        this.originLabel = this.defaultOriginLabel;
        this.usedFallbackRouting = false;
        this.updateRouteOriginLabel();
        this.updateRouteMetrics(null, null);
        this.clearRoute();
        this.clearRouteOptions();
        this.removeUserMarker();
        this.fitInitialView();
        this.syncExternalLinks();
        this.setRouteStatus(this.message('reset'));
    }

    syncExternalLinks() {
        const destination = this.destination
            ? `${this.destination.lat},${this.destination.lng}`
            : this.config.house?.address;

        if (!destination) {
            return;
        }

        const searchUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(destination)}&utm_source=BoardMatch&utm_campaign=boarding_house_location`;
        const mapUrl = this.destination
            ? `https://www.google.com/maps/@?api=1&map_action=map&center=${encodeURIComponent(destination)}&zoom=17&basemap=satellite&utm_source=BoardMatch&utm_campaign=larger_map`
            : searchUrl;
        const streetViewUrl = this.destination
            ? `https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=${encodeURIComponent(destination)}&utm_source=BoardMatch&utm_campaign=street_view`
            : searchUrl;
        const directionsParameters = new URLSearchParams({
            api: '1',
            destination,
            travelmode: googleUrlTravelMode(this.travelMode),
            utm_source: 'BoardMatch',
            utm_campaign: 'directions_request',
        });

        if (this.origin) {
            directionsParameters.set('origin', `${this.origin.lat},${this.origin.lng}`);
        }

        if (this.elements.openMaps) {
            this.elements.openMaps.href = this.origin
                ? `https://www.google.com/maps/dir/?${directionsParameters.toString()}`
                : searchUrl;
        }

        if (this.elements.largerMap) {
            this.elements.largerMap.href = mapUrl;
        }

        if (this.elements.streetViewLink) {
            this.elements.streetViewLink.href = streetViewUrl;
        }

        this.updateRouteDestinationLabel();
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
            locationMap.showUnavailable(locationMap.message('googleMapsFailed'));
        });
    });
};

document.addEventListener('DOMContentLoaded', initializeBoardingHouseMaps);
