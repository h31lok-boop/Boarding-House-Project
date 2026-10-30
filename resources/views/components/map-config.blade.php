<script id="boardmatch-map-config" type="application/json">{!! json_encode([
    'serviceArea' => app(\App\Services\MattiServiceArea::class)->mapConfig(),
    'openStreetMap' => [
        'tileUrl' => config('services.openstreetmap.tile_url'),
        'attribution' => config('services.openstreetmap.attribution'),
        'maxZoom' => (int) config('services.openstreetmap.max_zoom', 19),
        'nominatimUrl' => config('services.openstreetmap.nominatim_url'),
        'basemap' => config('services.openstreetmap.basemap', 'hybrid'),
        'imageryTileUrl' => config('services.openstreetmap.imagery_tile_url'),
        'transportationTileUrl' => config('services.openstreetmap.transportation_tile_url'),
        'referenceTileUrl' => config('services.openstreetmap.reference_tile_url'),
        'imageryAttribution' => config('services.openstreetmap.imagery_attribution'),
        'referenceAttribution' => config('services.openstreetmap.reference_attribution'),
    ],
    'routing' => [
        'drivingUrl' => config('services.openstreetmap.driving_routing_url'),
        'walkingUrl' => config('services.openstreetmap.walking_routing_url'),
        'fallbackUrl' => config('services.openstreetmap.fallback_routing_url'),
    ],
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
