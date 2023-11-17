<?php

use App\Http\Controllers\SimulationController;
use App\Http\Middleware\ClientIsValid;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/simulation', [SimulationController::class, 'simulationQuery'])->middleware(ClientIsValid::class);
Route::get('/simulation', [SimulationController::class, 'getSimulations']);
