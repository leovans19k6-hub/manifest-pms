<?php

use App\Http\Controllers\Admin\PropertyController;
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
    });
});
