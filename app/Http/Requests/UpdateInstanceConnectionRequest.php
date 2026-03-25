<?php

namespace App\Http\Requests;

use App\Models\InstanceConnection;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInstanceConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        $instanceConnection = $this->route('instanceConnection');

        if ($instanceConnection instanceof InstanceConnection && $instanceConnection->is_current_instance) {
            return [
                'name' => ['nullable', 'string', 'max:255'],
            ];
        }

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'base_url' => ['required', 'url:http,https', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'base_url.required' => 'Enter the Katra server URL you want to save.',
            'base_url.url' => 'Use a full server URL like https://katra.example.com.',
        ];
    }
}
