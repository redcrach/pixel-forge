<?php

use App\Actions\Credits\AddCredits;
use App\Actions\Credits\HoldCredits;
use App\Actions\Credits\ReleaseCredits;
use App\Actions\Credits\SettleCredits;
use App\Enums\CreditTransactionType;
use App\Exceptions\InsufficientCreditsException;
use App\Models\CreditTransaction;
use App\Models\Generation;
use App\Models\User;

test('buying credits adds them to the balance and the ledger', function () {
    $user = User::factory()->create();

    $transaction = app(AddCredits::class)($user, 10_000_000_000);

    expect($transaction->type)->toBe(CreditTransactionType::PURCHASE)
        ->and($transaction->balance_after_nano)->toBe(10_000_000_000)
        ->and($user->refresh()->balance_nano)->toBe(10_000_000_000);
});

test('submitting holds the maximum estimate', function () {
    $generation = userWithHeldGeneration();

    expect($generation->user->refresh()->balance_nano)->toBe(900_000_000)
        ->and($generation->heldCreditsNano())->toBe(100_000_000);
});

test('a balance equal to the maximum is enough', function () {
    $generation = userWithHeldGeneration(balanceNano: 100_000_000, maximumCostNano: 100_000_000);

    expect($generation->user->refresh()->balance_nano)->toBe(0);
});

test('a balance below the maximum is refused and nothing is held', function () {
    $user = User::factory()->create();
    app(AddCredits::class)($user, 99_999_999);
    $generation = Generation::factory()->for($user)->create();

    expect(fn () => app(HoldCredits::class)($generation, 100_000_000))
        ->toThrow(InsufficientCreditsException::class);

    expect($user->refresh()->balance_nano)->toBe(99_999_999)
        ->and($generation->creditTransactions()->exists())->toBeFalse();
});

test('success gives back the hold and charges exactly the actual price', function () {
    $generation = userWithHeldGeneration();

    $settlement = app(SettleCredits::class)($generation, 74_087_500);

    // +100,000,000 held - 74,087,500 price, in one row
    expect($settlement->amount_nano)->toBe(25_912_500)
        ->and($generation->user->refresh()->balance_nano)->toBe(1_000_000_000 - 74_087_500);
});

test('a generation is never charged twice', function () {
    $generation = userWithHeldGeneration();

    app(SettleCredits::class)($generation, 74_087_500);
    $secondSettlement = app(SettleCredits::class)($generation, 74_087_500);

    expect($secondSettlement)->toBeNull()
        ->and($generation->user->refresh()->balance_nano)->toBe(1_000_000_000 - 74_087_500);
});

test('failure gives the whole hold back, once', function () {
    $generation = userWithHeldGeneration();

    app(ReleaseCredits::class)($generation);
    $secondRelease = app(ReleaseCredits::class)($generation);

    expect($secondRelease)->toBeNull()
        ->and($generation->user->refresh()->balance_nano)->toBe(1_000_000_000)
        ->and($generation->heldCreditsNano())->toBe(0);
});

test('a settled generation is not released', function () {
    $generation = userWithHeldGeneration();
    app(SettleCredits::class)($generation, 74_087_500);

    expect(app(ReleaseCredits::class)($generation))->toBeNull()
        ->and($generation->user->refresh()->balance_nano)->toBe(1_000_000_000 - 74_087_500);
});

test('a generation pixelforge finishes after we gave up on it is charged the full price', function () {
    $generation = userWithHeldGeneration();
    app(ReleaseCredits::class)($generation);

    $settlement = app(SettleCredits::class)($generation, 74_087_500);

    expect($settlement->amount_nano)->toBe(-74_087_500)
        ->and($generation->user->refresh()->balance_nano)->toBe(1_000_000_000 - 74_087_500);
});

test('the cached balance always equals the sum of the ledger', function () {
    $succeeded = userWithHeldGeneration();
    $user = $succeeded->user;
    app(SettleCredits::class)($succeeded, 74_087_500);

    $failed = Generation::factory()->for($user)->create();
    app(HoldCredits::class)($failed, 120_000_000);
    app(ReleaseCredits::class)($failed);

    $stillRunning = Generation::factory()->for($user)->create();
    app(HoldCredits::class)($stillRunning, 50_000_000);
    app(AddCredits::class)($user, 5_000_000_000);

    $user->refresh();
    expect($user->balance_nano)->toBe(ledgerSumNano($user))
        ->and($user->balance_nano)->toBe(1_000_000_000 - 74_087_500 - 50_000_000 + 5_000_000_000)
        ->and(CreditTransaction::query()->latest('created_at')->latest('id')->first()->balance_after_nano)->toBe($user->balance_nano);
});
