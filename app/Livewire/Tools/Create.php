<?php

namespace App\Livewire\Tools;

use App\Models\Credential;
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

    public string $custom_category = '';

    public string $input_schema = '';

    public string $output_schema = '';

    public bool $requires_credential = false;

    public array $selected_credentials = [];

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
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', 'in:custom,mcp_server,package'],
            'category' => ['nullable', 'string', 'max:100'],
            'requires_credential' => ['boolean'],
            'selected_credentials' => ['array'],
            'selected_credentials.*' => ['exists:credentials,id'],
        ];

        // Only require schemas for custom tools
        if ($this->type === 'custom') {
            $rules['input_schema'] = ['required', 'json'];
            $rules['output_schema'] = ['nullable', 'json'];
        }

        $validated = $this->validate($rules);

        // Use custom category if "other" is selected
        $category = $this->category === 'other' ? $this->custom_category : $this->category;

        $tool = Tool::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'category' => $category,
            'input_schema' => isset($validated['input_schema']) ? json_decode($validated['input_schema'], true) : ['type' => 'object'],
            'output_schema' => isset($validated['output_schema']) && $validated['output_schema'] ? json_decode($validated['output_schema'], true) : null,
            'execution_method' => $this->type,
            'requires_credential' => $validated['requires_credential'],
            'created_by' => auth()->id(),
        ]);

        if (! empty($this->selected_credentials)) {
            $tool->credentials()->attach($this->selected_credentials);
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Tool created successfully!',
        ]);

        $this->redirect(route('tools.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.tools.create', [
            'credentials' => Credential::all(),
        ]);
    }
}
