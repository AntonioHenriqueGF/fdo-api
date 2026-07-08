<?php

namespace App\Models;

use App\Broadcast\JobRequestUpdated;
use Illuminate\Database\Eloquent\Model;

class JobRequestsModel extends Model
{
    protected $table = 'job_requests';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'additional_info',
        'error_message',
        'started_at',
        'completed_at',
    ];

    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'use_id');
    }

    public function createJobRequest(int $userId, string $type): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'status' => self::STATUS_PENDING,
        ]);
    }

    public function startJobRequest(int $jobRequestId): bool
    {
        $jobRequest = self::find($jobRequestId);

        if (!$jobRequest) {
            return false;
        }

        $jobRequest->status = self::STATUS_IN_PROGRESS;
        $jobRequest->started_at = now();

        return $jobRequest->save();
    }

    public function failJobRequest(int $jobRequestId, string $errorMessage): bool
    {
        $jobRequest = self::find($jobRequestId);

        if (!$jobRequest) {
            return false;
        }

        $jobRequest->status = self::STATUS_FAILED;
        $jobRequest->error_message = $errorMessage;
        $jobRequest->completed_at = now();

        return $jobRequest->save();
    }

    public function completeJobRequest(int $jobRequestId): bool
    {
        $jobRequest = self::find($jobRequestId);

        if (!$jobRequest) {
            return false;
        }

        $jobRequest->status = self::STATUS_COMPLETED;
        $jobRequest->completed_at = now();

        $newJobRequest = $jobRequest->save();

        broadcast(new JobRequestUpdated($newJobRequest));

        return $newJobRequest;
    }
}
