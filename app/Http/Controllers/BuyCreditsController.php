<?php

namespace App\Http\Controllers;

use App\Actions\Credits\AddCredits;
use App\Actions\Pricing\FormatMoney;
use App\Http\Requests\BuyCreditsRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class BuyCreditsController extends Controller
{
    /**
     * Simulated purchase: no payment is taken, the credits are simply added.
     */
    public function __invoke(BuyCreditsRequest $request, AddCredits $addCredits, FormatMoney $formatMoney): RedirectResponse
    {
        $addCredits($request->user(), $request->amountNano());

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':amount added to your balance.', [
            'amount' => $formatMoney($request->amountNano(), 2),
        ])]);

        return back();
    }
}
