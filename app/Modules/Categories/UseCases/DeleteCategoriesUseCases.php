<?php

namespace App\Modules\Categories\UseCases;

use App\Modules\Categories\Models\CategoriesModel;
use Illuminate\Support\Facades\Auth;

class DeleteCategoriesUseCases
{
    public function __construct(
        private CategoriesModel $categoriesModel
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

        $category->delete();

        return ['message' => 'Category deleted successfully'];
    }
}
