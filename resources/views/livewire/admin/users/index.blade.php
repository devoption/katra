<div>
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">User Management</h1>
        <p class="mt-1 text-sm text-nord3 dark:text-nord4">Manage user accounts and permissions</p>
    </div>

    <!-- Filters -->
    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                    Search Users
                </label>
                <input
                    type="text"
                    id="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by name or email..."
                    class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                >
            </div>

            <!-- Role Filter -->
            <div>
                <label for="roleFilter" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                    Filter by Role
                </label>
                <select
                    id="roleFilter"
                    wire:model.live="roleFilter"
                    class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                >
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>
        </div>
    </x-ui.card>

    <!-- Users Table -->
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-nord4 dark:bg-nord2 border-b border-nord4 dark:border-nord3">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-nord4 dark:divide-nord2">
                    @forelse($users as $user)
                        <tr class="hover:bg-nord4 dark:hover:bg-nord2 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-semibold">
                                        {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-nord0 dark:text-nord6">
                                            {{ $user->full_name }}
                                            @if($user->id === auth()->id())
                                                <span class="text-xs text-nord3 dark:text-nord4">(You)</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-nord3 dark:text-nord4">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4">
                                <button
                                    wire:click="toggleRole({{ $user->id }})"
                                    @if($user->id === auth()->id()) disabled @endif
                                    class="inline-flex items-center gap-2 @if($user->id !== auth()->id()) cursor-pointer hover:opacity-75 @else cursor-not-allowed opacity-50 @endif transition-opacity"
                                >
                                    <x-ui.badge :variant="$user->role === 'admin' ? 'primary' : 'default'" size="sm">
                                        {{ ucfirst($user->role) }}
                                    </x-ui.badge>
                                    @if($user->id !== auth()->id())
                                        <svg class="w-4 h-4 text-nord3 dark:text-nord4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <button
                                    wire:click="toggleActive({{ $user->id }})"
                                    @if($user->id === auth()->id()) disabled @endif
                                    class="inline-flex items-center gap-2 @if($user->id !== auth()->id()) cursor-pointer hover:opacity-75 @else cursor-not-allowed opacity-50 @endif transition-opacity"
                                >
                                    <x-ui.badge :variant="$user->is_active ? 'success' : 'warning'" size="sm">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </x-ui.badge>
                                    @if($user->id !== auth()->id())
                                        <svg class="w-4 h-4 text-nord3 dark:text-nord4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4 text-sm text-nord3 dark:text-nord4">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.button
                                        href="{{ route('admin.users.edit', $user) }}"
                                        wire:navigate
                                        variant="outline"
                                        size="sm"
                                    >
                                        Edit
                                    </x-ui.button>
                                    @if($user->id !== auth()->id())
                                        <x-ui.button
                                            wire:click="deleteUser({{ $user->id }})"
                                            wire:confirm="Are you sure you want to delete this user? This action cannot be undone."
                                            variant="danger"
                                            size="sm"
                                        >
                                            Delete
                                        </x-ui.button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-nord3 dark:text-nord4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p class="mt-4 text-sm text-nord3 dark:text-nord4">
                                    @if($search || $roleFilter)
                                        No users found matching your search.
                                    @else
                                        No users yet.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-nord4 dark:border-nord2">
                {{ $users->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
