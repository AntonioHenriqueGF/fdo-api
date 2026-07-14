<?php

namespace App\Modules\Transactions\UseCases;

use App\Modules\Transactions\Models\TransactionsModel;
use App\Modules\Users\UseCases\MeUseCase;

class CreateTransactionUseCase
{
    public function __construct(
        private TransactionsModel $transactionsModel,
        private MeUseCase $meUseCase
    ) {
        //
    }

    public function execute(array $data): array
    {
        $userId = $this->meUseCase->execute()->use_id;

        return $this->transactionsModel->createTransaction($userId, $data);
    }
}
