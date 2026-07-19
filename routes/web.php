<?php

use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\PropertyMediaController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\AvailabilityController;
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

        Route::get(
            '/units/{unit}/reservations',
            [ReservationController::class, 'index'],
        )
            ->middleware('permission:reservation.reservations.view')
            ->name('units.reservations.index');

		Route::get(
			'/reservations/{reservation}',
			[ReservationController::class, 'show'],
		)->name('reservations.show');

        Route::get(
            '/units/{unit}/reservations/create',
            [ReservationController::class, 'create'],
        )
            ->middleware('permission:reservation.reservations.create')
            ->name('units.reservations.create');

        Route::post(
            '/units/{unit}/reservations',
            [ReservationController::class, 'store'],
        )
            ->middleware('permission:reservation.reservations.create')
            ->name('units.reservations.store');

        Route::get(
            '/reservations/{reservation}/edit',
            [ReservationController::class, 'edit'],
        )
            ->middleware('permission:reservation.reservations.update')
            ->name('reservations.edit');

        Route::put(
            '/reservations/{reservation}',
            [ReservationController::class, 'update'],
        )
            ->middleware('permission:reservation.reservations.update')
            ->name('reservations.update');

        Route::delete(
            '/reservations/{reservation}',
            [ReservationController::class, 'destroy'],
        )
            ->middleware('permission:reservation.reservations.cancel')
            ->name('reservations.destroy');
			
		Route::get(
			'/units/{unit}/availability',
			[AvailabilityController::class, 'index'],
		)
			->middleware('permission:reservation.reservations.view')
			->name('units.availability.index');
			
        Route::get(
            '/properties/{property}/units',
            [UnitController::class, 'index'],
        )
            ->middleware('permission:inventory.units.view')
            ->name('properties.units.index');

        Route::get(
            '/properties/{property}/units/create',
            [UnitController::class, 'create'],
        )
            ->middleware('permission:inventory.units.create')
            ->name('properties.units.create');

        Route::post(
            '/properties/{property}/units',
            [UnitController::class, 'store'],
        )
            ->middleware('permission:inventory.units.create')
            ->name('properties.units.store');

        Route::get(
            '/units/{unit}/edit',
            [UnitController::class, 'edit'],
        )
            ->middleware('permission:inventory.units.update')
            ->name('units.edit');

        Route::put(
            '/units/{unit}',
            [UnitController::class, 'update'],
        )
            ->middleware('permission:inventory.units.update')
            ->name('units.update');

        Route::delete(
            '/units/{unit}',
            [UnitController::class, 'destroy'],
        )
            ->middleware('permission:inventory.units.archive')
            ->name('units.destroy');

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
        Route::post('/properties/{property}/media/documents', [PropertyMediaController::class, 'storeDocument'])
            ->middleware('permission:property.documents.create')
            ->name('properties.media.documents.store');

        Route::patch('/property-documents/{document}', [PropertyMediaController::class, 'updateDocument'])
            ->middleware('permission:property.documents.update')
            ->name('properties.media.documents.update');

        Route::patch('/property-documents/{document}/lifecycle', [PropertyMediaController::class, 'changeDocumentLifecycle'])
            ->middleware('permission:property.documents.update')
            ->name('properties.media.documents.lifecycle');

        Route::post('/property-documents/{document}/download', [PropertyMediaController::class, 'downloadDocument'])
            ->middleware('permission:property.documents.view')
            ->name('properties.media.documents.download');

        Route::delete('/property-documents/{document}', [PropertyMediaController::class, 'destroyDocument'])
            ->middleware('permission:property.documents.delete')
            ->name('properties.media.documents.destroy');
		
		Route::post(
			'/reservations/{reservation}/check-in',
			[ReservationController::class, 'checkIn']
		)
			->middleware('permission:reservation.reservations.update')
			->name('reservations.check-in');
			
		Route::post(
			'/reservations/{reservation}/check-out',
			[ReservationController::class, 'checkOut'],
		)
			->middleware('permission:reservation.reservations.update')
			->name('reservations.check-out');
			
		Route::view(
			'/ui/calendar',
			'admin.ui.calendar-preview'
		)->name('admin.ui.calendar');

    });
});
