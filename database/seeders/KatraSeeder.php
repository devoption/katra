<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Context;
use App\Models\Tool;
use App\Models\User;
use App\Models\Workflow;
use Illuminate\Database\Seeder;

class KatraSeeder extends Seeder
{
    public function run(): void
    {
        // Get the admin user
        $admin = User::where('email', 'admin@katra.test')->first();

        if (! $admin) {
            $this->command->warn('Admin user not found. Run DatabaseSeeder first.');

            return;
        }

        // Create the default Katra agent
        $katra = Agent::factory()->katra()->create([
            'created_by' => $admin->id,
        ]);

        $this->command->info('✓ Created Katra executive assistant agent');

        // Create some built-in tools
        $tools = [
            [
                'name' => 'Read File',
                'description' => 'Read contents of a file',
                'category' => 'file',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'path' => ['type' => 'string', 'description' => 'File path'],
                    ],
                    'required' => ['path'],
                ],
            ],
            [
                'name' => 'Write File',
                'description' => 'Write contents to a file',
                'category' => 'file',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'path' => ['type' => 'string', 'description' => 'File path'],
                        'content' => ['type' => 'string', 'description' => 'File content'],
                    ],
                    'required' => ['path', 'content'],
                ],
            ],
            [
                'name' => 'HTTP Request',
                'description' => 'Make HTTP requests',
                'category' => 'http',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'url' => ['type' => 'string'],
                        'method' => ['type' => 'string', 'enum' => ['GET', 'POST', 'PUT', 'DELETE']],
                        'headers' => ['type' => 'object'],
                        'body' => ['type' => 'object'],
                    ],
                    'required' => ['url', 'method'],
                ],
            ],
            [
                'name' => 'Git Commit',
                'description' => 'Create a git commit',
                'category' => 'git',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'message' => ['type' => 'string'],
                        'files' => ['type' => 'array', 'items' => ['type' => 'string']],
                    ],
                    'required' => ['message'],
                ],
            ],
            [
                'name' => 'Send Email',
                'description' => 'Send an email',
                'category' => 'communication',
                'requires_credential' => true,
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'to' => ['type' => 'string'],
                        'subject' => ['type' => 'string'],
                        'body' => ['type' => 'string'],
                    ],
                    'required' => ['to', 'subject', 'body'],
                ],
            ],
        ];

        foreach ($tools as $toolData) {
            Tool::factory()->builtin()->create(array_merge([
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'success' => ['type' => 'boolean'],
                        'result' => ['type' => 'string'],
                    ],
                ],
                'execution_method' => 'internal',
                'execution_config' => ['timeout' => 30],
            ], $toolData));
        }

        $this->command->info('✓ Created '.count($tools).' built-in tools');

        // Create some sample agents
        $agents = Agent::factory()->count(3)->create([
            'created_by' => $admin->id,
        ]);

        $this->command->info('✓ Created '.count($agents).' sample agents');

        // Attach some tools to agents
        foreach ($agents as $agent) {
            $agent->tools()->attach(
                Tool::inRandomOrder()->limit(rand(2, 4))->pluck('id')
            );
        }

        $this->command->info('✓ Attached tools to agents');

        // Create some sample contexts
        $contexts = Context::factory()->count(5)->create([
            'created_by' => $admin->id,
        ]);

        $this->command->info('✓ Created '.count($contexts).' sample contexts');

        // Create some sample workflows
        $workflows = Workflow::factory()->count(3)->create([
            'created_by' => $admin->id,
        ]);

        $this->command->info('✓ Created '.count($workflows).' sample workflows');

        // Skip creating credentials for now (Ollama doesn't require them)
        $this->command->info('✓ Skipped credential creation (using local Ollama)');

        $this->command->info('✨ Katra seeding completed successfully!');
    }
}
