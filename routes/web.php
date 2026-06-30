<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\FloorSightApiController;
use Illuminate\Support\Facades\Route;

// Root route – serves the SPA shell
Route::get('/', function () {
    return view('spa');
})->name('dashboard');

// Profile routes (still Blade‑based, but can be migrated later)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// API routes – protected by Sanctum (will use auth:sanctum later)
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/floors', [FloorSightApiController::class, 'index']);
    Route::post('/floors', [FloorSightApiController::class, 'storeFloor']);
    Route::put('/floors/{floor}', [FloorSightApiController::class, 'updateFloor']);
    Route::delete('/floors/{floor}', [FloorSightApiController::class, 'destroyFloor']);

    Route::get('/workstations', [FloorSightApiController::class, 'listWorkstations']);
    Route::post('/workstations', [FloorSightApiController::class, 'storeWorkstation']);
    Route::put('/workstations/{workstation}', [FloorSightApiController::class, 'updateWorkstation']);
    Route::delete('/workstations/{workstation}', [FloorSightApiController::class, 'destroyWorkstation']);
});

require __DIR__.'/auth.php';

// SPA catch‑all route – any non‑API path falls back to the SPA view, protected by auth.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('{any}', function () {
        return view('spa');
    })->where('any', '.*');
});
