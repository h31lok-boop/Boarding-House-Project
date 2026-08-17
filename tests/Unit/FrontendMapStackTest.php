<?php

test('the frontend map stack uses MapLibre without the previous renderer dependency', function () {
    $root = dirname(__DIR__, 2);
    $package = json_decode(file_get_contents($root.'/package.json'), true, flags: JSON_THROW_ON_ERROR);
    $mapFiles = collect([
        $root.'/resources/js/openstreetmap.js',
        $root.'/resources/js/boarding-house-map.js',
        $root.'/resources/js/boarding-house-browse-map.js',
        $root.'/resources/js/admin-boarding-house-maps.js',
    ])->map(fn (string $path) => file_get_contents($path))->join("\n");

    expect($package['dependencies'])
        ->toHaveKey('maplibre-gl')
        ->not->toHaveKey('leaflet');
    expect(strtolower($mapFiles))
        ->toContain('maplibre-gl')
        ->not->toContain('leaflet');
});
