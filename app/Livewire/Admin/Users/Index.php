<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('User Management - Katra')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function toggleRole(User $user): void
    {
        if ($user->id === auth()->id()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'You cannot change your own role.',
            ]);

            return;
        }

        $newRole = $user->role === 'admin' ? 'user' : 'admin';

        $user->update(['role' => $newRole]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "User role updated to {$newRole}.",
        ]);
    }

    public function deleteUser(User $user): void
    {
        if ($user->id === auth()->id()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'You cannot delete your own account.',
            ]);

            return;
        }

        // Check if user has created content
        $hasContent = $user->agents()->exists() ||
                     $user->workflows()->exists() ||
                     $user->contexts()->exists() ||
                     $user->tools()->exists();

        if ($hasContent) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Cannot delete user with existing content. Reassign their content first.',
            ]);

            return;
        }

        $user->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'User deleted successfully.',
        ]);
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->where('role', $this->roleFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.admin.users.index', [
            'users' => $users,
        ]);
    }
}
