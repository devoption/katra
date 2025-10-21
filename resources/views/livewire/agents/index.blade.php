<div>
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">Agents</h1>
            <p class="mt-1 text-sm text-nord3 dark:text-nord4">Manage your AI agents and their configurations</p>
        </div>
        <x-ui.button href="{{ route('agents.create') }}" wire:navigate variant="primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Agent
        </x-ui.button>
    </div>

    <!-- Filters -->
    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-ui.input
                wire:model.live.debounce.300ms="search"
                type="text"
                name="search"
                placeholder="Search agents..."
            />

            <x-ui.select
                wire:model.live="filterProvider"
                name="filterProvider"
            >
                <option value="">All Providers</option>
                @foreach($providers as $provider)
                    <option value="{{ $provider }}">{{ ucfirst($provider) }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select
                wire:model.live="filterActive"
                name="filterActive"
            >
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </x-ui.select>
        </div>
    </x-ui.card>

    <!-- Agents Table -->
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-nord4 dark:bg-nord2 border-b border-nord4 dark:border-nord3">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Agent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Model</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Tools</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-nord4 dark:divide-nord2">
                    @forelse($agents as $agent)
                        <tr class="hover:bg-nord4 dark:hover:bg-nord2 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="shrink-0 w-10 h-10 bg-primary bg-black/10 dark:bg-black/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-nord0 dark:text-nord6">
                                            {{ $agent->name }}
                                            @if($agent->is_default)
                                                <x-ui.badge variant="primary" size="sm" class="ml-2">Default</x-ui.badge>
                                            @endif
                                        </div>
                                        <div class="text-sm text-nord3 dark:text-nord4">{{ $agent->role }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-nord0 dark:text-nord6">{{ $agent->model_name }}</div>
                                <div class="text-xs text-nord3 dark:text-nord4">{{ ucfirst($agent->model_provider) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge variant="default" size="sm">{{ $agent->tools_count }} tools</x-ui.badge>
                            </td>
                            <td class="px-6 py-4">
                                <button
                                    wire:click="toggleActive({{ $agent->id }})"
                                    class="inline-flex items-center"
                                >
                                    @if($agent->is_active)
                                        <x-ui.badge variant="success" size="sm">Active</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="default" size="sm">Inactive</x-ui.badge>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4 text-sm text-nord3 dark:text-nord4">
                                {{ $agent->created_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.button
                                        href="{{ route('agents.edit', $agent) }}"
                                        wire:navigate
                                        variant="ghost"
                                        size="sm"
                                    >
                                        Edit
                                    </x-ui.button>
                                    @unless($agent->is_default)
                                        <x-ui.button
                                            wire:click="deleteAgent({{ $agent->id }})"
                                            wire:confirm="Are you sure you want to delete this agent?"
                                            variant="ghost"
                                            size="sm"
                                            class="text-nord11 hover:text-nord11"
                                        >
                                            Delete
                                        </x-ui.button>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-nord3 dark:text-nord4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-4 text-sm text-nord3 dark:text-nord4">No agents found</p>
                                <p class="mt-2">
                                    <x-ui.button href="{{ route('agents.create') }}" wire:navigate variant="primary" size="sm">
                                        Create your first agent
                                    </x-ui.button>
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($agents->hasPages())
            <div class="px-6 py-4 border-t border-nord4 dark:border-nord2">
                {{ $agents->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
