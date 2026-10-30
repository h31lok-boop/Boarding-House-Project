<?php

test('the frontend map stack keeps MapLibre and uses Leaflet for the landing map', function () {
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
        ->toHaveKey('leaflet');
    expect(strtolower($mapFiles))
        ->toContain('maplibre-gl')
        ->not->toContain('leaflet');

    expect(file_get_contents($root.'/resources/js/landing-map.js'))
        ->toContain("from 'leaflet'")
        ->toContain('tile.openstreetmap.org');
});

test('the MapLibre basemap uses keyless hybrid imagery and reference overlays', function () {
    $root = dirname(__DIR__, 2);
    $mapStack = file_get_contents($root.'/resources/js/openstreetmap.js');
    $mapConfig = file_get_contents($root.'/resources/views/components/map-config.blade.php');

    expect($mapStack)
        ->toContain("basemap: 'hybrid'")
        ->toContain('World_Imagery/MapServer/tile/{z}/{y}/{x}')
        ->toContain('World_Transportation/MapServer/tile/{z}/{y}/{x}')
        ->toContain('World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}')
        ->toContain("id: 'imagery-tiles'")
        ->toContain("id: 'transportation-overlay'")
        ->toContain("id: 'place-label-overlay'")
        ->not->toContain('GOOGLE_MAPS_API_KEY');

    expect($mapConfig)
        ->toContain("'serviceArea' => app(\\App\\Services\\MattiServiceArea::class)->mapConfig()")
        ->toContain("config('services.openstreetmap.basemap', 'hybrid')")
        ->toContain("config('services.openstreetmap.imagery_tile_url')")
        ->toContain("config('services.openstreetmap.transportation_tile_url')")
        ->toContain("config('services.openstreetmap.reference_tile_url')");
});

test('the MapLibre worker is emitted with a browser executable mime type', function () {
    $root = dirname(__DIR__, 2);
    $mapStack = file_get_contents($root.'/resources/js/openstreetmap.js');
    $viteConfig = file_get_contents($root.'/vite.config.js');

    expect($mapStack)
        ->toContain('maplibre-gl-worker.mjs?url')
        ->toContain('setWorkerUrl(maplibreWorkerUrl)');
    expect($viteConfig)
        ->toContain("originalName.endsWith('.mjs')")
        ->toContain("'assets/[name]-[hash].js'");
});

test('the MapLibre host fills the fixed-height route frame', function () {
    $mapStyles = file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');

    expect($mapStyles)
        ->toContain('.bm-location-map [data-map-canvas].maplibregl-map')
        ->toContain('position: absolute;')
        ->toContain('height: 100%;')
        ->toContain('width: 100%;');
});
