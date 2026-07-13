<?php

use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\PropertyMediaController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'organization'])->group(function (): void {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/properties', [PropertyController::class, 'index'])->middleware('permission:property.properties.view')->name('properties.index');
        Route::get('/properties/create', [PropertyController::class, 'create'])->middleware('permission:property.properties.create')->name('properties.create');
        Route::post('/properties', [PropertyController::class, 'store'])->middleware('permission:property.properties.create')->name('properties.store');
        Route::get('/properties/{property}/edit', [PropertyController::class, 'edit'])->middleware('permission:property.properties.update')->name('properties.edit');
        Route::put('/properties/{property}', [PropertyController::class, 'update'])->middleware('permission:property.properties.update')->name('properties.update');
        Route::delete('/properties/{property}', [PropertyController::class, 'destroy'])->middleware('permission:property.properties.archive')->name('properties.destroy');
        Route::get('/properties/{property}/media', [PropertyMediaController::class, 'index'])->name('properties.media.index');
        Route::post('/properties/{property}/media/assets', [PropertyMediaController::class, 'storeAsset'])
            ->middleware('permission:property.media.create')
            ->name('properties.media.assets.store');

        Route::patch('/property-assets/{asset}', [PropertyMediaController::class, 'updateAsset'])
            ->middleware('permission:property.media.update')
            ->name('properties.media.assets.update');

        Route::post('/properties/{property}/media/assets/reorder', [PropertyMediaController::class, 'reorderAssets'])
            ->middleware('permission:property.media.update')
            ->name('properties.media.assets.reorder');

        Route::post('/property-assets/{asset}/download', [PropertyMediaController::class, 'downloadAsset'])
            ->middleware('permission:property.media.view')
            ->name('properties.media.assets.download');

        Route::delete('/property-assets/{asset}', [PropertyMediaController::class, 'destroyAsset'])
            ->middleware('permission:property.media.delete')
            ->name('properties.media.assets.destroy');
    });
});
