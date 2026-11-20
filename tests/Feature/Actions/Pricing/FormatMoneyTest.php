<?php

use App\Actions\Pricing\FormatMoney;

test('it formats nanodollars like the frontend formatter', function (int $nano, int $decimals, bool $roundUp, string $expected) {
    expect(app(FormatMoney::class)($nano, $decimals, $roundUp))->toBe($expected);
})->with([
    'five decimals' => [343_675_000, 5, false, '$0.34368'],
    'rounds half up' => [92_887_500, 4, false, '$0.0929'],
    'max rounds up' => [340_018_750, 4, true, '$0.3401'],
    'max rounds up, case 2' => [447_531_250, 4, true, '$0.4476'],
    'exact stays exact' => [305_500_000, 4, true, '$0.3055'],
    'full precision' => [74_087_500, 9, false, '$0.074087500'],
    'dollars' => [10_000_000_000, 2, false, '$10.00'],
    'negative' => [-74_087_500, 4, false, '-$0.0741'],
]);

test('the compact form for users drops trailing zeros but keeps cents', function (int $nano, string $expected) {
    expect(app(FormatMoney::class)->compact($nano))->toBe($expected);
})->with([
    'estimate' => [58_125_000, '$0.058125'],
    'every digit used' => [23_451_953_125, '$23.451953125'],
    'whole dollars' => [10_000_000_000, '$10.00'],
    'zero' => [0, '$0.00'],
    'negative' => [-74_087_500, '-$0.0740875'],
]);

test('it shows every nanodollar by default, like cost_usd in pixelforge\'s usage report', function () {
    expect(app(FormatMoney::class)(343_675_000))->toBe('$0.343675000');
});
