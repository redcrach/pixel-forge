<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum GenerationStatus: string implements HasColor, HasLabel
{
    /** Created locally, not yet accepted by Pixelforge. */
    case PENDING = 'pending';
    case SUBMISSION_FAILED = 'submission_failed';
    case QUEUED = 'queued';
    case PROCESSING = 'processing';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';

    public function rank(): int
    {
        return match ($this) {
            self::PENDING, self::SUBMISSION_FAILED => 0,
            self::QUEUED => 1,
            self::PROCESSING => 2,
            self::SUCCEEDED, self::FAILED => 3,
        };
    }

    public function isFinal(): bool
    {
        return $this === self::SUCCEEDED || $this === self::FAILED;
    }

    public function canTransitionTo(self $nextStatus): bool
    {
        return ! $this->isFinal() && $nextStatus->rank() > $this->rank();
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SUBMISSION_FAILED => 'Submission failed',
            self::QUEUED => 'Queued',
            self::PROCESSING => 'Processing',
            self::SUCCEEDED => 'Succeeded',
            self::FAILED => 'Failed',
        };
    }

    /**
     * Badge color in the admin panel.
     */
    public function getColor(): string
    {
        return match ($this) {
            self::PENDING, self::QUEUED, self::PROCESSING => 'warning',
            self::SUCCEEDED => 'success',
            self::SUBMISSION_FAILED, self::FAILED => 'danger',
        };
    }
}
