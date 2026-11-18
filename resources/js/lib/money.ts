const NANO_DIGITS = 9;

/**
 * Format integer nanodollars (1 USD = 1,000,000,000) as dollars, e.g. 92_887_500 → "$0.0929".
 * Uses BigInt so no floating point math touches money. Rounds half up, for display only.
 */
export function formatMoney(nano: number, decimals: number = 4): string {
    const unit = 10n ** BigInt(NANO_DIGITS - decimals);
    const isNegative = nano < 0;
    const absoluteNano = BigInt(Math.abs(nano));
    const rounded = (absoluteNano + unit / 2n) / unit;
    const scale = 10n ** BigInt(decimals);
    const whole = rounded / scale;
    const fraction = (rounded % scale).toString().padStart(decimals, '0');

    return `${isNegative ? '-' : ''}$${whole}.${fraction}`;
}

/**
 * "$0.0775 – $0.1058", or a single amount when both ends are equal.
 */
export function formatMoneyRange(minNano: number, maxNano: number): string {
    return minNano === maxNano
        ? formatMoney(minNano)
        : `${formatMoney(minNano)} – ${formatMoney(maxNano)}`;
}
