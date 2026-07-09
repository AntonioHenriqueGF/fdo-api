<?php

namespace App\Jobs;

use App\Jobs\FDOJobClass\FDOJobClass;
use App\Models\User;
use App\Modules\Imports\Models\ImportsModel;
use App\Modules\Imports\Services\ImportFileService;
use App\Modules\Transactions\Models\DailyBalancesModel;
use App\Modules\Transactions\Models\TransactionsModel;

class FileImportJob extends FDOJobClass
{
    protected string $jobType = 'file_import';

    public function __construct(
        protected User $user,
        private array $data
    ) {}

    public function process(): void
    {
        $importFileService = new ImportFileService(
            new ImportsModel,
            new DailyBalancesModel,
            new TransactionsModel
        );

        $importFileService->execute($this->user->use_id, $this->data);
    }
}
