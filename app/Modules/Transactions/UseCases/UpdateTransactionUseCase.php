<?php

namespace App\Modules\Transactions\UseCases;

use App\Modules\Transactions\Models\TransactionsModel;
use App\Modules\Users\UseCases\MeUseCase;

class UpdateTransactionUseCase
{
    public function __construct(
        private TransactionsModel $transactionsModel,
        private MeUseCase $meUseCase
    ) {
        // Initialize any dependencies if needed
    }

    public function execute(int $transactionId, array $data): array
    {
        $userId = $this->meUseCase->execute()->use_id;

        return $this->transactionsModel->updateTransaction($transactionId, $data, $userId);
    }
}
