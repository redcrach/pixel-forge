<?php

use App\Actions\EstimateGenerationCost;
use App\Dto\Pricing\EstimateLineDto;
use App\Dto\Pricing\GenerationEstimateDto;
use App\Enums\ImageQuality;
use App\Enums\ImageSize;
use Illuminate\Http\Client\ConnectionException;

function estimateTotalling(int $totalMinNano, int $totalMaxNano): GenerationEstimateDto
{
    $emptyLine = new EstimateLineDto(0, 0, 0, 0);

    return new GenerationEstimateDto($emptyLine, $emptyLine, $emptyLine, $emptyLine, $totalMinNano, $totalMaxNano);
}

test('it returns the estimate for the submitted form values', function () {
    $estimate = estimateTotalling(1_000, 2_000);

    $this->mock(EstimateGenerationCost::class)
        ->shouldReceive('__invoke')
        ->once()
        ->with('A red fox', ImageSize::Landscape, ImageQuality::High, 2)
        ->andReturn($estimate);

    $response = $this->postJson(route('generations.estimate'), [
        'prompt' => 'A red fox',
        'size' => '1536x1024',
        'quality' => 'high',
        'reference_image_count' => 2,
    ]);

    $response->assertOk()->assertExactJson($estimate->toArray());
});

test('an empty prompt can still be estimated', function () {
    $this->mock(EstimateGenerationCost::class)
        ->shouldReceive('__invoke')
        ->once()
        ->with('', ImageSize::Square, ImageQuality::Medium, 0)
        ->andReturn(estimateTotalling(0, 0));

    $this->postJson(route('generations.estimate'), [
        'prompt' => null,
        'size' => '1024x1024',
        'quality' => 'medium',
        'reference_image_count' => 0,
    ])->assertOk();
});

test('it rejects invalid form values', function () {
    $this->mock(EstimateGenerationCost::class)->shouldNotReceive('__invoke');

    $this->postJson(route('generations.estimate'), [
        'prompt' => str_repeat('a', 4001),
        'size' => '512x512',
        'quality' => 'ultra',
        'reference_image_count' => 5,
    ])->assertUnprocessable()->assertJsonValidationErrors(['prompt', 'size', 'quality', 'reference_image_count']);
});

test('it answers 503 when pricing cannot be fetched', function () {
    $this->mock(EstimateGenerationCost::class)
        ->shouldReceive('__invoke')
        ->andThrow(new ConnectionException('timeout'));

    $this->postJson(route('generations.estimate'), [
        'prompt' => 'A red fox',
        'size' => '1024x1024',
        'quality' => 'medium',
        'reference_image_count' => 0,
    ])->assertServiceUnavailable()->assertJson(['message' => 'Pricing is temporarily unavailable.']);
});
