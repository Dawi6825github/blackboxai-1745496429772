<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\PatternController;
use App\Http\Controllers\Admin\RoundController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\User\BetController;
use App\Http\Controllers\User\CardController;
use App\Http\Controllers\User\GameboardController;

Route::get('/', function () {
    return view('welcome');
});

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('patterns', PatternController::class);
        Route::resource('rounds', RoundController::class);
        Route::resource('users', AdminUserController::class);
        
        // Additional admin routes for managing the game
        Route::post('/rounds/{round}/start', [RoundController::class, 'startRound'])->name('rounds.start');
        Route::post('/rounds/{round}/call', [RoundController::class, 'callNumber'])->name('rounds.call');
        Route::post('/rounds/{round}/end', [RoundController::class, 'endRound'])->name('rounds.end');
    });
    
    // User Routes
    Route::middleware('user')->prefix('user')->name('user.')->group(function () {
        Route::resource('cards', CardController::class);
        Route::resource('bets', BetController::class);
        Route::get('/gameboard', [GameboardController::class, 'index'])->name('gameboard');
        Route::get('/gameboard/{round}', [GameboardController::class, 'show'])->name('gameboard.show');
    });
});
