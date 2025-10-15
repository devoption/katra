<?php

namespace App\Livewire\Workflows;

use App\Models\Workflow;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Workflow Details - Katra')]
class Show extends Component
{
    use WithPagination;

    public Workflow $workflow;

    public function mount(Workflow $workflow): void
    {
        $this->workflow = $workflow;
    }

    public function triggerWorkflow(): void
    {
        if (! $this->workflow->is_active) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Cannot trigger inactive workflow.',
            ]);

            return;
        }

        $this->workflow->executions()->create([
            'workflow_version' => $this->workflow->version,
            'status' => 'pending',
            'triggered_by' => 'user',
            'triggered_by_id' => auth()->id(),
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Workflow triggered successfully!',
        ]);

        // Refresh the page to show new execution
        $this->redirect(route('workflows.show', $this->workflow), navigate: true);
    }

    public function render()
    {
        $executions = $this->workflow->executions()
            ->with('steps')
            ->latest()
            ->paginate(10);

        return view('livewire.workflows.show', [
            'executions' => $executions,
        ]);
    }
}
