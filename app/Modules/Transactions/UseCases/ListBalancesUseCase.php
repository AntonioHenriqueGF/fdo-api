<?php

namespace App\Modules\Transactions\UseCases;

use App\Modules\Transactions\Models\DailyBalancesModel;
use App\Modules\Users\UseCases\MeUseCase;

class ListBalancesUseCase
{
    public function __construct(
        private DailyBalancesModel $dailyBalancesModel,
        private MeUseCase $meUseCase
    ) {
        // Initialize any dependencies if needed
    }

    public function execute(array $filters = []): array
    {
        // Get the authenticated user's ID
        $userId = $this->meUseCase->execute()->use_id;

        return $this->dailyBalancesModel->listBalances($userId, $filters);
    }
}
