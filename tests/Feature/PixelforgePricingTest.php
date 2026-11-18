<?php

use App\Services\Pixelforge\PixelforgePricing;
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

    $pricing = app(PixelforgePricing::class)->current();

    expect($pricing['rates'])->toBe([
        'input_text_tokens' => '5.00',
        'input_image_tokens' => '10.00',
        'output_tokens' => '40.00',
        'reasoning_tokens' => '12.50',
    ]);
    expect($pricing['tokens']['output']['high']['1536x1024'])->toBe(6450);
});

test('it caches the pricing between calls', function () {
    Http::fake(['*/pricing' => Http::response($this->pricingResponse)]);

    app(PixelforgePricing::class)->current();
    app(PixelforgePricing::class)->current();

    Http::assertSentCount(1);
});

test('it throws and caches nothing when pixelforge is unavailable', function () {
    Http::fake(['*/pricing' => Http::response(['error' => ['code' => 'overloaded']], 503)]);

    expect(fn () => app(PixelforgePricing::class)->current())->toThrow(RequestException::class);
    expect(cache()->has(PixelforgePricing::CACHE_KEY))->toBeFalse();
});
