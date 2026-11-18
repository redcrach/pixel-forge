<?php

namespace App\Services\Pixelforge;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Current Pixelforge pricing (GET /v1/pricing), used for cost estimates.
 *
 * Actual costs never use this: each generation carries its own frozen rates.
 */
class PixelforgePricing
{
    public const CACHE_KEY = 'pixelforge.pricing';

    /** Served from cache without refreshing for 1 hour. */
    public const FRESH_SECONDS = 3600;

    /** After that, served while refreshing in the background, for up to 24 hours. */
    public const STALE_SECONDS = 86400;

    /**
     * The raw pricing response: `rates` (USD per 1M tokens, as decimal strings)
     * and `tokens` (estimation data). See tests/Fixtures/pixelforge/pricing.json.
     *
     * @return array<string, mixed>
     */
    public function current(): array
    {
        return Cache::flexible(
            self::CACHE_KEY,
            [self::FRESH_SECONDS, self::STALE_SECONDS],
            fn (): array => Http::pixelforge()->get('/pricing')->throw()->json(),
        );
    }
}
