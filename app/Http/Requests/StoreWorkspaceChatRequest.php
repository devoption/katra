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

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'chat_name' => ['required', 'string', 'max:255', 'regex:/\\S/'],
            'chat_kind' => ['required', 'string', 'in:direct,group'],
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
        ];
    }
}
