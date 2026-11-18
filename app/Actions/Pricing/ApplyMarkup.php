<?php

namespace App\Actions\Pricing;

use Illuminate\Support\Facades\Log;

/**
 * Turns a Pixelforge (provider) cost into the price we charge.
 */
class ApplyMarkup
{
    /** 100% expressed in basis points (1 basis point = 0.01%). */
    private const BASIS_POINTS = 10_000;

    /**
     * price = provider cost × (100% + markup), in integer nanodollars.
     * A fractional nanodollar is rounded up and logged.
     */
    public function __invoke(int $providerCostNano): int
    {
        $multiplier = self::BASIS_POINTS + (int) config('pixelforge.markup_bps');
        $scaled = $providerCostNano * $multiplier;

        if ($scaled % self::BASIS_POINTS !== 0) {
            Log::warning('Marked-up price has a fractional nanodollar, rounding up.', [
                'provider_cost_nano' => $providerCostNano,
            ]);
        }

        return intdiv($scaled + self::BASIS_POINTS - 1, self::BASIS_POINTS);
    }
}
