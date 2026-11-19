<?php

namespace App\Actions\Fortify;

use App\Actions\Credits\AddCredits;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\CreditTransactionType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /** Every new account starts with $2.00, so it can try the app straight away. */
    private const WELCOME_CREDIT_NANO = 2_000_000_000;

    public function __construct(
        private AddCredits $addCredits,
    ) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            ($this->addCredits)($user, self::WELCOME_CREDIT_NANO, CreditTransactionType::WELCOME);

            return $user->refresh();
        });
    }
}
