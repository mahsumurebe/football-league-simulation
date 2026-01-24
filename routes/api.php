<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeagueController;

Route::prefix('league')->group(function () {
    Route::post('/generate-fixtures', [LeagueController::class, 'generateFixtures']);
    Route::get('/teams', [LeagueController::class, 'getTeams']);
    Route::get('/matches', [LeagueController::class, 'getMatches']);
    Route::get('/current-week', [LeagueController::class, 'getCurrentWeek']);
    Route::get('/table', [LeagueController::class, 'getLeagueTable']);
    Route::post('/simulate-week', [LeagueController::class, 'simulateNextWeek']);
    Route::post('/simulate-all', [LeagueController::class, 'simulateAll']);
    Route::get('/matches/week/{week}', [LeagueController::class, 'getMatchesByWeek']);
    Route::put('/matches/{id}', [LeagueController::class, 'updateGame']);
    Route::post('/reset', [LeagueController::class, 'resetLeague']);
    Route::get('/predictions', [LeagueController::class, 'getPredictions']);
});
