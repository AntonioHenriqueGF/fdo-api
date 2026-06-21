<?php

namespace App\Modules\Categories\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Categories\Http\Requests\CreateRulesRequest;
use App\Modules\Categories\UseCases\CreateRulesUseCases;
use App\Modules\Categories\UseCases\DeleteRulesUseCases;
use App\Modules\Categories\UseCases\ListRulesUseCases;
use App\Modules\Categories\UseCases\UpdateRulesUseCases;

class RulesController extends Controller
{
    public function index(int $categoryId, ListRulesUseCases $useCase)
    {
        try {
            $rules = $useCase->execute($categoryId);
            return $this->successResponse('Rules retrieved successfully', $rules);
        } catch (\Throwable $th) {
            return $this->errorResponse('An error occurred while retrieving rules', $th->getMessage(), 500);
        }
    }

    public function store(int $categoryId, CreateRulesRequest $request, CreateRulesUseCases $useCase)
    {
        try {
            $rule = $useCase->execute($categoryId, $request->only(['pattern', 'priority']));
            return $this->successResponse('Rule created successfully', $rule, 201);
        } catch (\Throwable $th) {
            return $this->errorResponse('An error occurred while creating the rule', $th->getMessage(), 500);
        }
    }

    public function destroy(int $categoryId, int $ruleId, DeleteRulesUseCases $useCase)
    {
        try {
            $rule = $useCase->execute($ruleId);
            return $this->successResponse('Rule deleted successfully', $rule);
        } catch (\RuntimeException $th) {
            return $this->errorResponse($th->getMessage(), 404);
        } catch (\Throwable $th) {
            return $this->errorResponse('An error occurred while deleting the rule', $th->getMessage(), 500);
        }
    }

    public function update(int $categoryId, int $ruleId, CreateRulesRequest $request, UpdateRulesUseCases $useCase)
    {
        try {
            $rule = $useCase->execute($ruleId, $request->only(['pattern', 'priority']));
            return $this->successResponse('Rule updated successfully', $rule);
        } catch (\RuntimeException $th) {
            return $this->errorResponse($th->getMessage(), 404);
        } catch (\Throwable $th) {
            return $this->errorResponse('An error occurred while updating the rule', $th->getMessage(), 500);
        }
    }
}
