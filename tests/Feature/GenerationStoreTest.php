<?php

use App\Models\User;

function validGeneration(array $overrides = []): array
{
    return array_merge([
        'prompt' => 'A red fox in the snow',
        'size' => '1024x1024',
        'quality' => 'medium',
        'reference_images' => ['https://example.com/fox.png'],
    ], $overrides);
}

test('guests are redirected to the login page', function () {
    $this->post(route('generations.store'), validGeneration())
        ->assertRedirect(route('login'));
});

test('a valid generation is answered with an error toast for now', function () {
    $this->actingAs(User::factory()->create());

    $this->from(route('home'))
        ->post(route('generations.store'), validGeneration())
        ->assertRedirect(route('home'))
        ->assertSessionHasNoErrors()
        ->assertInertiaFlash('toast.type', 'error');
});

test('it validates the generation', function (array $overrides, string $invalidField) {
    $this->actingAs(User::factory()->create());

    $this->post(route('generations.store'), validGeneration($overrides))
        ->assertSessionHasErrors($invalidField);
})->with([
    'missing prompt' => [['prompt' => ''], 'prompt'],
    'prompt too long' => [['prompt' => str_repeat('a', 4001)], 'prompt'],
    'unknown size' => [['size' => '512x512'], 'size'],
    'unknown quality' => [['quality' => 'ultra'], 'quality'],
    'too many reference images' => [['reference_images' => array_fill(0, 5, 'https://example.com/a.png')], 'reference_images'],
    'reference image is not a url' => [['reference_images' => ['not a url']], 'reference_images.0'],
    'reference image is not http(s)' => [['reference_images' => ['ftp://example.com/a.png']], 'reference_images.0'],
]);
