<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Client Auth Routes (M2M)
Route::prefix('v1/client')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\ClientAuthController::class, 'login']);
    Route::post('/refresh', [\App\Http\Controllers\Api\ClientAuthController::class, 'refresh'])->middleware('auth:sanctum');
});

// External API Routes (Protected by Sanctum)
Route::middleware('auth:sanctum')->prefix('v1/external')->group(function () {
    Route::get('/projects', [\Modules\Project\Http\Controllers\Api\ExternalProjectController::class, 'index']);
    Route::get('/projects/{id}', [\Modules\Project\Http\Controllers\Api\ExternalProjectController::class, 'show']);
});
