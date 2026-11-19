<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * The balance can't cover the maximum a generation may cost.
 */
class InsufficientCreditsException extends RuntimeException
{
    public function __construct(
        public readonly int $requiredNano,
        public readonly int $balanceNano,
    ) {
        parent::__construct("Not enough credits: {$requiredNano} nanodollars required, {$balanceNano} available.");
    }
}
