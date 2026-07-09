<?php

namespace App\Http\Controllers;

use App\Models\JobRequestsModel;
use Illuminate\Http\Request;

class JobRequestsController extends Controller
{
    public function show(Request $request, int $jobRequestId)
    {
        $jobRequest = JobRequestsModel::query()
            ->where('id', $jobRequestId)
            ->where('user_id', $request->user()->use_id)
            ->first();

        if (! $jobRequest) {
            return $this->errorResponse('Job request not found', null, 404);
        }

        return $this->successResponse('Job request retrieved successfully', [
            'id' => $jobRequest->id,
            'type' => $jobRequest->type,
            'status' => $jobRequest->status,
            'started_at' => $jobRequest->started_at,
            'completed_at' => $jobRequest->completed_at,
            'error' => $jobRequest->error_message,
        ]);
    }
}
