import {
    boardMatchMapConfig,
    boardMatchServiceArea,
    circlePolygon,
    createHtmlMarker,
    createOpenStreetMap,
    isInsideServiceArea,
    openStreetMapConfig,
    serviceAreaMessage,
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

    const geocodeStatus = (mode) => {
        let status = document.getElementById(`${mode}-geocode-status`);
        if (!status && pickerFields(mode).map) {
            status = document.createElement('p');
            status.id = `${mode}-geocode-status`;
            status.className = 'mt-2 text-xs text-slate-500';
            status.setAttribute('aria-live', 'polite');
            pickerFields(mode).map.before(status);
        }

        return status;
    };

    const validateLocation = (mode, lat, lng) => {
        const valid = isInsideServiceArea(lat, lng);
        const message = valid ? '' : serviceAreaMessage();
        const fields = pickerFields(mode);
        fields.latitude?.setCustomValidity(message);
        fields.longitude?.setCustomValidity(message);
        const status = geocodeStatus(mode);
        if (status) status.textContent = message;

        return valid;
    };

    const isAddressDerivedPicker = (mode) => (
        pickerFields(mode).map?.dataset.addressDerivedLocation === 'true'
    );

    const addressQuery = (mode) => {
        const fields = pickerFields(mode);

        return [
            fields.address?.value,
            'Matti',
            fields.landmark?.value,
            'Digos City',
            'Davao del Sur',
            'Davao Region',
            'Philippines',
        ].map((part) => String(part || '').trim()).filter(Boolean).join(', ');
    };

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
        if (!validateLocation(mode, lat, lng)) return false;
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

        return true;
    };

    const reverseGeocode = async (mode, lat, lng) => {
        if (!isInsideServiceArea(lat, lng)) return;
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
            if (Number(fields.latitude.value) !== Number(Number(lat).toFixed(7))
                || Number(fields.longitude.value) !== Number(Number(lng).toFixed(7))) return;
            const status = geocodeStatus(mode);
            if (status) status.textContent = `Pin placed. Saved address was not changed${result.display_name ? `; suggestion: ${result.display_name}` : '.'}`;
        } catch (error) {
            // Coordinates and distance remain usable when reverse geocoding is unavailable.
        }
    };

    const placePickerMarker = (mode, lat, lng, reverseAddress = false, markExact = true) => {
        const picker = pickerMaps[mode];
        if (!picker || !validateLocation(mode, lat, lng)) return false;

        const addressDerived = isAddressDerivedPicker(mode);

        if (!picker.marker) {
            picker.marker = createHtmlMarker({
                map: picker.map,
                lngLat: [lng, lat],
                html: propertyMarkerHtml,
                title: 'Boarding house location',
                anchor: 'bottom',
                draggable: !addressDerived,
                popupOffset: 18,
                popupHtml: addressDerived
                    ? '<strong>Boarding house location</strong><br>Derived from the address entered by the owner.'
                    : '<strong>Boarding house location</strong><br>Drag or click the map to adjust.',
            });
            if (!addressDerived) {
                picker.marker.on('dragend', () => {
                    const point = picker.marker.getLngLat();
                    if (!placePickerMarker(mode, point.lat, point.lng, true)) {
                        picker.marker.setLngLat(picker.savedPoint);
                    }
                });
            }
            picker.marker.togglePopup();
        } else {
            picker.marker.setLngLat([lng, lat]);
        }

        updateLocationFields(mode, lat, lng, markExact);
        picker.savedPoint = [Number(lng), Number(lat)];
        // A manually placed or dragged marker is the owner's confirmation of
        // the pin, so an earlier address-search suggestion is no longer dirty.
        if (!addressDerived) pickerFields(mode).map.dataset.addressDirty = 'false';
        pickerFields(mode).map.dataset.locationConfirmed = markExact ? 'true' : 'false';
        if (reverseAddress) reverseGeocode(mode, lat, lng);

        return true;
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
        }, 'service-area-mask');
        map.addLayer({
            id: 'admin-dssc-radius-line',
            type: 'line',
            source: 'admin-dssc-radius',
            paint: { 'line-color': '#2563eb', 'line-width': 2 },
        }, 'service-area-mask');
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
        const hasSavedCoordinates = isInsideServiceArea(fields.latitude.value, fields.longitude.value);
        const area = boardMatchServiceArea();
        if (!area) {
            validateLocation(mode, null, null);
            return;
        }
        const startLat = hasSavedCoordinates ? fieldLat : area.center[1];
        const startLng = hasSavedCoordinates ? fieldLng : area.center[0];
        const addressDerived = isAddressDerivedPicker(mode);

        if (!pickerMaps[mode]) {
            const map = createOpenStreetMap(fields.map, {
                center: [startLng, startLat],
                zoom: 15,
                openStreetMap: osm,
            });
            pickerMaps[mode] = { map, marker: null, ready: whenMapLoaded(map) };
            await pickerMaps[mode].ready;
            addDsscRadius(map);
            if (!addressDerived) {
                map.on('click', (event) => {
                    placePickerMarker(mode, event.lngLat.lat, event.lngLat.lng, true);
                });
            }
        } else {
            await pickerMaps[mode].ready;
        }

        if (hasSavedCoordinates) {
            placePickerMarker(mode, startLat, startLng, false, false);
            fields.map.dataset.locationConfirmed = 'true';
        } else {
            pickerMaps[mode].marker?.remove();
            pickerMaps[mode].marker = null;
            if (fields.latitude.value !== '' || fields.longitude.value !== '') {
                validateLocation(mode, fields.latitude.value, fields.longitude.value);
            }
        }
        pickerMaps[mode].map.jumpTo({
            center: [startLng, startLat],
            zoom: pickerMaps[mode].map.getZoom() || 15,
        });
        window.setTimeout(() => pickerMaps[mode].map.resize(), 100);
    };

    const geocodeOwnerAddress = async (mode) => {
        const fields = pickerFields(mode);
        const status = geocodeStatus(mode);
        const query = addressQuery(mode);
        const area = boardMatchServiceArea();
        if (!area) return validateLocation(mode, null, null);

        if (!fields.address?.value.trim()) {
            if (status) status.textContent = 'Enter the complete property address first.';
            fields.address?.focus();
            return false;
        }

        if (status) status.textContent = 'Locating this address on the map…';

        const nominatimUrl = String(
            osm.nominatimUrl || boardMatchMapConfig().openStreetMap?.nominatimUrl,
        ).replace(/\/$/, '');
        try {
            const lookupQueries = [
                query,
                [fields.address?.value, 'Matti', 'Digos City', 'Davao del Sur', 'Philippines']
                    .map((part) => String(part || '').trim()).filter(Boolean).join(', '),
            ].filter((value, index, values) => value && values.indexOf(value) === index);

            let result = null;
            for (const lookupQuery of lookupQueries) {
                const params = new URLSearchParams({
                    format: 'jsonv2',
                    limit: '10',
                    countrycodes: 'ph',
                    addressdetails: '1',
                    q: lookupQuery,
                    ...area.geocodingParameters,
                });
                const response = await fetch(`${nominatimUrl}/search?${params.toString()}`, {
                    headers: { Accept: 'application/json', 'Accept-Language': 'en' },
                });
                if (!response.ok) continue;
                const results = await response.json();
                result = results.find((candidate) => isInsideServiceArea(candidate.lat, candidate.lon));
                if (result) break;
            }

            const lat = Number(result?.lat);
            const lng = Number(result?.lon);
            if (!isInsideServiceArea(lat, lng)) {
                throw new Error('Address not found.');
            }

            await openLocationPicker(mode);
            if (!placePickerMarker(mode, lat, lng, false, false)) return false;
            pickerMaps[mode].map.jumpTo({ center: [lng, lat], zoom: 17 });
            fields.map.dataset.addressDirty = 'false';
            fields.map.dataset.geocodedQuery = query;
            if (status) status.textContent = 'Map pin updated from the entered address.';

            return true;
        } catch (error) {
            if (status) {
                status.textContent = 'Address could not be located inside Matti. Enter the property street and a nearby landmark in Matti, Digos City, then try again.';
            }

            return false;
        }
    };

    const useOwnerLocation = async (mode) => {
        const fields = pickerFields(mode);
        const status = geocodeStatus(mode);

        if (! window.confirm('Use this device location as a suggestion? Your current location may differ from the boarding house. You must confirm the pin on the map before saving.')) return;
        if (! navigator.geolocation) {
            if (status) status.textContent = 'This browser does not provide location access.';
            return;
        }

        if (status) status.textContent = 'Requesting location permission…';
        navigator.geolocation.getCurrentPosition(async (position) => {
            await openLocationPicker(mode);
            const { latitude, longitude } = position.coords;
            if (! placePickerMarker(mode, latitude, longitude, false, false)) return;
            fields.map.dataset.addressDirty = 'true';
            if (status) status.textContent = 'Your location is only a suggestion. Confirm the building by clicking or dragging the marker, then save.';
        }, (error) => {
            if (status) status.textContent = error.code === error.PERMISSION_DENIED
                ? 'Location permission was not granted.'
                : 'Your location could not be read. Search the address or place the pin manually.';
        }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
    };

    document.addEventListener('click', (event) => {
        const addressButton = event.target.closest?.('[data-geocode-address]');
        if (addressButton) {
            geocodeOwnerAddress(addressButton.dataset.geocodeAddress).catch(console.error);
            return;
        }

        const button = event.target.closest?.('[data-location-picker]');
        if (button) {
            openLocationPicker(button.dataset.locationPicker).catch(console.error);
            return;
        }

        const locationButton = event.target.closest?.('[data-use-my-location]');
        if (locationButton) useOwnerLocation(locationButton.dataset.useMyLocation).catch(console.error);
    });

    window.addEventListener('boarding-house-map:edit', () => {
        window.setTimeout(() => openLocationPicker('edit').catch(console.error), 120);
    });

    // Edit forms may be mounted later by Alpine; delegate validation so those
    // coordinate fields receive the same geofence checks as the create form.
    document.addEventListener('change', (event) => {
        const match = event.target.id?.match(/^(create|edit)-(latitude|longitude)$/);
        if (!match) return;
        const mode = match[1];
        const fields = pickerFields(mode);
        const lat = fields.latitude.value;
        const lng = fields.longitude.value;
        if (!updateLocationFields(mode, lat, lng)) return;
        if (pickerMaps[mode]) placePickerMarker(mode, lat, lng);
    });

    document.addEventListener('input', (event) => {
        const match = event.target.id?.match(/^(create|edit)-(address|barangay|landmark)$/);
        if (!match) return;
        const mode = match[1];
        if (isAddressDerivedPicker(mode)) pickerFields(mode).map.dataset.addressDirty = 'true';
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        const mode = form.querySelector?.('#create-latitude') ? 'create'
            : (form.querySelector?.('#edit-latitude') ? 'edit' : null);
        if (!mode) return;

        const fields = pickerFields(mode);
        const hasCoordinates = isInsideServiceArea(fields.latitude?.value, fields.longitude?.value);
        if (!form.matches('[data-owner-address-form]')) {
            if (!validateLocation(mode, fields.latitude?.value, fields.longitude?.value)) {
                event.preventDefault();
                fields.latitude?.reportValidity();
            }
            return;
        }
        const addressChanged = fields.map?.dataset.addressDirty === 'true';
        const locationConfirmed = fields.map?.dataset.locationConfirmed === 'true';
        if (hasCoordinates && !addressChanged && locationConfirmed) {
            validateLocation(mode, fields.latitude.value, fields.longitude.value);
            return;
        }

        if (hasCoordinates && form.matches('[data-owner-address-form]') && !locationConfirmed) {
            event.preventDefault();
            const status = geocodeStatus(mode);
            if (status) status.textContent = 'Confirm the property pin by clicking or dragging the marker before saving.';
            return;
        }

        event.preventDefault();
        const located = await geocodeOwnerAddress(mode);
        if (!located) return;

        form.requestSubmit();
    });

    window.addEventListener('boarding-house-map:show', (event) => {
        window.setTimeout(async () => {
            const house = event.detail || {};
            const lat = Number(house.latitude);
            const lng = Number(house.longitude);

            const hasValidCoordinates = Number.isFinite(lat) && Number.isFinite(lng)
                && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
            if (!hasValidCoordinates) {
                detailMarker?.remove();
                detailMarker = null;
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
