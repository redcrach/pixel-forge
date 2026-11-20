<?php

use App\Models\Generation;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can visit the generate page on the home route', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Generate')
        ->where('auth.user', null)
    );
});

test('authenticated users see the generate page on the home route', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Generate')
        ->where('auth.user.id', $user->id)
    );
});

test('the studio knows whether the user is an admin, to show the admin panel link', function (bool $isAdmin) {
    $this->actingAs(User::factory()->create(['is_admin' => $isAdmin]));

    $this->get(route('home'))->assertInertia(fn (Assert $page) => $page
        ->where('auth.user.is_admin', $isAdmin)
    );
})->with([
    'admin' => [true],
    'regular user' => [false],
]);

test('guests are redirected from history to the login page', function () {
    $response = $this->get(route('history'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit history', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('history'));
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->component('History'));
});

test('the sidebar counts only the signed-in user\'s generations', function () {
    $user = User::factory()->create();
    Generation::factory()->for($user)->count(2)->create();
    Generation::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('generationCount', 2));
});

test('guests get no generation count', function () {
    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('generationCount', null));
});

test('remixing one of your generations prefills the generate page', function () {
    $user = User::factory()->create();
    $generation = Generation::factory()->for($user)->create(['prompt' => 'A red fox in the snow']);

    $this->actingAs($user)
        ->get(route('home', ['remix' => $generation->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Generate')
            ->where('remix.id', $generation->id)
            ->where('remix.prompt', 'A red fox in the snow')
        );
});

test('someone else\'s generation cannot be remixed', function (bool $isGuest) {
    $generation = Generation::factory()->create();

    if (! $isGuest) {
        $this->actingAs(User::factory()->create());
    }

    $this->get(route('home', ['remix' => $generation->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('remix', null));
})->with([
    'another user' => [false],
    'a guest' => [true],
]);

test('every page receives the signed-in user\'s balance', function () {
    $this->actingAs(User::factory()->create(['balance_nano' => 4_273_100_000]))
        ->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('balanceNano', 4_273_100_000));
});
