<?php

namespace App\Support\Chats;

use App\Models\Workspace;
use App\Models\WorkspaceAgent;
use Illuminate\Database\Eloquent\Collection;

class WorkspaceAgentManager
{
    /**
     * @var array<int, array{agent_key: string, name: string, agent_class: string, summary: string}>
     */
    private const DEFAULT_AGENTS = [
        [
            'agent_key' => WorkspaceAgent::KEY_WORKSPACE_GUIDE,
            'name' => 'Workspace Guide',
            'agent_class' => WorkspaceAgent::CLASS_WORKSPACE_GUIDE,
            'summary' => 'Helps shape durable, graph-native collaboration inside this workspace.',
        ],
    ];

    /**
     * @return Collection<int, WorkspaceAgent>
     */
    public function agentsFor(Workspace $workspace): Collection
    {
        return $this->ensureDefaults($workspace);
    }

    /**
     * @return Collection<int, WorkspaceAgent>
     */
    public function ensureDefaults(Workspace $workspace): Collection
    {
        $existingAgents = $workspace->agents()
            ->get()
            ->keyBy('agent_key');

        foreach (self::DEFAULT_AGENTS as $definition) {
            $workspaceAgent = $existingAgents->get($definition['agent_key']);

            if ($workspaceAgent instanceof WorkspaceAgent) {
                $workspaceAgent->forceFill([
                    'name' => $definition['name'],
                    'agent_class' => $definition['agent_class'],
                    'summary' => $definition['summary'],
                ])->save();

                continue;
            }

            $workspace->agents()->create([
                'agent_key' => $definition['agent_key'],
                'name' => $definition['name'],
                'agent_class' => $definition['agent_class'],
                'summary' => $definition['summary'],
            ]);
        }

        return $workspace->agents()
            ->orderBy('name')
            ->get()
            ->values();
    }

    public function agentForWorkspace(Workspace $workspace, ?int $agentId): ?WorkspaceAgent
    {
        if ($agentId === null) {
            return null;
        }

        return $workspace->agents()
            ->whereKey($agentId)
            ->first();
    }
}
