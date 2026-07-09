<?php

namespace App\Jobs\FDOJobClass;

use App\Broadcast\JobRequestUpdated;
use App\Models\JobRequestsModel;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

abstract class FDOJobClass implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected JobRequestsModel $jobRequest;

    protected string $jobType;

    public function __construct(
        protected User $user
    ) {}

    abstract public function process(): void;

    public function handle(): void
    {
        $this->jobRequest = (new JobRequestsModel)->createJobRequest($this->user->use_id, $this->jobType);

        try {
            $this->jobRequest->startJobRequest($this->jobRequest->id);

            $this->process();

            $this->jobRequest->completeJobRequest($this->jobRequest->id);
            $this->dispatchJobRequestUpdated();
        } catch (Throwable $e) {
            $this->jobRequest->failJobRequest($this->jobRequest->id, $e->getMessage());
            $this->dispatchJobRequestUpdated();

            throw $e;
        }
    }

    protected function dispatchJobRequestUpdated(): void
    {
        $this->jobRequest->refresh();

        event(new JobRequestUpdated($this->jobRequest));
    }
}
