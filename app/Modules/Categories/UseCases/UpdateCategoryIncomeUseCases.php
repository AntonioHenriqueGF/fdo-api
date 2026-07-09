<?php

namespace App\Modules\Categories\UseCases;

use App\Modules\Categories\Models\CategoriesModel;
use Illuminate\Support\Facades\Auth;

class UpdateCategoryIncomeUseCases
{
    public function __construct(
        private CategoriesModel $categoriesModel
    ) {
        //
    }

    public function execute(int $id, bool $isIncome): CategoriesModel
    {
        $user = Auth::user();

        $category = $this->categoriesModel
            ->where('cat_user_id', $user->use_id)
            ->find($id);

        if (! $category) {
            throw new \RuntimeException('Category not found or does not belong to the user.');
        }

        $category->cat_is_income = $isIncome;
        $category->save();

        return $category;
    }
}
