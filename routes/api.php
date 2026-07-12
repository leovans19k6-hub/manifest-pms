<?php

use App\Http\Controllers\Api\PropertyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'organization'])->prefix('v1')->group(function (): void {
    Route::get('/properties', [PropertyController::class, 'index'])->middleware('permission:property.properties.view');
    Route::get('/properties/{property}', [PropertyController::class, 'show'])->middleware('permission:property.properties.view');
    Route::post('/properties', [PropertyController::class, 'store'])->middleware('permission:property.properties.create');
    Route::put('/properties/{property}', [PropertyController::class, 'update'])->middleware('permission:property.properties.update');
    Route::patch('/properties/{property}', [PropertyController::class, 'update'])->middleware('permission:property.properties.update');
    Route::delete('/properties/{property}', [PropertyController::class, 'destroy'])->middleware('permission:property.properties.archive');
});
