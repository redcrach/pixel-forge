<?php

namespace App\Actions\Pricing;

use App\Dto\Pricing\PricingTableDto;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GetPixelforgePricing
{
    public const CACHE_KEY = 'pixelforge.pricing';

    public const FRESH_SECONDS = 3600;

    public const STALE_SECONDS = 86400;

    public function __invoke(): PricingTableDto
    {
        /** Cache the raw response, not the object: config/cache.php disallows unserializing classes. */
        $pricing = Cache::flexible(
            self::CACHE_KEY,
            [self::FRESH_SECONDS, self::STALE_SECONDS],
            fn (): array => Http::pixelforge()->get('/pricing')->throw()->json(),
        );

        return PricingTableDto::fromArray($pricing);
    }
}
