<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', HomeController::class);
    Route::resource('/categories', CategoryController::class)->except('show', 'destroy');
    Route::resource('/expenses', ExpenseController::class);
    Route::get('/account/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/account/password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::get('/login', [AuthController::class, 'loginView'])->name('login');
Route::post('/login', [AuthController::class, 'loginPost']);
