<?php

namespace App\Livewire\Credentials;

use App\Models\Credential;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Credentials - Katra')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterType = '';

    public string $filterProvider = '';

    public ?int $viewingCredentialId = null;

    public function deleteCredential(int $id): void
    {
        $credential = Credential::withCount('agents')->findOrFail($id);

        if ($credential->agents_count > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => "Cannot delete credential. It is currently used by {$credential->agents_count} agent(s).",
            ]);

            return;
        }

        $credential->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Credential deleted successfully.',
        ]);
    }

    public function viewCredential(int $id): void
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $this->viewingCredentialId = $id;
    }

    public function hideCredential(): void
    {
        $this->viewingCredentialId = null;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterProvider(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Credential::query()
            ->with('creator')
            ->withCount('agents');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%")
                    ->orWhere('provider', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterProvider) {
            $query->where('provider', $this->filterProvider);
        }

        $credentials = $query->latest()->paginate(15);

        $types = Credential::distinct()->pluck('type')->filter();
        $providers = Credential::distinct()->pluck('provider')->filter();

        return view('livewire.credentials.index', [
            'credentials' => $credentials,
            'types' => $types,
            'providers' => $providers,
        ]);
    }
}
