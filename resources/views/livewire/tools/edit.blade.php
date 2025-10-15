<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-nord3 dark:text-nord4 mb-2">
            <a href="{{ route('tools.index') }}" wire:navigate class="hover:text-nord8 transition-colors">Tools</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>{{ $tool->type === 'builtin' ? 'View' : 'Edit' }}</span>
        </div>
        <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">{{ $tool->type === 'builtin' ? 'View' : 'Edit' }} Tool: {{ $tool->name }}</h1>
        <p class="mt-1 text-sm text-nord3 dark:text-nord4">
            @if($tool->type === 'builtin')
                Built-in tools are read-only
            @else
                Update tool configuration
            @endif
        </p>
    </div>

    @if($tool->type === 'builtin')
        <x-ui.alert type="info" class="mb-6">
            <div class="font-medium">Built-in Tool</div>
            <div class="text-sm mt-1">This tool is provided by Katra and cannot be modified. You can view its configuration below.</div>
        </x-ui.alert>
    @endif

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
                            required
                            :disabled="$tool->type === 'builtin'"
                            :error="$errors->first('name')"
                        />

                        <x-ui.textarea
                            wire:model="description"
                            name="description"
                            label="Description"
                            :rows="3"
                            required
                            :disabled="$tool->type === 'builtin'"
                            :error="$errors->first('description')"
                        />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.select
                                wire:model="type"
                                name="type"
                                label="Type"
                                required
                                :disabled="$tool->type === 'builtin'"
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
                                required
                                :disabled="$tool->type === 'builtin'"
                                :error="$errors->first('category')"
                            />
                        </div>

                        <x-ui.input
                            wire:model="execution_method"
                            type="text"
                            name="execution_method"
                            label="Execution Method"
                            :disabled="$tool->type === 'builtin'"
                            :error="$errors->first('execution_method')"
                        />

                        <x-ui.checkbox
                            wire:model="requires_credential"
                            name="requires_credential"
                            label="This tool requires authentication credentials"
                            :disabled="$tool->type === 'builtin'"
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
                        :disabled="$tool->type === 'builtin'"
                        :error="$errors->first('input_schema')"
                        class="font-mono text-sm"
                    />
                </x-ui.card>

                <!-- Output Schema -->
                <x-ui.card title="Output Schema (JSON)">
                    <x-ui.textarea
                        wire:model="output_schema"
                        name="output_schema"
                        :rows="8"
                        :disabled="$tool->type === 'builtin'"
                        :error="$errors->first('output_schema')"
                        class="font-mono text-sm"
                    />
                </x-ui.card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Tool Info -->
                <x-ui.card title="Tool Info">
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-nord3 dark:text-nord4">UUID:</span>
                            <code class="block mt-1 p-2 bg-nord4 dark:bg-nord2 rounded text-xs break-all">{{ $tool->uuid }}</code>
                        </div>
                        <div>
                            <span class="text-nord3 dark:text-nord4">Used By:</span>
                            <div class="text-nord0 dark:text-nord6 mt-1">
                                {{ $tool->agents()->count() }} agent(s)
                            </div>
                        </div>
                        @if($tool->created_by)
                            <div>
                                <span class="text-nord3 dark:text-nord4">Created:</span>
                                <div class="text-nord0 dark:text-nord6 mt-1">{{ $tool->created_at->format('M j, Y g:i A') }}</div>
                            </div>
                            <div>
                                <span class="text-nord3 dark:text-nord4">Created By:</span>
                                <div class="text-nord0 dark:text-nord6 mt-1">{{ $tool->creator->full_name }}</div>
                            </div>
                        @else
                            <div>
                                <x-ui.badge variant="primary" size="sm">System Tool</x-ui.badge>
                            </div>
                        @endif
                    </div>
                </x-ui.card>

                <!-- Actions -->
                @if($tool->type !== 'builtin')
                    <x-ui.card>
                        <div class="space-y-3">
                            <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                                <span wire:loading.remove>Save Changes</span>
                                <span wire:loading class="flex items-center justify-center">
                                    <x-ui.loading size="sm" class="mr-2" />
                                    Saving...
                                </span>
                            </x-ui.button>

                            <x-ui.button href="{{ route('tools.index') }}" wire:navigate variant="ghost" class="w-full">
                                Cancel
                            </x-ui.button>
                        </div>
                    </x-ui.card>
                @else
                    <x-ui.card>
                        <x-ui.button href="{{ route('tools.index') }}" wire:navigate variant="ghost" class="w-full">
                            Back to Tools
                        </x-ui.button>
                    </x-ui.card>
                @endif
            </div>
        </div>
    </form>
</div>
