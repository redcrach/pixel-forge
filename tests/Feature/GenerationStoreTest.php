<?php

use App\Actions\EstimateGenerationCost;
use App\Dto\Pricing\EstimateLineDto;
use App\Dto\Pricing\GenerationEstimateDto;
use App\Enums\CreditTransactionType;
use App\Enums\GenerationStatus;
use App\Enums\ImageQuality;
use App\Enums\ImageSize;
use App\Jobs\SubmitGeneration;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

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

test('it stores a pending generation with the estimate, queues its submission and says it is being generated', function () {
    $user = User::factory()->create(['balance_nano' => 1_000_000_000]);
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

    Queue::assertPushedOn('generations', SubmitGeneration::class, fn (SubmitGeneration $job) => $job->generation->is($generation));
});

test('submitting holds the maximum estimate from the balance', function () {
    $user = User::factory()->create(['balance_nano' => 1_000_000_000]);
    $this->actingAs($user);
    $emptyLine = new EstimateLineDto(0, 0, 0, 0);
    $this->mock(EstimateGenerationCost::class)
        ->shouldReceive('__invoke')
        ->andReturn(new GenerationEstimateDto($emptyLine, $emptyLine, $emptyLine, $emptyLine, 58_437_500, 86_637_500));

    $this->post(route('generations.store'), validGeneration())->assertInertiaFlash('toast.type', 'success');

    $hold = $user->generations()->sole()->creditTransactions()->sole();
    expect($hold->type)->toBe(CreditTransactionType::HOLD)
        ->and($hold->amount_nano)->toBe(-86_637_500)
        ->and($user->refresh()->balance_nano)->toBe(1_000_000_000 - 86_637_500);
});

test('a balance below the maximum estimate is refused and nothing is created', function () {
    $user = User::factory()->create(['balance_nano' => 86_637_499]);
    $this->actingAs($user);
    $emptyLine = new EstimateLineDto(0, 0, 0, 0);
    $this->mock(EstimateGenerationCost::class)
        ->shouldReceive('__invoke')
        ->andReturn(new GenerationEstimateDto($emptyLine, $emptyLine, $emptyLine, $emptyLine, 58_437_500, 86_637_500));

    $this->from(route('home'))
        ->post(route('generations.store'), validGeneration())
        ->assertRedirect(route('home'))
        ->assertInertiaFlash('toast.type', 'error')
        ->assertInertiaFlash('toast.message', 'Not enough credits: this image can cost up to $0.0866375 and your balance is $0.086637499.');

    expect($user->generations()->exists())->toBeFalse()
        ->and($user->refresh()->balance_nano)->toBe(86_637_499);
    Queue::assertNothingPushed();
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

    $this->from(route('home', ['remix' => 'some-generation']))
        ->post(route('generations.store'), validGeneration(['reference_images' => null]))
        ->assertRedirect(route('home'))
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
    Queue::assertNothingPushed();
});

test('a precognitive request only validates and stores nothing', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->mock(EstimateGenerationCost::class)->shouldNotReceive('__invoke');

    $this->withPrecognition()
        ->post(route('generations.store'), validGeneration())
        ->assertSuccessfulPrecognition();

    expect($user->generations()->exists())->toBeFalse();
    Queue::assertNothingPushed();
});

test('a precognitive request reports an invalid reference image under its index', function () {
    $this->actingAs(User::factory()->create());

    $this->withPrecognition()
        ->withHeader('Precognition-Validate-Only', 'reference_images,reference_images.*')
        ->postJson(route('generations.store'), validGeneration([
            'prompt' => '',
            'reference_images' => ['https://example.com/fox.png', 'not a url'],
        ]))
        ->assertUnprocessable()
        ->assertOnlyJsonValidationErrors(['reference_images.1']);
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
