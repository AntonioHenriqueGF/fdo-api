<?php

namespace App\Modules\Categories\UseCases;

use App\Modules\Categories\Models\CategoriesModel;
use App\Modules\Categories\Models\RulesModel;
use Illuminate\Support\Facades\Auth;

class DeleteCategoriesUseCases
{
    public function __construct(
        private CategoriesModel $categoriesModel,
        private RulesModel $rulesModel
    ) {
        //
    }
    public function execute(int $id): array
    {
        $user = Auth::user();
        $category = $this->categoriesModel->where('cat_user_id', $user->use_id)->find($id);

        if (!$category) {
            throw new \RuntimeException('Category not found or does not belong to the user.');
        }

        // Deletes associated rules first to maintain referential integrity
        $this->rulesModel->deleteByCategoryId($user->use_id, $id);

        $category->delete();

        return ['message' => 'Category deleted successfully'];
    }
}
