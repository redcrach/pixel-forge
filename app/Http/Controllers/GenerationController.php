<?php

namespace App\Http\Controllers;

use App\Actions\EstimateGenerationCost;
use App\Enums\ImageQuality;
use App\Enums\ImageSize;
use App\Http\Requests\StoreGenerationRequest;
use App\Jobs\SubmitGeneration;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class GenerationController extends Controller
{
    public function __invoke(StoreGenerationRequest $request, EstimateGenerationCost $estimateGenerationCost): RedirectResponse
    {
        $prompt = (string) $request->validated('prompt');
        $size = $request->enum('size', ImageSize::class);
        $quality = $request->enum('quality', ImageQuality::class);
        $referenceImages = $request->validated('reference_images') ?? [];

        try {
            $estimateDto = $estimateGenerationCost(
                prompt: $prompt,
                size: $size,
                quality: $quality,
                referenceImageCount: count($referenceImages),
            );
        } catch (ConnectionException|RequestException) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Pricing is temporarily unavailable. Please try again.')]);

            return back();
        }

        $generation = $request->user()->generations()->create([
            'prompt' => $prompt,
            'size' => $size,
            'quality' => $quality,
            'reference_images' => $referenceImages,
            'meta_data' => [
                'estimate' => [
                    'min_nano' => $estimateDto->totalMinNano,
                    'max_nano' => $estimateDto->totalMaxNano,
                ],
            ],
        ]);

        SubmitGeneration::dispatch($generation);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Your image is being generated.')]);

        return back();
    }
}
