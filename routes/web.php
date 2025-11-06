<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return inertia('Home');
    });
});

Route::get('/login', [\App\Http\Controllers\AuthController::class, 'loginView'])->name('login');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'loginPost']);
