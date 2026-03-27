<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkspaceChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'chat_name' => is_string($this->chat_name) ? trim($this->chat_name) : $this->chat_name,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'chat_name' => ['required', 'string', 'max:255', 'regex:/\\S/'],
            'chat_kind' => ['required', 'string', 'in:direct,group'],
            'chat_submission_token' => ['required', 'string'],
            'workspace_agent_id' => ['nullable', 'integer', 'exists:workspace_agents,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'chat_name.required' => 'Enter a chat name to create it in this workspace.',
            'chat_name.regex' => 'Chat names cannot be blank.',
            'chat_kind.required' => 'Choose whether this chat is direct or group.',
            'chat_kind.in' => 'Chats must be direct or group conversations.',
            'chat_submission_token.required' => 'Refresh the workspace before creating a chat.',
            'workspace_agent_id.exists' => 'Choose a valid agent for this workspace.',
        ];
    }
}
