<?php

use App\Http\Controllers\MediaController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Media routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/media/upload', [MediaController::class, 'upload']);
    Route::get('/media', [MediaController::class, 'index']);
    Route::delete('/media/{media}', [MediaController::class, 'destroy']);
});
