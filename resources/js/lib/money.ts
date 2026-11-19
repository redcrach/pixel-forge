const NANO_DIGITS = 9;

/**
 * 'nearest' rounds half up; 'up' never shows less than the real amount (for maximums we hold).
 */
export type MoneyRounding = 'nearest' | 'up';

/**
 * Format integer nanodollars (1 USD = 1,000,000,000) as dollars, e.g. 92_887_500 → "$0.0929".
 * Uses BigInt so no floating point math touches money. For display only: charges stay exact.
 */
export function formatMoney(
    nano: number,
    decimals: number = 4,
    rounding: MoneyRounding = 'nearest',
): string {
    const unit = 10n ** BigInt(NANO_DIGITS - decimals);
    const isNegative = nano < 0;
    const absoluteNano = BigInt(Math.abs(nano));
    const roundingIncrement = rounding === 'up' ? unit - 1n : unit / 2n;
    const rounded = (absoluteNano + roundingIncrement) / unit;
    const scale = 10n ** BigInt(decimals);
    const whole = rounded / scale;
    const fraction = (rounded % scale).toString().padStart(decimals, '0');

    return `${isNegative ? '-' : ''}$${whole}.${fraction}`;
}

/**
 * "$0.0775 – $0.1058", or a single amount when both ends are equal.
 * The maximum is what gets held from the balance, so it is rounded up, never down.
 */
export function formatMoneyRange(minNano: number, maxNano: number): string {
    return minNano === maxNano
        ? formatMoney(maxNano, 4, 'up')
        : `${formatMoney(minNano)} – ${formatMoney(maxNano, 4, 'up')}`;
}
