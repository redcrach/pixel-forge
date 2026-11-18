<?php

use App\Actions\Pricing\CalculateProviderCost;
use App\Dto\Pricing\RatesDto;
use App\Dto\Pricing\TokenUsageDto;

test('it adds up every token type at its own rate', function () {
    $rates = new RatesDto(inputTextNano: 5_000, inputImageNano: 10_000, outputNano: 40_000, reasoningNano: 12_500);
    $usage = new TokenUsageDto(text: 115, referenceImages: 1530, output: 6450, reasoning: 8000);

    $providerCostNano = app(CalculateProviderCost::class)($rates, $usage);

    // The docs worst case: $0.373875
    expect($providerCostNano)->toBe(373_875_000);
});
