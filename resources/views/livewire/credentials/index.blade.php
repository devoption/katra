<div>
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-nord0 dark:text-nord6">Credentials</h1>
            <p class="mt-1 text-sm text-nord3 dark:text-nord4">Securely manage API keys and authentication tokens</p>
        </div>
        <x-ui.button href="{{ route('credentials.create') }}" wire:navigate variant="primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Credential
        </x-ui.button>
    </div>

    <!-- Filters -->
    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-ui.input
                wire:model.live.debounce.300ms="search"
                type="text"
                name="search"
                placeholder="Search credentials..."
            />

            <x-ui.select
                wire:model.live="filterType"
                name="filterType"
            >
                <option value="">All Types</option>
                @foreach($types as $type)
                    <option value="{{ $type }}">{{ str_replace('_', ' ', ucfirst($type)) }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select
                wire:model.live="filterProvider"
                name="filterProvider"
            >
                <option value="">All Providers</option>
                @foreach($providers as $provider)
                    <option value="{{ $provider }}">{{ ucfirst($provider) }}</option>
                @endforeach
            </x-ui.select>
        </div>
    </x-ui.card>

    <!-- Credentials Table -->
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-nord4 dark:bg-nord2 border-b border-nord4 dark:border-nord3">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Credential</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Usage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-nord4 dark:divide-nord2">
                    @forelse($credentials as $credential)
                        <tr class="hover:bg-nord4 dark:hover:bg-nord2 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="shrink-0 w-10 h-10 bg-nord15 bg-black/10 dark:bg-black/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-nord15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-nord0 dark:text-nord6">
                                            {{ $credential->name }}
                                        </div>
                                        @if($credential->provider)
                                            <div class="text-sm text-nord3 dark:text-nord4">{{ ucfirst($credential->provider) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge variant="default" size="sm">{{ str_replace('_', ' ', ucfirst($credential->type)) }}</x-ui.badge>
                            </td>
                            <td class="px-6 py-4">
                                @if($credential->agents_count > 0)
                                    <x-ui.badge variant="primary" size="sm">{{ $credential->agents_count }} agent(s)</x-ui.badge>
                                @else
                                    <span class="text-sm text-nord3 dark:text-nord4">Unused</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-nord3 dark:text-nord4">
                                {{ $credential->created_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @if($viewingCredentialId === $credential->id)
                                        <button
                                            wire:click="hideCredential"
                                            class="px-3 py-1.5 text-sm font-medium text-nord0 dark:text-nord4 hover:text-primary transition-colors"
                                        >
                                            Hide
                                        </button>
                                    @else
                                        <button
                                            wire:click="viewCredential({{ $credential->id }})"
                                            class="px-3 py-1.5 text-sm font-medium text-nord0 dark:text-nord4 hover:text-primary transition-colors"
                                        >
                                            View
                                        </button>
                                    @endif

                                    <x-ui.button
                                        href="{{ route('credentials.edit', $credential) }}"
                                        wire:navigate
                                        variant="ghost"
                                        size="sm"
                                    >
                                        Edit
                                    </x-ui.button>

                                    <x-ui.button
                                        wire:click="deleteCredential({{ $credential->id }})"
                                        wire:confirm="Are you sure you want to delete this credential?"
                                        variant="ghost"
                                        size="sm"
                                        class="text-nord11 hover:text-nord11"
                                    >
                                        Delete
                                    </x-ui.button>
                                </div>
                            </td>
                        </tr>
                        @if($viewingCredentialId === $credential->id)
                            <tr class="bg-nord4 dark:bg-nord2">
                                <td colspan="5" class="px-6 py-4">
                                    <div class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-nord13 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-nord0 dark:text-nord6 mb-2">Credential Value</p>
                                            <code class="block p-3 bg-nord5 dark:bg-nord1 rounded-lg text-sm text-nord0 dark:text-nord6 break-all border border-nord13">{{ $credential->value }}</code>
                                            <p class="text-xs text-nord13 mt-2">⚠️ Keep this value secure. Do not share it.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-nord3 dark:text-nord4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                <p class="mt-4 text-sm text-nord3 dark:text-nord4">No credentials found</p>
                                <p class="mt-2">
                                    <x-ui.button href="{{ route('credentials.create') }}" wire:navigate variant="primary" size="sm">
                                        Create your first credential
                                    </x-ui.button>
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($credentials->hasPages())
            <div class="px-6 py-4 border-t border-nord4 dark:border-nord2">
                {{ $credentials->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
