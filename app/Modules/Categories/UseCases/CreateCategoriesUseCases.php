<?php

namespace App\Modules\Categories\UseCases;

use App\Modules\Categories\Models\CategoriesModel;
use Illuminate\Support\Facades\Auth;

class CreateCategoriesUseCases
{
    public function __construct(
        private CategoriesModel $categoriesModel
    ) {
        //
    }
    public function execute(string $description): CategoriesModel
    {
        $user = Auth::user();

        // Primeiro, verifica se não existe uma categoria com a mesma descrição para o usuário
        $existingCategory = $this->categoriesModel->where('cat_user_id', $user->use_id)
            ->where('cat_description', $description)
            ->first();

        if ($existingCategory) {
            throw new \RuntimeException('Category already exists for this user.');
        }

        $this->categoriesModel->cat_user_id = $user->use_id;
        $this->categoriesModel->cat_description = $description;
        $this->categoriesModel->save();

        return $this->categoriesModel;
    }
}
