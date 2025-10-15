<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit User - Katra')]
class Edit extends Component
{
    public $userId;

    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $role = 'user';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(User $user): void
    {
        $this->userId = $user->id;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->role = $user->role;
    }

    public function getUserProperty(): User
    {
        return User::findOrFail($this->userId);
    }

    public function save(): void
    {
        $user = $this->user;

        if ($user->id === auth()->id() && $this->role !== $user->role) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'You cannot change your own role.',
            ]);

            return;
        }

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'in:admin,user'],
        ];

        // Only validate password if provided
        if ($this->password) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $this->validate($rules);

        $updateData = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if ($this->password) {
            $updateData['password'] = $this->password;
        }

        $user->update($updateData);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'User updated successfully!',
        ]);

        $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function render()
    {
        $user = $this->user;

        $stats = [
            'agents' => $user->agents()->count(),
            'workflows' => $user->workflows()->count(),
            'contexts' => $user->contexts()->count(),
            'tools' => $user->tools()->count(),
            'credentials' => $user->credentials()->count(),
            'executions' => $user->triggeredExecutions()->count(),
        ];

        return view('livewire.admin.users.edit', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }
}
