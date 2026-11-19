<?php

use App\Actions\ApplyGenerationUpdate;
use App\Dto\Pixelforge\PixelforgeGenerationResponseDto;
use App\Enums\GenerationStatus;
use App\Models\Generation;

beforeEach(function () {
    config(['pixelforge.markup_bps' => 2500]);
});

test('an accepted generation is queued with its pixelforge id and frozen rates', function () {
    $generation = Generation::factory()->create(['meta_data' => ['estimate' => ['min_nano' => 1, 'max_nano' => 2]]]);

    $updatedGeneration = app(ApplyGenerationUpdate::class)($generation, PixelforgeGenerationResponseDto::make(pixelforgeFixture('generation-queued')));

    expect($updatedGeneration->status)->toBe(GenerationStatus::QUEUED)
        ->and($updatedGeneration->pixelforge_id)->toBe('gen_01JC2Z8Q4N6V0M3H7X5T9R2B1K')
        ->and($updatedGeneration->meta_data)->toBe([
            'estimate' => ['min_nano' => 1, 'max_nano' => 2],
            'rates' => [
                'input_text_tokens' => '5.00',
                'input_image_tokens' => '10.00',
                'output_tokens' => '40.00',
                'reasoning_tokens' => '12.50',
            ],
        ]);
});

test('a succeeded generation records its actual cost from its own rates and usage', function () {
    $generation = Generation::factory()->create(['status' => GenerationStatus::PROCESSING]);

    $updatedGeneration = app(ApplyGenerationUpdate::class)($generation, PixelforgeGenerationResponseDto::make(pixelforgeFixture('generation-succeeded')));

    // 12×5,000 + 765×10,000 + 6,450×40,000 + 3,184×12,500 nanodollars, then +25%
    expect($updatedGeneration->status)->toBe(GenerationStatus::SUCCEEDED)
        ->and($updatedGeneration->provider_cost_nano)->toBe(305_510_000)
        ->and($updatedGeneration->final_price_nano)->toBe(381_887_500)
        ->and($updatedGeneration->image_url)->toStartWith('https://pixelforge.promptedit.dev/images/')
        ->and($updatedGeneration->completed_at->toIso8601ZuluString())->toBe('2026-11-04T14:03:29Z')
        ->and($updatedGeneration->meta_data['usage']['reasoning_tokens'])->toBe(3184);
});

test('a failed generation keeps the reason and costs nothing', function () {
    $generation = Generation::factory()->create(['status' => GenerationStatus::PROCESSING]);

    $updatedGeneration = app(ApplyGenerationUpdate::class)($generation, PixelforgeGenerationResponseDto::make(pixelforgeFixture('generation-failed')));

    expect($updatedGeneration->status)->toBe(GenerationStatus::FAILED)
        ->and($updatedGeneration->provider_cost_nano)->toBeNull()
        ->and($updatedGeneration->final_price_nano)->toBeNull()
        ->and($updatedGeneration->meta_data['error']['code'])->toBe('internal_error');
});

test('a late queued update never moves a finished generation backwards', function () {
    $generation = Generation::factory()->create(['status' => GenerationStatus::PENDING]);
    app(ApplyGenerationUpdate::class)($generation, PixelforgeGenerationResponseDto::make(pixelforgeFixture('generation-succeeded')));

    $updatedGeneration = app(ApplyGenerationUpdate::class)($generation, PixelforgeGenerationResponseDto::make(pixelforgeFixture('generation-queued')));

    expect($updatedGeneration->status)->toBe(GenerationStatus::SUCCEEDED)
        ->and($updatedGeneration->final_price_nano)->toBe(381_887_500);
});

test('success charges the actual price and gives the rest of the hold back', function () {
    $generation = userWithHeldGeneration(balanceNano: 1_000_000_000, maximumCostNano: 450_000_000);
    $generation->update(['status' => GenerationStatus::PROCESSING]);

    app(ApplyGenerationUpdate::class)($generation, PixelforgeGenerationResponseDto::make(pixelforgeFixture('generation-succeeded')));

    expect($generation->user->refresh()->balance_nano)->toBe(1_000_000_000 - 381_887_500);
});

test('failure gives the whole hold back', function () {
    $generation = userWithHeldGeneration(balanceNano: 1_000_000_000, maximumCostNano: 450_000_000);
    $generation->update(['status' => GenerationStatus::PROCESSING]);

    app(ApplyGenerationUpdate::class)($generation, PixelforgeGenerationResponseDto::make(pixelforgeFixture('generation-failed')));

    expect($generation->user->refresh()->balance_nano)->toBe(1_000_000_000);
});

test('a duplicate success webhook charges only once', function () {
    $generation = userWithHeldGeneration(balanceNano: 1_000_000_000, maximumCostNano: 450_000_000);
    $succeeded = PixelforgeGenerationResponseDto::make(pixelforgeFixture('generation-succeeded'));

    app(ApplyGenerationUpdate::class)($generation, $succeeded);
    app(ApplyGenerationUpdate::class)($generation, $succeeded);

    expect($generation->user->refresh()->balance_nano)->toBe(1_000_000_000 - 381_887_500);
});
