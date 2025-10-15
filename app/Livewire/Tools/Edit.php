<?php

namespace App\Livewire\Tools;

use App\Models\Credential;
use App\Models\Tool;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Tool - Katra')]
class Edit extends Component
{
    public Tool $tool;

    public string $name = '';

    public string $description = '';

    public string $type = '';

    public string $category = '';

    public string $input_schema = '';

    public string $output_schema = '';

    public string $execution_method = '';

    public bool $requires_credential = false;

    public array $selected_credentials = [];

    public function mount(Tool $tool): void
    {
        $this->tool = $tool;
        $this->name = $tool->name;
        $this->description = $tool->description;
        $this->type = $tool->type;
        $this->category = $tool->category ?? '';
        $this->input_schema = json_encode($tool->input_schema, JSON_PRETTY_PRINT);
        $this->output_schema = $tool->output_schema ? json_encode($tool->output_schema, JSON_PRETTY_PRINT) : '';
        $this->execution_method = $tool->execution_method ?? '';
        $this->requires_credential = $tool->requires_credential;
        $this->selected_credentials = $tool->credentials->pluck('id')->toArray();
    }

    public function save(): void
    {
        if ($this->tool->type === 'builtin') {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Built-in tools cannot be modified.',
            ]);

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', 'in:custom,mcp_server,package'],
            'category' => ['required', 'string', 'max:100'],
            'input_schema' => ['required', 'json'],
            'output_schema' => ['nullable', 'json'],
            'execution_method' => ['nullable', 'string', 'max:255'],
            'requires_credential' => ['boolean'],
            'selected_credentials' => ['array'],
            'selected_credentials.*' => ['exists:credentials,id'],
        ]);

        $this->tool->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'category' => $validated['category'],
            'input_schema' => json_decode($validated['input_schema'], true),
            'output_schema' => $validated['output_schema'] ? json_decode($validated['output_schema'], true) : null,
            'execution_method' => $validated['execution_method'],
            'requires_credential' => $validated['requires_credential'],
        ]);

        $this->tool->credentials()->sync($this->selected_credentials);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Tool updated successfully!',
        ]);

        $this->redirect(route('tools.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.tools.edit', [
            'credentials' => Credential::all(),
        ]);
    }
}
