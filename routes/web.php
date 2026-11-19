<?php

use App\Http\Controllers\GenerationController;
use App\Http\Controllers\GenerationEstimateController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Generate')->name('home');

Route::post('generations/estimate', GenerationEstimateController::class)
    ->middleware('throttle:120,1')
    ->name('generations.estimate');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('generations', GenerationController::class)
        ->middleware(HandlePrecognitiveRequests::class)
        ->name('generations.store');
    Route::inertia('history', 'History')->name('history');
});

require __DIR__.'/settings.php';
