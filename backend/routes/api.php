<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\User\BetController;
use App\Http\Controllers\Api\User\CardController;
use App\Http\Controllers\Api\Admin\PatternController;
use App\Http\Controllers\Api\Admin\RoundController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth API Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Admin API Routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::apiResource('patterns', PatternController::class);
        Route::apiResource('rounds', RoundController::class);
        Route::post('/rounds/{round}/call', [RoundController::class, 'callNumber']);
        Route::post('/rounds/{round}/verify-winners', [RoundController::class, 'verifyWinners']);
    });
    
    // User API Routes
    Route::middleware('user')->prefix('user')->group(function () {
        Route::apiResource('cards', CardController::class);
        Route::apiResource('bets', BetController::class);
        Route::get('/active-rounds', [RoundController::class, 'activeRounds']);
        Route::post('/bets/{bet}/claim-win', [BetController::class, 'claimWin']);
    });
});
