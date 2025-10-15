<?php

namespace Database\Factories;

use App\Models\Context;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent>
 */
class AgentFactory extends Factory
{
    public function definition(): array
    {
        $providers = ['openai', 'anthropic', 'google', 'ollama'];
        $provider = fake()->randomElement($providers);

        $models = [
            'openai' => ['gpt-4', 'gpt-4-turbo', 'gpt-3.5-turbo'],
            'anthropic' => ['claude-3-opus', 'claude-3-sonnet', 'claude-3-haiku'],
            'google' => ['gemini-pro', 'gemini-ultra'],
            'ollama' => ['llama2', 'mistral', 'codellama'],
        ];

        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake()->words(2, true).' Agent',
            'role' => fake()->randomElement(['Code Reviewer', 'Content Writer', 'Data Analyst', 'Customer Support', 'Researcher']),
            'description' => fake()->sentence(),
            'model_provider' => $provider,
            'model_name' => fake()->randomElement($models[$provider]),
            'system_prompt' => fake()->paragraph(),
            'creativity_level' => fake()->randomFloat(2, 0, 1),
            'is_default' => false,
            'is_active' => true,
            'context_id' => null,
            'created_by' => User::factory(),
        ];
    }

    public function withContext(): static
    {
        return $this->state(fn (array $attributes) => [
            'context_id' => Context::factory()->agent(),
        ]);
    }

    public function katra(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Katra',
            'role' => 'Executive Assistant',
            'description' => 'Your intelligent workflow assistant',
            'model_provider' => 'anthropic',
            'model_name' => 'claude-sonnet-4.5',
            'system_prompt' => 'You are Katra, an executive assistant AI designed to help users manage and execute workflows efficiently. You can trigger workflows, suggest new automations, and help users accomplish their goals.',
            'creativity_level' => 0.70,
            'is_default' => true,
            'is_active' => true,
        ]);
    }
}
