<?php

namespace App\Actions\Credits;

use App\Enums\CreditTransactionType;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Credits bought, or the welcome credit of a new account.
 */
class AddCredits
{
    public function __construct(
        private RecordCreditTransaction $recordCreditTransaction,
    ) {}

    public function __invoke(User $user, int $amountNano, CreditTransactionType $type = CreditTransactionType::PURCHASE): CreditTransaction
    {
        return DB::transaction(function () use ($user, $amountNano, $type): CreditTransaction {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            return ($this->recordCreditTransaction)($lockedUser, $type, $amountNano);
        });
    }
}
