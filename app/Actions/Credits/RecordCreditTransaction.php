<?php

namespace App\Actions\Credits;

use App\Enums\CreditTransactionType;
use App\Models\CreditTransaction;
use App\Models\Generation;
use App\Models\User;

/**
 * Adds one row to the ledger and moves the cached balance by the same amount.
 * Callers lock the user row inside a transaction first, so concurrent changes can't interleave.
 */
class RecordCreditTransaction
{
    public function __invoke(User $lockedUser, CreditTransactionType $type, int $amountNano, ?Generation $generation = null): CreditTransaction
    {
        $lockedUser->balance_nano += $amountNano;
        $lockedUser->save();

        return $lockedUser->creditTransactions()->create([
            'generation_id' => $generation?->id,
            'type' => $type,
            'amount_nano' => $amountNano,
            'balance_after_nano' => $lockedUser->balance_nano,
        ]);
    }
}
