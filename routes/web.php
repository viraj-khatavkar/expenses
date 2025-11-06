<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return inertia('Home');
    });

    Route::resource('/categories', CategoriesController::class);
});

Route::get('/login', [AuthController::class, 'loginView'])->name('login');
Route::post('/login', [AuthController::class, 'loginPost']);
