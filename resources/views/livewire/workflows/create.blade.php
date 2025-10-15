<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-nord3 dark:text-nord4 mb-2">
            <a href="{{ route('workflows.index') }}" wire:navigate class="hover:text-nord8 transition-colors">Workflows</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>Create</span>
        </div>
        <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">Create New Workflow</h1>
        <p class="mt-1 text-sm text-nord3 dark:text-nord4">Build an automated workflow by orchestrating multiple AI agents</p>
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
                            placeholder="e.g., Content Creation Pipeline"
                            required
                            :error="$errors->first('name')"
                        />

                        <x-ui.textarea
                            wire:model="description"
                            name="description"
                            label="Description (Optional)"
                            placeholder="What does this workflow accomplish?"
                            :rows="2"
                            :error="$errors->first('description')"
                        />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input
                                wire:model="version"
                                type="text"
                                name="version"
                                label="Version"
                                placeholder="1.0"
                                required
                                :error="$errors->first('version')"
                                help="Semantic version number"
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
                            help="Context shared across all agents in this workflow"
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
                    <p class="text-sm text-nord3 dark:text-nord4 mb-3">
                        Define the steps in your workflow using YAML format
                    </p>
                    <x-ui.textarea
                        wire:model="definition_yaml"
                        name="definition_yaml"
                        :rows="20"
                        required
                        :error="$errors->first('definition_yaml')"
                        class="font-mono text-sm"
                        help="Use YAML to define workflow steps, agents, and dependencies"
                    />
                </x-ui.card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
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
                    <p class="text-xs text-nord3 dark:text-nord4 mt-3">
                        Use these agent names in your workflow steps
                    </p>
                </x-ui.card>

                <!-- YAML Help -->
                <x-ui.card title="YAML Example">
                    <div class="text-xs">
                        <pre class="p-3 bg-nord4 dark:bg-nord2 rounded overflow-x-auto">steps:
  - name: research
    agent: Researcher Agent
    description: Gather information
    
  - name: write
    agent: Writer Agent
    description: Create content
    depends_on:
      - research
      
  - name: review
    agent: Editor Agent
    description: Review and edit
    depends_on:
      - write</pre>
                    </div>
                </x-ui.card>

                <!-- Actions -->
                <x-ui.card>
                    <div class="space-y-3">
                        <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                            <span wire:loading.remove>Create Workflow</span>
                            <span wire:loading class="flex items-center justify-center">
                                <x-ui.loading size="sm" class="mr-2" />
                                Creating...
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
