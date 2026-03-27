<?php

namespace Database\Factories;

use App\Models\Workspace;
use App\Models\WorkspaceChat;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WorkspaceChat>
 */
class WorkspaceChatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'workspace_id' => Workspace::factory(),
            'name' => str($name)->title()->value(),
            'slug' => Str::slug($name),
            'kind' => WorkspaceChat::KIND_GROUP,
            'visibility' => WorkspaceChat::VISIBILITY_PRIVATE,
            'summary' => fake()->sentence(),
        ];
    }

    public function direct(): static
    {
        return $this->state(fn (): array => [
            'kind' => WorkspaceChat::KIND_DIRECT,
        ]);
    }
}
