<?php

use App\Enums\GenerationStatus;
use App\Enums\ImageQuality;
use App\Enums\ImageSize;
use App\Models\User;
use Illuminate\Support\Str;

test('a new generation gets a uuid primary key and starts pending', function () {
    $generation = User::factory()->create()->generations()->create([
        'prompt' => 'A red fox',
        'size' => ImageSize::SQUARE,
        'quality' => ImageQuality::MEDIUM,
    ])->refresh();

    expect(Str::isUuid($generation->id))->toBeTrue()
        ->and($generation->status)->toBe(GenerationStatus::PENDING)
        ->and($generation->reference_images)->toBe([]);
});
