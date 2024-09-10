<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\LeaderboardController::class, 'index']);
Route::post('/leaderboard', [App\Http\Controllers\LeaderboardController::class, 'store'])->name('leaderboard.store');

