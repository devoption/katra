<?php

namespace App\Livewire\Workflows;

use App\Models\Workflow;
use App\Models\WorkflowExecution;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Workflows - Katra')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterMode = '';

    public string $filterActive = '';

    public function deleteWorkflow(int $id): void
    {
        $workflow = Workflow::withCount('executions')->findOrFail($id);

        if ($workflow->executions_count > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => "Cannot delete workflow. It has {$workflow->executions_count} execution(s) in history.",
            ]);

            return;
        }

        $workflow->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Workflow deleted successfully.',
        ]);
    }

    public function toggleActive(int $id): void
    {
        $workflow = Workflow::findOrFail($id);
        $workflow->is_active = ! $workflow->is_active;
        $workflow->save();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Workflow status updated.',
        ]);
    }

    public function triggerWorkflow(int $id): void
    {
        $workflow = Workflow::findOrFail($id);

        if (! $workflow->is_active) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Cannot trigger inactive workflow.',
            ]);

            return;
        }

        // Create a new execution
        $execution = WorkflowExecution::create([
            'workflow_id' => $workflow->id,
            'workflow_version' => $workflow->version,
            'status' => 'pending',
            'triggered_by' => 'user',
            'triggered_by_id' => auth()->id(),
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Workflow triggered successfully!',
        ]);

        $this->redirect(route('workflows.show', $workflow), navigate: true);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterMode(): void
    {
        $this->resetPage();
    }

    public function updatedFilterActive(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Workflow::query()
            ->with('creator')
            ->withCount(['executions', 'triggers']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterMode) {
            $query->where('execution_mode', $this->filterMode);
        }

        if ($this->filterActive !== '') {
            $query->where('is_active', $this->filterActive === '1');
        }

        $workflows = $query->latest()->paginate(15);

        return view('livewire.workflows.index', [
            'workflows' => $workflows,
        ]);
    }
}
