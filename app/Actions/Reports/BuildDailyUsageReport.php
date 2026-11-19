<?php

namespace App\Actions\Reports;

use App\Dto\Reports\DailyUsageDto;
use App\Enums\GenerationStatus;
use App\Models\Generation;
use Carbon\CarbonImmutable;
use stdClass;

/**
 * Credit spend, provider cost and profit per UTC day.
 *
 * Days follow completed_at, because Pixelforge bills a generation on the UTC day it completed,
 * so each day's provider cost can be checked against GET /v1/usage. Only succeeded generations
 * cost anything: failures are free for us and for the user.
 */
class BuildDailyUsageReport
{
    /**
     * @return list<DailyUsageDto> Only days with succeeded generations, newest first
     */
    public function __invoke(CarbonImmutable $startDate, CarbonImmutable $endDate, ?int $userId = null): array
    {
        $days = Generation::query()
            ->where('status', GenerationStatus::SUCCEEDED->value)
            ->whereBetween('completed_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->when($userId !== null, fn ($query) => $query->where('user_id', $userId))
            ->selectRaw('DATE(completed_at) as day')
            ->selectRaw('COUNT(*) as generations')
            ->selectRaw('COALESCE(SUM(final_price_nano), 0) as user_spend_nano')
            ->selectRaw('COALESCE(SUM(provider_cost_nano), 0) as provider_cost_nano')
            ->selectRaw('COALESCE(SUM(input_text_tokens), 0) as input_text_tokens')
            ->selectRaw('COALESCE(SUM(input_image_tokens), 0) as input_image_tokens')
            ->selectRaw('COALESCE(SUM(output_tokens), 0) as output_tokens')
            ->selectRaw('COALESCE(SUM(reasoning_tokens), 0) as reasoning_tokens')
            ->groupByRaw('DATE(completed_at)')
            ->orderByRaw('DATE(completed_at) desc')
            ->toBase()
            ->get();

        return array_values($days->map(fn (stdClass $day): DailyUsageDto => new DailyUsageDto(
            date: (string) $day->day,
            generations: (int) $day->generations,
            userSpendNano: (int) $day->user_spend_nano,
            providerCostNano: (int) $day->provider_cost_nano,
            inputTextTokens: (int) $day->input_text_tokens,
            inputImageTokens: (int) $day->input_image_tokens,
            outputTokens: (int) $day->output_tokens,
            reasoningTokens: (int) $day->reasoning_tokens,
        ))->all());
    }
}
