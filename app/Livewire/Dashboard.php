<?php

namespace App\Livewire;

use App\Models\Agent;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $stats = [
            'active_workflows' => Workflow::where('is_active', true)->count(),
            'total_agents' => Agent::where('is_active', true)->count(),
            'executions_today' => WorkflowExecution::whereDate('created_at', today())->count(),
            'success_rate' => $this->calculateSuccessRate(),
        ];

        $recent_executions = WorkflowExecution::with('workflow')
            ->latest()
            ->limit(5)
            ->get();

        return view('livewire.dashboard', [
            'stats' => $stats,
            'recent_executions' => $recent_executions,
        ])->layout('layouts.app');
    }

    protected function calculateSuccessRate(): ?string
    {
        $total = WorkflowExecution::count();

        if ($total === 0) {
            return null;
        }

        $successful = WorkflowExecution::where('status', 'completed')->count();

        return number_format(($successful / $total) * 100, 1).'%';
    }
}
