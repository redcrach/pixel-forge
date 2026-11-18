<?php

namespace App\Dto\Pricing;

final readonly class TokenUsageDto
{
    public function __construct(
        public int $text,
        public int $referenceImages,
        public int $output,
        public int $reasoning,
    ) {}
}
