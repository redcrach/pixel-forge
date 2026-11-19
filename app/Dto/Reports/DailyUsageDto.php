<?php

namespace App\Dto\Reports;

/**
 * One UTC day of the admin usage report. Money is in integer nanodollars.
 */
final readonly class DailyUsageDto
{
    /**
     * @param  string  $date  UTC day the generations completed on (Y-m-d), the day Pixelforge bills them
     */
    public function __construct(
        public string $date,
        public int $generations,
        public int $userSpendNano,
        public int $providerCostNano,
        public int $inputTextTokens,
        public int $inputImageTokens,
        public int $outputTokens,
        public int $reasoningTokens,
    ) {}

    public function profitNano(): int
    {
        return $this->userSpendNano - $this->providerCostNano;
    }
}
