<?php

namespace App\Events;

use App\Models\WorkflowExecution;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class WorkflowExecutionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public WorkflowExecution $workflowExecution
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('workflow-executions.'.$this->workflowExecution->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->workflowExecution->id,
            'status' => $this->workflowExecution->status,
            'started_at' => $this->workflowExecution->started_at?->toDateTimeString(),
            'completed_at' => $this->workflowExecution->completed_at?->toDateTimeString(),
            'duration' => $this->workflowExecution->started_at && $this->workflowExecution->completed_at
                ? $this->workflowExecution->started_at->diffInSeconds($this->workflowExecution->completed_at)
                : null,
        ];
    }
}
