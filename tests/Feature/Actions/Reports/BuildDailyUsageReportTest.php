<?php

use App\Actions\Reports\BuildDailyUsageReport;
use App\Dto\Reports\DailyUsageDto;
use App\Enums\GenerationStatus;
use App\Models\Generation;
use App\Models\User;
use Carbon\CarbonImmutable;

function succeededGeneration(User $user, string $completedAt, int $providerCostNano, int $finalPriceNano): Generation
{
    return Generation::factory()->for($user)->create([
        'status' => GenerationStatus::SUCCEEDED,
        'completed_at' => $completedAt,
        'provider_cost_nano' => $providerCostNano,
        'final_price_nano' => $finalPriceNano,
        'input_text_tokens' => 10,
        'input_image_tokens' => 765,
        'output_tokens' => 1100,
        'reasoning_tokens' => 500,
    ]);
}

function buildReport(string $startDate, string $endDate, ?int $userId = null): array
{
    return app(BuildDailyUsageReport::class)(CarbonImmutable::parse($startDate), CarbonImmutable::parse($endDate), $userId);
}

test('it totals succeeded generations per UTC day they completed on, newest first, skipping empty days', function () {
    $user = User::factory()->create();
    succeededGeneration($user, '2026-11-18 23:59:59', 200_000_000, 250_000_000);
    succeededGeneration($user, '2026-11-18 00:00:00', 100_000_000, 125_000_000);
    succeededGeneration($user, '2026-11-19 00:00:00', 100_000_000, 125_000_000);

    $report = buildReport('2026-11-16', '2026-11-19');

    expect(array_map(fn (DailyUsageDto $day): string => $day->date, $report))->toBe(['2026-11-19', '2026-11-18'])
        ->and($report[1]->generations)->toBe(2)
        ->and($report[1]->userSpendNano)->toBe(375_000_000)
        ->and($report[1]->providerCostNano)->toBe(300_000_000)
        ->and($report[1]->profitNano())->toBe(75_000_000)
        ->and($report[1]->inputTextTokens)->toBe(20)
        ->and($report[1]->inputImageTokens)->toBe(1530)
        ->and($report[1]->outputTokens)->toBe(2200)
        ->and($report[1]->reasoningTokens)->toBe(1000);
});

test('generations outside the range are left out', function () {
    succeededGeneration(User::factory()->create(), '2026-11-15 23:59:59', 100_000_000, 125_000_000);

    expect(buildReport('2026-11-16', '2026-11-19'))->toBe([]);
});

test('failed and unfinished generations cost nothing', function (GenerationStatus $status) {
    Generation::factory()->create([
        'status' => $status,
        'completed_at' => '2026-11-19 10:00:00',
        'provider_cost_nano' => 100_000_000,
        'final_price_nano' => 125_000_000,
    ]);

    expect(buildReport('2026-11-19', '2026-11-19'))->toBe([]);
})->with([GenerationStatus::FAILED, GenerationStatus::PROCESSING]);

test('one user\'s report only counts their generations', function () {
    $user = User::factory()->create();
    succeededGeneration($user, '2026-11-19 12:00:00', 100_000_000, 125_000_000);
    succeededGeneration(User::factory()->create(), '2026-11-19 12:00:00', 200_000_000, 250_000_000);

    $day = buildReport('2026-11-19', '2026-11-19', $user->id)[0];

    expect($day->generations)->toBe(1)
        ->and($day->providerCostNano)->toBe(100_000_000);
});
