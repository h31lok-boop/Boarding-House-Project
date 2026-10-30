// GeoJSON coordinates use [longitude, latitude]. Keep these edge semantics in
// sync with the server's PolygonGeofence: exterior edges are included, holes
// (including their edges) are excluded.
const numeric = (value) => (typeof value === 'number'
    || (typeof value === 'string' && /^[+-]?(?:\d+\.?\d*|\.\d+)(?:e[+-]?\d+)?$/i.test(value.trim())))
    && Number.isFinite(Number(value));

const validPoint = (point) => Array.isArray(point) && point.length >= 2
    && numeric(point[0]) && numeric(point[1])
    && Math.abs(Number(point[0])) <= 180 && Math.abs(Number(point[1])) <= 90;

const ringPosition = ([x, y], ring) => {
    let inside = false;

    for (let index = 1; index < ring.length; index += 1) {
        const [ax, ay] = ring[index - 1];
        const [bx, by] = ring[index];
        const cross = (x - ax) * (by - ay) - (y - ay) * (bx - ax);
        if (Math.abs(cross) <= 1e-12
            && x >= Math.min(ax, bx) && x <= Math.max(ax, bx)
            && y >= Math.min(ay, by) && y <= Math.max(ay, by)) return 0;

        if ((ay > y) !== (by > y) && x < (bx - ax) * (y - ay) / (by - ay) + ax) {
            inside = !inside;
        }
    }

    return inside ? 1 : -1;
};

export const createServiceArea = (config) => {
    let geometry = config?.geometry;
    let polygons = geometry?.type === 'Polygon' ? [geometry.coordinates]
        : (geometry?.type === 'MultiPolygon' ? geometry.coordinates : null);

    if (!Array.isArray(polygons) || !polygons.length) return null;
    for (const polygon of polygons) {
        if (!Array.isArray(polygon) || !polygon.length) return null;
        for (const ring of polygon) {
            if (!Array.isArray(ring) || ring.length < 4 || !ring.every(validPoint)) return null;
            const first = ring[0];
            const last = ring[ring.length - 1];
            if (Number(first[0]) !== Number(last[0]) || Number(first[1]) !== Number(last[1])) return null;
            let area = 0;
            for (let index = 1; index < ring.length; index += 1) {
                area += ring[index - 1][0] * ring[index][1] - ring[index][0] * ring[index - 1][1];
            }
            if (Math.abs(area) < 1e-12) return null;
        }
    }

    polygons = polygons.map((polygon) => polygon.map((ring) => ring.map((point) => point.slice(0, 2).map(Number))));
    geometry = { type: geometry.type, coordinates: geometry.type === 'Polygon' ? polygons[0] : polygons };

    const contains = (point) => validPoint(point) && polygons.some((polygon) => (
        ringPosition(point.map(Number), polygon[0]) >= 0
        && !polygon.slice(1).some((hole) => ringPosition(point.map(Number), hole) >= 0)
    ));
    const points = polygons.flatMap((polygon) => polygon[0]);
    // Derive the bounds from the actual geometry, so a stale configured box
    // cannot broaden the geocoder or camera restrictions.
    const bounds = [
        [Math.min(...points.map(([lng]) => Number(lng))), Math.min(...points.map(([, lat]) => Number(lat)))],
        [Math.max(...points.map(([lng]) => Number(lng))), Math.max(...points.map(([, lat]) => Number(lat)))],
    ];
    const center = contains(config.center) ? config.center.map(Number)
        : points.find((point) => contains(point))?.map(Number);
    if (!center) return null;

    return {
        ...config,
        geometry,
        polygons,
        bounds,
        center,
        contains,
        constrainCenter(point, previous = center) {
            return contains(point) ? point.map(Number) : (contains(previous) ? previous.map(Number) : [...center]);
        },
        geocodingParameters: {
            bounded: '1',
            viewbox: [bounds[0][0], bounds[1][1], bounds[1][0], bounds[0][1]].join(','),
        },
    };
};
