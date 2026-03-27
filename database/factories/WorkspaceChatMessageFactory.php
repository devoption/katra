<?php

namespace Database\Factories;

use App\Models\WorkspaceChat;
use App\Models\WorkspaceChatMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkspaceChatMessage>
 */
class WorkspaceChatMessageFactory extends Factory
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
            'sender_type' => WorkspaceChatMessage::SENDER_HUMAN,
            'sender_key' => 'human:'.fake()->uuid(),
            'sender_name' => fake()->name(),
            'body' => fake()->paragraph(),
        ];
    }
}
