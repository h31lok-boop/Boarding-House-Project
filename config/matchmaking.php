<?php

return [
    // A listing must meet this weighted score and the tenant's selected core
    // criteria before the UI may call it a preference match.
    'boarding_house_match_threshold' => 70,

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
        'social_style' => 0.05,
        'cooking_habit' => 0.04,
        'work_schedule' => 0.06,
        'guest_preference' => 0.03,
        'sharing_style' => 0.02,
        'hobbies' => 0.05,
    ],

    'boarding_house_weights' => [
        'budget' => 0.25,
        'location' => 0.20,
        'room_type' => 0.15,
        'amenities' => 0.15,
        'safety' => 0.10,
        'lifestyle' => 0.10,
        'distance' => 0.05,
    ],

    'max_recommendation_distance_km' => 8,
];
