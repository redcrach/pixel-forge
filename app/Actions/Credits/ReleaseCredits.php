<?php

namespace App\Actions\Credits;

use App\Enums\CreditTransactionType;
use App\Models\CreditTransaction;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Gives a failed generation's hold back: failures cost nothing. Does nothing when the generation
 * was already settled or released, or never held anything.
 */
class ReleaseCredits
{
    public function __construct(
        private RecordCreditTransaction $recordCreditTransaction,
    ) {}

    public function __invoke(Generation $generation): ?CreditTransaction
    {
        return DB::transaction(function () use ($generation): ?CreditTransaction {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($generation->user_id);

            $isAlreadyFinished = $generation->creditTransactions()
                ->whereIn('type', [CreditTransactionType::SETTLE, CreditTransactionType::RELEASE])
                ->exists();
            $heldCreditsNano = $generation->heldCreditsNano();

            if ($isAlreadyFinished || $heldCreditsNano === 0) {
                return null;
            }

            return ($this->recordCreditTransaction)($lockedUser, CreditTransactionType::RELEASE, $heldCreditsNano, $generation);
        });
    }
}
