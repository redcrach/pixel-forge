<?php

use App\Enums\GenerationStatus;
use App\Jobs\SubmitGeneration;
use App\Models\Generation;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['pixelforge.webhook_secret' => 'whsec_test_secret', 'pixelforge.markup_bps' => 2500]);
    Http::preventStrayRequests();
});

test('the submit times out, the succeeded webhook arrives, and the retry does not submit again', function () {
    Http::fake(['*/generations' => Http::failedConnection()]);
    $generation = Generation::factory()->create();

    runSubmitGeneration($generation)->assertReleased();

    postPixelforgeWebhook(pixelforgeEventBody('evt_1', 'generation.succeeded', succeededGenerationFor($generation)))
        ->assertNoContent();

    runSubmitGeneration($generation->fresh())->assertNotReleased();

    Http::assertSentCount(1);
    $generation->refresh();
    expect($generation->status)->toBe(GenerationStatus::SUCCEEDED)
        ->and($generation->pixelforge_id)->toBe('gen_01JC2Z8Q4N6V0M3H7X5T9R2B1K')
        ->and($generation->final_price_nano)->toBe(381_887_500);
});

test('the submit times out and the retry gets the already finished generation back without paying twice', function () {
    $generation = Generation::factory()->create();
    Http::fake(['*/generations' => Http::sequence()
        ->pushFailedConnection()
        ->push(succeededGenerationFor($generation), 202, ['Idempotent-Replayed' => 'true']),
    ]);

    runSubmitGeneration($generation)->assertReleased();
    runSubmitGeneration($generation->fresh())->assertNotReleased();

    postPixelforgeWebhook(pixelforgeEventBody('evt_1', 'generation.succeeded', succeededGenerationFor($generation)))
        ->assertNoContent();

    $generation->refresh();
    expect($generation->status)->toBe(GenerationStatus::SUCCEEDED)
        ->and($generation->provider_cost_nano)->toBe(305_510_000)
        ->and($generation->final_price_nano)->toBe(381_887_500);
});

test('pixelforge finishing a generation we gave up on still records it, as it was billed', function () {
    $generation = Generation::factory()->create();
    (new SubmitGeneration($generation))->failed(null);

    expect($generation->refresh()->status)->toBe(GenerationStatus::SUBMISSION_FAILED);

    postPixelforgeWebhook(pixelforgeEventBody('evt_1', 'generation.succeeded', succeededGenerationFor($generation)))
        ->assertNoContent();

    $generation->refresh();
    expect($generation->status)->toBe(GenerationStatus::SUCCEEDED)
        ->and($generation->final_price_nano)->toBe(381_887_500)
        ->and($generation->completed_at->toIso8601ZuluString())->toBe('2026-11-04T14:03:29Z')
        ->and($generation->meta_data)->not->toHaveKey('error');
});
