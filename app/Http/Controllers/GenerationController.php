<?php

namespace App\Http\Controllers;

use App\Actions\Credits\HoldCredits;
use App\Actions\EstimateGenerationCost;
use App\Actions\Pricing\FormatMoney;
use App\Enums\ImageQuality;
use App\Enums\ImageSize;
use App\Exceptions\InsufficientCreditsException;
use App\Http\Requests\StoreGenerationRequest;
use App\Jobs\SubmitGeneration;
use App\Models\Generation;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GenerationController extends Controller
{
    public function __invoke(
        StoreGenerationRequest $request,
        EstimateGenerationCost $estimateGenerationCost,
        HoldCredits $holdCredits,
        FormatMoney $formatMoney,
    ): RedirectResponse {
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

        // The generation and the hold on its maximum cost are saved together, or not at all
        try {
            $generation = DB::transaction(function () use ($request, $prompt, $size, $quality, $referenceImages, $estimateDto, $holdCredits): Generation {
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

                $holdCredits($generation, $estimateDto->totalMaxNano);

                return $generation;
            });
        } catch (InsufficientCreditsException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Not enough credits: this image can cost up to :maximum and your balance is :balance.', [
                'maximum' => $formatMoney($exception->requiredNano, roundUp: true),
                'balance' => $formatMoney($exception->balanceNano),
            ])]);

            return back();
        }

        SubmitGeneration::dispatch($generation);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Your image is being generated.')]);

        return to_route('home');
    }
}
