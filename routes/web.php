<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', HomeController::class);
    Route::resource('/categories', CategoryController::class)->except('show', 'destroy');
    Route::resource('/expenses', ExpenseController::class);
});

Route::get('/login', [AuthController::class, 'loginView'])->name('login');
Route::post('/login', [AuthController::class, 'loginPost']);
