<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkspaceAgent;
use App\Models\WorkspaceChat;
use App\Models\WorkspaceChatParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WorkspaceChatParticipant>
 */
class WorkspaceChatParticipantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_id' => WorkspaceChat::factory(),
            'user_id' => User::factory(),
            'workspace_agent_id' => null,
            'participant_type' => WorkspaceChatParticipant::TYPE_HUMAN,
            'participant_key' => 'human:'.fake()->unique()->safeEmail(),
            'display_name' => fake()->name(),
        ];
    }

    public function agent(): static
    {
        return $this->state(fn (): array => [
            'user_id' => null,
            'workspace_agent_id' => null,
            'participant_type' => WorkspaceChatParticipant::TYPE_AGENT,
            'participant_key' => 'agent:'.Str::slug(fake()->unique()->words(2, true)),
            'display_name' => str(fake()->unique()->words(2, true))->title()->append(' Agent')->value(),
        ]);
    }

    public function forAgent(WorkspaceAgent $agent): static
    {
        return $this->state(fn (): array => [
            'user_id' => null,
            'workspace_agent_id' => $agent->getKey(),
            'participant_type' => WorkspaceChatParticipant::TYPE_AGENT,
            'participant_key' => 'agent:'.$agent->agent_key,
            'display_name' => $agent->name,
        ]);
    }
}
