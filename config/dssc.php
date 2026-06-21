<?php

return [
    'landmark' => env('DSSC_MAIN_NAME', 'DSSC Main Campus'),
    'address' => env('DSSC_MAIN_ADDRESS', 'Matti, Digos City, Davao del Sur'),

    // Approximate campus coordinates used for routing previews until verified values are supplied.
    'latitude' => (float) env('DSSC_MAIN_LAT', 6.75874),
    'longitude' => (float) env('DSSC_MAIN_LNG', 125.30909),

    'nearby_radius_km' => 5.0,

    'areas' => [
        'Matti',
        'Purok 3, Matti',
        'Mahayahay',
        'Tres de Mayo',
        'Poblacion / City Proper',
    ],
];
