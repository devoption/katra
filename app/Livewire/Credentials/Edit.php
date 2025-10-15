<?php

namespace App\Livewire\Credentials;

use App\Models\Credential;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Credential - Katra')]
class Edit extends Component
{
    public Credential $credential;

    public string $name = '';

    public string $description = '';

    public string $type = '';

    public string $provider = '';

    public string $value = '';

    public bool $update_value = false;

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

    public function mount(Credential $credential): void
    {
        $this->credential = $credential;
        $this->name = $credential->name;
        $this->description = $credential->description ?? '';
        $this->type = $credential->type;
        $this->provider = $credential->provider ?? '';
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'max:100'],
            'provider' => ['nullable', 'string', 'max:100'],
        ];

        if ($this->update_value) {
            $rules['value'] = ['required', 'string'];
        }

        $validated = $this->validate($rules);

        $updateData = [
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'provider' => $validated['provider'],
        ];

        if ($this->update_value && ! empty($this->value)) {
            $updateData['value'] = $this->value; // Uses accessor to encrypt
        }

        $this->credential->update($updateData);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Credential updated successfully!',
        ]);

        $this->redirect(route('credentials.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.credentials.edit');
    }
}
