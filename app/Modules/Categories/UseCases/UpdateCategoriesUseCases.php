<?php

namespace App\Modules\Categories\UseCases;

use App\Modules\Categories\Models\CategoriesModel;
use Illuminate\Support\Facades\Auth;

class UpdateCategoriesUseCases
{
    public function __construct(
        private CategoriesModel $categoriesModel
    ) {
        //
    }
    public function execute(int $id, string $description): CategoriesModel
    {
        $user = Auth::user();
        $category = $this->categoriesModel->where('cat_user_id', $user->use_id)->find($id);

        if (!$category) {
            throw new \RuntimeException('Category not found or does not belong to the user.');
        }

        // Verifica se já existe uma categoria com a mesma descrição para o usuário, excluindo a categoria atual
        $existingCategory = $this->categoriesModel->where('cat_user_id', $user->use_id)
            ->where('cat_description', $description)
            ->where('cat_id', '!=', $id)
            ->first();

        if ($existingCategory) {
            throw new \RuntimeException('A category with the same description already exists for this user.');
        }

        $category->cat_description = $description;
        $category->save();
        return $category;
    }
}
