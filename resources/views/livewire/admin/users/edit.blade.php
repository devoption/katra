<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-nord3 dark:text-nord4 mb-2">
            <a href="{{ route('admin.users.index') }}" wire:navigate class="hover:text-nord8 transition-colors">Users</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>Edit User</span>
        </div>
        <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">Edit User</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2">
            <x-ui.card>
                <form wire:submit="save" class="space-y-6">
                    <!-- Name Fields -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                                First Name <span class="text-nord11">*</span>
                            </label>
                            <input
                                type="text"
                                id="first_name"
                                wire:model="first_name"
                                class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-nord8 focus:border-transparent transition-colors"
                                required
                            >
                            @error('first_name')
                                <p class="mt-1 text-sm text-nord11">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                                Last Name <span class="text-nord11">*</span>
                            </label>
                            <input
                                type="text"
                                id="last_name"
                                wire:model="last_name"
                                class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-nord8 focus:border-transparent transition-colors"
                                required
                            >
                            @error('last_name')
                                <p class="mt-1 text-sm text-nord11">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                            Email Address <span class="text-nord11">*</span>
                        </label>
                        <input
                            type="email"
                            id="email"
                            wire:model="email"
                            class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-nord8 focus:border-transparent transition-colors"
                            required
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-nord11">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                            Role <span class="text-nord11">*</span>
                        </label>
                        <select
                            id="role"
                            wire:model="role"
                            @if($user->id === auth()->id()) disabled @endif
                            class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-nord8 focus:border-transparent transition-colors @if($user->id === auth()->id()) opacity-50 cursor-not-allowed @endif"
                            required
                        >
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                        @if($user->id === auth()->id())
                            <p class="mt-1 text-xs text-nord3 dark:text-nord4">You cannot change your own role</p>
                        @endif
                        @error('role')
                            <p class="mt-1 text-sm text-nord11">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Account Status -->
                    <div>
                        <label class="block text-sm font-medium text-nord0 dark:text-nord6 mb-3">
                            Account Status
                        </label>
                        <div class="flex items-center gap-4">
                            <x-ui.badge :variant="$user->is_active ? 'success' : 'warning'" size="sm">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </x-ui.badge>
                            @if($user->id !== auth()->id())
                                <p class="text-sm text-nord3 dark:text-nord4">
                                    Toggle status from the <a href="{{ route('admin.users.index') }}" wire:navigate class="text-nord8 hover:underline">user list</a>
                                </p>
                            @else
                                <p class="text-xs text-nord3 dark:text-nord4">You cannot change your own status</p>
                            @endif
                        </div>
                    </div>

                    <!-- Password Change (Optional) -->
                    <div class="border-t border-nord4 dark:border-nord3 pt-6">
                        <h3 class="text-sm font-medium text-nord0 dark:text-nord6 mb-4">Change Password (Optional)</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="password" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                                    New Password
                                </label>
                                <input
                                    type="password"
                                    id="password"
                                    wire:model="password"
                                    class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-nord8 focus:border-transparent transition-colors"
                                    autocomplete="new-password"
                                >
                                @error('password')
                                    <p class="mt-1 text-sm text-nord11">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                                    Confirm Password
                                </label>
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    wire:model="password_confirmation"
                                    class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-nord8 focus:border-transparent transition-colors"
                                    autocomplete="new-password"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-nord4 dark:border-nord3">
                        <x-ui.button
                            href="{{ route('admin.users.index') }}"
                            wire:navigate
                            variant="outline"
                        >
                            Cancel
                        </x-ui.button>

                        <x-ui.button
                            type="submit"
                            variant="primary"
                        >
                            <span wire:loading.remove wire:target="save">Save Changes</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <!-- Sidebar - User Stats -->
        <div class="space-y-6">
            <x-ui.card title="User Activity">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-nord3 dark:text-nord4">Agents</span>
                        <span class="font-semibold text-nord0 dark:text-nord6">{{ $stats['agents'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-nord3 dark:text-nord4">Workflows</span>
                        <span class="font-semibold text-nord0 dark:text-nord6">{{ $stats['workflows'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-nord3 dark:text-nord4">Contexts</span>
                        <span class="font-semibold text-nord0 dark:text-nord6">{{ $stats['contexts'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-nord3 dark:text-nord4">Tools</span>
                        <span class="font-semibold text-nord0 dark:text-nord6">{{ $stats['tools'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-nord3 dark:text-nord4">Credentials</span>
                        <span class="font-semibold text-nord0 dark:text-nord6">{{ $stats['credentials'] }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-nord4 dark:border-nord3 pt-4">
                        <span class="text-sm text-nord3 dark:text-nord4">Workflow Executions</span>
                        <span class="font-semibold text-nord0 dark:text-nord6">{{ $stats['executions'] }}</span>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Account Details">
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-nord3 dark:text-nord4">User ID:</span>
                        <span class="block font-mono text-xs text-nord0 dark:text-nord6 mt-1">{{ $user->id }}</span>
                    </div>
                    <div>
                        <span class="text-nord3 dark:text-nord4">Joined:</span>
                        <span class="block text-nord0 dark:text-nord6 mt-1">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    <div>
                        <span class="text-nord3 dark:text-nord4">Last Updated:</span>
                        <span class="block text-nord0 dark:text-nord6 mt-1">{{ $user->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
