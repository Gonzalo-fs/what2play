<?php

use App\Http\Controllers\Api\V1\Admin\AdminGameController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GameController;
use App\Http\Controllers\Api\V1\LibraryController;
use App\Http\Controllers\Api\V1\PreferencesController;
use App\Http\Controllers\Api\V1\RecommendationController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1');
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [UserController::class, 'show']);
        Route::get('me/preferences', [PreferencesController::class, 'show']);
        Route::put('me/preferences', [PreferencesController::class, 'update']);
        Route::get('me/library', [LibraryController::class, 'index']);
        Route::put('me/library/{game}', [LibraryController::class, 'update']);
        Route::delete('me/library/{game}', [LibraryController::class, 'destroy']);
        Route::get('recommendations', [RecommendationController::class, 'index']);

        Route::prefix('admin')->group(function (): void {
            Route::post('games', [AdminGameController::class, 'store']);
            Route::patch('games/{game}', [AdminGameController::class, 'update']);
            Route::delete('games/{game}', [AdminGameController::class, 'destroy']);
            Route::post('games/{game}/image', [AdminGameController::class, 'updateImage']);
        });
    });

    Route::get('games', [GameController::class, 'index']);
    Route::get('games/{game}', [GameController::class, 'show']);
});
