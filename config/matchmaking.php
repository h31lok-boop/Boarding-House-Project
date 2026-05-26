<?php

return [
    'weights' => [
        'budget' => 0.18,
        'gender_preference' => 0.08,
        'sleep_schedule' => 0.12,
        'study_habits' => 0.10,
        'cleanliness_level' => 0.12,
        'noise_tolerance' => 0.10,
        'smoking_preference' => 0.08,
        'drinking_preference' => 0.06,
        'pets_preference' => 0.06,
        'internet_usage' => 0.05,
        'hobbies' => 0.05,
    ],

    'boarding_house_weights' => [
        'budget' => 0.28,
        'amenities' => 0.22,
        'distance' => 0.16,
        'availability' => 0.16,
        'occupant_compatibility' => 0.18,
    ],

    'max_recommendation_distance_km' => 8,
];
