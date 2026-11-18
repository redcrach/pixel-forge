<?php

namespace App\Http\Controllers;

use App\Actions\EstimateGenerationCost;
use App\Enums\ImageQuality;
use App\Enums\ImageSize;
use App\Http\Requests\EstimateGenerationRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;

class GenerationEstimateController extends Controller
{
    public function __invoke(EstimateGenerationRequest $request, EstimateGenerationCost $estimateGenerationCost): JsonResponse
    {
        try {
            $estimateDto = $estimateGenerationCost(
                prompt: (string) $request->validated('prompt'),
                size: $request->enum('size', ImageSize::class),
                quality: $request->enum('quality', ImageQuality::class),
                referenceImageCount: $request->integer('reference_image_count'),
            );
        } catch (ConnectionException|RequestException) {
            return response()->json(
                data: ['message' => __('Pricing is temporarily unavailable.')],
                status: 503,
            );
        }

        return response()->json(
            data: $estimateDto->toArray(),
        );
    }
}
