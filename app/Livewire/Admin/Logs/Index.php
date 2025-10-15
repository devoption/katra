<?php

namespace App\Livewire\Admin\Logs;

use App\Models\AiInteraction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('AI Interaction Logs - Katra')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public string $statusFilter = '';

    public string $providerFilter = '';

    public string $dateFilter = 'all'; // all, today, week, month

    public bool $onlyWithFeedback = false;

    public bool $onlyTrainingData = false;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingProviderFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFilter(): void
    {
        $this->resetPage();
    }

    public function updatingOnlyWithFeedback(): void
    {
        $this->resetPage();
    }

    public function updatingOnlyTrainingData(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->statusFilter = '';
        $this->providerFilter = '';
        $this->dateFilter = 'all';
        $this->onlyWithFeedback = false;
        $this->onlyTrainingData = false;
        $this->resetPage();
    }

    public function toggleTrainingInclusion(AiInteraction $interaction): void
    {
        $interaction->update([
            'include_in_training' => ! $interaction->include_in_training,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $interaction->include_in_training
                ? 'Interaction included in training data.'
                : 'Interaction excluded from training data.',
        ]);
    }

    public function render()
    {
        $interactions = AiInteraction::query()
            ->with(['user', 'agent', 'feedback'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('prompt', 'like', '%'.$this->search.'%')
                        ->orWhere('response', 'like', '%'.$this->search.'%')
                        ->orWhere('uuid', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->typeFilter, fn ($query) => $query->where('type', $this->typeFilter))
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->providerFilter, fn ($query) => $query->where('model_provider', $this->providerFilter))
            ->when($this->dateFilter !== 'all', function ($query) {
                match ($this->dateFilter) {
                    'today' => $query->whereDate('created_at', today()),
                    'week' => $query->where('created_at', '>=', now()->subWeek()),
                    'month' => $query->where('created_at', '>=', now()->subMonth()),
                    default => null,
                };
            })
            ->when($this->onlyWithFeedback, fn ($query) => $query->has('feedback'))
            ->when($this->onlyTrainingData, fn ($query) => $query->where('include_in_training', true))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(25);

        // Get unique providers and types for filters
        $providers = AiInteraction::query()
            ->distinct()
            ->whereNotNull('model_provider')
            ->pluck('model_provider');

        $types = AiInteraction::query()
            ->distinct()
            ->pluck('type');

        // Calculate summary stats
        $stats = [
            'total' => AiInteraction::count(),
            'today' => AiInteraction::whereDate('created_at', today())->count(),
            'with_feedback' => AiInteraction::has('feedback')->count(),
            'training_data' => AiInteraction::where('include_in_training', true)->count(),
            'total_cost' => AiInteraction::sum('cost_usd'),
            'total_tokens' => AiInteraction::sum('total_tokens'),
        ];

        return view('livewire.admin.logs.index', [
            'interactions' => $interactions,
            'providers' => $providers,
            'types' => $types,
            'stats' => $stats,
        ]);
    }
}
