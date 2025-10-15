<div>
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">Workflows</h1>
            <p class="mt-1 text-sm text-nord3 dark:text-nord4">Orchestrate AI agents to automate complex tasks</p>
        </div>
        <x-ui.button href="{{ route('workflows.create') }}" wire:navigate variant="primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Workflow
        </x-ui.button>
    </div>

    <!-- Filters -->
    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-ui.input
                wire:model.live.debounce.300ms="search"
                type="text"
                name="search"
                placeholder="Search workflows..."
            />

            <x-ui.select
                wire:model.live="filterMode"
                name="filterMode"
            >
                <option value="">All Execution Modes</option>
                <option value="series">Series</option>
                <option value="parallel">Parallel</option>
                <option value="dag">DAG</option>
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

    <!-- Workflows Table -->
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-nord4 dark:bg-nord2 border-b border-nord4 dark:border-nord3">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Workflow</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Mode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Triggers</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Executions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-nord4 dark:divide-nord2">
                    @forelse($workflows as $workflow)
                        <tr class="hover:bg-nord4 dark:hover:bg-nord2 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="shrink-0 w-10 h-10 bg-nord14 bg-black/10 dark:bg-black/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-nord14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-nord0 dark:text-nord6">{{ $workflow->name }}</div>
                                        @if($workflow->description)
                                            <div class="text-sm text-nord3 dark:text-nord4">{{ Str::limit($workflow->description, 60) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $modeVariants = [
                                        'series' => 'primary',
                                        'parallel' => 'success',
                                        'dag' => 'info',
                                    ];
                                @endphp
                                <x-ui.badge :variant="$modeVariants[$workflow->execution_mode] ?? 'default'" size="sm">
                                    {{ ucfirst($workflow->execution_mode) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4">
                                @if($workflow->triggers_count > 0)
                                    <x-ui.badge variant="default" size="sm">{{ $workflow->triggers_count }} trigger(s)</x-ui.badge>
                                @else
                                    <span class="text-sm text-nord3 dark:text-nord4">Manual only</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($workflow->executions_count > 0)
                                    <a href="{{ route('workflows.show', $workflow) }}" wire:navigate class="text-sm text-nord8 hover:text-nord7 transition-colors">
                                        {{ $workflow->executions_count }} run(s)
                                    </a>
                                @else
                                    <span class="text-sm text-nord3 dark:text-nord4">Never run</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <button
                                    wire:click="toggleActive({{ $workflow->id }})"
                                    class="inline-flex items-center"
                                >
                                    @if($workflow->is_active)
                                        <x-ui.badge variant="success" size="sm">Active</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="default" size="sm">Inactive</x-ui.badge>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @if($workflow->is_active)
                                        <x-ui.button
                                            wire:click="triggerWorkflow({{ $workflow->id }})"
                                            variant="primary"
                                            size="sm"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Run
                                        </x-ui.button>
                                    @endif

                                    <x-ui.button
                                        href="{{ route('workflows.edit', $workflow) }}"
                                        wire:navigate
                                        variant="ghost"
                                        size="sm"
                                    >
                                        Edit
                                    </x-ui.button>

                                    <x-ui.button
                                        wire:click="deleteWorkflow({{ $workflow->id }})"
                                        wire:confirm="Are you sure you want to delete this workflow?"
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
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-nord3 dark:text-nord4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="mt-4 text-sm text-nord3 dark:text-nord4">No workflows found</p>
                                <p class="mt-2">
                                    <x-ui.button href="{{ route('workflows.create') }}" wire:navigate variant="primary" size="sm">
                                        Create your first workflow
                                    </x-ui.button>
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($workflows->hasPages())
            <div class="px-6 py-4 border-t border-nord4 dark:border-nord2">
                {{ $workflows->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
