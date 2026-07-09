<?php

namespace App\Modules\Transactions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transactions\Http\Requests\ListTransactionsRequest;
use App\Modules\Transactions\Http\Requests\TimeIntervalRequest;
use App\Modules\Transactions\UseCases\DailyReconciliationUseCase;
use App\Modules\Transactions\UseCases\DailyTransactionsUseCase;
use App\Modules\Transactions\UseCases\ListTransactionsUseCase;
use App\Modules\Transactions\UseCases\MonthlyTransactionsUseCase;

class TransactionsController extends Controller
{
    public function getDailyTransactions(DailyTransactionsUseCase $dailyTransactionsUseCase)
    {
        $dailyTransactions = $dailyTransactionsUseCase->execute();

        return $this->successResponse('Daily transactions retrieved successfully', $dailyTransactions);
    }

    public function getMonthlyTransactions(MonthlyTransactionsUseCase $monthlyTransactionsUseCase)
    {
        $monthlyTransactions = $monthlyTransactionsUseCase->execute();

        return $this->successResponse('Monthly transactions retrieved successfully', $monthlyTransactions);
    }

    public function getDailyReconciliation(TimeIntervalRequest $request, DailyReconciliationUseCase $dailyReconciliationUseCase)
    {
        $reconciliation = $dailyReconciliationUseCase->execute($request->input('date_start'), $request->input('date_end'));

        return $this->successResponse('Daily reconciliation retrieved successfully', $reconciliation);
    }

    public function index(ListTransactionsRequest $request, ListTransactionsUseCase $listTransactionsUseCase)
    {
        try {
            $filters = $request->validated();
            $transactions = $listTransactionsUseCase->execute($filters);

            return $this->successResponse('Transactions retrieved successfully', $transactions);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to retrieve transactions', $th->getMessage(), 500);
        }
    }

    public function create()
    {
        // Implement the logic for creating a transaction
    }

    public function edit($id)
    {
        // Implement the logic for editing a transaction
    }

    public function show($id)
    {
        // Implement the logic for showing a specific transaction
    }
}
