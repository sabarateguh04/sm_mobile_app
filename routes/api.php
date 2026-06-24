<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\Api\PetugasTrackingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider or bootstrap/app.php.
|
*/

Route::post('login', [PetugasTrackingController::class, 'login']);
Route::get('profile', [PetugasTrackingController::class, 'profile']);
Route::post('update-location', [PetugasTrackingController::class, 'updateLocation']);
Route::post('update-profile', [PetugasTrackingController::class, 'updateProfile']);

Route::prefix('auth')->group(function () {
    Route::post('login', [ApiAuthController::class, 'login']);
    Route::post('register', [ApiAuthController::class, 'register']);
    Route::get('all-tokens', [ApiAuthController::class, 'allTokens']);
    Route::post('logout', [ApiAuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [ApiAuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('me', [ApiAuthController::class, 'me'])->middleware('auth:api');
});

// Route API untuk Turjagwali
Route::get('turjagwali', [\App\Http\Controllers\Api\TurjagwaliController::class, 'index']);
