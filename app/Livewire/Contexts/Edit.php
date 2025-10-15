<?php

namespace App\Livewire\Contexts;

use App\Models\Context;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Context - Katra')]
class Edit extends Component
{
    public Context $context;

    public string $name = '';

    public string $description = '';

    public string $type = '';

    public string $content_json = '';

    public function mount(Context $context): void
    {
        $this->context = $context;
        $this->name = $context->name;
        $this->description = $context->description ?? '';
        $this->type = $context->type;
        $this->content_json = json_encode($context->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:agent,workflow,execution'],
            'content_json' => ['required', 'json'],
        ]);

        $this->context->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'content' => json_decode($validated['content_json'], true),
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Context updated successfully!',
        ]);

        $this->redirect(route('contexts.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.contexts.edit');
    }
}
