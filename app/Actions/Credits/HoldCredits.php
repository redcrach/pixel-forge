<?php

namespace App\Actions\Credits;

use App\Enums\CreditTransactionType;
use App\Exceptions\InsufficientCreditsException;
use App\Models\CreditTransaction;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Sets the generation's maximum cost aside when it is submitted. Holding the maximum, not just
 * checking it, stops several generations submitted at once from spending the same credits.
 */
class HoldCredits
{
    public function __construct(
        private RecordCreditTransaction $recordCreditTransaction,
    ) {}

    /**
     * @throws InsufficientCreditsException when the balance is below the maximum cost
     */
    public function __invoke(Generation $generation, int $maximumCostNano): CreditTransaction
    {
        return DB::transaction(function () use ($generation, $maximumCostNano): CreditTransaction {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($generation->user_id);

            if ($lockedUser->balance_nano < $maximumCostNano) {
                throw new InsufficientCreditsException($maximumCostNano, $lockedUser->balance_nano);
            }

            return ($this->recordCreditTransaction)($lockedUser, CreditTransactionType::HOLD, -$maximumCostNano, $generation);
        });
    }
}
