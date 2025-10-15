<?php

namespace App\Livewire\Agents;

use App\Models\Agent;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Agents - Katra')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterProvider = '';

    public string $filterActive = '';

    public function deleteAgent(int $id): void
    {
        $agent = Agent::findOrFail($id);

        if ($agent->is_default) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Cannot delete the default Katra agent.',
            ]);

            return;
        }

        $agent->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Agent deleted successfully.',
        ]);
    }

    public function toggleActive(int $id): void
    {
        $agent = Agent::findOrFail($id);
        $agent->is_active = ! $agent->is_active;
        $agent->save();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Agent status updated.',
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterProvider(): void
    {
        $this->resetPage();
    }

    public function updatedFilterActive(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Agent::query()
            ->with('creator')
            ->withCount('tools');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('role', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterProvider) {
            $query->where('model_provider', $this->filterProvider);
        }

        if ($this->filterActive !== '') {
            $query->where('is_active', $this->filterActive === '1');
        }

        $agents = $query->latest()->paginate(15);

        $providers = Agent::distinct()->pluck('model_provider');

        return view('livewire.agents.index', [
            'agents' => $agents,
            'providers' => $providers,
        ]);
    }
}
