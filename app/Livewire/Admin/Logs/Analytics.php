<?php

namespace App\Livewire\Admin\Logs;

use App\Models\AiInteraction;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('AI Analytics - Katra')]
class Analytics extends Component
{
    public string $period = 'week'; // week, month, all

    public function export(): void
    {
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Export functionality coming soon!',
        ]);
    }

    public function render()
    {
        $dateConstraint = match ($this->period) {
            'week' => ['created_at', '>=', now()->subWeek()],
            'month' => ['created_at', '>=', now()->subMonth()],
            default => ['created_at', '>=', now()->subYears(10)],
        };

        // Overall Stats
        $stats = [
            'total_interactions' => AiInteraction::where($dateConstraint)->count(),
            'successful' => AiInteraction::where($dateConstraint)->where('status', 'success')->count(),
            'failed' => AiInteraction::where($dateConstraint)->where('status', 'error')->count(),
            'total_tokens' => AiInteraction::where($dateConstraint)->sum('total_tokens'),
            'total_cost' => AiInteraction::where($dateConstraint)->sum('cost_usd'),
            'avg_latency' => AiInteraction::where($dateConstraint)->avg('latency_ms'),
            'with_feedback' => AiInteraction::where($dateConstraint)->has('feedback')->count(),
            'training_data' => AiInteraction::where($dateConstraint)->where('include_in_training', true)->count(),
        ];

        // By Provider
        $byProvider = AiInteraction::where($dateConstraint)
            ->select('model_provider', DB::raw('count(*) as count'), DB::raw('sum(cost_usd) as total_cost'), DB::raw('sum(total_tokens) as total_tokens'))
            ->groupBy('model_provider')
            ->get();

        // By Type
        $byType = AiInteraction::where($dateConstraint)
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();

        // By Status
        $byStatus = AiInteraction::where($dateConstraint)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // Daily Activity (last 7/30 days)
        $days = match ($this->period) {
            'week' => 7,
            'month' => 30,
            default => 30,
        };

        $dailyActivity = AiInteraction::where('created_at', '>=', now()->subDays($days))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top Users
        $topUsers = AiInteraction::where($dateConstraint)
            ->select('user_id', DB::raw('count(*) as count'))
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->limit(10)
            ->with('user')
            ->get();

        return view('livewire.admin.logs.analytics', [
            'stats' => $stats,
            'byProvider' => $byProvider,
            'byType' => $byType,
            'byStatus' => $byStatus,
            'dailyActivity' => $dailyActivity,
            'topUsers' => $topUsers,
        ]);
    }
}
