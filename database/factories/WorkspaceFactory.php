<?php

namespace Database\Factories;

use App\Models\InstanceConnection;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Workspace>
 */
class WorkspaceFactory extends Factory
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
            'instance_connection_id' => InstanceConnection::factory(),
            'name' => str($name)->title()->value(),
            'slug' => Str::slug($name),
            'summary' => fake()->sentence(),
        ];
    }
}
