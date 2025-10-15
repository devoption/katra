<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Credential>
 */
class CredentialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake()->words(2, true).' Credential',
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(['api_key', 'oauth', 'password', 'certificate', 'custom']),
            'provider' => fake()->randomElement(['openai', 'github', 'slack', 'aws', null]),
            'encrypted_value' => Crypt::encryptString(fake()->password(32)),
            'metadata' => [
                'created_at' => fake()->dateTime()->format('Y-m-d H:i:s'),
            ],
            'created_by' => User::factory(),
        ];
    }

    public function apiKey(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'api_key',
            'name' => ($attributes['provider'] ?? 'API').' API Key',
        ]);
    }

    public function openai(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'api_key',
            'provider' => 'openai',
            'name' => 'OpenAI API Key',
        ]);
    }

    public function anthropic(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'api_key',
            'provider' => 'anthropic',
            'name' => 'Anthropic API Key',
        ]);
    }
}
