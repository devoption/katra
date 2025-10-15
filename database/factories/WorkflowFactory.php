<?php

namespace Database\Factories;

use App\Models\Context;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workflow>
 */
class WorkflowFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'version' => '1.0',
            'definition' => [
                'steps' => [
                    [
                        'name' => 'step1',
                        'agent' => 'agent_1',
                        'description' => fake()->sentence(),
                    ],
                ],
            ],
            'execution_mode' => fake()->randomElement(['series', 'parallel', 'dag']),
            'is_active' => true,
            'context_id' => null,
            'created_by' => User::factory(),
        ];
    }

    public function withContext(): static
    {
        return $this->state(fn (array $attributes) => [
            'context_id' => Context::factory()->workflow(),
        ]);
    }

    public function series(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_mode' => 'series',
        ]);
    }

    public function parallel(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_mode' => 'parallel',
        ]);
    }

    public function dag(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_mode' => 'dag',
        ]);
    }
}
