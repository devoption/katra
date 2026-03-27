<?php

namespace Database\Factories;

use App\Ai\Agents\WorkspaceGuide;
use App\Models\Workspace;
use App\Models\WorkspaceAgent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WorkspaceAgent>
 */
class WorkspaceAgentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = str(fake()->unique()->words(2, true))->title()->append(' Agent')->value();

        return [
            'workspace_id' => Workspace::factory(),
            'agent_key' => Str::slug($name),
            'name' => $name,
            'agent_class' => WorkspaceGuide::class,
            'summary' => fake()->sentence(),
        ];
    }

    public function workspaceGuide(): static
    {
        return $this->state(fn (): array => [
            'agent_key' => WorkspaceAgent::KEY_WORKSPACE_GUIDE,
            'name' => 'Workspace Guide',
            'agent_class' => WorkspaceAgent::CLASS_WORKSPACE_GUIDE,
            'summary' => 'Helps shape durable, graph-native collaboration inside this workspace.',
        ]);
    }
}
