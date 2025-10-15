<?php

namespace App\Livewire\Contexts;

use App\Models\Context;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Contexts - Katra')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterType = '';

    public function deleteContext(int $id): void
    {
        $context = Context::withCount(['agents', 'workflows', 'workflowExecutions'])->findOrFail($id);

        $totalUsage = $context->agents_count + $context->workflows_count + $context->workflow_executions_count;

        if ($totalUsage > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => "Cannot delete context. It is currently used by {$totalUsage} item(s).",
            ]);

            return;
        }

        $context->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Context deleted successfully.',
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Context::query()
            ->with('creator')
            ->withCount(['agents', 'workflows', 'workflowExecutions']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        $contexts = $query->latest()->paginate(15);

        return view('livewire.contexts.index', [
            'contexts' => $contexts,
        ]);
    }
}
