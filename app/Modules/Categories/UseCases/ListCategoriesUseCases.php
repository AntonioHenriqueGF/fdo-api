<?php

namespace App\Modules\Categories\UseCases;

use App\Modules\Categories\Models\CategoriesModel;
use Illuminate\Support\Facades\Auth;

class ListCategoriesUseCases
{
    public function __construct(
        private CategoriesModel $categoriesModel
    ) {
        //
    }
    public function execute(): array
    {
        $user = Auth::user();
        return $this->categoriesModel->where('cat_user_id', $user->use_id)->get()->toArray();
    }
}
