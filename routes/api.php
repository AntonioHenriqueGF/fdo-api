<?php

use App\Modules\Categories\Http\Controllers\CategoriesController;
use App\Modules\Categories\Http\Controllers\RulesController;
use App\Modules\Imports\Http\Controllers\ImportsController;
use App\Modules\Transactions\Http\Controllers\BalanceController;
use App\Modules\Transactions\Http\Controllers\TransactionsController;
use App\Modules\Users\Http\Controllers\LoginController;
use App\Modules\Users\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login']);

Route::post('/signup', [UserController::class, 'create']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [LoginController::class, 'me']);

    Route::post('/logout', [LoginController::class, 'logout']);

    Route::post('/import', [ImportsController::class, 'import']);

    Route::group(['prefix' => 'balance'], function () {
        Route::get('/daily', [BalanceController::class, 'getDailyBalance']);
        Route::get('/monthly', [BalanceController::class, 'getMonthlyBalance']);
    });

    Route::group(['prefix' => 'transactions'], function () {
        Route::get('/daily', [TransactionsController::class, 'getDailyTransactions']);
        Route::get('/monthly', [TransactionsController::class, 'getMonthlyTransactions']);
        Route::post('/reconciliation/daily', [TransactionsController::class, 'getDailyReconciliation']);
    });

    Route::resource('categories', CategoriesController::class);

    Route::resource('categories.rules', RulesController::class)->except(['create', 'edit', 'show']);
});
