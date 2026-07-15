<?php

namespace App\Modules\Transactions\UseCases;

use App\Modules\Transactions\Models\DailyBalancesModel;
use App\Modules\Users\UseCases\MeUseCase;

class UpdateBalanceUseCase
{
    public function __construct(
        private DailyBalancesModel $dailyBalancesModel,
        private MeUseCase $meUseCase
    ) {
        // Initialize any dependencies if needed
    }

    public function execute(int $balanceId, array $data): array
    {
        $userId = $this->meUseCase->execute()->use_id;

        return $this->dailyBalancesModel->updateBalance($balanceId, $data, $userId);
    }
}
