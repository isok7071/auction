<?php

use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\VotingModelController;
use App\Http\Controllers\VotingPairController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'voting.index')->name('voting.page');

Route::get('/voting/models', [VotingModelController::class, 'index'])->name('voting.models');
Route::get('/voting/pair', [VotingPairController::class, 'show'])
    ->name('voting.pair')
    ->block(10, 10);
Route::post('/voting/votes', [VoteController::class, 'store'])
    ->name('voting.votes')
    ->block(10, 10);
Route::view('/statistics', 'statistics.index')->name('statistics.page');
Route::get('/statistics/models', [StatisticsController::class, 'models'])->name('statistics.models');
Route::get('/statistics/data', [StatisticsController::class, 'index'])->name('statistics.data');
