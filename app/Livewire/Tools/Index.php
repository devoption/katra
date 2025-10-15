<?php

namespace App\Livewire\Tools;

use App\Models\Tool;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Tools - Katra')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterType = '';

    public string $filterCategory = '';

    public string $filterActive = '';

    public function deleteTool(int $id): void
    {
        $tool = Tool::withCount('agents')->findOrFail($id);

        if ($tool->type === 'builtin') {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Cannot delete built-in tools.',
            ]);

            return;
        }

        if ($tool->agents_count > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => "Cannot delete tool. It is currently used by {$tool->agents_count} agent(s).",
            ]);

            return;
        }

        $tool->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Tool deleted successfully.',
        ]);
    }

    public function toggleActive(int $id): void
    {
        $tool = Tool::findOrFail($id);
        $tool->is_active = ! $tool->is_active;
        $tool->save();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Tool status updated.',
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

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatedFilterActive(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Tool::query()
            ->with('creator')
            ->withCount('agents');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%")
                    ->orWhere('category', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }

        if ($this->filterActive !== '') {
            $query->where('is_active', $this->filterActive === '1');
        }

        $tools = $query->latest()->paginate(15);

        $categories = Tool::distinct()->pluck('category')->filter();

        return view('livewire.tools.index', [
            'tools' => $tools,
            'categories' => $categories,
        ]);
    }
}
