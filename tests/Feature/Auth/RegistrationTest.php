<?php

use App\Enums\CreditTransactionType;
use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home', absolute: false));
});

test('new users start with a $2.00 welcome credit', function () {
    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'welcome@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', 'welcome@example.com')->sole();

    expect($user->balance_nano)->toBe(2_000_000_000)
        ->and($user->creditTransactions()->sole()->type)->toBe(CreditTransactionType::WELCOME);
});
