<?php

namespace App\Actions\Credits;

use App\Enums\CreditTransactionType;
use App\Models\CreditTransaction;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Charges a succeeded generation its actual price, in one row: what is still held comes back,
 * and the price is taken. Usually that's the hold minus the price, so the difference returns.
 * If the hold was already released (the submission was given up on, but Pixelforge finished it
 * anyway), nothing is held any more and the full price is charged. Runs at most once.
 */
class SettleCredits
{
    public function __construct(
        private RecordCreditTransaction $recordCreditTransaction,
    ) {}

    public function __invoke(Generation $generation, int $actualPriceNano): ?CreditTransaction
    {
        return DB::transaction(function () use ($generation, $actualPriceNano): ?CreditTransaction {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($generation->user_id);

            if ($generation->creditTransactions()->where('type', CreditTransactionType::SETTLE)->exists()) {
                return null;
            }

            return ($this->recordCreditTransaction)(
                $lockedUser,
                CreditTransactionType::SETTLE,
                $generation->heldCreditsNano() - $actualPriceNano,
                $generation,
            );
        });
    }
}
