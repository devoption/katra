<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Context>
 */
class ContextFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(['agent', 'workflow', 'execution']),
            'content' => [
                'data' => fake()->sentences(3),
                'metadata' => [
                    'created_date' => fake()->date(),
                    'version' => '1.0',
                ],
            ],
            'metadata' => [
                'tags' => fake()->words(3),
            ],
            'created_by' => User::factory(),
        ];
    }

    public function agent(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'agent',
            'name' => 'Agent Context: '.fake()->words(2, true),
        ]);
    }

    public function workflow(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'workflow',
            'name' => 'Workflow Context: '.fake()->words(2, true),
        ]);
    }

    public function execution(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'execution',
            'name' => 'Execution Context: '.fake()->uuid(),
        ]);
    }
}
