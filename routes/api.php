<?php

use App\Http\Controllers\Api\PropertyAssetController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\PropertyDocumentController;
use App\Http\Controllers\Api\UnitController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'organization'])->prefix('v1')->group(function (): void {
    Route::get('/properties', [PropertyController::class, 'index'])
        ->middleware('permission:property.properties.view');

    Route::get('/properties/{property}', [PropertyController::class, 'show'])
        ->middleware('permission:property.properties.view');

    Route::post('/properties', [PropertyController::class, 'store'])
        ->middleware('permission:property.properties.create');

    Route::put('/properties/{property}', [PropertyController::class, 'update'])
        ->middleware('permission:property.properties.update');

    Route::patch('/properties/{property}', [PropertyController::class, 'update'])
        ->middleware('permission:property.properties.update');

    Route::delete('/properties/{property}', [PropertyController::class, 'destroy'])
        ->middleware('permission:property.properties.archive');

    Route::get('/properties/{property}/assets', [PropertyAssetController::class, 'index'])
        ->middleware('permission:property.media.view');

    Route::post('/properties/{property}/assets', [PropertyAssetController::class, 'store'])
        ->middleware('permission:property.media.create');

    Route::post('/properties/{property}/assets/reorder', [PropertyAssetController::class, 'reorder'])
        ->middleware('permission:property.media.update');

    Route::patch('/property-assets/{asset}', [PropertyAssetController::class, 'update'])
        ->middleware('permission:property.media.update');

    Route::delete('/property-assets/{asset}', [PropertyAssetController::class, 'destroy'])
        ->middleware('permission:property.media.delete');

    Route::post('/property-assets/{asset}/download', [PropertyAssetController::class, 'download'])
        ->middleware('permission:property.media.view');

    Route::get('/properties/{property}/documents', [PropertyDocumentController::class, 'index'])
        ->middleware('permission:property.documents.view');

    Route::post('/properties/{property}/documents', [PropertyDocumentController::class, 'store'])
        ->middleware('permission:property.documents.create');

    Route::patch('/property-documents/{document}', [PropertyDocumentController::class, 'update'])
        ->middleware('permission:property.documents.update');

    Route::patch('/property-documents/{document}/lifecycle', [PropertyDocumentController::class, 'changeLifecycle'])
        ->middleware('permission:property.documents.update');

    Route::delete('/property-documents/{document}', [PropertyDocumentController::class, 'destroy'])
        ->middleware('permission:property.documents.delete');

    Route::post('/property-documents/{document}/download', [PropertyDocumentController::class, 'download'])
        ->middleware('permission:property.documents.view');

    Route::get('/properties/{property}/units', [UnitController::class, 'index'])
        ->middleware('permission:inventory.units.view');

    Route::post('/properties/{property}/units', [UnitController::class, 'store'])
        ->middleware('permission:inventory.units.create');

    Route::get('/units/{unit}', [UnitController::class, 'show'])
        ->middleware('permission:inventory.units.view');

    Route::put('/units/{unit}', [UnitController::class, 'update'])
        ->middleware('permission:inventory.units.update');

    Route::patch('/units/{unit}', [UnitController::class, 'update'])
        ->middleware('permission:inventory.units.update');

    Route::delete('/units/{unit}', [UnitController::class, 'destroy'])
        ->middleware('permission:inventory.units.archive');
});
