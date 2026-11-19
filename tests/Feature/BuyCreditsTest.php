<?php

use App\Enums\CreditTransactionType;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->post(route('credits.store'), ['amount' => '10'])->assertRedirect(route('login'));
});

test('buying credits adds the exact amount to the balance', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('credits.store'), ['amount' => '12.34'])
        ->assertSessionHasNoErrors()
        ->assertInertiaFlash('toast.type', 'success');

    expect($user->refresh()->balance_nano)->toBe(12_340_000_000)
        ->and($user->creditTransactions()->sole()->type)->toBe(CreditTransactionType::PURCHASE);
});

test('the amount must be between $2.00 and $500.00, in cents at most', function (mixed $amount) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('credits.store'), ['amount' => $amount])
        ->assertSessionHasErrors('amount');

    expect($user->refresh()->balance_nano)->toBe(0);
})->with([
    'too small' => ['1.99'],
    'too large' => ['500.01'],
    'too precise' => ['10.001'],
    'not a number' => ['ten'],
    'missing' => [null],
]);
