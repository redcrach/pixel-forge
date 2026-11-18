<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pixelforge API
    |--------------------------------------------------------------------------
    |
    | Connection settings for the Pixelforge image generation API. Each
    | environment (local, production) uses its own API key.
    |
    */

    'base_url' => env('PIXELFORGE_BASE_URL', 'https://pixelforge.promptedit.dev/v1'),

    'api_key' => env('PIXELFORGE_API_KEY'),

    'timeout' => (int) env('PIXELFORGE_TIMEOUT', 10),

];
