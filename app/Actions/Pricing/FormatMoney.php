<?php

namespace App\Actions\Pricing;

/**
 * Formats integer nanodollars as dollars for display, e.g. 343_675_000 → "$0.343675000".
 * Mirrors formatMoney() in resources/js/lib/money.ts. Charges stay exact: only the display rounds.
 */
class FormatMoney
{
    private const NANO_DIGITS = 9;

    /** GET /v1/usage reports cost_usd with 9 decimals (whole nanodollars); we show the same, so amounts are exact and compare directly. */
    public const PIXELFORGE_DECIMALS = 9;

    /**
     * @param  bool  $roundUp  For maximums: never show less than the real amount
     */
    public function __invoke(int $nano, int $decimals = self::PIXELFORGE_DECIMALS, bool $roundUp = false): string
    {
        $unit = 10 ** (self::NANO_DIGITS - $decimals);
        $absoluteNano = abs($nano);
        $rounded = intdiv($absoluteNano + ($roundUp ? $unit - 1 : intdiv($unit, 2)), $unit);
        $scale = 10 ** $decimals;
        $fraction = str_pad((string) ($rounded % $scale), $decimals, '0', STR_PAD_LEFT);

        return ($nano < 0 ? '-' : '').'$'.intdiv($rounded, $scale).'.'.$fraction;
    }
}
