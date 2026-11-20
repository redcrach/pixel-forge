<?php

use App\Actions\ApplyGenerationUpdate;
use App\Dto\Pixelforge\PixelforgeGenerationResponseDto;
use App\Enums\GenerationStatus;
use App\Enums\ImageQuality;
use App\Enums\ImageSize;
use App\Jobs\SubmitGeneration;
use App\Models\Generation;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    Http::preventStrayRequests();
});

function pixelforgeError(int $status, string $code, array $headers = []): array
{
    return ['*/generations' => Http::response(['error' => ['type' => 'error', 'code' => $code, 'message' => "Pixelforge says {$code}."]], $status, $headers)];
}

test('it submits only the documented fields with the generation id as idempotency key', function () {
    $generation = Generation::factory()->create([
        'prompt' => 'A red fox',
        'size' => ImageSize::LANDSCAPE,
        'quality' => ImageQuality::HIGH,
        'reference_images' => ['https://example.com/fox.png'],
    ]);
    $acceptedGeneration = pixelforgeFixture('generation-queued');

    Http::fake(['*/generations' => Http::response($acceptedGeneration, 202)]);

    $this->mock(ApplyGenerationUpdate::class)
        ->shouldReceive('__invoke')
        ->once()
        ->withArgs(fn (Generation $updatedGeneration, PixelforgeGenerationResponseDto $pixelforgeGeneration) => $updatedGeneration->is($generation)
            && $pixelforgeGeneration == PixelforgeGenerationResponseDto::make($acceptedGeneration))
        ->andReturn($generation);

    runSubmitGeneration($generation)->assertNotReleased();

    Http::assertSent(fn (Request $request) => $request->method() === 'POST'
        && $request->header('Idempotency-Key') === [$generation->id]
        && $request->data() === [
            'prompt' => 'A red fox',
            'size' => '1536x1024',
            'quality' => 'high',
            'reference_images' => ['https://example.com/fox.png'],
            'metadata' => ['order_id' => $generation->id],
        ]);
});

test('a generation that is no longer pending is not submitted again', function () {
    Http::fake();

    runSubmitGeneration(Generation::factory()->create(['status' => GenerationStatus::QUEUED]))
        ->assertNotReleased();

    Http::assertNothingSent();
});

test('it waits without calling pixelforge while 3 generations are active', function () {
    Http::fake();
    Generation::factory()->count(2)->create(['status' => GenerationStatus::QUEUED]);
    Generation::factory()->create(['status' => GenerationStatus::PROCESSING]);

    $job = runSubmitGeneration(Generation::factory()->create());

    $job->assertReleased();
    expect($job->job->releaseDelay)->toBeBetween(5, 30);
    Http::assertNothingSent();
});

test('a stuck active generation does not hold a slot forever', function () {
    Http::fake(['*/generations' => Http::response(pixelforgeFixture('generation-queued'), 202)]);
    Generation::factory()->count(3)->create(['status' => GenerationStatus::QUEUED, 'updated_at' => now()->subMinutes(11)]);

    runSubmitGeneration(Generation::factory()->create())->assertNotReleased();

    Http::assertSentCount(1);
});

test('a temporary problem is retried later after a random delay', function (array $fakeResponse, int $minimumDelay, int $maximumDelay) {
    Http::fake($fakeResponse);
    $generation = Generation::factory()->create();

    $job = runSubmitGeneration($generation);

    $job->assertReleased();
    expect($job->job->releaseDelay)->toBeBetween($minimumDelay, $maximumDelay)
        ->and($generation->refresh()->status)->toBe(GenerationStatus::PENDING);
})->with([
    'concurrency limit' => [fn () => pixelforgeError(429, 'concurrency_limit_exceeded', ['Retry-After' => '5']), 5, 30],
    'rate limit' => [fn () => pixelforgeError(429, 'rate_limit_exceeded', ['Retry-After' => '20']), 21, 25],
    'overloaded' => [fn () => pixelforgeError(503, 'overloaded', ['Retry-After' => '2']), 3, 7],
    'server error' => [fn () => ['*/generations' => Http::response('Bad gateway', 502)], 10, 30],
    'connection failure' => [fn () => ['*/generations' => Http::failedConnection()], 10, 30],
]);

test('a permanent rejection fails the generation without retrying', function (int $status, string $errorCode, string $userMessage) {
    Http::fake(pixelforgeError($status, $errorCode));
    $generation = Generation::factory()->create();

    runSubmitGeneration($generation)->assertNotReleased();

    $generation->refresh();
    expect($generation->status)->toBe(GenerationStatus::SUBMISSION_FAILED)
        ->and($generation->completed_at)->not->toBeNull()
        ->and($generation->meta_data['error'])->toBe(['code' => $errorCode, 'message' => $userMessage]);
})->with([
    'daily limit' => [429, 'daily_limit_exceeded', 'The daily generation limit has been reached. Please try again tomorrow.'],
    'blocked prompt' => [400, 'content_policy_violation', 'This prompt was blocked by the content policy. Try rewording it.'],
    'invalid request' => [422, 'validation_failed', 'Something went wrong on our side, so the image could not be generated.'],
    'idempotency key reused' => [409, 'idempotency_key_reused', 'Something went wrong on our side, so the image could not be generated.'],
]);

test('pixelforge\'s own rejection message is logged, never shown to the user', function () {
    Http::fake(pixelforgeError(401, 'invalid_api_key'));
    Log::spy();
    $generation = Generation::factory()->create();

    runSubmitGeneration($generation);

    expect($generation->refresh()->meta_data['error'])->toBe([
        'code' => 'invalid_api_key',
        'message' => 'Something went wrong on our side, so the image could not be generated.',
    ]);
    Log::shouldHaveReceived('error')->withArgs(fn (string $message, array $context): bool => $context['generation_id'] === $generation->id
        && $context['status'] === 401
        && $context['error']['message'] === 'Pixelforge says invalid_api_key.');
});

test('an unknown rejection gets the generic message', function () {
    Http::fake(['*/generations' => Http::response(['error' => ['code' => 'something_new', 'message' => 'Internal detail.']], 400)]);
    $generation = Generation::factory()->create();

    runSubmitGeneration($generation);

    expect($generation->refresh()->meta_data['error'])->toBe([
        'code' => 'request_failed',
        'message' => 'Something went wrong on our side, so the image could not be generated.',
    ]);
});

test('running out of retries fails a pending generation', function () {
    $generation = Generation::factory()->create();

    (new SubmitGeneration($generation))->failed(null);

    $generation->refresh();
    expect($generation->status)->toBe(GenerationStatus::SUBMISSION_FAILED)
        ->and($generation->meta_data['error']['code'])->toBe('retries_exhausted');
});

test('running out of retries does not fail a generation pixelforge already accepted', function () {
    $generation = Generation::factory()->create(['status' => GenerationStatus::QUEUED]);

    (new SubmitGeneration($generation))->failed(null);

    expect($generation->refresh()->status)->toBe(GenerationStatus::QUEUED);
});

test('a rejected submission gives the hold back', function () {
    Http::fake(pixelforgeError(400, 'content_policy_violation'));
    $generation = userWithHeldGeneration();

    runSubmitGeneration($generation);

    expect($generation->user->refresh()->balance_nano)->toBe(1_000_000_000);
});

test('running out of retries gives the hold back', function () {
    $generation = userWithHeldGeneration();

    (new SubmitGeneration($generation))->failed(null);

    expect($generation->user->refresh()->balance_nano)->toBe(1_000_000_000);
});
