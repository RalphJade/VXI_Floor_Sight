<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FloorSightApiController;

Route::get('/workstations', [FloorSightApiController::class, 'listWorkstations']);
Route::patch('/workstations/{workstation}', [FloorSightApiController::class, 'updateWorkstation']);