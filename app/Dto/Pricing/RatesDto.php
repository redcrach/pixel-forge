<?php

namespace App\Dto\Pricing;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use UnexpectedValueException;

/**
 * Pixelforge rates converted to integer nanodollars per token.
 *
 * Pixelforge sends rates as USD per 1M tokens in decimal strings ("12.50").
 * 1 USD = 1,000,000,000 nanodollars, so nanodollars per token = rate × 1000.
 */
final readonly class RatesDto
{
    public function __construct(
        public int $inputTextNano,
        public int $inputImageNano,
        public int $outputNano,
        public int $reasoningNano,
    ) {}

    /**
     * Build from the `rates` object of GET /v1/pricing or of a generation.
     *
     * @param  array<string, mixed>  $rates
     */
    public static function fromArray(array $rates): self
    {
        return new self(
            inputTextNano: self::toNanoPerToken(Arr::string($rates, 'input_text_tokens')),
            inputImageNano: self::toNanoPerToken(Arr::string($rates, 'input_image_tokens')),
            outputNano: self::toNanoPerToken(Arr::string($rates, 'output_tokens')),
            reasoningNano: self::toNanoPerToken(Arr::string($rates, 'reasoning_tokens')),
        );
    }

    /**
     * Convert "USD per 1M tokens" to nanodollars per token with exact decimal math.
     * A rate finer than 1 nanodollar per token is rounded up (never undercharge) and logged.
     */
    private static function toNanoPerToken(string $usdPerMillionTokens): int
    {
        if (! is_numeric($usdPerMillionTokens) || preg_match('/^\d+(\.\d+)?$/', $usdPerMillionTokens) !== 1) {
            throw new UnexpectedValueException("Invalid Pixelforge rate [{$usdPerMillionTokens}].");
        }

        $exact = bcmul($usdPerMillionTokens, '1000', 9);
        $whole = bcmul($usdPerMillionTokens, '1000', 0);

        if (bccomp($exact, $whole, 9) === 0) {
            return (int) $whole;
        }

        Log::warning('Pixelforge rate is finer than 1 nanodollar per token, rounding up.', [
            'rate' => $usdPerMillionTokens,
        ]);

        return (int) $whole + 1;
    }
}
