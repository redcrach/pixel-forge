<?php

namespace App\Enums;

enum CreditTransactionType: string
{
    /** Credits bought (simulated, no real payment). */
    case PURCHASE = 'purchase';

    /** The maximum estimate set aside when a generation is submitted. */
    case HOLD = 'hold';

    /** On success: what is still held comes back and the actual price is charged, in one row. */
    case SETTLE = 'settle';

    /** On failure: what is still held comes back. Nothing is charged. */
    case RELEASE = 'release';
}
