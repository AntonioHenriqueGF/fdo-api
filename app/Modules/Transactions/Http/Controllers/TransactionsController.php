<?php

namespace App\Modules\Transactions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transactions\UseCases\DailyTransactionsUseCase;
use App\Modules\Transactions\UseCases\MonthlyTransactionsUseCase;

class TransactionsController extends Controller
{
    public function getDailyTransactions(DailyTransactionsUseCase $dailyTransactionsUseCase)
    {
        return response()->json($dailyTransactionsUseCase->execute());
    }

    public function getMonthlyTransactions(MonthlyTransactionsUseCase $monthlyTransactionsUseCase)
    {
        return response()->json($monthlyTransactionsUseCase->execute());
    }
}
