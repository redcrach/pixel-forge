<?php

use App\Actions\Pricing\GetPixelforgePricing;
use App\Enums\ImageQuality;
use App\Enums\ImageSize;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->pricingResponse = json_decode(
        file_get_contents(base_path('tests/Fixtures/pixelforge/pricing.json')),
        true,
    );
});

test('it fetches the current pricing from pixelforge', function () {
    Http::fake(['*/pricing' => Http::response($this->pricingResponse)]);

    $pricing = app(GetPixelforgePricing::class)();

    expect($pricing->rates->inputTextNano)->toBe(5_000)
        ->and($pricing->rates->inputImageNano)->toBe(10_000)
        ->and($pricing->rates->outputNano)->toBe(40_000)
        ->and($pricing->rates->reasoningNano)->toBe(12_500)
        ->and($pricing->outputTokens(ImageQuality::HIGH, ImageSize::LANDSCAPE))->toBe(6450)
        ->and($pricing->reasoningTokens(ImageQuality::MEDIUM))->toBe(['min' => 200, 'max' => 2000])
        ->and($pricing->textTokenVarianceBasisPoints())->toBe(1500);
});

test('it caches the pricing between calls', function () {
    Http::fake(['*/pricing' => Http::response($this->pricingResponse)]);

    app(GetPixelforgePricing::class)();
    app(GetPixelforgePricing::class)();

    Http::assertSentCount(1);
});

test('it throws and caches nothing when pixelforge is unavailable', function () {
    Http::fake(['*/pricing' => Http::response(['error' => ['code' => 'overloaded']], 503)]);

    expect(fn () => app(GetPixelforgePricing::class)())->toThrow(RequestException::class);
    expect(cache()->has(GetPixelforgePricing::CACHE_KEY))->toBeFalse();
});
