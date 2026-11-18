<?php

namespace App\Enums;

enum GenerationStatus: string
{
    /** Created locally, not yet accepted by Pixelforge. */
    case PENDING = 'pending';
    case QUEUED = 'queued';
    case PROCESSING = 'processing';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';

    public function rank(): int
    {
        return match ($this) {
            self::PENDING => 0,
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
}
