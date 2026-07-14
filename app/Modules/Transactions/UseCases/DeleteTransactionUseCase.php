<?php

namespace App\Modules\Transactions\UseCases;

use App\Modules\Transactions\Models\TransactionsModel;

class DeleteTransactionUseCase
{
    public function __construct(
        private TransactionsModel $transactionsModel
    ) {
        // Initialize any dependencies if needed
    }

    public function execute(int $transactionId): void
    {
        $this->transactionsModel->deleteTransaction($transactionId);
    }
}
