import { describe, expect, test } from 'vite-plus/test';
import {
    dollarsToNano,
    formatMoney,
    formatMoneyCompact,
    formatMoneyRange,
} from '@/lib/money';

describe('formatMoney', () => {
    test('shows every nanodollar by default, like cost_usd in Pixelforge usage', () => {
        expect(formatMoney(343_675_000)).toBe('$0.343675000');
        expect(formatMoney(92_887_500)).toBe('$0.092887500');
        expect(formatMoney(-74_087_500)).toBe('-$0.074087500');
    });

    test('rounds half up when asked for fewer decimals', () => {
        expect(formatMoney(343_675_000, 5)).toBe('$0.34368');
        expect(formatMoney(447_531_250, 5)).toBe('$0.44753');
    });

    test('can round up so a maximum is never shown lower than it is', () => {
        expect(formatMoney(340_018_750, 5, 'up')).toBe('$0.34002');
        expect(formatMoney(447_531_250, 5, 'up')).toBe('$0.44754');
    });

    test('an exact amount is not rounded up', () => {
        expect(formatMoney(74_087_500, 9)).toBe('$0.074087500');
        expect(formatMoney(305_500_000, 5, 'up')).toBe('$0.30550');
    });
});

describe('formatMoneyCompact', () => {
    test('drops trailing zeros for users but keeps cents', () => {
        expect(formatMoneyCompact(58_125_000)).toBe('$0.058125');
        expect(formatMoneyCompact(86_250_000)).toBe('$0.08625');
        expect(formatMoneyCompact(23_451_953_125)).toBe('$23.451953125');
        expect(formatMoneyCompact(10_000_000_000)).toBe('$10.00');
        expect(formatMoneyCompact(0)).toBe('$0.00');
        expect(formatMoneyCompact(-74_087_500)).toBe('-$0.0740875');
    });
});

describe('formatMoneyRange', () => {
    test('shows both ends exactly, without trailing zeros', () => {
        expect(formatMoneyRange(58_125_000, 86_250_000)).toBe(
            '$0.058125 – $0.08625',
        );
        expect(formatMoneyRange(340_000_000, 447_531_250)).toBe(
            '$0.34 – $0.44753125',
        );
    });

    test('a single amount is shown once', () => {
        expect(formatMoneyRange(447_531_250, 447_531_250)).toBe('$0.44753125');
    });
});

describe('dollarsToNano', () => {
    test('converts typed dollars exactly', () => {
        expect(dollarsToNano('10')).toBe(10_000_000_000);
        expect(dollarsToNano('12.34')).toBe(12_340_000_000);
        expect(dollarsToNano('0.1')).toBe(100_000_000);
        expect(dollarsToNano(' 500.00 ')).toBe(500_000_000_000);
    });

    test('rejects anything that is not a plain amount with up to 2 decimals', () => {
        expect(dollarsToNano('')).toBeNull();
        expect(dollarsToNano('10.001')).toBeNull();
        expect(dollarsToNano('-5')).toBeNull();
        expect(dollarsToNano('ten')).toBeNull();
        expect(dollarsToNano('1e3')).toBeNull();
    });
});
