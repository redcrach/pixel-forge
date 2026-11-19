import { describe, expect, test } from 'vite-plus/test';
import { formatMoney, formatMoneyRange } from '@/lib/money';

describe('formatMoney', () => {
    test('rounds half up to 4 decimals by default', () => {
        expect(formatMoney(92_887_500)).toBe('$0.0929');
        expect(formatMoney(340_018_750)).toBe('$0.3400');
        expect(formatMoney(447_531_250)).toBe('$0.4475');
    });

    test('can round up so a maximum is never shown lower than it is', () => {
        expect(formatMoney(340_018_750, 4, 'up')).toBe('$0.3401');
        expect(formatMoney(447_531_250, 4, 'up')).toBe('$0.4476');
    });

    test('an exact amount is not rounded up', () => {
        expect(formatMoney(74_087_500, 9)).toBe('$0.074087500');
        expect(formatMoney(305_500_000, 4, 'up')).toBe('$0.3055');
    });
});

describe('formatMoneyRange', () => {
    test('rounds the minimum normally and the maximum up', () => {
        expect(formatMoneyRange(250_012_500, 340_018_750)).toBe(
            '$0.2500 – $0.3401',
        );
        expect(formatMoneyRange(340_000_000, 447_531_250)).toBe(
            '$0.3400 – $0.4476',
        );
    });

    test('a single amount is shown once, rounded up', () => {
        expect(formatMoneyRange(447_531_250, 447_531_250)).toBe('$0.4476');
    });
});
