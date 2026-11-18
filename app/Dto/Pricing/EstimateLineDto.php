<?php

namespace App\Dto\Pricing;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, int>
 */
final readonly class EstimateLineDto implements Arrayable
{
    public function __construct(
        public int $tokensMin,
        public int $tokensMax,
        public int $priceMinNano,
        public int $priceMaxNano,
    ) {}

    /**
     * @return array{tokens_min: int, tokens_max: int, price_min_nano: int, price_max_nano: int}
     */
    public function toArray(): array
    {
        return [
            'tokens_min' => $this->tokensMin,
            'tokens_max' => $this->tokensMax,
            'price_min_nano' => $this->priceMinNano,
            'price_max_nano' => $this->priceMaxNano,
        ];
    }
}
