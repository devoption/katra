<?php

namespace App\Livewire\Agents;

use App\Models\Agent;
use App\Models\Context;
use App\Models\Tool;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Agent - Katra')]
class Edit extends Component
{
    public Agent $agent;

    public string $name = '';

    public string $role = '';

    public string $description = '';

    public string $model_provider = '';

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

    public function mount(Agent $agent): void
    {
        $this->agent = $agent;
        $this->name = $agent->name;
        $this->role = $agent->role;
        $this->description = $agent->description ?? '';
        $this->model_provider = $agent->model_provider;
        $this->model_name = $agent->model_name;
        $this->system_prompt = $agent->system_prompt;
        $this->creativity_level = (float) $agent->creativity_level;
        $this->context_id = $agent->context_id;
        $this->selected_tools = $agent->tools->pluck('id')->toArray();
    }

    public function updatedModelProvider(): void
    {
        if (! empty($this->modelOptions[$this->model_provider])) {
            $this->model_name = $this->modelOptions[$this->model_provider][0];
        }
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

        $this->agent->update($validated);

        $this->agent->tools()->sync($this->selected_tools);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Agent updated successfully!',
        ]);

        $this->redirect(route('agents.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.agents.edit', [
            'contexts' => Context::where('type', 'agent')->get(),
            'tools' => Tool::where('is_active', true)->get(),
        ]);
    }
}
