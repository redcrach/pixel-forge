<?php

namespace App\Filament\Widgets;

use App\Actions\Pricing\FormatMoney;
use App\Enums\CreditTransactionType;
use App\Enums\DashboardPeriod;
use App\Enums\GenerationStatus;
use App\Models\CreditTransaction;
use App\Models\Generation;
use Carbon\CarbonImmutable;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GenerationStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $startsAt = $this->period()->startsAt();
        $succeeded = $this->succeededTotals($startsAt);
        $netIncomeNano = $succeeded['user_spend_nano'] - $succeeded['provider_cost_nano'];

        return [
            Stat::make('Generations', number_format(Generation::query()->where('created_at', '>=', $startsAt)->count()))
                ->description('Submitted, any status'),
            Stat::make('Pixelforge cost', $this->formatMoney($succeeded['provider_cost_nano']))
                ->description('What Pixelforge charged us'),
            Stat::make('Credits purchased', $this->formatMoney($this->creditsPurchasedNano($startsAt)))
                ->description('Top-ups users paid for'),
            Stat::make('Net income', $this->formatMoney($netIncomeNano))
                ->description('User spend minus Pixelforge cost')
                ->color($netIncomeNano < 0 ? 'danger' : 'success'),
        ];
    }

    private function period(): DashboardPeriod
    {
        return DashboardPeriod::tryFrom((string) ($this->pageFilters['period'] ?? '')) ?? DashboardPeriod::TODAY;
    }

    /**
     * Money moves when a generation succeeds, so costs follow completed_at like the usage report.
     *
     * @return array{user_spend_nano: int, provider_cost_nano: int}
     */
    private function succeededTotals(CarbonImmutable $startsAt): array
    {
        $totals = Generation::query()
            ->where('status', GenerationStatus::SUCCEEDED->value)
            ->where('completed_at', '>=', $startsAt)
            ->selectRaw('COALESCE(SUM(final_price_nano), 0) as user_spend_nano')
            ->selectRaw('COALESCE(SUM(provider_cost_nano), 0) as provider_cost_nano')
            ->toBase()
            ->first();

        return [
            'user_spend_nano' => (int) $totals?->user_spend_nano,
            'provider_cost_nano' => (int) $totals?->provider_cost_nano,
        ];
    }

    private function creditsPurchasedNano(CarbonImmutable $startsAt): int
    {
        return (int) CreditTransaction::query()
            ->where('type', CreditTransactionType::PURCHASE->value)
            ->where('created_at', '>=', $startsAt)
            ->sum('amount_nano');
    }

    private function formatMoney(int $nano): string
    {
        return app(FormatMoney::class)($nano);
    }
}
