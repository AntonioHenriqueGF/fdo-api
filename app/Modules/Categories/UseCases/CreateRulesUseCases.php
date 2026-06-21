<?php

namespace App\Modules\Categories\UseCases;

use App\Modules\Categories\Models\RulesModel;
use Illuminate\Support\Facades\Auth;

class CreateRulesUseCases
{
    public function __construct(
        private RulesModel $rulesModel
    ) {
        // Constructor can be used for dependency injection if needed
    }

    public function execute(int $categoryId, array $data)
    {
        $userId = Auth::id();

        // Primeiro, verifica se não existe uma regra com a mesma descrição para o usuário
        $existingRule = $this->rulesModel->where('rul_user_id', $userId)
            ->where('rul_category_id', $categoryId)
            ->where('rul_pattern', $data['pattern'])
            ->first();

        if ($existingRule) {
            throw new \Exception('A rule with the same description already exists for this user.');
        }

        $rule = $this->rulesModel->create([
            'rul_user_id' => $userId,
            'rul_category_id' => $categoryId,
            'rul_pattern' => $data['pattern'],
            'rul_priority' => $data['priority'],
        ]);

        return $rule->toArray();
    }
}
