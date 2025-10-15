<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-nord3 dark:text-nord4 mb-2">
            <a href="{{ route('tools.index') }}" wire:navigate class="hover:text-nord8 transition-colors">Tools</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>Create</span>
        </div>
        <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">Create New Tool</h1>
        <p class="mt-1 text-sm text-nord3 dark:text-nord4">Define a custom tool for your agents to use</p>
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
                            label="Tool Name"
                            placeholder="e.g., Deploy to Production"
                            required
                            :error="$errors->first('name')"
                        />

                        <x-ui.textarea
                            wire:model="description"
                            name="description"
                            label="Description"
                            placeholder="What does this tool do?"
                            :rows="3"
                            required
                            :error="$errors->first('description')"
                        />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.select
                                wire:model="type"
                                name="type"
                                label="Type"
                                required
                                :error="$errors->first('type')"
                            >
                                <option value="custom">Custom</option>
                                <option value="mcp_server">MCP Server</option>
                                <option value="package">Package</option>
                            </x-ui.select>

                            <x-ui.input
                                wire:model="category"
                                type="text"
                                name="category"
                                label="Category"
                                placeholder="e.g., deployment"
                                required
                                :error="$errors->first('category')"
                                help="Group similar tools together"
                            />
                        </div>

                        <x-ui.input
                            wire:model="execution_method"
                            type="text"
                            name="execution_method"
                            label="Execution Method"
                            placeholder="e.g., script, api, internal"
                            :error="$errors->first('execution_method')"
                            help="How this tool will be executed"
                        />

                        <x-ui.checkbox
                            wire:model="requires_credential"
                            name="requires_credential"
                            label="This tool requires authentication credentials"
                        />
                    </div>
                </x-ui.card>

                <!-- Input Schema -->
                <x-ui.card title="Input Schema (JSON)">
                    <x-ui.textarea
                        wire:model="input_schema"
                        name="input_schema"
                        :rows="12"
                        required
                        :error="$errors->first('input_schema')"
                        help="JSON Schema defining the tool's input parameters"
                        class="font-mono text-sm"
                    />
                </x-ui.card>

                <!-- Output Schema -->
                <x-ui.card title="Output Schema (JSON)">
                    <x-ui.textarea
                        wire:model="output_schema"
                        name="output_schema"
                        :rows="8"
                        :error="$errors->first('output_schema')"
                        help="Optional JSON Schema defining the tool's output"
                        class="font-mono text-sm"
                    />
                </x-ui.card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Schema Help -->
                <x-ui.card title="JSON Schema Help">
                    <div class="space-y-3 text-xs text-nord3 dark:text-nord4">
                        <div>
                            <p class="font-medium text-nord0 dark:text-nord6 mb-1">Basic Types:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>string</li>
                                <li>number</li>
                                <li>boolean</li>
                                <li>object</li>
                                <li>array</li>
                            </ul>
                        </div>
                        <div>
                            <p class="font-medium text-nord0 dark:text-nord6 mb-1">Example:</p>
                            <pre class="p-2 bg-nord4 dark:bg-nord2 rounded text-xs overflow-x-auto">{
  "type": "object",
  "properties": {
    "file": {
      "type": "string"
    }
  },
  "required": ["file"]
}</pre>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Actions -->
                <x-ui.card>
                    <div class="space-y-3">
                        <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                            <span wire:loading.remove>Create Tool</span>
                            <span wire:loading class="flex items-center justify-center">
                                <x-ui.loading size="sm" class="mr-2" />
                                Creating...
                            </span>
                        </x-ui.button>

                        <x-ui.button href="{{ route('tools.index') }}" wire:navigate variant="ghost" class="w-full">
                            Cancel
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </form>
</div>
