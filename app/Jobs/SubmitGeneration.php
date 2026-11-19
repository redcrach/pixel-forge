<?php

namespace App\Jobs;

use App\Actions\ApplyGenerationUpdate;
use App\Dto\Pixelforge\PixelforgeGenerationResponseDto;
use App\Enums\GenerationStatus;
use App\Enums\PixelforgeErrorCode;
use App\Models\Generation;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\MaxExceptions;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\RetryFor;
use Illuminate\Queue\Attributes\UniqueFor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Sends a pending generation to Pixelforge, retrying while it is temporarily unavailable.
 * The generation id is the Idempotency-Key, so every retry is safe and never billed twice.
 */
#[Queue('generations')]
#[RetryFor(minutes: 15)]
#[UniqueFor(20 * 60)]
#[MaxExceptions(3)]
#[Backoff(10)]
class SubmitGeneration implements ShouldBeUnique, ShouldQueue, ShouldQueueAfterCommit
{
    use Queueable;

    /** Pixelforge allows 3 generations queued or processing at once, for the whole account. */
    private const MAX_ACTIVE_GENERATIONS = 3;

    /** An active generation not updated for this long is probably stuck, so it no longer holds a slot. */
    private const STUCK_AFTER_MINUTES = 10;

    public function __construct(public Generation $generation) {}

    public function uniqueId(): string
    {
        return $this->generation->id;
    }

    public function handle(ApplyGenerationUpdate $applyGenerationUpdate): void
    {
        if ($this->generation->status !== GenerationStatus::PENDING) {
            return;
        }

        if (! $this->isSlotAvailable()) {
            $this->release(random_int(5, 30));

            return;
        }

        try {
            $response = Http::pixelforge()
                ->withHeader('Idempotency-Key', $this->generation->id)
                ->post('/generations', $this->requestBody());
        } catch (ConnectionException) {
            $this->release(random_int(10, 30));

            return;
        }

        if ($response->successful()) {
            $applyGenerationUpdate($this->generation, PixelforgeGenerationResponseDto::make($response->json()));

            return;
        }

        $this->handleRejection($response);
    }

    /**
     * Every retry was used up (or the job kept crashing): give up without charging anything.
     */
    public function failed(?Throwable $exception): void
    {
        $this->markFailed('retries_exhausted', __('Pixelforge stayed unavailable, so the image could not be generated.'));
    }

    /**
     * Checked locally first so a full account doesn't spend requests from the 60/minute limit.
     * Pixelforge's own 429 stays the source of truth.
     */
    private function isSlotAvailable(): bool
    {
        return Generation::query()
            ->whereIn('status', [GenerationStatus::QUEUED, GenerationStatus::PROCESSING])
            ->where('updated_at', '>=', now()->subMinutes(self::STUCK_AFTER_MINUTES))
            ->count() < self::MAX_ACTIVE_GENERATIONS;
    }

    /**
     * Only the documented fields: Pixelforge rejects unknown ones with a 422.
     *
     * @return array<string, mixed>
     */
    private function requestBody(): array
    {
        return [
            'prompt' => $this->generation->prompt,
            'size' => $this->generation->size->value,
            'quality' => $this->generation->quality->value,
            'reference_images' => $this->generation->reference_images,
            'metadata' => ['order_id' => $this->generation->id],
        ];
    }

    /**
     * Temporary problems are retried after a random delay, so queued jobs don't all retry at the same moment.
     * Anything else is permanent and fails the generation.
     */
    private function handleRejection(Response $response): void
    {
        $retryAfterSeconds = (int) $response->header('Retry-After');
        $errorCode = PixelforgeErrorCode::tryFrom((string) $response->json('error.code'));
        match ($errorCode) {
            PixelforgeErrorCode::CONCURRENCY_LIMIT_EXCEEDED => $this->release(random_int(5, 30)),
            PixelforgeErrorCode::RATE_LIMIT_EXCEEDED,
            PixelforgeErrorCode::OVERLOADED => $this->release($retryAfterSeconds + random_int(1, 5)),
            default => $response->serverError()
                ? $this->release(random_int(10, 30))
                : $this->markFailed(
                    (string) $response->json('error.code', 'request_failed'),
                    (string) $response->json('error.message', __('Pixelforge rejected the generation.')),
                ),
        };
    }

    private function markFailed(string $errorCode, string $errorMessage): void
    {
        DB::transaction(function () use ($errorCode, $errorMessage): void {
            $generation = Generation::query()->lockForUpdate()->findOrFail($this->generation->id);

            if ($generation->status !== GenerationStatus::PENDING) {
                return;
            }

            $generation->update([
                'status' => GenerationStatus::FAILED,
                'completed_at' => now(),
                'meta_data' => [
                    ...($generation->meta_data ?? []),
                    'error' => ['code' => $errorCode, 'message' => $errorMessage],
                ],
            ]);
        });
    }
}
