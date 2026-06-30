<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FloorSightApiController;

// API routes protected by Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/workstations', [FloorSightApiController::class, 'listWorkstations']);
    Route::patch('/workstations/{id}', [FloorSightApiController::class, 'updateWorkstation']);
});
