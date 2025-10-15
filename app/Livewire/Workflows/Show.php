<?php

namespace App\Livewire\Workflows;

use App\Jobs\ProcessWorkflowExecution;
use App\Models\Workflow;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Workflow Details - Katra')]
class Show extends Component
{
    use WithPagination;

    public $workflowId;

    public function mount(Workflow $workflow): void
    {
        $this->workflowId = $workflow->id;
    }

    public function getWorkflowProperty(): Workflow
    {
        return Workflow::findOrFail($this->workflowId);
    }

    #[On('workflow-execution-updated')]
    public function refreshExecution(): void
    {
        // Livewire will automatically re-render when this event is received
    }

    public function triggerWorkflow(): void
    {
        $workflow = Workflow::findOrFail($this->workflowId);

        if (! $workflow->is_active) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Cannot trigger inactive workflow.',
            ]);

            return;
        }

        $execution = $workflow->executions()->create([
            'workflow_version' => $workflow->version,
            'status' => 'pending',
            'triggered_by' => 'user',
            'triggered_by_id' => auth()->id(),
        ]);

        // Dispatch the job to process the execution
        ProcessWorkflowExecution::dispatch($execution);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Workflow triggered successfully!',
        ]);

        // Notify frontend to subscribe to this execution's updates
        $this->dispatch('workflowTriggered', ['executionId' => (string) $execution->id]);

        // Component will auto-refresh and show the new execution
    }

    public function render()
    {
        $workflow = $this->workflow;

        $executions = $workflow->executions()
            ->with('steps')
            ->latest()
            ->paginate(10);

        return view('livewire.workflows.show', [
            'workflow' => $workflow,
            'executions' => $executions,
        ]);
    }
}
