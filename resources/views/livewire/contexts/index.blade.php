<div>
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">Contexts</h1>
            <p class="mt-1 text-sm text-nord3 dark:text-nord4">Store and manage knowledge that agents can access</p>
        </div>
        <x-ui.button href="{{ route('contexts.create') }}" wire:navigate variant="primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Context
        </x-ui.button>
    </div>

    <!-- Filters -->
    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-ui.input
                wire:model.live.debounce.300ms="search"
                type="text"
                name="search"
                placeholder="Search contexts..."
            />

            <x-ui.select
                wire:model.live="filterType"
                name="filterType"
            >
                <option value="">All Types</option>
                <option value="agent">Agent Context</option>
                <option value="workflow">Workflow Context</option>
                <option value="execution">Execution Context</option>
            </x-ui.select>
        </div>
    </x-ui.card>

    <!-- Contexts Table -->
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-nord4 dark:bg-nord2 border-b border-nord4 dark:border-nord3">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Context</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Usage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-nord4 dark:divide-nord2">
                    @forelse($contexts as $context)
                        <tr class="hover:bg-nord4 dark:hover:bg-nord2 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="shrink-0 w-10 h-10 bg-nord10 bg-black/10 dark:bg-black/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-nord10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-nord0 dark:text-nord6">{{ $context->name }}</div>
                                        @if($context->description)
                                            <div class="text-sm text-nord3 dark:text-nord4">{{ Str::limit($context->description, 60) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $typeVariants = [
                                        'agent' => 'primary',
                                        'workflow' => 'success',
                                        'execution' => 'info',
                                    ];
                                @endphp
                                <x-ui.badge :variant="$typeVariants[$context->type] ?? 'default'" size="sm">
                                    {{ ucfirst($context->type) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $totalUsage = $context->agents_count + $context->workflows_count + $context->workflow_executions_count;
                                @endphp
                                @if($totalUsage > 0)
                                    <div class="flex gap-1">
                                        @if($context->agents_count > 0)
                                            <x-ui.badge variant="default" size="sm">{{ $context->agents_count }} agent(s)</x-ui.badge>
                                        @endif
                                        @if($context->workflows_count > 0)
                                            <x-ui.badge variant="default" size="sm">{{ $context->workflows_count }} workflow(s)</x-ui.badge>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-nord3 dark:text-nord4">Unused</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-nord3 dark:text-nord4">
                                {{ $context->created_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.button
                                        href="{{ route('contexts.edit', $context) }}"
                                        wire:navigate
                                        variant="ghost"
                                        size="sm"
                                    >
                                        Edit
                                    </x-ui.button>

                                    <x-ui.button
                                        wire:click="deleteContext({{ $context->id }})"
                                        wire:confirm="Are you sure you want to delete this context?"
                                        variant="ghost"
                                        size="sm"
                                        class="text-nord11 hover:text-nord11"
                                    >
                                        Delete
                                    </x-ui.button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-nord3 dark:text-nord4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <p class="mt-4 text-sm text-nord3 dark:text-nord4">No contexts found</p>
                                <p class="mt-2">
                                    <x-ui.button href="{{ route('contexts.create') }}" wire:navigate variant="primary" size="sm">
                                        Create your first context
                                    </x-ui.button>
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contexts->hasPages())
            <div class="px-6 py-4 border-t border-nord4 dark:border-nord2">
                {{ $contexts->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
