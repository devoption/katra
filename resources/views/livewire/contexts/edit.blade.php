<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-nord3 dark:text-nord4 mb-2">
            <a href="{{ route('contexts.index') }}" wire:navigate class="hover:text-nord8 transition-colors">Contexts</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>Edit</span>
        </div>
        <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">Edit Context: {{ $context->name }}</h1>
        <p class="mt-1 text-sm text-nord3 dark:text-nord4">Update context information and content</p>
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
                    </div>
                </x-ui.card>

                <!-- Content Editor -->
                <x-ui.card title="Content (JSON)">
                    <p class="text-sm text-nord3 dark:text-nord4 mb-3">
                        Store structured data, documentation, guidelines, or any information agents should know
                    </p>
                    <x-ui.textarea
                        wire:model="content_json"
                        name="content_json"
                        :rows="20"
                        required
                        :error="$errors->first('content_json')"
                        class="font-mono text-sm"
                        help="Structured data in JSON format"
                    />
                </x-ui.card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Context Info -->
                <x-ui.card title="Context Info">
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-nord3 dark:text-nord4">UUID:</span>
                            <code class="block mt-1 p-2 bg-nord4 dark:bg-nord2 rounded text-xs break-all">{{ $context->uuid }}</code>
                        </div>
                        <div>
                            <span class="text-nord3 dark:text-nord4">Used By:</span>
                            <div class="text-nord0 dark:text-nord6 mt-1">
                                @php
                                    $usage = [];
                                    if($context->agents()->count() > 0) $usage[] = $context->agents()->count() . ' agent(s)';
                                    if($context->workflows()->count() > 0) $usage[] = $context->workflows()->count() . ' workflow(s)';
                                @endphp
                                {{ empty($usage) ? 'Not used yet' : implode(', ', $usage) }}
                            </div>
                        </div>
                        <div>
                            <span class="text-nord3 dark:text-nord4">Created:</span>
                            <div class="text-nord0 dark:text-nord6 mt-1">{{ $context->created_at->format('M j, Y g:i A') }}</div>
                        </div>
                        <div>
                            <span class="text-nord3 dark:text-nord4">Created By:</span>
                            <div class="text-nord0 dark:text-nord6 mt-1">{{ $context->creator->full_name }}</div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Content Examples -->
                <x-ui.card title="Example Content">
                    <div class="text-xs">
                        <p class="font-medium text-nord0 dark:text-nord6 mb-2">Simple data:</p>
                        <pre class="p-2 bg-nord4 dark:bg-nord2 rounded overflow-x-auto">{
  "guidelines": [
    "Use clear variable names",
    "Write tests for all features"
  ],
  "standards": {
    "indent": "4 spaces",
    "max_line_length": 120
  }
}</pre>
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

                        <x-ui.button href="{{ route('contexts.index') }}" wire:navigate variant="ghost" class="w-full">
                            Cancel
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </form>
</div>
