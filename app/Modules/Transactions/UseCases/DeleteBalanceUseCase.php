<?php

namespace App\Modules\Transactions\UseCases;

use App\Modules\Transactions\Models\DailyBalancesModel;
use App\Modules\Users\UseCases\MeUseCase;

class DeleteBalanceUseCase
{
    public function __construct(
        private DailyBalancesModel $dailyBalancesModel,
        private MeUseCase $meUseCase
    ) {
        // Initialize any dependencies if needed
    }

    public function execute(int $balanceId): void
    {
        $userId = $this->meUseCase->execute()->use_id;

        $this->dailyBalancesModel->deleteBalance($balanceId, $userId);
    }
}
