<?php

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
