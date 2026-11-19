<?php

namespace App\Enums;

use Carbon\CarbonImmutable;

/**
 * The period filter on the admin dashboard. Periods are whole UTC days up to now.
 */
enum DashboardPeriod: string
{
    case TODAY = 'today';
    case LAST_7_DAYS = 'last_7_days';
    case LAST_30_DAYS = 'last_30_days';

    public function label(): string
    {
        return match ($this) {
            self::TODAY => 'Today',
            self::LAST_7_DAYS => 'Last 7 days',
            self::LAST_30_DAYS => 'Last 30 days',
        };
    }

    public function startsAt(): CarbonImmutable
    {
        $today = CarbonImmutable::now('UTC')->startOfDay();

        return match ($this) {
            self::TODAY => $today,
            self::LAST_7_DAYS => $today->subDays(6),
            self::LAST_30_DAYS => $today->subDays(29),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $period): array => [$period->value => $period->label()])
            ->all();
    }
}
