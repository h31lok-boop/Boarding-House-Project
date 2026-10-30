import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const DSSC = [6.75874, 125.30909];

const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

const markerIcon = (type) => L.divIcon({
    className: `boardmatch-map-marker boardmatch-map-marker--${type}`,
    html: `<span class="boardmatch-map-marker__glyph"><i class="fas fa-${type === 'campus' ? 'graduation-cap' : 'house'}"></i></span>`,
    iconSize: [38, 46],
    iconAnchor: [19, 43],
    popupAnchor: [0, -39],
});

const popupFor = (listing) => `
    <article class="boardmatch-map-popup">
        <img src="${escapeHtml(listing.image)}" alt="" class="boardmatch-map-popup__image">
        <div class="boardmatch-map-popup__body">
            <h3>${escapeHtml(listing.name)}</h3>
            <p class="boardmatch-map-popup__price">${listing.price !== null ? `₱${Number(listing.price).toLocaleString()}/mo` : 'Ask owner'}</p>
            <p>${escapeHtml(listing.distance)}</p>
            <p>${escapeHtml(listing.availability)}</p>
            <a href="${escapeHtml(listing.url)}" class="boardmatch-map-popup__link">View Details <i class="fas fa-arrow-right"></i></a>
        </div>
    </article>`;

const initializeLandingMap = () => {
    const container = document.getElementById('landing-osm-map');
    const dataNode = document.getElementById('landing-map-listings');

    if (!container || container.dataset.mapReady === 'true') return;

    let listings = [];
    try {
        listings = JSON.parse(dataNode?.textContent || '[]');
    } catch (error) {
        console.error('Invalid landing map listing data.', error);
    }

    container.dataset.mapReady = 'true';
    const map = L.map(container, {
        center: DSSC,
        zoom: 16,
        zoomControl: true,
        scrollWheelZoom: true,
        attributionControl: true,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    const dsscMarker = L.marker(DSSC, { icon: markerIcon('campus'), title: 'Davao del Sur State College – Main Campus' })
        .addTo(map)
        .bindPopup('<strong>Davao del Sur State College – Main Campus</strong><br><span>Brgy. Matti, Digos City</span>');

    const points = [DSSC];
    listings.forEach((listing) => {
        const latitude = Number(listing.latitude);
        const longitude = Number(listing.longitude);
        if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) return;

        points.push([latitude, longitude]);
        L.marker([latitude, longitude], { icon: markerIcon('house'), title: listing.name })
            .addTo(map)
            .bindPopup(popupFor(listing), { maxWidth: 260 });
    });

    if (points.length > 1) {
        map.fitBounds(L.latLngBounds(points), { padding: [28, 28], maxZoom: 16 });
    } else {
        map.setView(DSSC, 16);
    }

    const refreshMapSize = () => window.requestAnimationFrame(() => {
        map.invalidateSize({ pan: false });
    });

    window.setTimeout(refreshMapSize, 100);
    window.addEventListener('resize', refreshMapSize, { passive: true });

    if ('ResizeObserver' in window) {
        const mapObserver = new ResizeObserver(refreshMapSize);
        mapObserver.observe(container);
        mapObserver.observe(container.parentElement);
    }
    // Keep the map clear on first load. Users can open the campus details by
    // tapping the campus marker instead of having a popup cover the map.
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeLandingMap, { once: true });
} else {
    initializeLandingMap();
}
