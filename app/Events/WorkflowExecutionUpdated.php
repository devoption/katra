<?php

namespace App\Events;

use App\Models\WorkflowExecution;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkflowExecutionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public WorkflowExecution $execution
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('workflow.'.$this->execution->workflow_id);
    }

    public function broadcastWith(): array
    {
        return [
            'execution_id' => $this->execution->id,
            'status' => $this->execution->status,
            'started_at' => $this->execution->started_at?->toIso8601String(),
            'completed_at' => $this->execution->completed_at?->toIso8601String(),
        ];
    }
}
