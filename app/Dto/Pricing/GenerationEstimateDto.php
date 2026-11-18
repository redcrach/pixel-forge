<?php

namespace App\Dto\Pricing;

use Illuminate\Contracts\Support\Arrayable;

/**
 * The price range we charge for a generation: each line and the total include the markup.
 *
 * @implements Arrayable<string, mixed>
 */
final readonly class GenerationEstimateDto implements Arrayable
{
    public function __construct(
        public EstimateLineDto $text,
        public EstimateLineDto $referenceImages,
        public EstimateLineDto $output,
        public EstimateLineDto $reasoning,
        public int $totalMinNano,
        public int $totalMaxNano,
    ) {}

    /**
     * @return array{
     *     lines: array{
     *         text: array{tokens_min: int, tokens_max: int, price_min_nano: int, price_max_nano: int},
     *         reference_images: array{tokens_min: int, tokens_max: int, price_min_nano: int, price_max_nano: int},
     *         output: array{tokens_min: int, tokens_max: int, price_min_nano: int, price_max_nano: int},
     *         reasoning: array{tokens_min: int, tokens_max: int, price_min_nano: int, price_max_nano: int},
     *     },
     *     total_min_nano: int,
     *     total_max_nano: int,
     * }
     */
    public function toArray(): array
    {
        return [
            'lines' => [
                'text' => $this->text->toArray(),
                'reference_images' => $this->referenceImages->toArray(),
                'output' => $this->output->toArray(),
                'reasoning' => $this->reasoning->toArray(),
            ],
            'total_min_nano' => $this->totalMinNano,
            'total_max_nano' => $this->totalMaxNano,
        ];
    }
}
