<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Generate')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('history', 'History')->name('history');
});

require __DIR__.'/settings.php';
