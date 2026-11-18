<?php

namespace App\Actions;

use App\Actions\Pricing\ApplyMarkup;
use App\Actions\Pricing\CalculateProviderCost;
use App\Actions\Pricing\GetPixelforgePricing;
use App\Dto\Pricing\EstimateLineDto;
use App\Dto\Pricing\GenerationEstimateDto;
use App\Dto\Pricing\PricingTableDto;
use App\Dto\Pricing\TokenUsageDto;
use App\Enums\ImageQuality;
use App\Enums\ImageSize;

/**
 * Output and reference image tokens are exact. Text tokens (± variance) and
 * reasoning tokens (range by quality) are only known after completion.
 */
class EstimateGenerationCost
{
    private const BASIS_POINTS = 10_000;

    public function __construct(
        private GetPixelforgePricing $getPixelforgePricing,
        private CalculateProviderCost $calculateProviderCost,
        private ApplyMarkup $applyMarkup,
    ) {}

    public function __invoke(string $prompt, ImageSize $size, ImageQuality $quality, int $referenceImageCount): GenerationEstimateDto
    {
        $pricing = ($this->getPixelforgePricing)();
        $rates = $pricing->rates;

        [$minimumUsage, $maximumUsage] = $this->estimateTokenUsage($pricing, $prompt, $size, $quality, $referenceImageCount);

        return new GenerationEstimateDto(
            text: $this->line($minimumUsage->text, $maximumUsage->text, $rates->inputTextNano),
            referenceImages: $this->line($minimumUsage->referenceImages, $maximumUsage->referenceImages, $rates->inputImageNano),
            output: $this->line($minimumUsage->output, $maximumUsage->output, $rates->outputNano),
            reasoning: $this->line($minimumUsage->reasoning, $maximumUsage->reasoning, $rates->reasoningNano),
            totalMinNano: ($this->applyMarkup)(($this->calculateProviderCost)($rates, $minimumUsage)),
            totalMaxNano: ($this->applyMarkup)(($this->calculateProviderCost)($rates, $maximumUsage)),
        );
    }

    /**
     * @return array{TokenUsageDto, TokenUsageDto}
     */
    private function estimateTokenUsage(PricingTableDto $pricing, string $prompt, ImageSize $size, ImageQuality $quality, int $referenceImageCount): array
    {
        $textTokens = $this->textTokenRange($pricing, $prompt);
        $reasoningTokens = $pricing->reasoningTokens($quality);
        $referenceImageTokens = $referenceImageCount * $pricing->tokensPerReferenceImage();
        $outputTokens = $pricing->outputTokens($quality, $size);

        return [
            new TokenUsageDto($textTokens['min'], $referenceImageTokens, $outputTokens, $reasoningTokens['min']),
            new TokenUsageDto($textTokens['max'], $referenceImageTokens, $outputTokens, $reasoningTokens['max']),
        ];
    }

    /**
     * @return array{min: int, max: int}
     */
    private function textTokenRange(PricingTableDto $pricing, string $prompt): array
    {
        $baseTokens = $this->divideRoundingUp(mb_strlen($prompt), $pricing->charactersPerTextToken());
        $variance = $pricing->textTokenVarianceBasisPoints();

        return [
            'min' => intdiv($baseTokens * (self::BASIS_POINTS - $variance), self::BASIS_POINTS),
            'max' => $this->divideRoundingUp($baseTokens * (self::BASIS_POINTS + $variance), self::BASIS_POINTS),
        ];
    }

    private function line(int $tokensMin, int $tokensMax, int $nanoPerToken): EstimateLineDto
    {
        return new EstimateLineDto(
            tokensMin: $tokensMin,
            tokensMax: $tokensMax,
            priceMinNano: ($this->applyMarkup)($tokensMin * $nanoPerToken),
            priceMaxNano: ($this->applyMarkup)($tokensMax * $nanoPerToken),
        );
    }

    private function divideRoundingUp(int $dividend, int $divisor): int
    {
        return intdiv($dividend + $divisor - 1, $divisor);
    }
}
