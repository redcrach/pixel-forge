<?php

namespace App\Filament\Pages;

use App\Actions\Pricing\FormatMoney;
use App\Actions\Reports\BuildDailyUsageReport;
use App\Dto\Reports\DailyUsageDto;
use App\Models\User;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Credit spend, provider cost and profit per UTC day, for checking against Pixelforge's GET /v1/usage.
 */
class UsageReport extends Page implements HasTable
{
    use InteractsWithTable;

    private const DEFAULT_DAYS = 14;

    protected string $view = 'filament.pages.usage-report';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Usage report';

    protected static ?string $title = 'Usage report';

    protected ?string $subheading = 'Succeeded generations per UTC day they completed on, the day Pixelforge bills them.';

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (array $filters): array => $this->reportRows($filters))
            ->paginated(false)
            ->columns([
                TextColumn::make('date')
                    ->label('Day (UTC)')
                    ->date('D j M Y'),
                TextColumn::make('generations')
                    ->numeric(),
                TextColumn::make('input_text_tokens')
                    ->label('Input text')
                    ->numeric(),
                TextColumn::make('input_image_tokens')
                    ->label('Input images')
                    ->numeric(),
                TextColumn::make('output_tokens')
                    ->label('Output')
                    ->numeric(),
                TextColumn::make('reasoning_tokens')
                    ->label('Reasoning')
                    ->numeric(),

                TextColumn::make('provider_cost_nano')
                    ->label('Provider cost')
                    ->formatStateUsing(fn (int $state): string => $this->formatMoney($state)),

                TextColumn::make('user_spend_nano')
                    ->label('User spend')
                    ->formatStateUsing(fn (int $state): string => $this->formatMoney($state)),

                TextColumn::make('profit_nano')
                    ->label('Profit')
                    ->formatStateUsing(fn (int $state): string => $this->formatMoney($state))
                    ->color(fn (int $state): string => $state < 0 ? 'danger' : 'success'),
            ])
            ->filters([
                Filter::make('period')
                    ->schema([
                        DatePicker::make('from')
                            ->label('From (UTC)')
                            ->default(now('UTC')->subDays(self::DEFAULT_DAYS - 1)->toDateString())
                            ->maxDate(now('UTC')),
                        DatePicker::make('until')
                            ->label('Until (UTC)')
                            ->default(now('UTC')->toDateString())
                            ->maxDate(now('UTC')),
                    ]),
                SelectFilter::make('user')
                    ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, array<string, mixed>>
     */
    private function reportRows(array $filters): array
    {
        [$startDate, $endDate] = $this->reportPeriod($filters['period'] ?? []);
        $userId = filled($filters['user']['value'] ?? null) ? (int) $filters['user']['value'] : null;

        return collect(app(BuildDailyUsageReport::class)($startDate, $endDate, $userId))
            ->mapWithKeys(fn (DailyUsageDto $day): array => [$day->date => [
                'date' => $day->date,
                'generations' => $day->generations,
                'input_text_tokens' => $day->inputTextTokens,
                'input_image_tokens' => $day->inputImageTokens,
                'output_tokens' => $day->outputTokens,
                'reasoning_tokens' => $day->reasoningTokens,
                'user_spend_nano' => $day->userSpendNano,
                'provider_cost_nano' => $day->providerCostNano,
                'profit_nano' => $day->profitNano(),
            ]])
            ->all();
    }

    /**
     * Missing dates fall back to the last 14 days, and swapped dates are put back in order.
     *
     * @param  array<string, mixed>  $period
     * @return array{CarbonImmutable, CarbonImmutable}
     */
    private function reportPeriod(array $period): array
    {
        $today = CarbonImmutable::now('UTC')->startOfDay();

        $endDate = filled($period['until'] ?? null) ? CarbonImmutable::parse($period['until'], 'UTC') : $today;
        $startDate = filled($period['from'] ?? null)
            ? CarbonImmutable::parse($period['from'], 'UTC')
            : $endDate->subDays(self::DEFAULT_DAYS - 1);

        if ($startDate->gt($endDate)) {
            return [$endDate, $startDate];
        }

        return [$startDate, $endDate];
    }

    private function formatMoney(int $nano): string
    {
        return app(FormatMoney::class)($nano, FormatMoney::PIXELFORGE_DECIMALS);
    }
}
