<?php

namespace App\Dto\Pricing;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, int>
 */
final readonly class TokenUsageDto implements Arrayable
{
    public function __construct(
        public int $text,
        public int $referenceImages,
        public int $output,
        public int $reasoning,
    ) {}

    /**
     * Keyed like Pixelforge's usage object.
     *
     * @return array{input_text_tokens: int, input_image_tokens: int, output_tokens: int, reasoning_tokens: int}
     */
    public function toArray(): array
    {
        return [
            'input_text_tokens' => $this->text,
            'input_image_tokens' => $this->referenceImages,
            'output_tokens' => $this->output,
            'reasoning_tokens' => $this->reasoning,
        ];
    }
}
