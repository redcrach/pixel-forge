<?php

use App\Actions\Pricing\FormatMoney;

test('it formats nanodollars like the frontend formatter', function (int $nano, int $decimals, bool $roundUp, string $expected) {
    expect(app(FormatMoney::class)($nano, $decimals, $roundUp))->toBe($expected);
})->with([
    'rounds half up' => [92_887_500, 4, false, '$0.0929'],
    'max rounds up' => [340_018_750, 4, true, '$0.3401'],
    'max rounds up, case 2' => [447_531_250, 4, true, '$0.4476'],
    'exact stays exact' => [305_500_000, 4, true, '$0.3055'],
    'full precision' => [74_087_500, 9, false, '$0.074087500'],
    'dollars' => [10_000_000_000, 2, false, '$10.00'],
    'negative' => [-74_087_500, 4, false, '-$0.0741'],
]);
