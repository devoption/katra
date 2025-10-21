<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-nord3 dark:text-nord4 mb-2">
            <a href="{{ route('credentials.index') }}" wire:navigate class="hover:text-primary transition-colors">Credentials</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>Create</span>
        </div>
        <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">Add New Credential</h1>
        <p class="mt-1 text-sm text-nord3 dark:text-nord4">Securely store API keys and authentication tokens</p>
    </div>

    <!-- Security Warning -->
    <x-ui.alert type="warning" class="mb-6">
        <div class="font-medium mb-1">Security Notice</div>
        <div class="text-sm">
            Credentials are encrypted at rest. Only administrators can view the actual values. Regular users can select credentials for their agents but cannot see the secret values.
        </div>
    </x-ui.alert>

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
                            label="Credential Name"
                            placeholder="e.g., OpenAI Production API Key"
                            required
                            :error="$errors->first('name')"
                        />

                        <x-ui.textarea
                            wire:model="description"
                            name="description"
                            label="Description"
                            placeholder="What is this credential used for?"
                            :rows="2"
                            :error="$errors->first('description')"
                        />
                    </div>
                </x-ui.card>

                <!-- Credential Configuration -->
                <x-ui.card title="Credential Configuration">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.select
                                wire:model="type"
                                name="type"
                                label="Type"
                                required
                                :error="$errors->first('type')"
                            >
                                <option value="api_key">API Key</option>
                                <option value="oauth">OAuth Token</option>
                                <option value="password">Password</option>
                                <option value="certificate">Certificate</option>
                                <option value="custom">Custom</option>
                            </x-ui.select>

                            <x-ui.select
                                wire:model="provider"
                                name="provider"
                                label="Provider"
                                :error="$errors->first('provider')"
                            >
                                <option value="">None</option>
                                @foreach($providerOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <x-ui.textarea
                            wire:model="value"
                            name="value"
                            label="Credential Value"
                            placeholder="Enter the API key, token, or secret..."
                            :rows="4"
                            required
                            :error="$errors->first('value')"
                            help="This value will be encrypted and stored securely"
                        />
                    </div>
                </x-ui.card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Help -->
                <x-ui.card title="Common Providers">
                    <div class="space-y-2 text-sm text-nord3 dark:text-nord4">
                        <p><strong class="text-nord0 dark:text-nord6">OpenAI:</strong> Get your key at platform.openai.com</p>
                        <p><strong class="text-nord0 dark:text-nord6">Anthropic:</strong> console.anthropic.com</p>
                        <p><strong class="text-nord0 dark:text-nord6">Google:</strong> console.cloud.google.com</p>
                        <p class="pt-2 text-xs">Select the appropriate provider to help filter credentials when creating agents.</p>
                    </div>
                </x-ui.card>

                <!-- Actions -->
                <x-ui.card>
                    <div class="space-y-3">
                        <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                            <span wire:loading.remove>Create Credential</span>
                            <span wire:loading class="flex items-center justify-center">
                                <x-ui.loading size="sm" class="mr-2" />
                                Creating...
                            </span>
                        </x-ui.button>

                        <x-ui.button href="{{ route('credentials.index') }}" wire:navigate variant="ghost" class="w-full">
                            Cancel
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </form>
</div>
