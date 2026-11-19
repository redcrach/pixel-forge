<?php

namespace App\Http\Controllers;

use App\Actions\ApplyGenerationUpdate;
use App\Dto\Pixelforge\PixelforgeGenerationResponseDto;
use App\Models\Generation;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Receives Pixelforge webhooks, already signature-checked by VerifyPixelforgeSignature.
 * Delivery is at least once and in any order: events are deduplicated by id, and
 * ApplyGenerationUpdate never moves a generation backwards.
 */
class PixelforgeWebhookController extends Controller
{
    private const PING = 'ping';

    public function __invoke(Request $request, ApplyGenerationUpdate $applyGenerationUpdate): Response
    {
        if ($request->input('type') === self::PING) {
            return response()->noContent();
        }

        $pixelforgeGeneration = PixelforgeGenerationResponseDto::make($request->array('data.object'));

        $webhookEvent = WebhookEvent::query()->createOrFirst(['id' => $request->string('id')->value()], [
            'type' => $request->string('type')->value(),
            'pixelforge_generation_id' => $pixelforgeGeneration->id,
            'payload' => $request->json()->all(),
        ]);

        if ($webhookEvent->processed_at !== null) {
            return response()->noContent();
        }

        $generation = $this->findGeneration($pixelforgeGeneration);

        if ($generation === null) {
            Log::warning('Pixelforge webhook for an unknown generation.', [
                'event_id' => $webhookEvent->id,
                'pixelforge_generation_id' => $pixelforgeGeneration->id,
            ]);

            return response()->noContent();
        }

        $applyGenerationUpdate($generation, $pixelforgeGeneration);

        $webhookEvent->update(['processed_at' => now()]);

        return response()->noContent();
    }

    /**
     * A webhook can arrive before the submit job has saved the Pixelforge id,
     * so fall back to our own id, which Pixelforge echoes back in the metadata.
     */
    private function findGeneration(PixelforgeGenerationResponseDto $pixelforgeGeneration): ?Generation
    {
        return Generation::query()->where('pixelforge_id', $pixelforgeGeneration->id)->first()
            ?? Generation::query()->find($pixelforgeGeneration->orderId);
    }
}
