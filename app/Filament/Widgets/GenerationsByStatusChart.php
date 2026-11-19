<?php

namespace App\Filament\Widgets;

use App\Enums\DashboardPeriod;
use App\Enums\GenerationStatus;
use App\Models\Generation;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Str;

class GenerationsByStatusChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected ?string $heading = 'Generations by status';

    protected ?string $description = 'Generations submitted in the period, by their current status.';

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $period = DashboardPeriod::tryFrom((string) ($this->pageFilters['period'] ?? '')) ?? DashboardPeriod::TODAY;

        $countsByStatus = Generation::query()
            ->where('created_at', '>=', $period->startsAt())
            ->selectRaw('status, COUNT(*) as generations')
            ->groupBy('status')
            ->toBase()
            ->pluck('generations', 'status');

        return [
            'labels' => array_map(fn (GenerationStatus $status): string => Str::headline($status->value), GenerationStatus::cases()),
            'datasets' => [[
                'label' => 'Generations',
                'data' => array_map(fn (GenerationStatus $status): int => (int) ($countsByStatus[$status->value] ?? 0), GenerationStatus::cases()),
                'backgroundColor' => array_map($this->statusColor(...), GenerationStatus::cases()),
            ]],
        ];
    }

    private function statusColor(GenerationStatus $status): string
    {
        return match ($status) {
            GenerationStatus::PENDING => '#94a3b8',
            GenerationStatus::QUEUED => '#14b8a6',
            GenerationStatus::PROCESSING => '#fde68a',
            GenerationStatus::SUCCEEDED => '#6ee7b7',
            GenerationStatus::FAILED, GenerationStatus::SUBMISSION_FAILED => '#f87171',
        };
    }
}
