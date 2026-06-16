<?php

namespace App\Modules\Transactions\UseCases;

use App\Modules\Transactions\Models\TransactionsModel;
use App\Modules\Users\UseCases\MeUseCase;

class DailyTransactionsUseCase
{
    public function __construct(
        private TransactionsModel $transactionsModel,
        private MeUseCase $meUseCase
    ) {}
    public function execute(): array
    {
        $userId = $this->meUseCase->execute()->use_id;
        return $this->transactionsModel->getDailyTransactions($userId);
    }
}
