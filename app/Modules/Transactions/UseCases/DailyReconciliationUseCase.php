<?php

namespace App\Modules\Transactions\UseCases;

use App\Modules\Transactions\Models\TransactionsModel;
use App\Modules\Users\UseCases\MeUseCase;

class DailyReconciliationUseCase
{
    public function __construct(
        private TransactionsModel $transactionsModel,
        private MeUseCase $meUseCase
    ) {}

    public function execute(?string $dateStart = null, ?string $dateEnd = null)
    {
        $userId = $this->meUseCase->execute()->use_id;
        return $this->transactionsModel->getTransactionsWithBalance($userId, $dateStart, $dateEnd);
    }
}
