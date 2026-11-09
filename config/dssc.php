<?php

return [
    'landmark' => env('DSSC_MAIN_NAME', 'DSSC Main Campus'),
    'address' => env('DSSC_MAIN_ADDRESS', 'Matti, Digos City, Davao del Sur'),

    // Approximate campus coordinates used for routing previews until verified values are supplied.
    'latitude' => (float) env('DSSC_MAIN_LAT', 6.75874),
    'longitude' => (float) env('DSSC_MAIN_LNG', 125.30909),

    'nearby_radius_km' => 5.0,

    // Tenant rent reminders are shown this many days before an unpaid due date.
    'payment_reminder_days' => (int) env('DSSC_PAYMENT_REMINDER_DAYS', 7),

    'areas' => [
        'Matti',
        'Purok 3, Matti',
    ],
];
