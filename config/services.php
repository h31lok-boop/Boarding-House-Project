<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('OPENAI_MODEL', 'gpt-5.6'),
        'timeout' => env('OPENAI_TIMEOUT', 45),
        'connect_timeout' => env('OPENAI_CONNECT_TIMEOUT', 20),
    ],

    'ai_evaluation' => [
        'provider' => env('AI_EVALUATION_PROVIDER', 'openai'),
        'prompt_version' => env('AI_EVALUATION_PROMPT_VERSION', 'v1'),
        'temperature' => (float) env('AI_EVALUATION_TEMPERATURE', 0.2),
    ],

    'freemodel' => [
        'enabled' => filter_var(env('FREEMODEL_ENABLED', false), FILTER_VALIDATE_BOOL),
        'api_key' => env('FREEMODEL_API_KEY'),
        'base_url' => env('FREEMODEL_BASE_URL', 'https://api.freemodel.dev'),
        'model' => env('FREEMODEL_MODEL', 'auto'),
        'timeout' => env('FREEMODEL_TIMEOUT', 120),
        'connect_timeout' => env('FREEMODEL_CONNECT_TIMEOUT', 20),
    ],

    'deepseek' => [
        'enabled' => filter_var(env('DEEPSEEK_ENABLED', env('FREEMODEL_ENABLED', false)), FILTER_VALIDATE_BOOL),
        // Keep the legacy variable as a local compatibility fallback while
        // deployments migrate their secret to DEEPSEEK_API_KEY.
        'api_key' => env('DEEPSEEK_API_KEY', env('FREEMODEL_API_KEY')),
        'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-v4-flash'),
        'timeout' => env('DEEPSEEK_TIMEOUT', 120),
        'connect_timeout' => env('DEEPSEEK_CONNECT_TIMEOUT', 20),
    ],

    'groq' => [
        'enabled' => filter_var(env('GROQ_ENABLED', false), FILTER_VALIDATE_BOOL),
        'api_key' => env('GROQ_API_KEY'),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        'model' => env('GROQ_MODEL', 'openai/gpt-oss-20b'),
        'timeout' => env('GROQ_TIMEOUT', 120),
        'connect_timeout' => env('GROQ_CONNECT_TIMEOUT', 20),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', rtrim((string) env('APP_URL'), '/').'/auth/google/callback'),
    ],

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
        'map_id' => env('GOOGLE_MAPS_MAP_ID', 'DEMO_MAP_ID'),
        'language' => env('GOOGLE_MAPS_LANGUAGE', 'en'),
        'region' => env('GOOGLE_MAPS_REGION', 'PH'),
    ],

    'openstreetmap' => [
        'tile_url' => env('OPENSTREETMAP_TILE_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'attribution' => env('OPENSTREETMAP_ATTRIBUTION', '© OpenStreetMap contributors'),
        'max_zoom' => (int) env('OPENSTREETMAP_MAX_ZOOM', 19),
        'nominatim_url' => env('OPENSTREETMAP_NOMINATIM_URL', 'https://nominatim.openstreetmap.org'),
        'driving_routing_url' => env('BOARDMATCH_DRIVING_ROUTING_URL', 'https://routing.openstreetmap.de/routed-car'),
        'walking_routing_url' => env('BOARDMATCH_WALKING_ROUTING_URL', 'https://routing.openstreetmap.de/routed-foot'),
        'fallback_routing_url' => env('BOARDMATCH_FALLBACK_ROUTING_URL', 'https://router.project-osrm.org/route/v1'),
    ],

    'paymongo' => [
        'base_url' => env('PAYMONGO_BASE_URL', 'https://api.paymongo.com'),
        'public_key' => env('PAYMONGO_PUBLIC_KEY'),
        'secret_key' => env('PAYMONGO_SECRET_KEY'),
        'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'),
        'payment_methods' => array_values(array_filter(array_map(
            'trim',
            explode(',', env('PAYMONGO_PAYMENT_METHODS', 'card,gcash,paymaya,qrph'))
        ))),
    ],

];
