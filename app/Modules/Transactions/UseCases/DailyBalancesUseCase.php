<?php

namespace App\Modules\Transactions\UseCases;

use App\Modules\Transactions\Models\DailyBalancesModel;
use App\Modules\Users\UseCases\MeUseCase;

class DailyBalancesUseCase
{
    public function __construct(
        private DailyBalancesModel $dailyBalancesModel,
        private MeUseCase $meUseCase
    ) {}
    public function execute(): array
    {
        // Implement logic to calculate and return the user's daily balances
        $userId = $this->meUseCase->execute()->use_id;
        return $this->dailyBalancesModel->listDailyBalances($userId);
    }
}
