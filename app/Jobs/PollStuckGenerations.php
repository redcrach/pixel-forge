<?php

namespace App\Jobs;

use App\Actions\ApplyGenerationUpdate;
use App\Dto\Pixelforge\PixelforgeGenerationResponseDto;
use App\Enums\GenerationStatus;
use App\Models\Generation;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\Attributes\UniqueFor;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

#[UniqueFor(30 * 60)]
class PollStuckGenerations implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /** A webhook normally moves a generation within seconds; 10 quiet minutes means it was missed. */
    private const STUCK_AFTER_MINUTES = 10;

    /** Keeps a run well inside Pixelforge's 60 requests per minute, which every environment shares. */
    private const MAX_GENERATIONS_PER_RUN = 50;

    public function handle(ApplyGenerationUpdate $applyGenerationUpdate): void
    {
        foreach ($this->stuckGenerations() as $generation) {
            try {
                $response = Http::pixelforge()->get("/generations/{$generation->pixelforge_id}")->throw();
            } catch (RequestException $exception) {
                if ($exception->response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
                    // Pixelforge rate limit reached while polling stuck generations; the next run continues.
                    return;
                }

                continue;
            } catch (ConnectionException $exception) {
                continue;
            }

            $applyGenerationUpdate(
                generation: $generation,
                pixelforgeGeneration: PixelforgeGenerationResponseDto::make($response->json()),
            );
        }
    }

    /**
     * Accepted by Pixelforge but unchanged for a while, oldest first. updated_at only moves when an
     * update actually changes the generation, so a duplicate or late webhook doesn't hide it.
     * Pending generations are left to SubmitGeneration, which is still retrying them.
     *
     * @return Collection<int, Generation>
     */
    private function stuckGenerations(): Collection
    {
        return Generation::query()
            ->whereIn('status', [GenerationStatus::QUEUED, GenerationStatus::PROCESSING])
            ->whereNotNull('pixelforge_id')
            ->where('updated_at', '<=', now()->subMinutes(self::STUCK_AFTER_MINUTES))
            ->orderBy('updated_at')
            ->limit(self::MAX_GENERATIONS_PER_RUN)
            ->get();
    }
}
