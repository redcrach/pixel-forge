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

beforeEach(function () {
    Http::preventStrayRequests();
});

/**
 * Runs the job the way a worker would, with queue interactions (release, fail) faked.
 */
function runSubmitGeneration(Generation $generation): SubmitGeneration
{
    $job = (new SubmitGeneration($generation))->withFakeQueueInteractions();
    $job->handle(app(ApplyGenerationUpdate::class));

    return $job;
}

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

test('a permanent rejection fails the generation without retrying', function (int $status, string $errorCode) {
    Http::fake(pixelforgeError($status, $errorCode));
    $generation = Generation::factory()->create();

    runSubmitGeneration($generation)->assertNotReleased();

    $generation->refresh();
    expect($generation->status)->toBe(GenerationStatus::FAILED)
        ->and($generation->completed_at)->not->toBeNull()
        ->and($generation->meta_data['error'])->toBe(['code' => $errorCode, 'message' => "Pixelforge says {$errorCode}."]);
})->with([
    'daily limit' => [429, 'daily_limit_exceeded'],
    'blocked prompt' => [400, 'content_policy_violation'],
    'invalid request' => [422, 'validation_failed'],
    'idempotency key reused' => [409, 'idempotency_key_reused'],
]);

test('running out of retries fails a pending generation', function () {
    $generation = Generation::factory()->create();

    (new SubmitGeneration($generation))->failed(null);

    $generation->refresh();
    expect($generation->status)->toBe(GenerationStatus::FAILED)
        ->and($generation->meta_data['error']['code'])->toBe('retries_exhausted');
});

test('running out of retries does not fail a generation pixelforge already accepted', function () {
    $generation = Generation::factory()->create(['status' => GenerationStatus::QUEUED]);

    (new SubmitGeneration($generation))->failed(null);

    expect($generation->refresh()->status)->toBe(GenerationStatus::QUEUED);
});
