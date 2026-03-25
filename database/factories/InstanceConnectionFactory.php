<?php

namespace Database\Factories;

use App\Models\InstanceConnection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InstanceConnection>
 */
class InstanceConnectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company(),
            'kind' => InstanceConnection::KIND_SERVER,
            'base_url' => 'https://'.fake()->domainName(),
            'session_context' => null,
            'last_authenticated_at' => null,
            'last_used_at' => null,
        ];
    }

    public function currentInstance(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Katra',
            'kind' => InstanceConnection::KIND_CURRENT_INSTANCE,
            'base_url' => 'https://katra.test',
            'last_authenticated_at' => now(),
            'last_used_at' => now(),
        ]);
    }
}
