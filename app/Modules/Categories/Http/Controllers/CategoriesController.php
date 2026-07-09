<?php

namespace App\Modules\Categories\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Categories\Http\Requests\CreateCategoriesRequest;
use App\Modules\Categories\Http\Requests\UpdateCategoryIncomeRequest;
use App\Modules\Categories\UseCases\CreateCategoriesUseCases;
use App\Modules\Categories\UseCases\DeleteCategoriesUseCases;
use App\Modules\Categories\UseCases\ListCategoriesUseCases;
use App\Modules\Categories\UseCases\UpdateCategoriesUseCases;
use App\Modules\Categories\UseCases\UpdateCategoryIncomeUseCases;

class CategoriesController extends Controller
{
    public function index(ListCategoriesUseCases $useCase)
    {
        try {
            $categories = $useCase->execute();

            return $this->successResponse('Categories retrieved successfully', $categories);
        } catch (\Throwable $th) {
            return $this->errorResponse('An error occurred while retrieving categories: ' . $th->getMessage(), 500);
        }
    }

    public function store(CreateCategoriesRequest $request, CreateCategoriesUseCases $useCase)
    {
        try {
            $category = $useCase->execute($request->input('description'));

            return $this->successResponse('Category created successfully', $category, 201);
        } catch (\Throwable $th) {
            return $this->errorResponse('An error occurred while creating the category', $th->getMessage(), 500);
        }
    }

    public function destroy(int $id, DeleteCategoriesUseCases $useCase)
    {
        try {
            $category = $useCase->execute($id);

            return $this->successResponse('Category deleted successfully', $category);
        } catch (\RuntimeException $th) {
            return $this->errorResponse($th->getMessage(), 404);
        } catch (\Throwable $th) {
            return $this->errorResponse('An error occurred while deleting the category: ' . $th->getMessage(), 500);
        }
    }

    public function update(int $id, CreateCategoriesRequest $request, UpdateCategoriesUseCases $useCase)
    {
        try {
            $category = $useCase->execute($id, $request->input('description'));

            return $this->successResponse('Category updated successfully', $category);
        } catch (\RuntimeException $th) {
            return $this->errorResponse($th->getMessage(), 404);
        } catch (\Throwable $th) {
            return $this->errorResponse('An error occurred while updating the category: ' . $th->getMessage(), 500);
        }
    }

    public function updateIncome(int $id, UpdateCategoryIncomeRequest $request, UpdateCategoryIncomeUseCases $useCase)
    {
        try {
            $category = $useCase->execute($id, $request->boolean('cat_is_income'));

            return $this->successResponse('Category income flag updated successfully', $category);
        } catch (\RuntimeException $th) {
            return $this->errorResponse($th->getMessage(), 404);
        } catch (\Throwable $th) {
            return $this->errorResponse('An error occurred while updating category income flag: ' . $th->getMessage(), 500);
        }
    }
}
