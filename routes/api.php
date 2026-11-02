<?php

use App\Http\Controllers\Api\ProductTrackerApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/tracker/items', [ProductTrackerApiController::class, 'index'])->name('tracker.items');
    Route::post('/tracker/check', [ProductTrackerApiController::class, 'check'])->name('tracker.check');
});
