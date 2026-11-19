<?php

namespace App\Dto\Pixelforge;

use App\Dto\Pricing\TokenUsageDto;
use App\Enums\GenerationStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * Typed access to a Pixelforge generation object, as returned by POST /v1/generations,
 * GET /v1/generations/{id} and every webhook.
 */
final readonly class PixelforgeGenerationResponseDto
{
    /**
     * @param  array<string, string>  $rates  USD per 1M tokens, frozen when the generation was created.
     * @param  TokenUsageDto|null  $usage  Null until the generation succeeded.
     * @param  array{code: string, message: string}|null  $error  Null unless the generation failed.
     * @param  string|null  $orderId  Our generation id, from the metadata we sent.
     */
    public function __construct(
        public string $id,
        public GenerationStatus $status,
        public ?string $orderId,
        public array $rates,
        public ?TokenUsageDto $usage,
        public ?array $error,
        public ?string $imageUrl,
        public ?Carbon $completedAt,
    ) {}

    /**
     * @param  array<string, mixed>  $generation
     */
    public static function make(array $generation): self
    {
        return new self(
            id: Arr::string($generation, 'id'),
            status: GenerationStatus::from(Arr::string($generation, 'status')),
            orderId: Arr::get($generation, 'metadata.order_id'),
            rates: Arr::array($generation, 'rates'),
            usage: isset($generation['usage']) ? self::makeTokenUsage(Arr::array($generation, 'usage')) : null,
            error: isset($generation['error']) ? [
                'code' => Arr::string($generation, 'error.code'),
                'message' => Arr::string($generation, 'error.message'),
            ] : null,
            imageUrl: isset($generation['image_url']) ? Arr::string($generation, 'image_url') : null,
            completedAt: isset($generation['completed_at']) ? Carbon::parse(Arr::string($generation, 'completed_at')) : null,
        );
    }

    /**
     * @param  array<string, mixed>  $usage
     */
    private static function makeTokenUsage(array $usage): TokenUsageDto
    {
        return new TokenUsageDto(
            text: Arr::integer($usage, 'input_text_tokens'),
            referenceImages: Arr::integer($usage, 'input_image_tokens'),
            output: Arr::integer($usage, 'output_tokens'),
            reasoning: Arr::integer($usage, 'reasoning_tokens'),
        );
    }
}
