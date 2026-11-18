<?php

use App\Actions\Pricing\ApplyMarkup;

beforeEach(function () {
    config(['pixelforge.markup_bps' => 2500]);
});

test('the markup adds exactly 25 percent', function () {
    expect(app(ApplyMarkup::class)(373_875_000))->toBe(467_343_750);
});

test('a fractional nanodollar after markup is rounded up', function () {
    expect(app(ApplyMarkup::class)(1))->toBe(2);
});
