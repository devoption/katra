<?php

namespace App\Livewire\Contexts;

use App\Models\Context;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Create Context - Katra')]
class Create extends Component
{
    public string $name = '';

    public string $description = '';

    public string $type = 'agent';

    public array $content_data = [];

    public string $content_json = '';

    public bool $use_json_editor = false;

    public function mount(): void
    {
        $this->content_json = json_encode([], JSON_PRETTY_PRINT);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:agent,workflow,execution'],
            'content_json' => ['nullable', 'json'],
        ]);

        $content = $this->use_json_editor && $validated['content_json']
            ? json_decode($validated['content_json'], true)
            : $this->content_data;

        Context::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'content' => $content,
            'created_by' => auth()->id(),
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Context created successfully!',
        ]);

        $this->redirect(route('contexts.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.contexts.create');
    }
}
