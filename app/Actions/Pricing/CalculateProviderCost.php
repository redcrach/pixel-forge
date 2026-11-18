<?php

namespace App\Actions\Pricing;

use App\Dto\Pricing\RatesDto;
use App\Dto\Pricing\TokenUsageDto;

/**
 * What Pixelforge charges us for the given token usage, in nanodollars (before our markup).
 */
class CalculateProviderCost
{
    public function __invoke(RatesDto $rates, TokenUsageDto $usage): int
    {
        return $usage->text * $rates->inputTextNano
            + $usage->referenceImages * $rates->inputImageNano
            + $usage->output * $rates->outputNano
            + $usage->reasoning * $rates->reasoningNano;
    }
}
