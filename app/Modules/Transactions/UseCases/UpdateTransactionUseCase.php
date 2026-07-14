<?php

namespace App\Modules\Transactions\UseCases;

use App\Modules\Transactions\Models\TransactionsModel;

class UpdateTransactionUseCase
{
    public function __construct(
        private TransactionsModel $transactionsModel
    ) {
        // Initialize any dependencies if needed
    }

    public function execute(int $transactionId, array $data): array
    {
        return $this->transactionsModel->updateTransaction($transactionId, $data);
    }
}
