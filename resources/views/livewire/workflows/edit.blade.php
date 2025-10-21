<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-nord3 dark:text-nord4 mb-2">
            <a href="{{ route('workflows.index') }}" wire:navigate class="hover:text-primary transition-colors">Workflows</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>Edit</span>
        </div>
        <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">Edit Workflow: {{ $workflow->name }}</h1>
        <p class="mt-1 text-sm text-nord3 dark:text-nord4">Update workflow configuration and steps</p>
    </div>

    <!-- Form -->
    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <x-ui.card title="Basic Information">
                    <div class="space-y-4">
                        <x-ui.input
                            wire:model="name"
                            type="text"
                            name="name"
                            label="Workflow Name"
                            required
                            :error="$errors->first('name')"
                        />

                        <x-ui.textarea
                            wire:model="description"
                            name="description"
                            label="Description (Optional)"
                            :rows="2"
                            :error="$errors->first('description')"
                        />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input
                                wire:model="version"
                                type="text"
                                name="version"
                                label="Version"
                                required
                                :error="$errors->first('version')"
                            />

                            <x-ui.select
                                wire:model="execution_mode"
                                name="execution_mode"
                                label="Execution Mode"
                                required
                                :error="$errors->first('execution_mode')"
                            >
                                <option value="series">Series - Steps run one after another</option>
                                <option value="parallel">Parallel - Steps run at the same time</option>
                                <option value="dag">DAG - Steps run based on dependencies</option>
                            </x-ui.select>
                        </div>

                        <x-ui.select
                            wire:model="context_id"
                            name="context_id"
                            label="Shared Context (Optional)"
                            :error="$errors->first('context_id')"
                        >
                            <option value="">None</option>
                            @foreach($contexts as $context)
                                <option value="{{ $context->id }}">{{ $context->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                </x-ui.card>

                <!-- Workflow Definition -->
                <x-ui.card title="Workflow Definition (YAML)">
                    <x-ui.textarea
                        wire:model="definition_yaml"
                        name="definition_yaml"
                        :rows="20"
                        required
                        :error="$errors->first('definition_yaml')"
                        class="font-mono text-sm"
                    />
                </x-ui.card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Workflow Info -->
                <x-ui.card title="Workflow Info">
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-nord3 dark:text-nord4">UUID:</span>
                            <code class="block mt-1 p-2 bg-nord4 dark:bg-nord2 rounded text-xs break-all">{{ $workflow->uuid }}</code>
                        </div>
                        <div>
                            <span class="text-nord3 dark:text-nord4">Executions:</span>
                            <div class="text-nord0 dark:text-nord6 mt-1">
                                <a href="{{ route('workflows.show', $workflow) }}" wire:navigate class="text-primary hover:text-primary">
                                    {{ $workflow->executions()->count() }} run(s)
                                </a>
                            </div>
                        </div>
                        <div>
                            <span class="text-nord3 dark:text-nord4">Created:</span>
                            <div class="text-nord0 dark:text-nord6 mt-1">{{ $workflow->created_at->format('M j, Y') }}</div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Available Agents -->
                <x-ui.card title="Available Agents">
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @forelse($agents as $agent)
                            <div class="p-2 bg-nord4 dark:bg-nord2 rounded text-sm">
                                <div class="font-medium text-nord0 dark:text-nord6">{{ $agent->name }}</div>
                                <div class="text-xs text-nord3 dark:text-nord4">{{ $agent->role }}</div>
                            </div>
                        @empty
                            <p class="text-xs text-nord3 dark:text-nord4 text-center py-4">No agents available</p>
                        @endforelse
                    </div>
                </x-ui.card>

                <!-- Actions -->
                <x-ui.card>
                    <div class="space-y-3">
                        <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                            <span wire:loading.remove>Save Changes</span>
                            <span wire:loading class="flex items-center justify-center">
                                <x-ui.loading size="sm" class="mr-2" />
                                Saving...
                            </span>
                        </x-ui.button>

                        <x-ui.button href="{{ route('workflows.index') }}" wire:navigate variant="ghost" class="w-full">
                            Cancel
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </form>
</div>
