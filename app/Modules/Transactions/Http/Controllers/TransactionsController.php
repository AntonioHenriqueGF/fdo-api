<?php

namespace App\Modules\Transactions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transactions\Http\Requests\CreateTransactionRequest;
use App\Modules\Transactions\Http\Requests\ListTransactionsRequest;
use App\Modules\Transactions\Http\Requests\TimeIntervalRequest;
use App\Modules\Transactions\Http\Requests\UpdateTransactionRequest;
use App\Modules\Transactions\UseCases\CreateTransactionUseCase;
use App\Modules\Transactions\UseCases\DailyReconciliationUseCase;
use App\Modules\Transactions\UseCases\DailyTransactionsUseCase;
use App\Modules\Transactions\UseCases\DeleteTransactionUseCase;
use App\Modules\Transactions\UseCases\ListTransactionsUseCase;
use App\Modules\Transactions\UseCases\MonthlyTransactionsUseCase;
use App\Modules\Transactions\UseCases\UpdateTransactionUseCase;

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

    public function store(CreateTransactionRequest $request, CreateTransactionUseCase $createTransactionUseCase)
    {
        try {
            $transaction = $createTransactionUseCase->execute($request->validated());

            return $this->successResponse('Transaction created successfully', $transaction, 201);
        } catch (\InvalidArgumentException $th) {
            return $this->errorResponse($th->getMessage(), null, 400);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to create transaction', $th->getMessage(), 500);
        }
    }

    public function update(int $id, UpdateTransactionRequest $request, UpdateTransactionUseCase $updateTransactionUseCase)
    {
        try {
            $transaction = $updateTransactionUseCase->execute($id, $request->validated());

            return $this->successResponse('Transaction updated successfully', $transaction);
        } catch (\InvalidArgumentException $th) {
            return $this->errorResponse($th->getMessage(), null, 400);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to update transaction', $th->getMessage(), 500);
        }
    }

    public function destroy(int $id, DeleteTransactionUseCase $deleteTransactionUseCase)
    {
        try {
            $deleteTransactionUseCase->execute($id);

            return $this->successResponse('Transaction deleted successfully');
        } catch (\InvalidArgumentException $th) {
            return $this->errorResponse($th->getMessage(), null, 400);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to delete transaction', $th->getMessage(), 500);
        }
    }
}
