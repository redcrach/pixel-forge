<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use LogicException;

class BuyCreditsRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'decimal:0,2', 'min:2', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.decimal' => 'Enter an amount in dollars, with at most 2 decimals.',
            'amount.min' => 'The minimum is $2.00.',
            'amount.max' => 'The maximum is $500.00.',
        ];
    }

    /**
     * The amount in integer nanodollars, converted with exact decimal math (no floats).
     */
    public function amountNano(): int
    {
        $amount = (string) $this->validated('amount');

        if (! is_numeric($amount)) {
            throw new LogicException('The amount was validated as a decimal.');
        }

        return (int) bcmul($amount, '1000000000', 0);
    }
}
