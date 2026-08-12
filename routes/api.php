<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\Api\PetugasTrackingController;
use App\Http\Controllers\Api\CadApiController;

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
Route::post('save-fcm-token', [PetugasTrackingController::class, 'saveFcmToken']);

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

// CAD Mobile API
Route::prefix('cad')->group(function () {
    Route::get('my-task', [CadApiController::class, 'myTask']);
    Route::get('task-detail', [CadApiController::class, 'taskDetail']);
    Route::post('terima-task', [CadApiController::class, 'terimaTask']);
    Route::post('menuju', [CadApiController::class, 'menujuTkp']);
    Route::post('tiba', [CadApiController::class, 'tibaTkp']);
    Route::post('selesai', [CadApiController::class, 'selesaiTask']);
    Route::post('upload-photo', [CadApiController::class, 'uploadPhoto']);
    Route::get('stats', [CadApiController::class, 'stats']);
});

// CAD Backoffice API
Route::prefix('backoffice')->group(function () {
    Route::get('dashboard', [CadApiController::class, 'boDashboard']);
    Route::get('list-petugas', [CadApiController::class, 'boListPetugas']);
    Route::get('all-task', [CadApiController::class, 'boAllTask']);
    Route::get('task-detail', [CadApiController::class, 'taskDetail']);
    Route::post('create-task', [CadApiController::class, 'boCreateTask']);
    Route::post('assign-task', [CadApiController::class, 'boAssignTask']);
    Route::post('close-task', [CadApiController::class, 'boCloseTask']);
});

// Web Integration (Data Sharing API)
Route::prefix('integration')->middleware('auth:api')->group(function () {
    Route::get('{module}', [\App\Http\Controllers\Api\IntegrationController::class, 'getModuleData']);
});

// Health check
Route::get('health', [CadApiController::class, 'health']);
