<?php

namespace App\Livewire\Agents;

use App\Models\Agent;
use App\Models\Context;
use App\Models\Tool;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Create Agent - Katra')]
class Create extends Component
{
    public string $name = '';

    public string $role = '';

    public string $description = '';

    public string $model_provider = 'openai';

    public string $model_name = '';

    public string $system_prompt = '';

    public float $creativity_level = 0.70;

    public ?int $context_id = null;

    public array $selected_tools = [];

    public array $modelOptions = [
        'openai' => ['gpt-4', 'gpt-4-turbo', 'gpt-3.5-turbo'],
        'anthropic' => ['claude-3-opus-20240229', 'claude-3-sonnet-20240229', 'claude-3-haiku-20240307', 'claude-sonnet-4.5'],
        'google' => ['gemini-pro', 'gemini-ultra', 'gemini-1.5-pro'],
        'ollama' => ['llama2', 'mistral', 'codellama', 'mixtral'],
        'custom' => [],
    ];

    public function mount(): void
    {
        $this->model_name = $this->modelOptions[$this->model_provider][0] ?? '';
    }

    public function updatedModelProvider(): void
    {
        $this->model_name = $this->modelOptions[$this->model_provider][0] ?? '';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'model_provider' => ['required', 'in:openai,anthropic,google,ollama,custom'],
            'model_name' => ['required', 'string', 'max:255'],
            'system_prompt' => ['required', 'string'],
            'creativity_level' => ['required', 'numeric', 'min:0', 'max:1'],
            'context_id' => ['nullable', 'exists:contexts,id'],
            'selected_tools' => ['array'],
            'selected_tools.*' => ['exists:tools,id'],
        ]);

        $agent = Agent::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        if (! empty($this->selected_tools)) {
            $agent->tools()->attach($this->selected_tools);
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Agent created successfully!',
        ]);

        $this->redirect(route('agents.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.agents.create', [
            'contexts' => Context::where('type', 'agent')->get(),
            'tools' => Tool::where('is_active', true)->get(),
        ]);
    }
}
