<?php

namespace App\Broadcast;

use App\Models\JobRequestsModel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobRequestUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public JobRequestsModel $jobRequest
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("users.{$this->jobRequest->user_id}")
        ];
    }

    public function broadcastAs(): string
    {
        return 'job-request.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->jobRequest->id,
            'type' => $this->jobRequest->type,
            'status' => $this->jobRequest->status,
            'finished_at' => $this->jobRequest->finished_at,
        ];
    }
}
