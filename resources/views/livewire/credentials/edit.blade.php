<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-nord3 dark:text-nord4 mb-2">
            <a href="{{ route('credentials.index') }}" wire:navigate class="hover:text-primary transition-colors">Credentials</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>Edit</span>
        </div>
        <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">Edit Credential: {{ $credential->name }}</h1>
        <p class="mt-1 text-sm text-nord3 dark:text-nord4">Update credential information</p>
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
                            label="Credential Name"
                            required
                            :error="$errors->first('name')"
                        />

                        <x-ui.textarea
                            wire:model="description"
                            name="description"
                            label="Description"
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

                        <!-- Update Value Toggle -->
                        <div class="p-4 bg-nord4 dark:bg-nord2 rounded-lg">
                            <x-ui.checkbox
                                wire:model.live="update_value"
                                name="update_value"
                                label="Update credential value"
                            />
                            <p class="text-xs text-nord3 dark:text-nord4 mt-2">
                                Check this box if you want to change the stored credential value. Leave unchecked to keep the current value.
                            </p>
                        </div>

                        @if($update_value)
                            <x-ui.textarea
                                wire:model="value"
                                name="value"
                                label="New Credential Value"
                                placeholder="Enter the new API key, token, or secret..."
                                :rows="4"
                                required
                                :error="$errors->first('value')"
                                help="This value will be encrypted and stored securely"
                            />
                        @endif
                    </div>
                </x-ui.card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Credential Info -->
                <x-ui.card title="Credential Info">
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-nord3 dark:text-nord4">UUID:</span>
                            <code class="block mt-1 p-2 bg-nord4 dark:bg-nord2 rounded text-xs break-all">{{ $credential->uuid }}</code>
                        </div>
                        <div>
                            <span class="text-nord3 dark:text-nord4">Used By:</span>
                            <div class="text-nord0 dark:text-nord6 mt-1">
                                {{ $credential->agents()->count() }} agent(s)
                            </div>
                        </div>
                        <div>
                            <span class="text-nord3 dark:text-nord4">Created:</span>
                            <div class="text-nord0 dark:text-nord6 mt-1">{{ $credential->created_at->format('M j, Y g:i A') }}</div>
                        </div>
                        <div>
                            <span class="text-nord3 dark:text-nord4">Created By:</span>
                            <div class="text-nord0 dark:text-nord6 mt-1">{{ $credential->creator->full_name }}</div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Current Value (View Only) -->
                <x-ui.card title="Current Value">
                    <div class="p-3 bg-nord4 dark:bg-nord2 rounded-lg">
                        <div class="flex items-center gap-2 text-sm text-nord3 dark:text-nord4">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>Value is encrypted and hidden</span>
                        </div>
                    </div>
                    <p class="text-xs text-nord3 dark:text-nord4 mt-2">
                        View the actual value from the credentials list page.
                    </p>
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

                        <x-ui.button href="{{ route('credentials.index') }}" wire:navigate variant="ghost" class="w-full">
                            Cancel
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </form>
</div>
