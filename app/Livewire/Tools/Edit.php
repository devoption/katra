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

    public string $custom_category = '';

    public string $input_schema = '';

    public string $output_schema = '';

    public bool $requires_credential = false;

    public array $selected_credentials = [];

    public function mount(Tool $tool): void
    {
        $this->tool = $tool;
        $this->name = $tool->name;
        $this->description = $tool->description;
        $this->type = $tool->type;
        // Check if category is a standard one or custom
        $standardCategories = ['file', 'git', 'http', 'database', 'communication', 'deployment', 'testing'];
        if ($tool->category && ! in_array($tool->category, $standardCategories)) {
            $this->category = 'other';
            $this->custom_category = $tool->category;
        } else {
            $this->category = $tool->category ?? '';
        }

        $this->input_schema = json_encode($tool->input_schema, JSON_PRETTY_PRINT);
        $this->output_schema = $tool->output_schema ? json_encode($tool->output_schema, JSON_PRETTY_PRINT) : '';
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

        $this->tool->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'category' => $category,
            'input_schema' => isset($validated['input_schema']) ? json_decode($validated['input_schema'], true) : $this->tool->input_schema,
            'output_schema' => isset($validated['output_schema']) && $validated['output_schema'] ? json_decode($validated['output_schema'], true) : null,
            'execution_method' => $this->type,
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
