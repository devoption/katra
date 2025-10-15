<?php

namespace App\Livewire\Credentials;

use App\Models\Credential;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Create Credential - Katra')]
class Create extends Component
{
    public string $name = '';

    public string $description = '';

    public string $type = 'api_key';

    public string $provider = '';

    public string $value = '';

    public array $metadata = [];

    public array $providerOptions = [
        'openai' => 'OpenAI',
        'anthropic' => 'Anthropic',
        'google' => 'Google',
        'github' => 'GitHub',
        'gitlab' => 'GitLab',
        'slack' => 'Slack',
        'aws' => 'AWS',
        'azure' => 'Azure',
        'custom' => 'Custom',
    ];

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'max:100'],
            'provider' => ['nullable', 'string', 'max:100'],
            'value' => ['required', 'string'],
        ]);

        Credential::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'provider' => $validated['provider'],
            'value' => $validated['value'], // Uses the accessor to encrypt
            'created_by' => auth()->id(),
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Credential created successfully!',
        ]);

        $this->redirect(route('credentials.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.credentials.create');
    }
}
