<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GameController;
use App\Http\Controllers\Api\V1\RecommendationController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [UserController::class, 'show']);
        Route::get('me/preferences', [UserController::class, 'showPreferences']);
        Route::put('me/preferences', [UserController::class, 'updatePreferences']);
        Route::get('recommendations', [RecommendationController::class, 'index']);
    });

    Route::get('games', [GameController::class, 'index']);
    Route::get('games/{game}', [GameController::class, 'show']);
});
