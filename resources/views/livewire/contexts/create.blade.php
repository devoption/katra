<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-nord3 dark:text-nord4 mb-2">
            <a href="{{ route('contexts.index') }}" wire:navigate class="hover:text-primary transition-colors">Contexts</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>Create</span>
        </div>
        <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">Create New Context</h1>
        <p class="mt-1 text-sm text-nord3 dark:text-nord4">Store knowledge, documentation, or data that agents can access</p>
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
                            label="Context Name"
                            placeholder="e.g., Laravel Documentation"
                            required
                            :error="$errors->first('name')"
                        />

                        <x-ui.textarea
                            wire:model="description"
                            name="description"
                            label="Description (Optional)"
                            placeholder="What information does this context contain?"
                            :rows="2"
                            :error="$errors->first('description')"
                        />

                        <x-ui.select
                            wire:model="type"
                            name="type"
                            label="Context Type"
                            required
                            :error="$errors->first('type')"
                        >
                            <option value="agent">Agent Context - Knowledge specific to an agent</option>
                            <option value="workflow">Workflow Context - Shared data across workflow steps</option>
                            <option value="execution">Execution Context - Temporary runtime data</option>
                        </x-ui.select>
                        <p class="text-xs text-nord3 dark:text-nord4 -mt-2">
                            @if($type === 'agent')
                                Agent contexts store knowledge that a specific agent can access (e.g., coding standards, documentation)
                            @elseif($type === 'workflow')
                                Workflow contexts are shared between all agents in a workflow
                            @else
                                Execution contexts store temporary data during workflow runs
                            @endif
                        </p>
                    </div>
                </x-ui.card>

                <!-- Content Editor -->
                <x-ui.card title="Content">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-nord3 dark:text-nord4">
                                You can add content now or edit it later
                            </p>
                            <button
                                type="button"
                                @click="$wire.use_json_editor = !$wire.use_json_editor"
                                class="text-xs text-primary hover:text-primary transition-colors"
                            >
                                {{ $use_json_editor ? 'Switch to Simple Editor' : 'Switch to JSON Editor' }}
                            </button>
                        </div>

                        @if($use_json_editor)
                            <x-ui.textarea
                                wire:model="content_json"
                                name="content_json"
                                label="Content (JSON)"
                                :rows="15"
                                :error="$errors->first('content_json')"
                                class="font-mono text-sm"
                                help="Enter structured data in JSON format"
                            />
                        @else
                            <div class="p-4 bg-nord4 dark:bg-nord2 rounded-lg">
                                <p class="text-sm text-nord3 dark:text-nord4">
                                    Start with an empty context. You can add structured data after creation.
                                </p>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Context Types Info -->
                <x-ui.card title="About Contexts">
                    <div class="space-y-3 text-xs text-nord3 dark:text-nord4">
                        <div>
                            <p class="font-medium text-nord0 dark:text-nord6 mb-1">What are contexts?</p>
                            <p>Contexts store knowledge, documentation, guidelines, or data that agents can reference when performing tasks.</p>
                        </div>
                        <div>
                            <p class="font-medium text-nord0 dark:text-nord6 mb-1">Examples:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>Company coding standards</li>
                                <li>API documentation</li>
                                <li>Brand guidelines</li>
                                <li>Process checklists</li>
                            </ul>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Actions -->
                <x-ui.card>
                    <div class="space-y-3">
                        <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                            <span wire:loading.remove>Create Context</span>
                            <span wire:loading class="flex items-center justify-center">
                                <x-ui.loading size="sm" class="mr-2" />
                                Creating...
                            </span>
                        </x-ui.button>

                        <x-ui.button href="{{ route('contexts.index') }}" wire:navigate variant="ghost" class="w-full">
                            Cancel
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </form>
</div>
