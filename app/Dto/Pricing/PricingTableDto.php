<?php

namespace App\Dto\Pricing;

use App\Enums\ImageQuality;
use App\Enums\ImageSize;
use Illuminate\Support\Arr;

/**
 * Typed access to the GET /v1/pricing response: the current rates and the
 * token figures used to estimate a generation's cost.
 */
final readonly class PricingTableDto
{
    /**
     * @param  array<string, mixed>  $tokens
     */
    public function __construct(
        public RatesDto $rates,
        private array $tokens,
    ) {}

    /**
     * @param  array<string, mixed>  $pricing
     */
    public static function fromArray(array $pricing): self
    {
        return new self(
            rates: RatesDto::fromArray(Arr::array($pricing, 'rates')),
            tokens: Arr::array($pricing, 'tokens'),
        );
    }

    public function charactersPerTextToken(): int
    {
        return Arr::integer($this->tokens, 'characters_per_text_token');
    }

    /**
     * How far the real text token count can be from the estimate, in basis points (0.15 → 1500).
     */
    public function textTokenVarianceBasisPoints(): int
    {
        return (int) round(Arr::float($this->tokens, 'text_token_variance') * 10_000);
    }

    public function tokensPerReferenceImage(): int
    {
        return Arr::integer($this->tokens, 'per_reference_image');
    }

    public function outputTokens(ImageQuality $quality, ImageSize $size): int
    {
        return Arr::integer($this->tokens, "output.{$quality->value}.{$size->value}");
    }

    /**
     * @return array{min: int, max: int}
     */
    public function reasoningTokens(ImageQuality $quality): array
    {
        return [
            'min' => Arr::integer($this->tokens, "reasoning.{$quality->value}.min"),
            'max' => Arr::integer($this->tokens, "reasoning.{$quality->value}.max"),
        ];
    }
}
