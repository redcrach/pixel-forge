<?php

namespace App\Actions;

use App\Actions\Pricing\ApplyMarkup;
use App\Actions\Pricing\CalculateProviderCost;
use App\Dto\Pixelforge\PixelforgeGenerationResponseDto;
use App\Dto\Pricing\RatesDto;
use App\Enums\GenerationStatus;
use App\Models\Generation;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * The single place that turns a Pixelforge generation object into local state.
 * Used by the submit job now, and by webhooks and polling later.
 */
class ApplyGenerationUpdate
{
    public function __construct(
        private CalculateProviderCost $calculateProviderCost,
        private ApplyMarkup $applyMarkup,
    ) {}

    public function __invoke(Generation $generation, PixelforgeGenerationResponseDto $pixelforgeGeneration): Generation
    {
        return DB::transaction(fn (): Generation => $this->applyToLockedGeneration($generation->id, $pixelforgeGeneration));
    }

    /**
     * The row is locked so a webhook and the submit job can't update the same generation at once.
     * The status only moves forward: a late or duplicate update never undoes a newer one.
     */
    private function applyToLockedGeneration(string $generationId, PixelforgeGenerationResponseDto $pixelforgeGeneration): Generation
    {
        $generation = Generation::query()->lockForUpdate()->findOrFail($generationId);

        $generation->pixelforge_id ??= $pixelforgeGeneration->id;
        $generation->meta_data = [...($generation->meta_data ?? []), 'rates' => $pixelforgeGeneration->rates];

        if ($generation->status->canTransitionTo($pixelforgeGeneration->status)) {
            $generation->status = $pixelforgeGeneration->status;

            match ($pixelforgeGeneration->status) {
                GenerationStatus::SUCCEEDED => $this->recordSuccess($generation, $pixelforgeGeneration),
                GenerationStatus::FAILED => $this->recordFailure($generation, $pixelforgeGeneration),
            };
        }

        $generation->save();

        return $generation;
    }

    /**
     * The actual cost uses the generation's own frozen rates and its usage, never the current /pricing.
     */
    private function recordSuccess(Generation $generation, PixelforgeGenerationResponseDto $pixelforgeGeneration): void
    {
        $usage = $pixelforgeGeneration->usage
            ?? throw new LogicException("Pixelforge generation [{$pixelforgeGeneration->id}] succeeded without usage.");

        $providerCostNano = ($this->calculateProviderCost)(RatesDto::fromArray($pixelforgeGeneration->rates), $usage);

        $generation->provider_cost_nano = $providerCostNano;
        $generation->final_price_nano = ($this->applyMarkup)($providerCostNano);
        $generation->image_url = $pixelforgeGeneration->imageUrl;
        $generation->completed_at = $pixelforgeGeneration->completedAt;
        $generation->meta_data = [...$generation->meta_data ?? [], 'usage' => $usage->toArray()];
    }

    /**
     * A failed generation costs nothing: only the reason is kept.
     */
    private function recordFailure(Generation $generation, PixelforgeGenerationResponseDto $pixelforgeGeneration): void
    {
        $generation->completed_at = $pixelforgeGeneration->completedAt;
        $generation->meta_data = [...$generation->meta_data ?? [], 'error' => $pixelforgeGeneration->error];
    }
}
