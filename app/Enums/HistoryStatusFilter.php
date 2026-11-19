<?php

namespace App\Enums;

/**
 * The status tabs on the History page.
 */
enum HistoryStatusFilter: string
{
    case ALL = 'all';
    case IN_PROGRESS = 'in_progress';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';

    /**
     * @return list<GenerationStatus> The statuses this tab shows, every status for ALL.
     */
    public function statuses(): array
    {
        return match ($this) {
            self::ALL => GenerationStatus::cases(),
            self::IN_PROGRESS => [GenerationStatus::PENDING, GenerationStatus::QUEUED, GenerationStatus::PROCESSING],
            self::SUCCEEDED => [GenerationStatus::SUCCEEDED],
            self::FAILED => [GenerationStatus::FAILED, GenerationStatus::SUBMISSION_FAILED],
        };
    }
}
