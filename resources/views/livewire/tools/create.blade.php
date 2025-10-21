<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-nord3 dark:text-nord4 mb-2">
            <a href="{{ route('tools.index') }}" wire:navigate class="hover:text-primary transition-colors">Tools</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>Create</span>
        </div>
        <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">Create New Tool</h1>
        <p class="mt-1 text-sm text-nord3 dark:text-nord4">Define a tool that your agents can use to perform actions</p>
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
                            placeholder="Explain what this tool does and when agents should use it..."
                            :rows="3"
                            required
                            :error="$errors->first('description')"
                            help="Be clear about what this tool does so agents know when to use it"
                        />

                        <x-ui.select
                            wire:model.live="type"
                            name="type"
                            label="Tool Type"
                            required
                            :error="$errors->first('type')"
                        >
                            <option value="custom">Custom - A tool you define with input/output schemas</option>
                            <option value="mcp_server">MCP Server - Connect to an external MCP server</option>
                            <option value="package">Package - Install from Composer marketplace</option>
                        </x-ui.select>
                        <p class="text-xs text-nord3 dark:text-nord4 -mt-2">
                            @if($type === 'custom')
                                Create a custom tool by defining how it receives and returns data
                            @elseif($type === 'mcp_server')
                                Connect to an external Model Context Protocol server for advanced integrations
                            @else
                                Install a pre-built tool package from the Composer marketplace
                            @endif
                        </p>

                        <!-- Category -->
                        <div>
                            <x-ui.select
                                wire:model.live="category"
                                name="category"
                                label="Category (Optional)"
                                :error="$errors->first('category')"
                                help="Helps organize tools by function"
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

                            @if($category === 'other')
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
                            />
                        </div>

                        @if($requires_credential)
                            <div class="pl-6 pt-2 border-l-2 border-primary">
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
                                                class="mt-0.5 w-4 h-4 rounded border-nord4 dark:border-nord2 text-primary focus:ring-2 focus:ring-primary focus:ring-offset-0"
                                            >
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-nord0 dark:text-nord6">{{ $credential->name }}</div>
                                                @if($credential->provider)
                                                    <div class="text-xs text-nord3 dark:text-nord4">{{ ucfirst($credential->provider) }}</div>
                                                @endif
                                            </div>
                                        </label>
                                    @empty
                                        <p class="text-xs text-nord12 text-center py-2">No credentials available. Admins can create them in the Credentials section.</p>
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
                            :error="$errors->first('output_schema')"
                            class="font-mono text-sm"
                        />
                    </x-ui.card>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                @if($type === 'custom')
                    <!-- Schema Help -->
                    <x-ui.card title="Schema Guide">
                        <div class="space-y-3 text-xs">
                            <div>
                                <p class="font-medium text-nord0 dark:text-nord6 mb-2">Common Types:</p>
                                <ul class="list-disc list-inside space-y-1 text-nord3 dark:text-nord4">
                                    <li><code class="text-primary">string</code> - Text</li>
                                    <li><code class="text-primary">number</code> - Numbers</li>
                                    <li><code class="text-primary">boolean</code> - True/False</li>
                                    <li><code class="text-primary">array</code> - Lists</li>
                                    <li><code class="text-primary">object</code> - Complex data</li>
                                </ul>
                            </div>
                            <div>
                                <p class="font-medium text-nord0 dark:text-nord6 mb-1">Example:</p>
                                <pre class="p-2 bg-nord4 dark:bg-nord2 rounded text-xs overflow-x-auto">{
  "type": "object",
  "properties": {
    "filename": {
      "type": "string",
      "description": "File to process"
    },
    "count": {
      "type": "number"
    }
  },
  "required": ["filename"]
}</pre>
                            </div>
                        </div>
                    </x-ui.card>
                @else
                    <!-- Help for MCP/Package -->
                    <x-ui.card title="About {{ ucfirst(str_replace('_', ' ', $type)) }}">
                        <div class="text-sm text-nord3 dark:text-nord4">
                            @if($type === 'mcp_server')
                                <p>MCP (Model Context Protocol) servers provide pre-built tools that follow a standard interface. You'll configure the connection details after creation.</p>
                            @else
                                <p>Tool packages can be installed from Composer. The package will provide its own schemas and configuration.</p>
                            @endif
                        </div>
                    </x-ui.card>
                @endif

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
