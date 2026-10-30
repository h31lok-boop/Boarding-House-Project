import assert from 'node:assert/strict';
import test from 'node:test';
import { createServiceArea } from '../../resources/js/service-area.js';

const concaveRing = [[0, 0], [4, 0], [4, 1], [1, 1], [1, 4], [0, 4], [0, 0]];
const areaFor = (coordinates, center = [0.5, 0.5], type = 'Polygon') => createServiceArea({
    name: 'Test Matti boundary',
    geometry: { type, coordinates },
    center,
});

test('an in-bounds point outside a concave polygon is rejected', () => {
    const area = areaFor([concaveRing]);
    assert.equal(area.contains([0.5, 3]), true);
    assert.equal(area.contains([3, 0.5]), true);
    assert.equal(area.contains([3, 3]), false);
    assert.equal(area.contains([5, 0.5]), false);
});

test('exterior vertices and edges are accepted in either winding direction', () => {
    for (const ring of [concaveRing, [...concaveRing].reverse()]) {
        const area = areaFor([ring]);
        assert.equal(area.contains([0, 0]), true);
        assert.equal(area.contains([0, 2]), true);
        assert.equal(area.contains([1, 2]), true);
        assert.equal(area.contains([1.000001, 2]), false);
    }
});

test('holes and their boundaries are excluded as on the server', () => {
    const area = areaFor([
        [[0, 0], [4, 0], [4, 4], [0, 4], [0, 0]],
        [[1, 1], [2, 1], [2, 2], [1, 2], [1, 1]],
    ]);
    assert.equal(area.contains([0.5, 1.5]), true);
    assert.equal(area.contains([1.5, 1.5]), false);
    assert.equal(area.contains([1, 1.5]), false);
    assert.equal(area.contains([1, 1]), false);
});

test('a multipolygon includes each separate component and excludes the gap', () => {
    const area = areaFor([
        [concaveRing],
        [[[10, 10], [12, 10], [12, 12], [10, 12], [10, 10]]],
    ], [0.5, 0.5], 'MultiPolygon');
    assert.equal(area.contains([0.5, 3]), true);
    assert.equal(area.contains([11, 11]), true);
    assert.equal(area.contains([5, 5]), false);
});

test('missing, malformed, unclosed and zero-area boundaries fail closed', () => {
    const configurations = [
        undefined, {}, { geometry: { type: 'Point', coordinates: [1, 1] } },
        { geometry: { type: 'Polygon', coordinates: [] } },
        { geometry: { type: 'MultiPolygon', coordinates: [[]] } },
        { geometry: { type: 'Polygon', coordinates: [[[0, 0], [1, 0], [1, 1], [0, 1]]] } },
        { geometry: { type: 'Polygon', coordinates: [[[0, 0], [1, 1], [2, 2], [0, 0]]] } },
        { geometry: { type: 'Polygon', coordinates: [[[0, 0], [181, 0], [1, 1], [0, 0]]] } },
        { geometry: { type: 'Polygon', coordinates: [[[0, 0], [1, null], [1, 1], [0, 0]]] } },
    ];
    configurations.forEach((config) => assert.equal(createServiceArea(config), null));
});

test('missing, nonnumeric and out-of-range property coordinates fail closed', () => {
    const area = areaFor([concaveRing]);
    for (const point of [null, [], [null, 0], ['', 0], [' ', 0], [false, 0], [NaN, 0],
        [Infinity, 0], [181, 0], [0, 91], ['0x0', 0], [{}, 0]]) {
        assert.equal(area.contains(point), false, `Rejected ${JSON.stringify(point)}`);
    }
    assert.equal(area.contains([' 0.5 ', '3.0']), true);
});

test('geocoder bounds derive from the polygon and remain bounded despite stale config', () => {
    const area = createServiceArea({
        geometry: { type: 'Polygon', coordinates: [concaveRing] },
        bounds: [[-180, -90], [180, 90]],
        center: [0.5, 0.5],
    });
    assert.deepEqual(area.bounds, [[0, 0], [4, 4]]);
    assert.deepEqual(area.geocodingParameters, { bounded: '1', viewbox: '0,4,4,0' });
});

test('camera updates cannot leave the polygon through gestures or an outside route origin', () => {
    const area = areaFor([concaveRing]);
    let center = area.center;
    for (const candidate of [[0.5, 3], [3, 3], [125, 7], [3, 0.5], [0, 0], null]) {
        const previous = center;
        center = area.constrainCenter(candidate, center);
        assert.equal(area.contains(center), true);
        if (!area.contains(candidate)) assert.deepEqual(center, previous);
    }
});

test('an outside configured center cannot initialize an unrestricted camera', () => {
    const area = areaFor([concaveRing], [3, 3]);
    assert.equal(area.contains(area.center), true);
    assert.equal(area.contains(area.constrainCenter([99, 99], [99, 99])), true);
});

test('numeric GeoJSON strings have the same containment as numeric geometry', () => {
    const area = areaFor([concaveRing.map((point) => point.map(String))]);
    assert.equal(area.contains([0.5, 3]), true);
    assert.equal(area.contains([3, 3]), false);
});
