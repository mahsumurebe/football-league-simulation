<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/generate-fixtures');
});

Route::get('/generate-fixtures', function () {
    return Inertia::render('GenerateFixtures');
})->name('generate-fixtures');

Route::get('/fixtures', function () {
    return Inertia::render('Fixtures');
})->name('fixtures');

Route::get('/simulation', function () {
    return Inertia::render('Simulation');
})->name('simulation');
