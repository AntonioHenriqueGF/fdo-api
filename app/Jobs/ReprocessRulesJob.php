<?php

namespace App\Jobs;

use App\Jobs\FDOJobClass\FDOJobClass;
use App\Models\JobRequestsModel;
use App\Models\User;
use App\Modules\Categories\Services\ReprocessRulesService;
use Illuminate\Support\Facades\Log;

class ReprocessRulesJob extends FDOJobClass
{
    private ReprocessRulesService $reprocessRulesService;

    protected string $jobType = 'reprocess_rules';

    public function __construct(
        protected User $user
    ) {
        $this->reprocessRulesService = new ReprocessRulesService();
    }

    public function process(): void
    {
        $this->reprocessRulesService->reprocessRulesForUser($this->user->use_id);
    }
}
