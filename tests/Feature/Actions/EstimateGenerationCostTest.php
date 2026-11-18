<?php

use App\Actions\EstimateGenerationCost;
use App\Actions\Pricing\GetPixelforgePricing;
use App\Dto\Pricing\PricingTableDto;
use App\Enums\ImageQuality;
use App\Enums\ImageSize;

beforeEach(function () {
    config(['pixelforge.markup_bps' => 2500]);

    $this->mock(GetPixelforgePricing::class)
        ->shouldReceive('__invoke')
        ->andReturn(PricingTableDto::fromArray(json_decode(
            file_get_contents(base_path('tests/Fixtures/pixelforge/pricing.json')),
            true,
        )));
});

test('the docs worst case costs $0.373875 at most, plus the markup', function () {
    $estimate = app(EstimateGenerationCost::class)(
        prompt: str_repeat('a', 400),
        size: ImageSize::Landscape,
        quality: ImageQuality::High,
        referenceImageCount: 2,
    );

    // $0.373875 Pixelforge cost × 1.25 = $0.46734375
    expect($estimate->totalMaxNano)->toBe(467_343_750);
});

test('it estimates every line as a min and max price', function () {
    $estimate = app(EstimateGenerationCost::class)(
        prompt: str_repeat('a', 214),
        size: ImageSize::Square,
        quality: ImageQuality::Medium,
        referenceImageCount: 2,
    );

    expect($estimate->toArray())->toBe([
        'lines' => [
            'text' => ['tokens_min' => 45, 'tokens_max' => 63, 'price_min_nano' => 281_250, 'price_max_nano' => 393_750],
            'reference_images' => ['tokens_min' => 1530, 'tokens_max' => 1530, 'price_min_nano' => 19_125_000, 'price_max_nano' => 19_125_000],
            'output' => ['tokens_min' => 1100, 'tokens_max' => 1100, 'price_min_nano' => 55_000_000, 'price_max_nano' => 55_000_000],
            'reasoning' => ['tokens_min' => 200, 'tokens_max' => 2000, 'price_min_nano' => 3_125_000, 'price_max_nano' => 31_250_000],
        ],
        'total_min_nano' => 77_531_250,
        'total_max_nano' => 105_768_750,
    ]);
});

test('the output tokens depend on size and quality', function (ImageQuality $quality, ImageSize $size, int $outputTokens) {
    $estimate = app(EstimateGenerationCost::class)('', $size, $quality, 0);

    expect($estimate->output->tokensMax)->toBe($outputTokens);
})->with([
    [ImageQuality::Low, ImageSize::Square, 280],
    [ImageQuality::Low, ImageSize::Portrait, 420],
    [ImageQuality::Medium, ImageSize::Square, 1100],
    [ImageQuality::Medium, ImageSize::Landscape, 1650],
    [ImageQuality::High, ImageSize::Square, 4300],
    [ImageQuality::High, ImageSize::Landscape, 6450],
]);

test('prompt length is counted in unicode characters, not bytes', function () {
    // 8 characters but 16 bytes: ceil(8 / 4) = 2 base tokens.
    $estimate = app(EstimateGenerationCost::class)('éééééééé', ImageSize::Square, ImageQuality::Low, 0);

    expect($estimate->text->tokensMin)->toBe(1)
        ->and($estimate->text->tokensMax)->toBe(3);
});

test('an empty prompt has no text cost', function () {
    $estimate = app(EstimateGenerationCost::class)('', ImageSize::Square, ImageQuality::Low, 0);

    expect($estimate->text->toArray())->toBe([
        'tokens_min' => 0, 'tokens_max' => 0, 'price_min_nano' => 0, 'price_max_nano' => 0,
    ]);
});
