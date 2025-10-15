<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiInteraction>
 */
class AiInteractionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['chat', 'workflow_execution', 'agent_step', 'tool_execution'];
        $providers = ['openai', 'anthropic', 'ollama', 'google'];
        $models = [
            'openai' => ['gpt-4', 'gpt-4-turbo', 'gpt-3.5-turbo'],
            'anthropic' => ['claude-3-sonnet', 'claude-3-opus', 'claude-3-haiku'],
            'ollama' => ['llama2', 'mistral', 'codellama'],
            'google' => ['gemini-pro', 'gemini-ultra'],
        ];

        $provider = fake()->randomElement($providers);
        $promptTokens = fake()->numberBetween(50, 2000);
        $completionTokens = fake()->numberBetween(100, 1500);

        return [
            'uuid' => fake()->uuid(),
            'type' => fake()->randomElement($types),
            'status' => fake()->randomElement(['success', 'error'], [90, 10]),
            'model_provider' => $provider,
            'model_name' => fake()->randomElement($models[$provider]),
            'temperature' => fake()->randomFloat(2, 0, 1),
            'max_tokens' => fake()->randomElement([1000, 2000, 4000, 8000]),
            'system_prompt' => fake()->paragraph(),
            'prompt' => fake()->paragraph(3),
            'response' => fake()->paragraph(5),
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $promptTokens + $completionTokens,
            'latency_ms' => fake()->numberBetween(100, 5000),
            'cost_usd' => fake()->randomFloat(6, 0.001, 0.5),
            'user_id' => User::factory(),
            'include_in_training' => fake()->boolean(30), // 30% opt-in
            'quality_score' => fake()->randomFloat(2, 0.5, 1),
        ];
    }

    public function withAgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'agent_id' => Agent::factory(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'error',
            'error_message' => fake()->sentence(),
            'error_details' => [
                'code' => fake()->randomElement(['timeout', 'rate_limit', 'invalid_request']),
                'retry_after' => fake()->numberBetween(1, 60),
            ],
            'response' => null,
            'completion_tokens' => 0,
            'total_tokens' => $attributes['prompt_tokens'] ?? fake()->numberBetween(50, 2000),
        ]);
    }

    public function chat(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'chat',
            'messages' => [
                ['role' => 'user', 'content' => $attributes['prompt']],
                ['role' => 'assistant', 'content' => $attributes['response']],
            ],
        ]);
    }

    public function includeInTraining(): static
    {
        return $this->state(fn (array $attributes) => [
            'include_in_training' => true,
            'quality_score' => fake()->randomFloat(2, 0.8, 1),
        ]);
    }
}
