import 'maplibre-gl/dist/maplibre-gl.css';
import * as maplibregl from 'maplibre-gl';
import maplibreWorkerUrl from 'maplibre-gl/dist/maplibre-gl-worker.mjs?url';
import { createServiceArea } from './service-area';

// Use an explicit same-origin worker asset. The default blob worker can start
// without completing GeoJSON updates in some local Chrome/Herd environments,
// leaving route layers present but visually empty.
maplibregl.setWorkerUrl(maplibreWorkerUrl);

const DEFAULTS = {
    tileUrl: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19,
    nominatimUrl: 'https://nominatim.openstreetmap.org',
    basemap: 'hybrid',
    imageryTileUrl: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
    transportationTileUrl: 'https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}',
    referenceTileUrl: 'https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}',
    imageryAttribution: 'Source: Esri, Vantor, Earthstar Geographics, and the GIS User Community',
    referenceAttribution: 'Esri, HERE, Garmin, &copy; OpenStreetMap contributors, and the GIS User Community',
};

let documentConfig = null;
let documentServiceArea;

export const boardMatchMapConfig = () => {
    if (documentConfig) {
        return documentConfig;
    }

    const node = document.getElementById('boardmatch-map-config');

    try {
        documentConfig = JSON.parse(node?.textContent || '{}');
    } catch (error) {
        console.error('Invalid BoardMatch map configuration.', error);
        documentConfig = {};
    }

    return documentConfig;
};

export const boardMatchServiceArea = () => {
    if (documentServiceArea === undefined) {
        documentServiceArea = createServiceArea(boardMatchMapConfig().serviceArea);
    }

    return documentServiceArea;
};

export const isInsideServiceArea = (latitude, longitude) => (
    boardMatchServiceArea()?.contains([longitude, latitude]) === true
);

export const serviceAreaMessage = () => `Choose a property location inside the ${
    boardMatchServiceArea()?.name || 'Matti, Digos City, Davao del Sur, Davao Region'
} boundary.`;

const addServiceAreaBoundary = (map, area) => {
    const ringArea = (ring) => ring.reduce((sum, [lng, lat], index) => {
        const [nextLng, nextLat] = ring[(index + 1) % ring.length];

        return sum + (lng * nextLat) - (nextLng * lat);
    }, 0);
    const worldRing = [[-180, -85], [180, -85], [180, 85], [-180, 85], [-180, -85]];
    const worldWinding = Math.sign(ringArea(worldRing));
    const exteriorMaskHoles = area.polygons.map((polygon) => {
        const ring = polygon[0];

        return Math.sign(ringArea(ring)) === worldWinding ? [...ring].reverse() : ring;
    });

    map.addSource('service-area', {
        type: 'geojson',
        data: { type: 'Feature', properties: {}, geometry: area.geometry },
    });
    map.addSource('service-area-outside', {
        type: 'geojson',
        data: {
            type: 'Feature',
            properties: {},
            geometry: {
                type: 'Polygon',
                coordinates: [worldRing, ...exteriorMaskHoles],
            },
        },
    });
    map.addLayer({
        id: 'service-area-mask',
        type: 'fill',
        source: 'service-area-outside',
        paint: { 'fill-color': '#0f172a', 'fill-opacity': 1 },
    });
    map.addLayer({
        id: 'service-area-boundary',
        type: 'line',
        source: 'service-area',
        paint: { 'line-color': '#93c5fd', 'line-width': 2 },
    });
};

export const openStreetMapConfig = (overrides = {}) => ({
    ...DEFAULTS,
    ...(boardMatchMapConfig().openStreetMap || {}),
    ...(overrides || {}),
});

export const openStreetMapStyle = (overrides = {}) => {
    const config = openStreetMapConfig(overrides);
    const maxZoom = Number(config.maxZoom) || DEFAULTS.maxZoom;

    if (String(config.basemap).toLowerCase() === 'hybrid') {
        return {
            version: 8,
            sources: {
                imagery: {
                    type: 'raster',
                    tiles: [config.imageryTileUrl],
                    tileSize: 256,
                    minzoom: 0,
                    maxzoom: maxZoom,
                    attribution: config.imageryAttribution,
                },
                transportation: {
                    type: 'raster',
                    tiles: [config.transportationTileUrl],
                    tileSize: 256,
                    minzoom: 0,
                    maxzoom: maxZoom,
                    attribution: config.referenceAttribution,
                },
                reference: {
                    type: 'raster',
                    tiles: [config.referenceTileUrl],
                    tileSize: 256,
                    minzoom: 0,
                    maxzoom: maxZoom,
                    attribution: config.referenceAttribution,
                },
            },
            layers: [
                { id: 'imagery-tiles', type: 'raster', source: 'imagery' },
                { id: 'transportation-overlay', type: 'raster', source: 'transportation' },
                { id: 'place-label-overlay', type: 'raster', source: 'reference' },
            ],
        };
    }

    return {
        version: 8,
        sources: {
            openstreetmap: {
                type: 'raster',
                tiles: [config.tileUrl],
                tileSize: 256,
                minzoom: 0,
                maxzoom: maxZoom,
                attribution: config.attribution,
            },
        },
        layers: [{
            id: 'openstreetmap-tiles',
            type: 'raster',
            source: 'openstreetmap',
        }],
    };
};

