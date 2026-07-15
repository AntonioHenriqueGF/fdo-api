<?php

namespace App\Modules\Transactions\UseCases;

use App\Modules\Transactions\Models\TransactionsModel;
use App\Modules\Users\UseCases\MeUseCase;

class ListTransactionsUseCase
{
    public function __construct(
        private TransactionsModel $transactionsModel,
        private MeUseCase $meUseCase
    ) {
        // Initialize any dependencies if needed
    }

    public function execute(array $filters = []): array
    {
        // Get the authenticated user's ID
        $userId = $this->meUseCase->execute()->use_id;

        return $this->transactionsModel->listTransactions($filters, $userId);
    }
}
