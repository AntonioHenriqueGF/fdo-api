<?php

namespace App\Modules\Transactions\UseCases;

use App\Modules\Transactions\Models\DailyBalancesModel;
use App\Modules\Users\UseCases\MeUseCase;

class CreateBalanceUseCase
{
    public function __construct(
        private DailyBalancesModel $dailyBalancesModel,
        private MeUseCase $meUseCase
    ) {
        //
    }

    public function execute(array $data): array
    {
        $userId = $this->meUseCase->execute()->use_id;

        return $this->dailyBalancesModel->createBalance($userId, $data);
    }
}
