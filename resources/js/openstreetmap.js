import 'maplibre-gl/dist/maplibre-gl.css';
import * as maplibregl from 'maplibre-gl';

const DEFAULTS = {
    tileUrl: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19,
    nominatimUrl: 'https://nominatim.openstreetmap.org',
};

let documentConfig = null;

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

export const openStreetMapConfig = (overrides = {}) => ({
    ...DEFAULTS,
    ...(boardMatchMapConfig().openStreetMap || {}),
    ...(overrides || {}),
});

export const openStreetMapStyle = (overrides = {}) => {
    const config = openStreetMapConfig(overrides);

    return {
        version: 8,
        sources: {
            openstreetmap: {
                type: 'raster',
                tiles: [config.tileUrl],
                tileSize: 256,
                minzoom: 0,
                maxzoom: Number(config.maxZoom) || DEFAULTS.maxZoom,
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
    const map = new maplibregl.Map({
        container,
        style: openStreetMapStyle(config),
        center: options.center || [125.30909, 6.75874],
        zoom: options.zoom ?? 14,
        maxZoom: Number(config.maxZoom) || DEFAULTS.maxZoom,
        attributionControl: false,
        dragRotate: false,
        pitchWithRotate: false,
        cooperativeGestures: false,
    });

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
    const bounds = boundsForLngLats(points);

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
