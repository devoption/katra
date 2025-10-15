<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tool>
 */
class ToolFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(['builtin', 'custom', 'mcp_server', 'package']),
            'category' => fake()->randomElement(['file', 'git', 'http', 'database', 'communication']),
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'input' => ['type' => 'string'],
                ],
                'required' => ['input'],
            ],
            'output_schema' => [
                'type' => 'object',
                'properties' => [
                    'result' => ['type' => 'string'],
                ],
            ],
            'execution_method' => 'script',
            'execution_config' => [
                'timeout' => 30,
            ],
            'requires_credential' => fake()->boolean(30),
            'is_active' => true,
            'created_by' => null,
        ];
    }

    public function builtin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'builtin',
            'created_by' => null,
        ]);
    }

    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'custom',
            'created_by' => User::factory(),
        ]);
    }

    public function requiresCredential(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_credential' => true,
        ]);
    }
}
