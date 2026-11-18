<?php

use App\Enums\GenerationStatus;

test('a status can move forward', function (GenerationStatus $from, GenerationStatus $to) {
    expect($from->canTransitionTo($to))->toBeTrue();
})->with([
    [GenerationStatus::PENDING, GenerationStatus::QUEUED],
    [GenerationStatus::PENDING, GenerationStatus::FAILED],
    [GenerationStatus::QUEUED, GenerationStatus::PROCESSING],
    [GenerationStatus::QUEUED, GenerationStatus::SUCCEEDED],
    [GenerationStatus::PROCESSING, GenerationStatus::SUCCEEDED],
    [GenerationStatus::PROCESSING, GenerationStatus::FAILED],
]);

test('a status never moves backwards or stays the same', function (GenerationStatus $from, GenerationStatus $to) {
    expect($from->canTransitionTo($to))->toBeFalse();
})->with([
    [GenerationStatus::PROCESSING, GenerationStatus::QUEUED],
    [GenerationStatus::QUEUED, GenerationStatus::PENDING],
    [GenerationStatus::PROCESSING, GenerationStatus::PROCESSING],
]);

test('a final status never changes', function (GenerationStatus $from, GenerationStatus $to) {
    expect($from->canTransitionTo($to))->toBeFalse();
})->with([
    [GenerationStatus::SUCCEEDED, GenerationStatus::FAILED],
    [GenerationStatus::FAILED, GenerationStatus::SUCCEEDED],
    [GenerationStatus::SUCCEEDED, GenerationStatus::PROCESSING],
    [GenerationStatus::FAILED, GenerationStatus::QUEUED],
]);
