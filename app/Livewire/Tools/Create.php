<?php

namespace App\Livewire\Tools;

use App\Models\Tool;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Create Tool - Katra')]
class Create extends Component
{
    public string $name = '';

    public string $description = '';

    public string $type = 'custom';

    public string $category = '';

    public string $input_schema = '';

    public string $output_schema = '';

    public string $execution_method = '';

    public bool $requires_credential = false;

    public function mount(): void
    {
        // Default JSON schemas
        $this->input_schema = json_encode([
            'type' => 'object',
            'properties' => [
                'input' => [
                    'type' => 'string',
                    'description' => 'Input parameter',
                ],
            ],
            'required' => ['input'],
        ], JSON_PRETTY_PRINT);

        $this->output_schema = json_encode([
            'type' => 'object',
            'properties' => [
                'result' => [
                    'type' => 'string',
                ],
            ],
        ], JSON_PRETTY_PRINT);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', 'in:custom,mcp_server,package'],
            'category' => ['required', 'string', 'max:100'],
            'input_schema' => ['required', 'json'],
            'output_schema' => ['nullable', 'json'],
            'execution_method' => ['nullable', 'string', 'max:255'],
            'requires_credential' => ['boolean'],
        ]);

        Tool::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'category' => $validated['category'],
            'input_schema' => json_decode($validated['input_schema'], true),
            'output_schema' => $validated['output_schema'] ? json_decode($validated['output_schema'], true) : null,
            'execution_method' => $validated['execution_method'],
            'requires_credential' => $validated['requires_credential'],
            'created_by' => auth()->id(),
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Tool created successfully!',
        ]);

        $this->redirect(route('tools.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.tools.create');
    }
}
