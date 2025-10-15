<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-nord3 dark:text-nord4 mb-2">
            <a href="{{ route('agents.index') }}" wire:navigate class="hover:text-nord8 transition-colors">Agents</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>Create</span>
        </div>
        <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">Create New Agent</h1>
        <p class="mt-1 text-sm text-nord3 dark:text-nord4">Configure a new AI agent with custom capabilities</p>
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
                            placeholder="e.g., Code Review Assistant"
                            required
                            :error="$errors->first('name')"
                        />

                        <x-ui.input
                            wire:model="role"
                            type="text"
                            name="role"
                            label="Role"
                            placeholder="e.g., Code Reviewer"
                            required
                            :error="$errors->first('role')"
                            help="What is this agent's primary function?"
                        />

                        <x-ui.textarea
                            wire:model="description"
                            name="description"
                            label="Description"
                            placeholder="Describe what this agent does..."
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
                                placeholder="Enter model identifier"
                                :error="$errors->first('model_name')"
                            />
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
                            @error('creativity_level')
                                <p class="mt-1 text-sm text-nord11">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </x-ui.card>

                <!-- System Prompt -->
                <x-ui.card title="System Prompt">
                    <x-ui.textarea
                        wire:model="system_prompt"
                        name="system_prompt"
                        placeholder="You are an AI assistant that..."
                        :rows="10"
                        required
                        :error="$errors->first('system_prompt')"
                        help="Define the agent's behavior, expertise, and personality"
                    />
                </x-ui.card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Context -->
                <x-ui.card title="Context">
                    <x-ui.select
                        wire:model="context_id"
                        name="context_id"
                        label="Attach Context"
                        :error="$errors->first('context_id')"
                        help="Optional context for this agent"
                    >
                        <option value="">None</option>
                        @foreach($contexts as $context)
                            <option value="{{ $context->id }}">{{ $context->name }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.card>

                <!-- Tools -->
                <x-ui.card title="Tools">
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @forelse($tools as $tool)
                            <label class="flex items-start p-3 rounded-lg hover:bg-nord4 dark:hover:bg-nord2 transition-colors cursor-pointer">
                                <input
                                    type="checkbox"
                                    wire:model="selected_tools"
                                    value="{{ $tool->id }}"
                                    class="mt-0.5 w-4 h-4 rounded border-nord4 dark:border-nord2 text-nord8 focus:ring-2 focus:ring-nord8 focus:ring-offset-0"
                                >
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-nord0 dark:text-nord6">{{ $tool->name }}</div>
                                    <div class="text-xs text-nord3 dark:text-nord4">{{ $tool->category }}</div>
                                </div>
                            </label>
                        @empty
                            <p class="text-sm text-nord3 dark:text-nord4 text-center py-4">No tools available</p>
                        @endforelse
                    </div>
                    @error('selected_tools')
                        <p class="mt-2 text-sm text-nord11">{{ $message }}</p>
                    @enderror
                </x-ui.card>

                <!-- Actions -->
                <x-ui.card>
                    <div class="space-y-3">
                        <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                            <span wire:loading.remove>Create Agent</span>
                            <span wire:loading class="flex items-center justify-center">
                                <x-ui.loading size="sm" class="mr-2" />
                                Creating...
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
</div>
