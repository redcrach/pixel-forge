<?php

use App\Actions\ApplyGenerationUpdate;
use App\Dto\Pixelforge\PixelforgeGenerationResponseDto;
use App\Enums\GenerationStatus;
use App\Models\Generation;
use App\Models\WebhookEvent;

beforeEach(function () {
    config(['pixelforge.webhook_secret' => 'whsec_test_secret']);
});

test('a ping is acknowledged without being stored', function () {
    postPixelforgeWebhook('{"id":"evt_ping","object":"event","type":"ping"}')->assertNoContent();

    expect(WebhookEvent::query()->exists())->toBeFalse();
});

test('a generation event is applied to the generation it belongs to', function () {
    $generation = Generation::factory()->create([
        'status' => GenerationStatus::QUEUED,
        'pixelforge_id' => 'gen_01JC2Z8Q4N6V0M3H7X5T9R2B1K',
    ]);

    $this->mock(ApplyGenerationUpdate::class)
        ->shouldReceive('__invoke')
        ->once()
        ->withArgs(fn (Generation $updatedGeneration, PixelforgeGenerationResponseDto $pixelforgeGeneration) => $updatedGeneration->is($generation)
            && $pixelforgeGeneration->status === GenerationStatus::SUCCEEDED)
        ->andReturn($generation);

    postPixelforgeWebhook(pixelforgeEventBody('evt_1', 'generation.succeeded', succeededGenerationFor($generation)))
        ->assertNoContent();

    $webhookEvent = WebhookEvent::query()->findOrFail('evt_1');
    expect($webhookEvent->type)->toBe('generation.succeeded')
        ->and($webhookEvent->pixelforge_generation_id)->toBe('gen_01JC2Z8Q4N6V0M3H7X5T9R2B1K')
        ->and($webhookEvent->processed_at)->not->toBeNull();
});

test('a webhook that arrives before the submit response is matched by our own id', function () {
    $generation = Generation::factory()->create(['status' => GenerationStatus::PENDING, 'pixelforge_id' => null]);

    $this->mock(ApplyGenerationUpdate::class)
        ->shouldReceive('__invoke')
        ->once()
        ->withArgs(fn (Generation $updatedGeneration) => $updatedGeneration->is($generation))
        ->andReturn($generation);

    postPixelforgeWebhook(pixelforgeEventBody('evt_1', 'generation.succeeded', succeededGenerationFor($generation)))
        ->assertNoContent();
});

test('a duplicate event is processed only once', function () {
    $generation = Generation::factory()->create(['pixelforge_id' => 'gen_01JC2Z8Q4N6V0M3H7X5T9R2B1K']);
    $eventBody = pixelforgeEventBody('evt_1', 'generation.succeeded', succeededGenerationFor($generation));

    $this->mock(ApplyGenerationUpdate::class)
        ->shouldReceive('__invoke')
        ->once()
        ->andReturn($generation);

    postPixelforgeWebhook($eventBody)->assertNoContent();
    postPixelforgeWebhook($eventBody)->assertNoContent();

    expect(WebhookEvent::query()->count())->toBe(1);
});

test('an event for an unknown generation is stored and acknowledged so pixelforge stops retrying', function () {
    $this->mock(ApplyGenerationUpdate::class)->shouldNotReceive('__invoke');

    postPixelforgeWebhook(pixelforgeEventBody('evt_1', 'generation.succeeded', pixelforgeFixture('generation-succeeded')))
        ->assertNoContent();

    expect(WebhookEvent::query()->findOrFail('evt_1')->processed_at)->toBeNull();
});

test('a succeeded webhook before the submit response is not undone by that late response', function () {
    config(['pixelforge.markup_bps' => 2500]);
    $generation = Generation::factory()->create(['status' => GenerationStatus::PENDING, 'pixelforge_id' => null]);

    postPixelforgeWebhook(pixelforgeEventBody('evt_1', 'generation.succeeded', succeededGenerationFor($generation)))
        ->assertNoContent();
    app(ApplyGenerationUpdate::class)($generation, PixelforgeGenerationResponseDto::make(pixelforgeFixture('generation-queued')));

    $generation->refresh();
    expect($generation->status)->toBe(GenerationStatus::SUCCEEDED)
        ->and($generation->pixelforge_id)->toBe('gen_01JC2Z8Q4N6V0M3H7X5T9R2B1K')
        ->and($generation->final_price_nano)->toBe(381_887_500);
});
