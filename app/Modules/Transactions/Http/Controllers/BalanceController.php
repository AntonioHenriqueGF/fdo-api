<?php

namespace App\Modules\Transactions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transactions\Http\Requests\CreateBalanceRequest;
use App\Modules\Transactions\Http\Requests\ListBalancesRequest;
use App\Modules\Transactions\Http\Requests\UpdateBalanceRequest;
use App\Modules\Transactions\UseCases\CreateBalanceUseCase;
use App\Modules\Transactions\UseCases\DailyBalancesUseCase;
use App\Modules\Transactions\UseCases\DeleteBalanceUseCase;
use App\Modules\Transactions\UseCases\ListBalancesUseCase;
use App\Modules\Transactions\UseCases\MonthlyBalancesUseCase;
use App\Modules\Transactions\UseCases\UpdateBalanceUseCase;

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

    public function index(ListBalancesRequest $request, ListBalancesUseCase $listBalancesUseCase)
    {
        try {
            $filters = $request->validated();
            $balances = $listBalancesUseCase->execute($filters);

            return $this->successResponse('Balances retrieved successfully', $balances);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to retrieve balances', $th->getMessage(), 500);
        }
    }

    public function store(CreateBalanceRequest $request, CreateBalanceUseCase $createBalanceUseCase)
    {
        try {
            $balance = $createBalanceUseCase->execute($request->validated());

            return $this->successResponse('Balance created successfully', $balance, 201);
        } catch (\InvalidArgumentException $th) {
            return $this->errorResponse($th->getMessage(), null, 400);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to create balance', $th->getMessage(), 500);
        }
    }

    public function update(int $id, UpdateBalanceRequest $request, UpdateBalanceUseCase $updateBalanceUseCase)
    {
        try {
            $balance = $updateBalanceUseCase->execute($id, $request->validated());

            return $this->successResponse('Balance updated successfully', $balance);
        } catch (\InvalidArgumentException $th) {
            return $this->errorResponse($th->getMessage(), null, 400);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to update balance', $th->getMessage(), 500);
        }
    }

    public function destroy(int $id, DeleteBalanceUseCase $deleteBalanceUseCase)
    {
        try {
            $deleteBalanceUseCase->execute($id);

            return $this->successResponse('Balance deleted successfully');
        } catch (\InvalidArgumentException $th) {
            return $this->errorResponse($th->getMessage(), null, 400);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to delete balance', $th->getMessage(), 500);
        }
    }
}
