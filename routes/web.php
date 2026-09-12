<?php

use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\VotingModelController;
use App\Http\Controllers\VotingPairController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/voting/models', [VotingModelController::class, 'index'])->name('voting.models');
Route::get('/voting/pair', [VotingPairController::class, 'show'])
    ->name('voting.pair')
    ->block(10, 10);
Route::post('/voting/votes', [VoteController::class, 'store'])
    ->name('voting.votes')
    ->block(10, 10);
Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
