<?php

namespace App\Modules\Transactions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transactions\UseCases\DailyBalancesUseCase;
use App\Modules\Transactions\UseCases\MonthlyBalancesUseCase;

class BalanceController extends Controller
{
    public function getDailyBalance(DailyBalancesUseCase $dailyBalancesUseCase)
    {
        // Implement logic to calculate and return the user's daily balance
        $dailyBalances = $dailyBalancesUseCase->execute();
        return $this->successResponse('Daily balances retrieved successfully', $dailyBalances);
    }

    public function getMonthlyBalance(MonthlyBalancesUseCase $monthlyBalancesUseCase)
    {
        // Implement logic to calculate and return the user's monthly balance
        $monthlyBalances = $monthlyBalancesUseCase->execute();
        return $this->successResponse('Monthly balances retrieved successfully', $monthlyBalances);
    }
}