export const createOpenStreetMap = (container, options = {}) => {
    const config = openStreetMapConfig(options.openStreetMap);
    const area = boardMatchServiceArea();
    if (!area) throw new Error('The Matti service-area boundary is unavailable.');

    let allowedCenter = area.constrainCenter(options.center);
    const maxZoom = Number(config.maxZoom) || DEFAULTS.maxZoom;
    const map = new maplibregl.Map({
        container,
        style: openStreetMapStyle(config),
        center: allowedCenter,
        zoom: options.zoom ?? 14,
        minZoom: 12,
        maxZoom,
        maxBounds: area.bounds,
        renderWorldCopies: false,
        // Apply polygon containment to every gesture, resize and programmatic
        // move. The default rectangular viewport constraint can otherwise push
        // the center into a concavity of the Matti polygon.
        transformConstrain: (center, zoom) => {
            allowedCenter = area.constrainCenter([center.lng, center.lat], allowedCenter);
            return {
                center: new maplibregl.LngLat(...allowedCenter),
                zoom: Math.max(12, Math.min(maxZoom, zoom)),
            };
        },
        attributionControl: false,
        dragRotate: false,
        pitchWithRotate: false,
        cooperativeGestures: false,
    });

    map.once('load', () => addServiceAreaBoundary(map, area));

    map.touchZoomRotate.disableRotation();
    map.addControl(new maplibregl.NavigationControl({
        showCompass: false,
        visualizePitch: false,
    }), 'top-left');
    map.addControl(new maplibregl.AttributionControl({ compact: false }), 'bottom-right');

    if (options.scrollZoom === false) {
        map.scrollZoom.disable();
    }

    return map;
};

export const whenMapLoaded = (map) => {
    if (map.loaded()) {
        return Promise.resolve(map);
    }

    return new Promise((resolve, reject) => {
        const onLoad = () => {
            map.off('error', onError);
            resolve(map);
        };
        const onError = (event) => {
            if (!map.loaded()) {
                map.off('load', onLoad);
                reject(event?.error || new Error('The OpenStreetMap tiles could not be loaded.'));
            }
        };

        map.once('load', onLoad);
        map.once('error', onError);
    });
};

export const createHtmlMarker = ({
    map,
    lngLat,
    html,
    title = '',
    anchor = 'center',
    draggable = false,
    popupHtml = null,
    popupOffset = 22,
}) => {
    const element = document.createElement('div');
    element.innerHTML = html.trim();
    const markerElement = element.firstElementChild || element;

    if (title) {
        markerElement.setAttribute('title', title);
        markerElement.setAttribute('aria-label', title);
    }

    const marker = new maplibregl.Marker({
        element: markerElement,
        anchor,
        draggable,
    }).setLngLat(lngLat).addTo(map);

    if (popupHtml) {
        marker.setPopup(new maplibregl.Popup({ offset: popupOffset }).setHTML(popupHtml));
    }

    return marker;
};

export const boundsForLngLats = (points = []) => {
    const bounds = new maplibregl.LngLatBounds();

    points.forEach((point) => {
        if (Array.isArray(point) && point.length >= 2) {
            bounds.extend(point);
        }
    });

    return bounds;
};

export const fitMapToLngLats = (map, points, options = {}) => {
    // A user's route can start elsewhere, but fitting it must not expand the
    // property browsing area beyond Matti.
    const bounds = boundsForLngLats(points.filter((point) => boardMatchServiceArea()?.contains(point)));

    if (!bounds.isEmpty()) {
        map.fitBounds(bounds, {
            padding: options.padding ?? 44,
            maxZoom: options.maxZoom ?? 16,
            duration: options.duration ?? 0,
        });
    }
};

export const circlePolygon = ([lng, lat], radiusMeters, steps = 72) => {
    const earthRadius = 6371008.8;
    const angularDistance = radiusMeters / earthRadius;
    const latitude = lat * Math.PI / 180;
    const longitude = lng * Math.PI / 180;
    const coordinates = [];

    for (let index = 0; index <= steps; index += 1) {
        const bearing = (index / steps) * Math.PI * 2;
        const circleLatitude = Math.asin(
            Math.sin(latitude) * Math.cos(angularDistance)
            + Math.cos(latitude) * Math.sin(angularDistance) * Math.cos(bearing),
        );
        const circleLongitude = longitude + Math.atan2(
            Math.sin(bearing) * Math.sin(angularDistance) * Math.cos(latitude),
            Math.cos(angularDistance) - Math.sin(latitude) * Math.sin(circleLatitude),
        );

        coordinates.push([
            circleLongitude * 180 / Math.PI,
            circleLatitude * 180 / Math.PI,
        ]);
    }

    return {
        type: 'Feature',
        properties: {},
        geometry: { type: 'Polygon', coordinates: [coordinates] },
    };
};

export const lineFeature = (coordinates = []) => ({
    type: 'Feature',
    properties: {},
    geometry: { type: 'LineString', coordinates },
});

export const removeMapLayerAndSource = (map, layerId, sourceId = layerId) => {
    if (map.getLayer(layerId)) {
        map.removeLayer(layerId);
    }
    if (map.getSource(sourceId)) {
        map.removeSource(sourceId);
    }
};

export const distanceMeters = (from, to) => {
    if (!from || !to) return Number.POSITIVE_INFINITY;

    const toRadians = (value) => value * Math.PI / 180;
    const earthRadius = 6371008.8;
    const lat1 = toRadians(from.lat);
    const lat2 = toRadians(to.lat);
    const deltaLat = lat2 - lat1;
    const deltaLng = toRadians(to.lng - from.lng);
    const haversine = Math.sin(deltaLat / 2) ** 2
        + Math.cos(lat1) * Math.cos(lat2) * Math.sin(deltaLng / 2) ** 2;

    return 2 * earthRadius * Math.asin(Math.sqrt(haversine));
};

export { maplibregl };
