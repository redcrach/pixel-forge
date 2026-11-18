<?php

use App\Actions\EstimateGenerationCost;
use App\Dto\Pricing\EstimateLineDto;
use App\Dto\Pricing\GenerationEstimateDto;
use App\Enums\GenerationStatus;
use App\Enums\ImageQuality;
use App\Enums\ImageSize;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;

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

test('it stores a pending generation with the estimate and says it is being generated', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $emptyLine = new EstimateLineDto(0, 0, 0, 0);

    $this->mock(EstimateGenerationCost::class)
        ->shouldReceive('__invoke')
        ->once()
        ->with('A red fox in the snow', ImageSize::SQUARE, ImageQuality::MEDIUM, 1)
        ->andReturn(new GenerationEstimateDto($emptyLine, $emptyLine, $emptyLine, $emptyLine, 58_437_500, 86_637_500));

    $this->from(route('home'))
        ->post(route('generations.store'), validGeneration())
        ->assertRedirect(route('home'))
        ->assertSessionHasNoErrors()
        ->assertInertiaFlash('toast.type', 'success');

    $generation = $user->generations()->sole();

    expect($generation->status)->toBe(GenerationStatus::PENDING)
        ->and($generation->prompt)->toBe('A red fox in the snow')
        ->and($generation->size)->toBe(ImageSize::SQUARE)
        ->and($generation->quality)->toBe(ImageQuality::MEDIUM)
        ->and($generation->reference_images)->toBe(['https://example.com/fox.png'])
        ->and($generation->meta_data)->toBe(['estimate' => ['min_nano' => 58_437_500, 'max_nano' => 86_637_500]]);
});

test('reference images are optional', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $emptyLine = new EstimateLineDto(0, 0, 0, 0);

    $this->mock(EstimateGenerationCost::class)
        ->shouldReceive('__invoke')
        ->once()
        ->with('A red fox in the snow', ImageSize::SQUARE, ImageQuality::MEDIUM, 0)
        ->andReturn(new GenerationEstimateDto($emptyLine, $emptyLine, $emptyLine, $emptyLine, 0, 0));

    $this->post(route('generations.store'), validGeneration(['reference_images' => null]))
        ->assertInertiaFlash('toast.type', 'success');

    expect($user->generations()->sole()->reference_images)->toBe([]);
});

test('it stores nothing and shows an error toast when pricing is unavailable', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->mock(EstimateGenerationCost::class)
        ->shouldReceive('__invoke')
        ->once()
        ->andThrow(new ConnectionException('Pixelforge is down'));

    $this->from(route('home'))
        ->post(route('generations.store'), validGeneration())
        ->assertRedirect(route('home'))
        ->assertInertiaFlash('toast.type', 'error');

    expect($user->generations()->exists())->toBeFalse();
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
