<?php

namespace App\Modules\Transactions\UseCases;

use App\Modules\Transactions\Models\TransactionsModel;

class ListTransactionsUseCase
{
    public function __construct(
        private TransactionsModel $transactionsModel
    ) {
        // Initialize any dependencies if needed
    }

    public function execute(array $filters = []): array
    {
        return $this->transactionsModel->listTransactions($filters);
    }
}
