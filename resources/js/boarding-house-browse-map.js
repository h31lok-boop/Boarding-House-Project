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

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll(ROOT_SELECTOR).forEach((root) => {
        initializeMap(root).catch(() => {
            root.innerHTML = '<div class="flex h-full items-center justify-center p-5 text-center text-sm text-slate-500">Location map is temporarily unavailable.</div>';
        });
    });
});
