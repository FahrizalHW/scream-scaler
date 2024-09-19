<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeaderboardController;

Route::get('/', [App\Http\Controllers\LeaderboardController::class, 'index'])->name('leaderboard.index');
Route::post('/leaderboard', [App\Http\Controllers\LeaderboardController::class, 'store']);
Route::post('/check-name', [LeaderboardController::class, 'checkName']);


