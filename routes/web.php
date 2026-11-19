<?php

use App\Http\Controllers\GenerationController;
use App\Http\Controllers\GenerationEstimateController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\PixelforgeWebhookController;
use App\Http\Middleware\VerifyPixelforgeSignature;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Generate')->name('home');

Route::post('generations/estimate', GenerationEstimateController::class)
    ->middleware('throttle:120,1')
    ->name('generations.estimate');

Route::post('webhooks/pixelforge', PixelforgeWebhookController::class)
    ->middleware(VerifyPixelforgeSignature::class)
    ->name('webhooks.pixelforge');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('generations', GenerationController::class)
        ->middleware(HandlePrecognitiveRequests::class)
        ->name('generations.store');
    Route::get('history', HistoryController::class)->name('history');
});

require __DIR__.'/settings.php';
