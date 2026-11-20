const NANO_DIGITS = 9;

/** GET /v1/usage reports cost_usd with 9 decimals (whole nanodollars); we show the same (FormatMoney::PIXELFORGE_DECIMALS in PHP). */
export const MONEY_DECIMALS = 9;

/**
 * 'nearest' rounds half up; 'up' never shows less than the real amount (for maximums we hold).
 */
export type MoneyRounding = 'nearest' | 'up';

/**
 * Format integer nanodollars (1 USD = 1,000,000,000) as dollars, e.g. 343_675_000 → "$0.343675000".
 * Uses BigInt so no floating point math touches money. For display only: charges stay exact.
 */
export function formatMoney(
    nano: number,
    decimals: number = MONEY_DECIMALS,
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
 * "$0.077500000 – $0.105800000", or a single amount when both ends are equal.
 * The maximum is what gets held from the balance, so it is rounded up, never down.
 */
export function formatMoneyRange(minNano: number, maxNano: number): string {
    return minNano === maxNano
        ? formatMoney(maxNano, MONEY_DECIMALS, 'up')
        : `${formatMoney(minNano)} – ${formatMoney(maxNano, MONEY_DECIMALS, 'up')}`;
}

/**
 * A dollar amount typed by the user ("12.34") as integer nanodollars, without floating point,
 * or null when it isn't a plain amount with at most 2 decimals (the server validates the same).
 */
export function dollarsToNano(amount: string): number | null {
    const match = /^(\d{1,6})(?:\.(\d{1,2}))?$/.exec(amount.trim());

    if (match === null) {
        return null;
    }

    const cents =
        BigInt(match[1]) * 100n + BigInt((match[2] ?? '').padEnd(2, '0'));

    return Number(cents * 10_000_000n);
}
