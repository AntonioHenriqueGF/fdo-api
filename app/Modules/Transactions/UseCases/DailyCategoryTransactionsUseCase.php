<?php

namespace App\Modules\Transactions\UseCases;

use App\Modules\Transactions\Models\TransactionsModel;
use App\Modules\Users\UseCases\MeUseCase;

class DailyCategoryTransactionsUseCase
{
    public function __construct(
        private TransactionsModel $transactionsModel,
        private MeUseCase $meUseCase
    ) {}

    public function execute(array $filters = []): array
    {
        $userId = $this->meUseCase->execute()->use_id;

        return $this->transactionsModel->getDailyCategoryTransactions(
            $userId,
            $filters['date_start'] ?? null,
            $filters['date_end'] ?? null,
            $filters['category_id'] ?? []
        );
    }
}
