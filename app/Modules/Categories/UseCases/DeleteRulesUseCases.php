<?php

namespace App\Modules\Categories\UseCases;

use App\Modules\Categories\Models\RulesModel;
use Illuminate\Support\Facades\Auth;

class DeleteRulesUseCases
{
    public function __construct(
        private RulesModel $rulesModel
    ) {
        // Constructor can be used for dependency injection if needed
    }

    public function execute(int $id): array
    {
        $userId = Auth::id();

        $rule = $this->rulesModel->where('rul_user_id', $userId)
            ->where('rul_id', $id)
            ->first();

        if (!$rule) {
            throw new \RuntimeException('Rule not found or does not belong to the user.');
        }

        $rule->delete();

        return ['message' => 'Rule deleted successfully'];
    }
}
