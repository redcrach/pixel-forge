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

    /*
    |--------------------------------------------------------------------------
    | Markup
    |--------------------------------------------------------------------------
    |
    | What we charge on top of the Pixelforge cost, in basis points
    | (1/100 of a percent). 2500 = +25%.
    |
    */

    'markup_bps' => (int) env('PIXELFORGE_MARKUP_BPS', 2500),

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    |
    | The signing secret (whsec_...) of this environment's API key, and how
    | many seconds a signature's timestamp may be off before it is rejected
    | as a replay.
    |
    */

    'webhook_secret' => env('PIXELFORGE_WEBHOOK_SECRET'),

    'webhook_tolerance' => (int) env('PIXELFORGE_WEBHOOK_TOLERANCE', 300),

];
