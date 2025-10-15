<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-nord3 dark:text-nord4 mb-2">
            <a href="{{ route('agents.index') }}" wire:navigate class="hover:text-nord8 transition-colors">Agents</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>Edit</span>
        </div>
        <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">Edit Agent: {{ $agent->name }}</h1>
        <p class="mt-1 text-sm text-nord3 dark:text-nord4">Modify agent configuration and capabilities</p>
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
                            label="Agent Name"
                            required
                            :error="$errors->first('name')"
                        />

                        <x-ui.input
                            wire:model="role"
                            type="text"
                            name="role"
                            label="Role"
                            required
                            :error="$errors->first('role')"
                        />

                        <x-ui.textarea
                            wire:model="description"
                            name="description"
                            label="Description"
                            :rows="3"
                            :error="$errors->first('description')"
                        />
                    </div>
                </x-ui.card>

                <!-- Model Configuration -->
                <x-ui.card title="Model Configuration">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.select
                                wire:model.live="model_provider"
                                name="model_provider"
                                label="Provider"
                                required
                                :error="$errors->first('model_provider')"
                            >
                                <option value="openai">OpenAI</option>
                                <option value="anthropic">Anthropic</option>
                                <option value="google">Google</option>
                                <option value="ollama">Ollama</option>
                                <option value="custom">Custom</option>
                            </x-ui.select>

                            <x-ui.select
                                wire:model="model_name"
                                name="model_name"
                                label="Model"
                                required
                                :error="$errors->first('model_name')"
                            >
                                @foreach($modelOptions[$model_provider] ?? [] as $model)
                                    <option value="{{ $model }}">{{ $model }}</option>
                                @endforeach
                                @if(empty($modelOptions[$model_provider]))
                                    <option value="">Enter custom model name below</option>
                                @endif
                            </x-ui.select>
                        </div>

                        @if($model_provider === 'custom' || empty($modelOptions[$model_provider]))
                            <x-ui.input
                                wire:model="model_name"
                                type="text"
                                name="custom_model_name"
                                label="Custom Model Name"
                                placeholder="e.g., my-custom-model-v1"
                                :error="$errors->first('model_name')"
                            />

                            <x-ui.input
                                wire:model="custom_api_endpoint"
                                type="url"
                                name="custom_api_endpoint"
                                label="API Endpoint"
                                placeholder="https://api.example.com/v1/chat/completions"
                                required
                                :error="$errors->first('custom_api_endpoint')"
                                help="The base URL for your custom model API"
                            />

                            <x-ui.select
                                wire:model="custom_auth_type"
                                name="custom_auth_type"
                                label="Authentication Type"
                            >
                                <option value="bearer">Bearer Token</option>
                                <option value="api_key">API Key</option>
                                <option value="custom">Custom Headers</option>
                            </x-ui.select>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-nord0 dark:text-nord4 mb-2">
                                Creativity Level: <span class="text-nord8">{{ number_format($creativity_level, 2) }}</span>
                            </label>
                            <input
                                type="range"
                                wire:model.live="creativity_level"
                                min="0"
                                max="1"
                                step="0.01"
                                class="w-full h-2 bg-nord4 dark:bg-nord2 rounded-lg appearance-none cursor-pointer accent-nord8"
                            >
                            <div class="flex justify-between text-xs text-nord3 dark:text-nord4 mt-1">
                                <span>Focused (0.0)</span>
                                <span>Balanced (0.5)</span>
                                <span>Creative (1.0)</span>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- System Prompt -->
                <x-ui.card title="System Prompt">
                    <x-ui.textarea
                        wire:model="system_prompt"
                        name="system_prompt"
                        :rows="10"
                        required
                        :error="$errors->first('system_prompt')"
                    />
                </x-ui.card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Agent Info -->
                <x-ui.card title="Agent Info">
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-nord3 dark:text-nord4">UUID:</span>
                            <code class="block mt-1 p-2 bg-nord4 dark:bg-nord2 rounded text-xs break-all">{{ $agent->uuid }}</code>
                        </div>
                        <div>
                            <span class="text-nord3 dark:text-nord4">Created:</span>
                            <div class="text-nord0 dark:text-nord6 mt-1">{{ $agent->created_at->format('M j, Y g:i A') }}</div>
                        </div>
                        <div>
                            <span class="text-nord3 dark:text-nord4">Created By:</span>
                            <div class="text-nord0 dark:text-nord6 mt-1">{{ $agent->creator->full_name }}</div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Context -->
                <x-ui.card title="Context">
                    <div class="space-y-3">
                        <x-ui.select
                            wire:model="context_id"
                            name="context_id"
                            label="Attach Context"
                            :error="$errors->first('context_id')"
                        >
                            <option value="">None</option>
                            @foreach($contexts as $context)
                                <option value="{{ $context->id }}">{{ $context->name }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.button
                            type="button"
                            @click="$wire.show_create_context_modal = true"
                            variant="outline"
                            size="sm"
                            class="w-full"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Create New Context
                        </x-ui.button>
                    </div>
                </x-ui.card>

                <!-- Tools -->
                <x-ui.card title="Tools">
                    <div class="space-y-3">
                        <!-- Tool Search -->
                        <x-ui.input
                            wire:model.live.debounce.300ms="tool_search"
                            type="text"
                            name="tool_search"
                            placeholder="Search tools..."
                        />

                        <!-- Selected Tools Count -->
                        @if(count($selected_tools) > 0)
                            <div class="text-sm text-nord8">
                                {{ count($selected_tools) }} tool(s) selected
                            </div>
                        @endif

                        <!-- Tools List -->
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            @forelse($tools as $tool)
                                <label class="flex items-start p-3 rounded-lg hover:bg-nord4 dark:hover:bg-nord2 transition-colors cursor-pointer">
                                    <input
                                        type="checkbox"
                                        wire:model="selected_tools"
                                        value="{{ $tool->id }}"
                                        class="mt-0.5 w-4 h-4 rounded border-nord4 dark:border-nord2 text-nord8 focus:ring-2 focus:ring-nord8 focus:ring-offset-0"
                                    >
                                    <div class="ml-3 flex-1">
                                        <div class="text-sm font-medium text-nord0 dark:text-nord6">{{ $tool->name }}</div>
                                        <div class="text-xs text-nord3 dark:text-nord4">
                                            <x-ui.badge variant="default" size="sm">{{ $tool->category }}</x-ui.badge>
                                            @if($tool->type === 'builtin')
                                                <x-ui.badge variant="primary" size="sm" class="ml-1">Built-in</x-ui.badge>
                                            @endif
                                        </div>
                                        @if($tool->description)
                                            <div class="text-xs text-nord3 dark:text-nord4 mt-1">{{ Str::limit($tool->description, 60) }}</div>
                                        @endif
                                    </div>
                                </label>
                            @empty
                                <p class="text-sm text-nord3 dark:text-nord4 text-center py-4">
                                    @if($tool_search)
                                        No tools found matching "{{ $tool_search }}"
                                    @else
                                        No tools available
                                    @endif
                                </p>
                            @endforelse
                        </div>
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

                        <x-ui.button href="{{ route('agents.index') }}" wire:navigate variant="ghost" class="w-full">
                            Cancel
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </form>

    <!-- Create Context Modal -->
    <x-ui.modal name="create-context" :show="$show_create_context_modal" max-width="md">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-nord0 dark:text-nord6 mb-4">Create New Context</h3>

            <form wire:submit="createContext" class="space-y-4">
                <x-ui.input
                    wire:model="new_context_name"
                    type="text"
                    name="new_context_name"
                    label="Context Name"
                    placeholder="e.g., Code Review Guidelines"
                    required
                    autofocus
                    :error="$errors->first('new_context_name')"
                />

                <x-ui.textarea
                    wire:model="new_context_description"
                    name="new_context_description"
                    label="Description"
                    placeholder="Describe the purpose of this context..."
                    :rows="3"
                    :error="$errors->first('new_context_description')"
                />

                <div class="flex gap-3 pt-2">
                    <x-ui.button type="submit" variant="primary" class="flex-1" wire:loading.attr="disabled">
                        <span wire:loading.remove>Create Context</span>
                        <span wire:loading class="flex items-center justify-center">
                            <x-ui.loading size="sm" class="mr-2" />
                            Creating...
                        </span>
                    </x-ui.button>

                    <x-ui.button
                        type="button"
                        @click="$wire.show_create_context_modal = false; $wire.reset(['new_context_name', 'new_context_description'])"
                        variant="ghost"
                        class="flex-1"
                    >
                        Cancel
                    </x-ui.button>
                </div>
            </form>
        </div>
    </x-ui.modal>
</div>
