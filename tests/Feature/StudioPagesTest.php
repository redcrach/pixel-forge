<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function (string $routeName) {
    $response = $this->get(route($routeName));
    $response->assertRedirect(route('login'));
})->with(['generate', 'history']);

test('authenticated users can visit the studio pages', function (string $routeName, string $component) {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route($routeName));
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    ['generate', 'Generate'],
    ['history', 'History'],
]);
