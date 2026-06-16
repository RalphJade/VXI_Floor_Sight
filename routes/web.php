<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified', 'throttle:60,1'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // API routes for dashboard
    Route::get('/api/search', [DashboardController::class, 'search']);
    Route::get('/api/workstations/{workstation}', [DashboardController::class, 'getWorkstationDetails']);
    Route::put('/api/workstations/{workstation}', [DashboardController::class, 'updateWorkstation']);
    Route::get('/api/workstations-statuses', [DashboardController::class, 'getWorkstationStatuses']);
    Route::post('/api/workstations/{workstation}/remote-session', [DashboardController::class, 'launchRemoteSession']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
