<?php

namespace App\Livewire\Agents;

use App\Models\Agent;
use App\Models\Context;
use App\Models\Credential;
use App\Models\Tool;
use App\Services\OllamaService;
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

    public string $model_provider = 'ollama';

    public string $model_name = '';

    public string $system_prompt = '';

    public float $creativity_level = 0.70;

    public ?int $context_id = null;

    public ?int $credential_id = null;

    public array $selected_tools = [];

    public string $tool_search = '';

    public array $availableModels = [];

    public bool $ollamaAvailable = true;

    // Custom model provider config
    public string $custom_api_endpoint = '';

    public array $custom_headers = [];

    public string $custom_auth_type = 'bearer';

    // New context creation
    public bool $show_create_context_modal = false;

    public string $new_context_name = '';

    public string $new_context_description = '';

    public function mount(): void
    {
        $this->loadOllamaModels();
    }

    public function loadOllamaModels(): void
    {
        $ollamaService = app(OllamaService::class);

        $this->ollamaAvailable = $ollamaService->isAvailable();

        if ($this->ollamaAvailable) {
            $this->availableModels = $ollamaService->getModelNames();

            if (empty($this->model_name) && ! empty($this->availableModels)) {
                $this->model_name = $this->availableModels[0];
            }
        } else {
            $this->availableModels = [];
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Ollama is not available. Please ensure Ollama is running.',
            ]);
        }
    }

    public function updatedModelProvider(): void
    {
        if ($this->model_provider === 'ollama') {
            $this->loadOllamaModels();
        } else {
            $this->model_name = '';
            $this->availableModels = [];
        }
    }

    public function refreshModels(): void
    {
        $this->loadOllamaModels();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Models refreshed successfully!',
        ]);
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
            'model_provider' => ['required', 'in:ollama,custom'],
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

        $agentData = [
            'name' => $validated['name'],
            'role' => $validated['role'],
            'description' => $validated['description'],
            'model_provider' => $validated['model_provider'],
            'model_name' => $validated['model_name'],
            'system_prompt' => $validated['system_prompt'],
            'creativity_level' => $validated['creativity_level'],
            'context_id' => $validated['context_id'],
            'credential_id' => $validated['credential_id'],
            'created_by' => auth()->id(),
        ];

        $agent = Agent::create($agentData);

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
        $toolsQuery = Tool::where('is_active', true);

        if ($this->tool_search) {
            $toolsQuery->where(function ($q) {
                $q->where('name', 'like', "%{$this->tool_search}%")
                    ->orWhere('description', 'like', "%{$this->tool_search}%")
                    ->orWhere('category', 'like', "%{$this->tool_search}%");
            });
        }

        return view('livewire.agents.create', [
            'contexts' => Context::where('type', 'agent')->get(),
            'tools' => $toolsQuery->get(),
            'credentials' => Credential::all(),
        ]);
    }
}
