<?php

namespace App\Livewire\Agents;

use App\Models\Agent;
use App\Models\Context;
use App\Models\Credential;
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

    public ?int $credential_id = null;

    public array $selected_tools = [];

    public string $tool_search = '';

    // Custom model provider config
    public string $custom_api_endpoint = '';

    public array $custom_headers = [];

    public string $custom_auth_type = 'bearer';

    // New context creation
    public bool $show_create_context_modal = false;

    public string $new_context_name = '';

    public string $new_context_description = '';

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
        $this->credential_id = $agent->credential_id;
        $this->selected_tools = $agent->tools->pluck('id')->toArray();
    }

    public function updatedModelProvider(): void
    {
        if (! empty($this->modelOptions[$this->model_provider])) {
            $this->model_name = $this->modelOptions[$this->model_provider][0];
        }
    }

    public function createContext(): void
    {
        $validated = $this->validate([
            'new_context_name' => ['required', 'string', 'max:255'],
            'new_context_description' => ['nullable', 'string'],
        ], [
            'new_context_name.required' => 'Context name is required.',
        ]);

        $context = Context::create([
            'name' => $validated['new_context_name'],
            'description' => $validated['new_context_description'] ?? null,
            'type' => 'agent',
            'content' => [],
            'created_by' => auth()->id(),
        ]);

        $this->context_id = $context->id;
        $this->show_create_context_modal = false;
        $this->reset(['new_context_name', 'new_context_description']);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Context created successfully!',
        ]);
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'model_provider' => ['required', 'in:openai,anthropic,google,ollama,custom'],
            'model_name' => ['required', 'string', 'max:255'],
            'system_prompt' => ['required', 'string'],
            'creativity_level' => ['required', 'numeric', 'min:0', 'max:1'],
            'context_id' => ['nullable', 'exists:contexts,id'],
            'credential_id' => ['nullable', 'exists:credentials,id'],
            'selected_tools' => ['array'],
            'selected_tools.*' => ['exists:tools,id'],
        ];

        if ($this->model_provider === 'custom') {
            $rules['custom_api_endpoint'] = ['required', 'url'];
        }

        $validated = $this->validate($rules);

        $this->agent->update([
            'name' => $validated['name'],
            'role' => $validated['role'],
            'description' => $validated['description'],
            'model_provider' => $validated['model_provider'],
            'model_name' => $validated['model_name'],
            'system_prompt' => $validated['system_prompt'],
            'creativity_level' => $validated['creativity_level'],
            'context_id' => $validated['context_id'],
            'credential_id' => $validated['credential_id'],
        ]);

        $this->agent->tools()->sync($this->selected_tools);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Agent updated successfully!',
        ]);

        $this->redirect(route('agents.index'), navigate: true);
    }

    public function render()
    {
        $toolsQuery = Tool::where('is_active', true);

        if ($this->tool_search) {
            $toolsQuery->where(function ($q) {
                $q->where('name', 'like', "%{$this->tool_search}%")
                    ->orWhere('description', 'like', "%{$this->tool_search}%")
                    ->orWhere('category', 'like', "%{$this->tool_search}%");
            });
        }

        // Get credentials filtered by provider if applicable
        $credentialsQuery = Credential::query();

        if (in_array($this->model_provider, ['openai', 'anthropic', 'google'])) {
            $credentialsQuery->where('provider', $this->model_provider);
        }

        return view('livewire.agents.edit', [
            'contexts' => Context::where('type', 'agent')->get(),
            'tools' => $toolsQuery->get(),
            'credentials' => $credentialsQuery->get(),
        ]);
    }
}
