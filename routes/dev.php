<?php

use App\Http\Controllers\Dev\ApiInspectorController;
use Illuminate\Support\Facades\Route;

Route::prefix('dev/api')->group(function () {
    Route::get('/', [ApiInspectorController::class, 'index'])->name('dev.api.index');
    Route::get('/calls', [ApiInspectorController::class, 'calls'])->name('dev.api.calls');
    Route::get('/calls/{id}', [ApiInspectorController::class, 'call'])->name('dev.api.call');
    Route::get('/quota', [ApiInspectorController::class, 'quota'])->name('dev.api.quota');
    Route::get('/catalog', [ApiInspectorController::class, 'catalog'])->name('dev.api.catalog');
    Route::post('/try', [ApiInspectorController::class, 'tryIt'])->name('dev.api.try');
    Route::post('/fixture', [ApiInspectorController::class, 'saveFixture'])->name('dev.api.fixture');
});

