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

                        <x-ui.select
                            wire:model.live="type"
                            name="type"
                            label="Tool Type"
                            required
                            :disabled="$tool->type === 'builtin'"
                            :error="$errors->first('type')"
                        >
                            <option value="custom">Custom - A tool you define with input/output schemas</option>
                            <option value="mcp_server">MCP Server - Connect to an external MCP server</option>
                            <option value="package">Package - Install from Composer marketplace</option>
                        </x-ui.select>

                        <!-- Category -->
                        <div>
                            <x-ui.select
                                wire:model.live="category"
                                name="category"
                                label="Category (Optional)"
                                :disabled="$tool->type === 'builtin'"
                                :error="$errors->first('category')"
                            >
                                <option value="">None</option>
                                <option value="file">File Operations</option>
                                <option value="git">Git & Version Control</option>
                                <option value="http">HTTP & API Requests</option>
                                <option value="database">Database Operations</option>
                                <option value="communication">Communication (Email, Slack, etc.)</option>
                                <option value="deployment">Deployment & DevOps</option>
                                <option value="testing">Testing & Quality Assurance</option>
                                <option value="other">Other (specify below)</option>
                            </x-ui.select>

                            @if($category === 'other' && $tool->type !== 'builtin')
                                <x-ui.input
                                    wire:model="custom_category"
                                    type="text"
                                    name="custom_category"
                                    placeholder="Enter custom category name"
                                    required
                                    class="mt-3"
                                    :error="$errors->first('custom_category')"
                                />
                            @endif
                        </div>

                        <div class="pt-2">
                            <x-ui.checkbox
                                wire:model.live="requires_credential"
                                name="requires_credential"
                                label="This tool requires authentication (API keys, tokens, etc.)"
                                :disabled="$tool->type === 'builtin'"
                            />
                        </div>

                        @if($requires_credential && $tool->type !== 'builtin')
                            <div class="pl-6 pt-2 border-l-2 border-nord8">
                                <label class="block text-sm font-medium text-nord0 dark:text-nord4 mb-2">
                                    Select Credentials
                                </label>
                                <div class="space-y-2 max-h-48 overflow-y-auto p-3 bg-nord4 dark:bg-nord2 rounded-lg">
                                    @forelse($credentials as $credential)
                                        <label class="flex items-start p-2 rounded hover:bg-nord5 dark:hover:bg-nord1 transition-colors cursor-pointer">
                                            <input
                                                type="checkbox"
                                                wire:model="selected_credentials"
                                                value="{{ $credential->id }}"
                                                class="mt-0.5 w-4 h-4 rounded border-nord4 dark:border-nord2 text-nord8 focus:ring-2 focus:ring-nord8 focus:ring-offset-0"
                                            >
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-nord0 dark:text-nord6">{{ $credential->name }}</div>
                                                @if($credential->provider)
                                                    <div class="text-xs text-nord3 dark:text-nord4">{{ ucfirst($credential->provider) }}</div>
                                                @endif
                                            </div>
                                        </label>
                                    @empty
                                        <p class="text-xs text-nord12 text-center py-2">No credentials available. Admins can create them.</p>
                                    @endforelse
                                </div>
                                <p class="text-xs text-nord3 dark:text-nord4 mt-2">
                                    Agents using this tool can choose from these credentials
                                </p>
                            </div>
                        @endif
                    </div>
                </x-ui.card>

                <!-- JSON Schemas - Only for Custom Tools -->
                @if($type === 'custom')
                    <x-ui.card title="Input Parameters">
                        <p class="text-sm text-nord3 dark:text-nord4 mb-3">
                            Define what information the agent needs to provide when using this tool
                        </p>
                        <x-ui.textarea
                            wire:model="input_schema"
                            name="input_schema"
                            label="Input Schema (JSON)"
                            :rows="12"
                            required
                            :disabled="$tool->type === 'builtin'"
                            :error="$errors->first('input_schema')"
                            class="font-mono text-sm"
                        />
                    </x-ui.card>

                    <x-ui.card title="Output Format (Optional)">
                        <p class="text-sm text-nord3 dark:text-nord4 mb-3">
                            Define what data this tool returns after execution
                        </p>
                        <x-ui.textarea
                            wire:model="output_schema"
                            name="output_schema"
                            label="Output Schema (JSON) - Optional"
                            :rows="8"
                            :disabled="$tool->type === 'builtin'"
                            :error="$errors->first('output_schema')"
                            class="font-mono text-sm"
                        />
                    </x-ui.card>
                @endif
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

                @if($type === 'custom' && $tool->type !== 'builtin')
                    <!-- Schema Help -->
                    <x-ui.card title="Schema Guide">
                        <div class="space-y-3 text-xs">
                            <div>
                                <p class="font-medium text-nord0 dark:text-nord6 mb-2">Common Types:</p>
                                <ul class="list-disc list-inside space-y-1 text-nord3 dark:text-nord4">
                                    <li><code class="text-nord8">string</code> - Text</li>
                                    <li><code class="text-nord8">number</code> - Numbers</li>
                                    <li><code class="text-nord8">boolean</code> - True/False</li>
                                    <li><code class="text-nord8">array</code> - Lists</li>
                                    <li><code class="text-nord8">object</code> - Complex data</li>
                                </ul>
                            </div>
                        </div>
                    </x-ui.card>
                @endif

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
