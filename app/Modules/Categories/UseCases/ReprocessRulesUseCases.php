<?php

namespace App\Modules\Categories\UseCases;

use App\Jobs\ReprocessRulesJob;
use Illuminate\Support\Facades\Auth;

class ReprocessRulesUseCases
{
    public function execute()
    {
        $user = Auth::user();
        // Dispatch the job to reprocess rules for all users
        ReprocessRulesJob::dispatch($user);
    }
}
