<?php

namespace App\Livewire\Workflows;

use App\Models\Agent;
use App\Models\Context;
use App\Models\Workflow;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Create Workflow - Katra')]
class Create extends Component
{
    public string $name = '';

    public string $description = '';

    public string $version = '1.0';

    public string $execution_mode = 'series';

    public ?int $context_id = null;

    public string $definition_yaml = '';

    public function mount(): void
    {
        // Default YAML template
        $this->definition_yaml = <<<'YAML'
steps:
  - name: step_1
    agent: agent_name
    description: First step description
    
  - name: step_2
    agent: agent_name
    description: Second step description
    depends_on:
      - step_1
YAML;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'version' => ['required', 'string', 'max:50'],
            'execution_mode' => ['required', 'in:series,parallel,dag'],
            'context_id' => ['nullable', 'exists:contexts,id'],
            'definition_yaml' => ['required', 'string'],
        ]);

        // For now, store the YAML as a simple array
        // Later we'll add proper YAML parsing
        $definition = [
            'yaml' => $validated['definition_yaml'],
            'steps' => [],
        ];

        Workflow::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'version' => $validated['version'],
            'execution_mode' => $validated['execution_mode'],
            'context_id' => $validated['context_id'],
            'definition' => $definition,
            'created_by' => auth()->id(),
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Workflow created successfully!',
        ]);

        $this->redirect(route('workflows.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.workflows.create', [
            'contexts' => Context::where('type', 'workflow')->get(),
            'agents' => Agent::where('is_active', true)->get(),
        ]);
    }
}
