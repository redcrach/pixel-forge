<?php

use App\Enums\GenerationStatus;
use App\Models\Generation;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('history'))->assertRedirect(route('login'));
});

test('it lists only the signed-in user\'s generations, newest first', function () {
    $user = User::factory()->create();
    $olderGeneration = Generation::factory()->for($user)->create(['created_at' => now()->subHour()]);
    $newerGeneration = Generation::factory()->for($user)->create(['created_at' => now()]);
    Generation::factory()->create();

    $this->actingAs($user)
        ->get(route('history'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('History')
            ->has('generations.data', 2)
            ->where('generations.data.0.id', $newerGeneration->id)
            ->where('generations.data.1.id', $olderGeneration->id)
        );
});

test('older generations are loaded page by page', function () {
    $user = User::factory()->create();
    Generation::factory()->for($user)->count(30)->sequence(fn ($sequence) => [
        'created_at' => now()->subMinutes($sequence->index),
    ])->create();

    $firstPage = $this->actingAs($user)->get(route('history'));
    $firstPage->assertInertia(fn (Assert $page) => $page->has('generations.data', 24));

    $nextCursor = $firstPage->viewData('page')['scrollProps']['generations']['nextPage'];

    $lastPage = $this->get(route('history', ['cursor' => $nextCursor]));
    $lastPage->assertInertia(fn (Assert $page) => $page->has('generations.data', 6));

    expect($lastPage->viewData('page')['scrollProps']['generations']['nextPage'])->toBeNull();
});

test('a generation shows its estimate, its price and why it failed, in nanodollars', function () {
    $user = User::factory()->create();
    Generation::factory()->for($user)->create([
        'status' => GenerationStatus::SUCCEEDED,
        'meta_data' => ['estimate' => ['min_nano' => 58_437_500, 'max_nano' => 86_637_500]],
        'final_price_nano' => 74_087_500,
        'image_url' => 'https://pixelforge.promptedit.dev/images/gen_1.png',
        'completed_at' => now()->subHour(),
        'created_at' => now(),
    ]);
    Generation::factory()->for($user)->create([
        'status' => GenerationStatus::FAILED,
        'meta_data' => ['error' => ['code' => 'generation_failed', 'message' => 'The model could not produce an image.']],
        'completed_at' => now(),
        'created_at' => now()->subMinute(),
    ]);

    $this->actingAs($user)
        ->get(route('history'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('generations.data.0.estimated_min_nano', 58_437_500)
            ->where('generations.data.0.estimated_max_nano', 86_637_500)
            ->where('generations.data.0.final_price_nano', 74_087_500)
            ->where('generations.data.0.image_url', 'https://pixelforge.promptedit.dev/images/gen_1.png')
            ->where('generations.data.0.is_image_expired', false)
            ->where('generations.data.1.final_price_nano', null)
            ->where('generations.data.1.error.code', 'generation_failed')
        );
});

test('an image url older than 24 hours is no longer sent', function () {
    $user = User::factory()->create();
    Generation::factory()->for($user)->create([
        'status' => GenerationStatus::SUCCEEDED,
        'image_url' => 'https://pixelforge.promptedit.dev/images/gen_1.png',
        'completed_at' => now()->subHours(25),
    ]);

    $this->actingAs($user)
        ->get(route('history'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('generations.data.0.image_url', null)
            ->where('generations.data.0.is_image_expired', true)
        );
});
