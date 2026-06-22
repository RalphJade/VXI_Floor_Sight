<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\FloorSightApiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified', 'throttle:60,1'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/floors', [FloorSightApiController::class, 'index']);
    Route::post('/floors', [FloorSightApiController::class, 'storeFloor']);
    Route::put('/floors/{floor}', [FloorSightApiController::class, 'updateFloor']);
    Route::delete('/floors/{floor}', [FloorSightApiController::class, 'destroyFloor']);

    Route::post('/workstations', [FloorSightApiController::class, 'storeWorkstation']);
    Route::put('/workstations/{workstation}', [FloorSightApiController::class, 'updateWorkstation']);
    Route::delete('/workstations/{workstation}', [FloorSightApiController::class, 'destroyWorkstation']);
});

require __DIR__.'/auth.php';
