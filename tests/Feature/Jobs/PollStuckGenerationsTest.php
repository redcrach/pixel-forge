<?php

use App\Enums\GenerationStatus;
use App\Jobs\PollStuckGenerations;
use App\Models\Generation;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['pixelforge.markup_bps' => 2500]);
    Http::preventStrayRequests();
});

/**
 * A generation Pixelforge accepted that hasn't changed for the given minutes, holding $0.45 of a $1.00 balance.
 */
function stuckGeneration(string $pixelforgeId, int $quietMinutes = 31, GenerationStatus $status = GenerationStatus::PROCESSING): Generation
{
    $generation = userWithHeldGeneration(balanceNano: 1_000_000_000, maximumCostNano: 450_000_000);
    $generation->forceFill([
        'pixelforge_id' => $pixelforgeId,
        'status' => $status,
        'updated_at' => now()->subMinutes($quietMinutes),
    ])->saveQuietly();

    return $generation;
}

/**
 * Pixelforge's answer for that generation, from a fixture.
 */
function pixelforgeGenerationResponse(Generation $generation, string $fixture): array
{
    return [...pixelforgeFixture($fixture), 'id' => $generation->pixelforge_id, 'metadata' => ['order_id' => $generation->id]];
}

function runPollStuckGenerations(): void
{
    app()->call([new PollStuckGenerations, 'handle']);
}

test('a stuck generation that succeeded at pixelforge is completed and charged its actual price', function () {
    $generation = stuckGeneration('gen_succeeded');
    Http::fake(['*/generations/gen_succeeded' => Http::response(pixelforgeGenerationResponse($generation, 'generation-succeeded'))]);

    runPollStuckGenerations();

    $generation->refresh();
    expect($generation->status)->toBe(GenerationStatus::SUCCEEDED)
        ->and($generation->final_price_nano)->toBe(381_887_500)
        ->and($generation->user->balance_nano)->toBe(1_000_000_000 - 381_887_500);
});

test('a stuck generation that failed at pixelforge gets its hold back', function () {
    $generation = stuckGeneration('gen_failed');
    Http::fake(['*/generations/gen_failed' => Http::response(pixelforgeGenerationResponse($generation, 'generation-failed'))]);

    runPollStuckGenerations();

    expect($generation->refresh()->status)->toBe(GenerationStatus::FAILED)
        ->and($generation->user->balance_nano)->toBe(1_000_000_000);
});

test('a generation still running at pixelforge is left as it is', function () {
    $generation = stuckGeneration('gen_running', status: GenerationStatus::PROCESSING);
    Http::fake(['*/generations/gen_running' => Http::response([...pixelforgeGenerationResponse($generation, 'generation-queued'), 'status' => 'processing'])]);

    runPollStuckGenerations();

    expect($generation->refresh()->status)->toBe(GenerationStatus::PROCESSING)
        ->and($generation->user->balance_nano)->toBe(1_000_000_000 - 450_000_000);
});

test('only generations accepted by pixelforge and quiet for 30 minutes are polled', function () {
    Http::fake();
    stuckGeneration('gen_recent', quietMinutes: 20);
    stuckGeneration('gen_done', status: GenerationStatus::SUCCEEDED);
    stuckGeneration('gen_pending', status: GenerationStatus::PENDING);
    Generation::factory()->create(['status' => GenerationStatus::QUEUED, 'pixelforge_id' => null, 'updated_at' => now()->subHour()]);

    runPollStuckGenerations();

    Http::assertNothingSent();
});

test('one failing request does not stop the others', function () {
    $unreachable = stuckGeneration('gen_unreachable', quietMinutes: 60);
    $succeeded = stuckGeneration('gen_succeeded', quietMinutes: 40);
    Http::fake([
        '*/generations/gen_unreachable' => Http::response(['error' => ['code' => 'not_found']], 404),
        '*/generations/gen_succeeded' => Http::response(pixelforgeGenerationResponse($succeeded, 'generation-succeeded')),
    ]);

    runPollStuckGenerations();

    expect($unreachable->refresh()->status)->toBe(GenerationStatus::PROCESSING)
        ->and($succeeded->refresh()->status)->toBe(GenerationStatus::SUCCEEDED);
});

test('a rate limit stops the run so the next one continues', function () {
    stuckGeneration('gen_first', quietMinutes: 60);
    stuckGeneration('gen_second', quietMinutes: 40);
    Http::fake(['*/generations/*' => Http::response(['error' => ['code' => 'rate_limit_exceeded']], 429, ['Retry-After' => '30'])]);

    runPollStuckGenerations();

    Http::assertSentCount(1);
});

test('it runs one at a time, every 30 minutes', function () {
    expect(new PollStuckGenerations)->toBeInstanceOf(ShouldBeUnique::class);

    $this->artisan('schedule:list')->expectsOutputToContain('*/30 * * * *  App\Jobs\PollStuckGenerations');
});
