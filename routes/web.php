<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', HomeController::class);
    Route::get('/reports', ReportController::class);
    Route::resource('/categories', CategoryController::class)->except('show', 'destroy');
    Route::resource('/expenses', ExpenseController::class);
    Route::resource('/sources', SourceController::class)->except('show', 'destroy');
    Route::resource('/income', IncomeController::class);
    Route::resource('/subscriptions', SubscriptionController::class)->except('show');
    Route::get('/account/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/account/password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::get('/login', [AuthController::class, 'loginView'])->name('login');
Route::post('/login', [AuthController::class, 'loginPost']);
