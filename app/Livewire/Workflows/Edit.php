<?php

namespace App\Livewire\Workflows;

use App\Models\Agent;
use App\Models\Context;
use App\Models\Workflow;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Workflow - Katra')]
class Edit extends Component
{
    public Workflow $workflow;

    public string $name = '';

    public string $description = '';

    public string $version = '';

    public string $execution_mode = '';

    public ?int $context_id = null;

    public string $definition_yaml = '';

    public function mount(Workflow $workflow): void
    {
        $this->workflow = $workflow;
        $this->name = $workflow->name;
        $this->description = $workflow->description ?? '';
        $this->version = $workflow->version;
        $this->execution_mode = $workflow->execution_mode;
        $this->context_id = $workflow->context_id;
        $this->definition_yaml = $workflow->definition['yaml'] ?? '';
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

        $definition = [
            'yaml' => $validated['definition_yaml'],
            'steps' => [],
        ];

        $this->workflow->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'version' => $validated['version'],
            'execution_mode' => $validated['execution_mode'],
            'context_id' => $validated['context_id'],
            'definition' => $definition,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Workflow updated successfully!',
        ]);

        $this->redirect(route('workflows.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.workflows.edit', [
            'contexts' => Context::where('type', 'workflow')->get(),
            'agents' => Agent::where('is_active', true)->get(),
        ]);
    }
}
