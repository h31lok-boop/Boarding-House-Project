import {
    boardMatchMapConfig,
    circlePolygon,
    createHtmlMarker,
    createOpenStreetMap,
    openStreetMapConfig,
    whenMapLoaded,
} from './openstreetmap';

const CONFIG_SELECTOR = '[data-admin-boarding-house-map-config]';

const escapeHtml = (value) => String(value || '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[character]));

const propertyMarkerHtml = '<span class="bm-admin-map-marker-wrap" aria-hidden="true"><span class="bm-admin-map-marker"></span></span>';
const campusMarkerHtml = '<span class="bm-admin-map-campus-marker" aria-hidden="true">D</span>';

const initializeAdminBoardingHouseMaps = () => {
    const configNode = document.querySelector(CONFIG_SELECTOR);
    if (!configNode) return;

    let pageConfig = {};
    try {
        pageConfig = JSON.parse(configNode.textContent || '{}');
    } catch (error) {
        console.error('Invalid admin map configuration.', error);
        return;
    }

    let detailMap = null;
    let detailMarker = null;
    const dssc = {
        lat: Number(pageConfig.dssc?.latitude),
        lng: Number(pageConfig.dssc?.longitude),
        name: pageConfig.dssc?.name || 'DSSC Main Campus',
    };
    const pickerMaps = {};
    const osm = openStreetMapConfig(pageConfig.openStreetMap);

    const setMapEmpty = (isEmpty) => {
        const mapElement = document.getElementById('boardingHouseDetailMap');
        const emptyElement = document.getElementById('boardingHouseDetailMapEmpty');

        mapElement?.classList.toggle('hidden', isEmpty);
        emptyElement?.classList.toggle('hidden', !isEmpty);
    };

    const pickerFields = (mode) => ({
        address: document.getElementById(`${mode}-address`),
        barangay: document.getElementById(`${mode}-barangay`),
        landmark: document.getElementById(`${mode}-landmark`),
        distance: document.getElementById(`${mode}-distance`),
        latitude: document.getElementById(`${mode}-latitude`),
        longitude: document.getElementById(`${mode}-longitude`),
        nearDssc: document.getElementById(`${mode}-near-dssc`),
        locationStatus: document.getElementById(`${mode}-location-status`),
        map: document.getElementById(`${mode}-location-map`),
    });

    const distanceFromDssc = (lat, lng) => {
        const earthRadius = 6371;
        const toRadians = (value) => value * Math.PI / 180;
        const latDelta = toRadians(lat - dssc.lat);
        const lngDelta = toRadians(lng - dssc.lng);
        const startLat = toRadians(dssc.lat);
        const endLat = toRadians(lat);
        const angle = 2 * Math.asin(Math.sqrt(
            Math.sin(latDelta / 2) ** 2
            + Math.cos(startLat) * Math.cos(endLat) * Math.sin(lngDelta / 2) ** 2,
        ));

        return earthRadius * angle;
    };

    const updateLocationFields = (mode, lat, lng, markExact = false) => {
        const fields = pickerFields(mode);
        const distance = distanceFromDssc(lat, lng);

        fields.latitude.value = Number(lat).toFixed(7);
        fields.longitude.value = Number(lng).toFixed(7);
        fields.distance.value = distance.toFixed(2);
        fields.nearDssc.checked = distance <= 5;
        if (fields.nearDssc.checked && !fields.landmark.value.trim()) {
            fields.landmark.value = dssc.name;
        }
        if (markExact) {
            fields.locationStatus.value = 'exact';
        }
    };

    const reverseGeocode = async (mode, lat, lng) => {
        const fields = pickerFields(mode);
        const nominatimUrl = String(
            osm.nominatimUrl || boardMatchMapConfig().openStreetMap?.nominatimUrl,
        ).replace(/\/$/, '');

        try {
            const response = await fetch(`${nominatimUrl}/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`, {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) return;
            const result = await response.json();
            fields.address.value = result.display_name || fields.address.value;
            fields.barangay.value = result.address?.village
                || result.address?.suburb
                || result.address?.quarter
                || fields.barangay.value;
        } catch (error) {
            // Coordinates and distance remain usable when reverse geocoding is unavailable.
        }
    };

    const placePickerMarker = (mode, lat, lng, reverseAddress = false, markExact = true) => {
        const picker = pickerMaps[mode];
        if (!picker) return;

        if (!picker.marker) {
            picker.marker = createHtmlMarker({
                map: picker.map,
                lngLat: [lng, lat],
                html: propertyMarkerHtml,
                title: 'Boarding house location',
                anchor: 'bottom',
                draggable: true,
                popupOffset: 18,
                popupHtml: '<strong>Boarding house location</strong><br>Drag or click the map to adjust.',
            });
            picker.marker.on('dragend', () => {
                const point = picker.marker.getLngLat();
                updateLocationFields(mode, point.lat, point.lng, true);
                reverseGeocode(mode, point.lat, point.lng);
            });
            picker.marker.togglePopup();
        } else {
            picker.marker.setLngLat([lng, lat]);
        }

        updateLocationFields(mode, lat, lng, markExact);
        if (reverseAddress) reverseGeocode(mode, lat, lng);
    };

    const addDsscRadius = (map) => {
        map.addSource('admin-dssc-radius', {
            type: 'geojson',
            data: circlePolygon([dssc.lng, dssc.lat], 5000),
        });
        map.addLayer({
            id: 'admin-dssc-radius-fill',
            type: 'fill',
            source: 'admin-dssc-radius',
            paint: { 'fill-color': '#60a5fa', 'fill-opacity': 0.07 },
        });
        map.addLayer({
            id: 'admin-dssc-radius-line',
            type: 'line',
            source: 'admin-dssc-radius',
            paint: { 'line-color': '#2563eb', 'line-width': 2 },
        });
        createHtmlMarker({
            map,
            lngLat: [dssc.lng, dssc.lat],
            html: campusMarkerHtml,
            title: dssc.name,
            anchor: 'center',
            popupOffset: 20,
            popupHtml: `<strong>${escapeHtml(dssc.name)}</strong><br>Matti, Digos City`,
        });
    };

    const openLocationPicker = async (mode) => {
        const fields = pickerFields(mode);
        if (!fields.map) return;

        fields.map.classList.remove('hidden');
        const fieldLat = Number(fields.latitude.value);
        const fieldLng = Number(fields.longitude.value);
        const startLat = Number.isFinite(fieldLat) && fields.latitude.value !== '' ? fieldLat : dssc.lat;
        const startLng = Number.isFinite(fieldLng) && fields.longitude.value !== '' ? fieldLng : dssc.lng;

        if (!pickerMaps[mode]) {
            const map = createOpenStreetMap(fields.map, {
                center: [startLng, startLat],
                zoom: 15,
                openStreetMap: osm,
            });
            pickerMaps[mode] = { map, marker: null, ready: whenMapLoaded(map) };
            await pickerMaps[mode].ready;
            addDsscRadius(map);
            map.on('click', (event) => {
                placePickerMarker(mode, event.lngLat.lat, event.lngLat.lng, true);
            });
        } else {
            await pickerMaps[mode].ready;
        }

        placePickerMarker(mode, startLat, startLng, false, false);
        pickerMaps[mode].map.jumpTo({
            center: [startLng, startLat],
            zoom: pickerMaps[mode].map.getZoom() || 15,
        });
        window.setTimeout(() => pickerMaps[mode].map.resize(), 100);
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest?.('[data-location-picker]');
        if (!button) return;
        openLocationPicker(button.dataset.locationPicker).catch(console.error);
    });

    window.addEventListener('boarding-house-map:edit', () => {
        window.setTimeout(() => openLocationPicker('edit').catch(console.error), 120);
    });

    ['create', 'edit'].forEach((mode) => {
        const fields = pickerFields(mode);
        [fields.latitude, fields.longitude].forEach((input) => input?.addEventListener('change', () => {
            const lat = Number(fields.latitude.value);
            const lng = Number(fields.longitude.value);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
            updateLocationFields(mode, lat, lng);
            if (pickerMaps[mode]) placePickerMarker(mode, lat, lng);
        }));
    });

    window.addEventListener('boarding-house-map:show', (event) => {
        window.setTimeout(async () => {
            const house = event.detail || {};
            const lat = Number(house.latitude);
            const lng = Number(house.longitude);

            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                setMapEmpty(true);
                return;
            }

            setMapEmpty(false);

            if (!detailMap) {
                detailMap = createOpenStreetMap('boardingHouseDetailMap', {
                    center: [lng, lat],
                    zoom: 16,
                    openStreetMap: osm,
                });
                await whenMapLoaded(detailMap);
            }

            detailMap.jumpTo({ center: [lng, lat], zoom: 16 });

            if (!detailMarker) {
                detailMarker = createHtmlMarker({
                    map: detailMap,
                    lngLat: [lng, lat],
                    html: propertyMarkerHtml,
                    title: house.name || 'Boarding House',
                    anchor: 'bottom',
                    popupOffset: 18,
                    popupHtml: `<strong>${escapeHtml(house.name || 'Boarding House')}</strong><br>${escapeHtml(house.address || '')}`,
                });
            } else {
                detailMarker.setLngLat([lng, lat]);
                detailMarker.setPopup(detailMarker.getPopup()?.setHTML(
                    `<strong>${escapeHtml(house.name || 'Boarding House')}</strong><br>${escapeHtml(house.address || '')}`,
                ));
            }

            if (!detailMarker.getPopup()?.isOpen()) detailMarker.togglePopup();
            detailMap.resize();
        }, 150);
    });
};

document.addEventListener('DOMContentLoaded', initializeAdminBoardingHouseMaps);
