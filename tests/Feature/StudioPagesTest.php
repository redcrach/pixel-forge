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
