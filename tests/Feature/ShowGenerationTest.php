<?php

use App\Enums\GenerationStatus;
use App\Models\Generation;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('generations.show', Generation::factory()->create()->id))
        ->assertRedirect(route('login'));
});

test('the owner sees the generation with its prompt, settings, usage and price', function () {
    $user = User::factory()->create();
    $generation = Generation::factory()->for($user)->create([
        'prompt' => 'A red fox in the snow',
        'reference_images' => ['https://example.com/fox.png'],
        'status' => GenerationStatus::SUCCEEDED,
        'meta_data' => ['usage' => ['input_text_tokens' => 54, 'input_image_tokens' => 765, 'output_tokens' => 1100, 'reasoning_tokens' => 1200]],
        'final_price_nano' => 74_087_500,
        'completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('generations.show', $generation->id))
        ->assertInertia(fn (Assert $page) => $page
            ->component('generations/Show')
            ->where('generation.id', $generation->id)
            ->where('generation.prompt', 'A red fox in the snow')
            ->where('generation.reference_images', ['https://example.com/fox.png'])
            ->where('generation.usage.reasoning_tokens', 1200)
            ->where('generation.final_price_nano', 74_087_500)
        );
});

test('someone else\'s generation is not found', function () {
    $generation = Generation::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('generations.show', $generation->id))
        ->assertNotFound();
});
