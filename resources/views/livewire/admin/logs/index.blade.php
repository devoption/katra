<div>
    <!-- Header with Stats -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-nord0 dark:text-nord6 mb-4">AI Interaction Logs</h1>
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <x-ui.card :padding="true" class="text-center">
                <div class="text-2xl font-bold text-primary">{{ number_format($stats['total']) }}</div>
                <div class="text-xs text-nord3 dark:text-nord4 mt-1">Total</div>
            </x-ui.card>
            <x-ui.card :padding="true" class="text-center">
                <div class="text-2xl font-bold text-primary">{{ number_format($stats['today']) }}</div>
                <div class="text-xs text-nord3 dark:text-nord4 mt-1">Today</div>
            </x-ui.card>
            <x-ui.card :padding="true" class="text-center">
                <div class="text-2xl font-bold text-nord14">{{ number_format($stats['with_feedback']) }}</div>
                <div class="text-xs text-nord3 dark:text-nord4 mt-1">With Feedback</div>
            </x-ui.card>
            <x-ui.card :padding="true" class="text-center">
                <div class="text-2xl font-bold text-nord15">{{ number_format($stats['training_data']) }}</div>
                <div class="text-xs text-nord3 dark:text-nord4 mt-1">Training Data</div>
            </x-ui.card>
            <x-ui.card :padding="true" class="text-center">
                <div class="text-2xl font-bold text-nord7">${{ number_format($stats['total_cost'], 2) }}</div>
                <div class="text-xs text-nord3 dark:text-nord4 mt-1">Total Cost</div>
            </x-ui.card>
            <x-ui.card :padding="true" class="text-center">
                <div class="text-2xl font-bold text-nord9">{{ number_format($stats['total_tokens'] / 1000000, 2) }}M</div>
                <div class="text-xs text-nord3 dark:text-nord4 mt-1">Tokens</div>
            </x-ui.card>
        </div>
    </div>

    <!-- Filters -->
    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-4">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                    Search
                </label>
                <input
                    type="text"
                    id="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search prompts, responses, UUID..."
                    class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                >
            </div>

            <!-- Type Filter -->
            <div>
                <label for="typeFilter" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                    Type
                </label>
                <select
                    id="typeFilter"
                    wire:model.live="typeFilter"
                    class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                >
                    <option value="">All Types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="statusFilter" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                    Status
                </label>
                <select
                    id="statusFilter"
                    wire:model.live="statusFilter"
                    class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                >
                    <option value="">All Status</option>
                    <option value="success">Success</option>
                    <option value="error">Error</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                </select>
            </div>

            <!-- Provider Filter -->
            <div>
                <label for="providerFilter" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                    Provider
                </label>
                <select
                    id="providerFilter"
                    wire:model.live="providerFilter"
                    class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                >
                    <option value="">All Providers</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider }}">{{ ucfirst($provider) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Date Filter -->
            <div>
                <label for="dateFilter" class="block text-sm font-medium text-nord0 dark:text-nord6 mb-2">
                    Date Range
                </label>
                <select
                    id="dateFilter"
                    wire:model.live="dateFilter"
                    class="w-full px-4 py-2 rounded-lg border border-nord4 dark:border-nord3 bg-white dark:bg-nord1 text-nord0 dark:text-nord6 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                >
                    <option value="all">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">Last 7 Days</option>
                    <option value="month">Last 30 Days</option>
                </select>
            </div>

            <!-- Feedback Filter -->
            <div class="flex items-end">
                <label class="flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model.live="onlyWithFeedback"
                        class="rounded border-nord4 dark:border-nord3 text-primary focus:ring-primary focus:ring-offset-0"
                    >
                    <span class="ml-2 text-sm text-nord0 dark:text-nord6">Only with Feedback</span>
                </label>
            </div>

            <!-- Training Data Filter -->
            <div class="flex items-end">
                <label class="flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model.live="onlyTrainingData"
                        class="rounded border-nord4 dark:border-nord3 text-primary focus:ring-primary focus:ring-offset-0"
                    >
                    <span class="ml-2 text-sm text-nord0 dark:text-nord6">Training Data Only</span>
                </label>
            </div>

            <!-- Clear Filters -->
            <div class="flex items-end">
                <x-ui.button wire:click="clearFilters" variant="outline" class="w-full">
                    Clear Filters
                </x-ui.button>
            </div>
        </div>
    </x-ui.card>

    <!-- Interactions Table -->
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-nord4 dark:bg-nord2 border-b border-nord4 dark:border-nord3">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">
                            <button wire:click="sortBy('created_at')" class="flex items-center gap-1 hover:text-primary">
                                Time
                                @if($sortBy === 'created_at')
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="{{ $sortDirection === 'asc' ? 'M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z' : 'M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z' }}" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Model</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Prompt</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Tokens</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Cost</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-nord0 dark:text-nord4 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-nord4 dark:divide-nord2">
                    @forelse($interactions as $interaction)
                        <tr class="hover:bg-nord4 dark:hover:bg-nord2 transition-colors">
                            <td class="px-4 py-3 text-sm text-nord3 dark:text-nord4 whitespace-nowrap">
                                {{ $interaction->created_at->format('M d, H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <x-ui.badge :variant="match($interaction->type) {
                                    'chat' => 'primary',
                                    'workflow_execution' => 'success',
                                    'agent_step' => 'default',
                                    'tool_execution' => 'warning',
                                    default => 'default'
                                }" size="sm">
                                    {{ ucfirst(str_replace('_', ' ', $interaction->type)) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-sm text-nord0 dark:text-nord6">
                                <div>{{ $interaction->model_provider }}</div>
                                <div class="text-xs text-nord3 dark:text-nord4">{{ $interaction->model_name }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-nord0 dark:text-nord6 max-w-md">
                                <div class="truncate">{{ Str::limit($interaction->prompt, 80) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <x-ui.badge :variant="$interaction->status === 'success' ? 'success' : 'danger'" size="sm">
                                    {{ ucfirst($interaction->status) }}
                                </x-ui.badge>
                                @if($interaction->hasFeedback())
                                    <span class="ml-1" title="Has Feedback">💬</span>
                                @endif
                                @if($interaction->include_in_training)
                                    <span class="ml-1" title="Training Data">🎓</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-nord3 dark:text-nord4">
                                {{ number_format($interaction->total_tokens) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-nord3 dark:text-nord4">
                                {{ $interaction->formatted_cost }}
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <x-ui.button
                                    href="{{ route('admin.logs.show', $interaction) }}"
                                    wire:navigate
                                    variant="outline"
                                    size="sm"
                                >
                                    View
                                </x-ui.button>
                                <button
                                    wire:click="toggleTrainingInclusion({{ $interaction->id }})"
                                    class="text-sm {{ $interaction->include_in_training ? 'text-nord11' : 'text-nord14' }} hover:opacity-75"
                                    title="{{ $interaction->include_in_training ? 'Exclude from training' : 'Include in training' }}"
                                >
                                    {{ $interaction->include_in_training ? '🚫' : '✅' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-nord3 dark:text-nord4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="mt-4 text-sm text-nord3 dark:text-nord4">
                                    No AI interactions logged yet.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($interactions->hasPages())
            <div class="px-6 py-4 border-t border-nord4 dark:border-nord2">
                {{ $interactions->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
