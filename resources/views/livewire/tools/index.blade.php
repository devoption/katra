<div>
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">Tools</h1>
            <p class="mt-1 text-sm text-nord3 dark:text-nord4">Manage built-in and custom tools for your agents</p>
        </div>
        <x-ui.button href="{{ route('tools.create') }}" wire:navigate variant="primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Tool
        </x-ui.button>
    </div>

    <!-- Filters -->
    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-ui.input
                wire:model.live.debounce.300ms="search"
                type="text"
                name="search"
                placeholder="Search tools..."
            />

            <x-ui.select
                wire:model.live="filterType"
                name="filterType"
            >
                <option value="">All Types</option>
                <option value="builtin">Built-in</option>
                <option value="custom">Custom</option>
                <option value="mcp_server">MCP Server</option>
                <option value="package">Package</option>
            </x-ui.select>

            <x-ui.select
                wire:model.live="filterCategory"
                name="filterCategory"
            >
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}">{{ ucfirst($category) }}</option>
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

    <!-- Tools Table -->
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-nord4 dark:bg-nord2 border-b border-nord4 dark:border-nord3">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Tool</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Usage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-nord4 dark:divide-nord2">
                    @forelse($tools as $tool)
                        <tr class="hover:bg-nord4 dark:hover:bg-nord2 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="shrink-0 w-10 h-10 bg-nord9 bg-black/10 dark:bg-black/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-nord9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-nord0 dark:text-nord6">{{ $tool->name }}</div>
                                        <div class="text-sm text-nord3 dark:text-nord4">{{ Str::limit($tool->description, 60) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($tool->category)
                                    <x-ui.badge variant="default" size="sm">{{ ucfirst($tool->category) }}</x-ui.badge>
                                @else
                                    <span class="text-sm text-nord3 dark:text-nord4">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $typeVariants = [
                                        'builtin' => 'primary',
                                        'custom' => 'success',
                                        'mcp_server' => 'info',
                                        'package' => 'warning',
                                    ];
                                @endphp
                                <x-ui.badge :variant="$typeVariants[$tool->type] ?? 'default'" size="sm">
                                    {{ str_replace('_', ' ', ucfirst($tool->type)) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4">
                                @if($tool->agents_count > 0)
                                    <x-ui.badge variant="primary" size="sm">{{ $tool->agents_count }} agent(s)</x-ui.badge>
                                @else
                                    <span class="text-sm text-nord3 dark:text-nord4">Unused</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <button
                                    wire:click="toggleActive({{ $tool->id }})"
                                    class="inline-flex items-center"
                                    @if($tool->type === 'builtin') disabled title="Built-in tools cannot be disabled" @endif
                                >
                                    @if($tool->is_active)
                                        <x-ui.badge variant="success" size="sm">Active</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="default" size="sm">Inactive</x-ui.badge>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.button
                                        href="{{ route('tools.edit', $tool) }}"
                                        wire:navigate
                                        variant="ghost"
                                        size="sm"
                                    >
                                        {{ $tool->type === 'builtin' ? 'View' : 'Edit' }}
                                    </x-ui.button>
                                    @if($tool->type !== 'builtin')
                                        <x-ui.button
                                            wire:click="deleteTool({{ $tool->id }})"
                                            wire:confirm="Are you sure you want to delete this tool?"
                                            variant="ghost"
                                            size="sm"
                                            class="text-nord11 hover:text-nord11"
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="mt-4 text-sm text-nord3 dark:text-nord4">No tools found</p>
                                <p class="mt-2">
                                    <x-ui.button href="{{ route('tools.create') }}" wire:navigate variant="primary" size="sm">
                                        Create your first custom tool
                                    </x-ui.button>
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tools->hasPages())
            <div class="px-6 py-4 border-t border-nord4 dark:border-nord2">
                {{ $tools->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
