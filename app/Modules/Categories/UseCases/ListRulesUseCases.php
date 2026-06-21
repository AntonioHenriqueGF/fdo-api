<?php

namespace App\Modules\Categories\UseCases;

use App\Modules\Categories\Models\RulesModel;
use Illuminate\Support\Facades\Auth;

class ListRulesUseCases
{
    public function __construct(
        private RulesModel $rulesModel
    ) {
        // Constructor can be used for dependency injection if needed
    }
    public function execute(int $categoryId): array
    {
        $userId = Auth::id();

        return $this->rulesModel->where('rul_user_id', $userId)
            ->where('rul_category_id', $categoryId)
            ->get()->toArray();
    }
}
